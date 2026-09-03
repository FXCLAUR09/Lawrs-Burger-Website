<?php

session_start();
require_once "../config/db.php";

/*
 * Make sure admin is logged in.
 */
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/*
 * Only allow POST requests.
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: orders.php");
    exit;
}

$order_id = isset($_POST["order_id"])
    ? (int)$_POST["order_id"]
    : 0;

$status = isset($_POST["status"])
    ? trim($_POST["status"])
    : "";

/*
 * Allowed order statuses.
 */
$allowed_statuses = [
    "Pending",
    "Confirmed",
    "Preparing",
    "Ready",
    "Out for Delivery",
    "Completed",
    "Cancelled"
];

if ($order_id <= 0 || !in_array($status, $allowed_statuses, true)) {
    header("Location: orders.php");
    exit;
}

/*
 * Start transaction.
 */
$conn->begin_transaction();

try {

    /*
     * Get current order status.
     */
    $order_stmt = $conn->prepare("
        SELECT id, status
        FROM orders
        WHERE id = ?
        FOR UPDATE
    ");

    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();

    $order_result = $order_stmt->get_result();

    if ($order_result->num_rows !== 1) {
        throw new Exception("Order not found.");
    }

    $order = $order_result->fetch_assoc();

    $old_status = $order["status"];

    $order_stmt->close();


    /*
     * If changing to Cancelled,
     * restore stock only if the order wasn't already cancelled.
     */
    if ($status === "Cancelled" && $old_status !== "Cancelled") {

        /*
         * Get all items from this order.
         */
        $items_stmt = $conn->prepare("
            SELECT burger_id, quantity
            FROM order_items
            WHERE order_id = ?
        ");

        $items_stmt->bind_param("i", $order_id);
        $items_stmt->execute();

        $items_result = $items_stmt->get_result();

        while ($item = $items_result->fetch_assoc()) {

            $burger_id = (int)$item["burger_id"];
            $quantity = (int)$item["quantity"];

            /*
             * Return quantity to stock.
             */
            $stock_stmt = $conn->prepare("
                UPDATE burgers
                SET stock_quantity = stock_quantity + ?
                WHERE id = ?
            ");

            $stock_stmt->bind_param(
                "ii",
                $quantity,
                $burger_id
            );

            if (!$stock_stmt->execute()) {
                throw new Exception(
                    "Failed to restore burger stock."
                );
            }

            $stock_stmt->close();
        }

        $items_stmt->close();
    }


    /*
     * Update order status.
     */
    $update_stmt = $conn->prepare("
        UPDATE orders
        SET
            status = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $update_stmt->bind_param(
        "si",
        $status,
        $order_id
    );

    if (!$update_stmt->execute()) {
        throw new Exception(
            "Failed to update order status."
        );
    }

    $update_stmt->close();


    /*
     * Commit everything.
     */
    $conn->commit();

    header(
        "Location: view_order.php?id=" .
        $order_id .
        "&updated=1"
    );

    exit;

} catch (Exception $e) {

    /*
     * Undo all database changes if something failed.
     */
    $conn->rollback();

    echo "Error: " . htmlspecialchars($e->getMessage());
}

$conn->close();

?>