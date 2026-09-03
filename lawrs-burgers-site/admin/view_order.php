```php
<?php

session_start();
require_once "../config/db.php";

/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Order ID
|--------------------------------------------------------------------------
*/

$order_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($order_id <= 0) {
    header("Location: orders.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Order Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        phone,
        address,
        notes,
        payment_method,
        total,
        status,
        created_at,
        updated_at
    FROM orders
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: orders.php");
    exit;
}

$order = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get Order Items
|--------------------------------------------------------------------------
*/

$item_stmt = $conn->prepare("
    SELECT
        id,
        burger_id,
        burger_name,
        price,
        quantity,
        subtotal
    FROM order_items
    WHERE order_id = ?
    ORDER BY id ASC
");

$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();

$items_result = $item_stmt->get_result();


/*
|--------------------------------------------------------------------------
| Status Class
|--------------------------------------------------------------------------
*/

$status = strtolower(trim($order["status"]));

switch ($status) {

    case "confirmed":
        $status_class = "order-confirmed";
        break;

    case "preparing":
        $status_class = "order-preparing";
        break;

    case "ready":
        $status_class = "order-ready";
        break;

    case "out for delivery":
        $status_class = "order-delivery";
        break;

    case "completed":
        $status_class = "order-completed";
        break;

    case "cancelled":
    case "canceled":
        $status_class = "order-cancelled";
        break;

    case "pending":
    default:
        $status_class = "order-pending";
        break;
}


/*
|--------------------------------------------------------------------------
| Update Message
|--------------------------------------------------------------------------
*/

$updated = isset($_GET["updated"]) && $_GET["updated"] === "1";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        View Order #<?php echo $order["id"]; ?> | Lawr's Burgers
    </title>

    <link
        rel="stylesheet"
        href="../css/admin.css"
    >

</head>

<body>

<div class="admin-layout">


    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

    <aside class="admin-sidebar">

        <div class="sidebar-brand">

            <div class="brand-main">
                LAWR'S
            </div>

            <div class="brand-sub">
                BURGERS
            </div>

            <div class="sidebar-label">
                ADMIN PANEL
            </div>

        </div>


        <nav class="sidebar-nav">

            <a
                href="dashboard.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    📊
                </span>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="burgers.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    🍔
                </span>

                <span>
                    Burgers
                </span>

            </a>


            <a
                href="orders.php"
                class="nav-item active"
            >

                <span class="nav-icon">
                    📦
                </span>

                <span>
                    Orders
                </span>

            </a>


            <a
                href="reports.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    📈
                </span>

                <span>
                    Reports
                </span>

            </a>


            <div class="sidebar-divider"></div>


            <a
                href="../index.php"
                class="nav-item"
            >

                <span class="nav-icon">
                    🌐
                </span>

                <span>
                    View Website
                </span>

            </a>


            <a
                href="logout.php"
                class="nav-item logout-link"
            >

                <span class="nav-icon">
                    🚪
                </span>

                <span>
                    Logout
                </span>

            </a>

        </nav>


        <div class="sidebar-footer">

            <p>
                Lawr's Burgers
            </p>

            <span>
                Admin Management System
            </span>

        </div>

    </aside>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header class="admin-topbar">

            <div>

                <span class="page-label">
                    ORDER MANAGEMENT
                </span>

                <h1>
                    Order #<?php echo $order["id"]; ?>
                </h1>

            </div>


            <div class="admin-user">

                <div class="user-avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $_SESSION["admin_username"],
                            0,
                            1
                        )
                    );

                    ?>

                </div>


                <div class="user-info">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["admin_username"]
                        );

                        ?>

                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </header>



        <!-- =================================================
             CONTENT
        ================================================== -->

        <section class="admin-content">


            <!-- PAGE HEADER -->

            <div class="content-header">

                <div>

                    <h2>
                        Order #<?php echo $order["id"]; ?>
                    </h2>

                    <p>
                        View customer information, ordered items, and manage the order.
                    </p>

                </div>


                <div class="order-header-actions">

                    <a
                        href="orders.php"
                        class="admin-button cancel-button"
                    >
                        ← Back to Orders
                    </a>

                </div>

            </div>



            <!-- =================================================
                 SUCCESS MESSAGE
            ================================================== -->

            <?php if ($updated): ?>

                <div class="admin-alert admin-alert-success">

                    <span class="alert-icon">
                        ✓
                    </span>

                    <span>
                        Order status updated successfully.
                    </span>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 ORDER OVERVIEW
            ================================================== -->

            <div class="order-detail-grid">


                <!-- CUSTOMER INFORMATION -->

                <div class="admin-card order-detail-card">

                    <div class="detail-card-header">

                        <div class="detail-icon">
                            👤
                        </div>

                        <div>

                            <h3>
                                Customer Information
                            </h3>

                            <p>
                                Customer contact details
                            </p>

                        </div>

                    </div>


                    <div class="detail-content">

                        <div class="detail-row">

                            <span>
                                Name
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $order["customer_name"]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                Phone
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $order["phone"]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="detail-row detail-row-column">

                            <span>
                                Delivery Address
                            </span>

                            <strong>
                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $order["address"]
                                    )
                                );
                                ?>
                            </strong>

                        </div>


                        <?php if (!empty($order["notes"])): ?>

                            <div class="detail-row detail-row-column">

                                <span>
                                    Order Notes
                                </span>

                                <strong>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $order["notes"]
                                        )
                                    );
                                    ?>
                                </strong>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>



                <!-- ORDER INFORMATION -->

                <div class="admin-card order-detail-card">

                    <div class="detail-card-header">

                        <div class="detail-icon">
                            📋
                        </div>

                        <div>

                            <h3>
                                Order Information
                            </h3>

                            <p>
                                Payment and order status
                            </p>

                        </div>

                    </div>


                    <div class="detail-content">

                        <div class="detail-row">

                            <span>
                                Order Number
                            </span>

                            <strong>
                                #<?php echo $order["id"]; ?>
                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                Payment Method
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $order["payment_method"]
                                );
                                ?>
                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                Order Date
                            </span>

                            <strong>

                                <?php

                                echo date(
                                    "M d, Y h:i A",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                );

                                ?>

                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                Current Status
                            </span>

                            <span
                                class="order-status-badge <?php echo $status_class; ?>"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $order["status"]
                                );
                                ?>
                            </span>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 ORDER ITEMS
            ================================================== -->

            <div class="admin-card order-items-card">

                <div class="card-header">

                    <div>

                        <h3>
                            Ordered Items
                        </h3>

                        <p>
                            Burgers included in this order.
                        </p>

                    </div>

                </div>


                <div class="table-wrapper">

                    <table class="admin-table order-items-table">

                        <thead>

                            <tr>

                                <th>
                                    Burger
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Quantity
                                </th>

                                <th>
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($items_result->num_rows > 0): ?>

                            <?php while ($item = $items_result->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <div class="order-item-name">

                                            <div class="order-item-icon">
                                                🍔
                                            </div>

                                            <div>

                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item["burger_name"]
                                                    );
                                                    ?>
                                                </strong>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        ₱<?php

                                        echo number_format(
                                            (float)$item["price"],
                                            2
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <span class="quantity-badge">

                                            <?php
                                            echo (int)$item["quantity"];
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <strong class="item-subtotal">

                                            ₱<?php

                                            echo number_format(
                                                (float)$item["subtotal"],
                                                2
                                            );

                                            ?>

                                        </strong>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="empty-table"
                                >

                                    <div class="empty-icon">
                                        📦
                                    </div>

                                    <h3>
                                        No Order Items
                                    </h3>

                                    <p>
                                        No items were found for this order.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>



                <!-- ORDER TOTAL -->

                <div class="order-total-section">

                    <span>
                        Order Total
                    </span>

                    <strong>

                        ₱<?php

                        echo number_format(
                            (float)$order["total"],
                            2
                        );

                        ?>

                    </strong>

                </div>

            </div>



            <!-- =================================================
                 UPDATE STATUS
            ================================================== -->

            <div class="admin-card update-status-card">

                <div class="detail-card-header">

                    <div class="detail-icon">
                        🔄
                    </div>

                    <div>

                        <h3>
                            Manage Order
                        </h3>

                        <p>
                            Update, cancel, or delete this order.
                        </p>

                    </div>

                </div>


                <form
                    action="update_order.php"
                    method="POST"
                    class="status-form"
                    id="orderStatusForm"
                >

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?php echo $order["id"]; ?>"
                    >


                    <div class="status-form-group">

                        <label for="status">
                            Order Status
                        </label>


                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <option
                                value="Pending"
                                <?php
                                echo ($order["status"] === "Pending")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Pending
                            </option>


                            <option
                                value="Confirmed"
                                <?php
                                echo ($order["status"] === "Confirmed")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Confirmed
                            </option>


                            <option
                                value="Preparing"
                                <?php
                                echo ($order["status"] === "Preparing")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Preparing
                            </option>


                            <option
                                value="Ready"
                                <?php
                                echo ($order["status"] === "Ready")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Ready
                            </option>


                            <option
                                value="Out for Delivery"
                                <?php
                                echo ($order["status"] === "Out for Delivery")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Out for Delivery
                            </option>


                            <option
                                value="Completed"
                                <?php
                                echo ($order["status"] === "Completed")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Completed
                            </option>


                            <option
                                value="Cancelled"
                                <?php
                                echo ($order["status"] === "Cancelled")
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                Cancelled
                            </option>

                        </select>

                    </div>


                    <div class="order-management-actions">

                        <button
                            type="submit"
                            class="admin-button save-button"
                        >
                            Update Status
                        </button>


                        <?php if ($order["status"] !== "Cancelled"): ?>

                            <button
                                type="button"
                                class="cancel-order-button"
                                onclick="cancelOrder()"
                            >
                                Cancel Order
                            </button>

                        <?php endif; ?>


                        <?php if ($order["status"] === "Cancelled"): ?>

                            <a
                                href="delete_order.php?id=<?php echo $order["id"]; ?>"
                                class="delete-order-button"
                                onclick="return confirmDeleteOrder();"
                            >
                                Delete Order
                            </a>

                        <?php endif; ?>

                    </div>

                </form>

            </div>


        </section>



        <!-- =================================================
             FOOTER
        ================================================== -->

        <footer class="admin-footer">

            <p>

                © <?php echo date("Y"); ?>

                Lawr's Burgers. Admin Panel.

            </p>

        </footer>


    </main>

</div>



<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script>

function cancelOrder() {

    const confirmed = confirm(
        "Are you sure you want to cancel Order #" +
        "<?php echo $order["id"]; ?>" +
        "?\n\n" +
        "The burger quantities from this order will be returned to stock."
    );

    if (!confirmed) {
        return;
    }

    /*
     * Set status to Cancelled.
     */
    const statusSelect = document.getElementById("status");

    statusSelect.value = "Cancelled";

    /*
     * Submit the existing form.
     */
    document.getElementById("orderStatusForm").submit();
}


function confirmDeleteOrder() {

    return confirm(
        "Are you sure you want to permanently delete Order #" +
        "<?php echo $order["id"]; ?>" +
        "?\n\n" +
        "This action cannot be undone."
    );

}

</script>


</body>

</html>

<?php

$item_stmt->close();
$conn->close();

?>
```
