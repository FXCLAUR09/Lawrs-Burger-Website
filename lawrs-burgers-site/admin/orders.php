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
| Get All Orders
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        customer_name,
        phone,
        address,
        payment_method,
        total,
        status,
        created_at
    FROM orders
    ORDER BY created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Failed to load orders: " . $conn->error);
}

/*
|--------------------------------------------------------------------------
| Count Orders
|--------------------------------------------------------------------------
*/

$total_orders = $result->num_rows;

/*
|--------------------------------------------------------------------------
| Success Messages
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "";

if (isset($_GET["updated"]) && $_GET["updated"] === "1") {

    $message = "Order status updated successfully.";
    $message_type = "success";

} elseif (isset($_GET["deleted"]) && $_GET["deleted"] === "1") {

    $message = "Order deleted successfully.";
    $message_type = "success";

}

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
        Order Management | Lawr's Burgers
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

            <!-- Dashboard -->

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


            <!-- Burgers -->

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


            <!-- Orders -->

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


            <!-- Reports -->

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


            <!-- Website -->

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


            <!-- Logout -->

            <a
                href="../logout.php"
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
         MAIN CONTENT
    ====================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header class="admin-topbar">

            <div>

                <span class="page-label">
                    ADMIN PANEL
                </span>

                <h1>
                    Order Management
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
             PAGE CONTENT
        ================================================== -->

        <section class="admin-content">


            <!-- PAGE HEADER -->

            <div class="content-header">

                <div>

                    <h2>
                        Orders
                    </h2>

                    <p>
                        View and manage customer orders placed through the website.
                    </p>

                </div>

            </div>



            <!-- =================================================
                 SUCCESS MESSAGE
            ================================================== -->

            <?php if ($message !== ""): ?>

                <div class="admin-alert admin-alert-<?php echo $message_type; ?>">

                    <span class="alert-icon">
                        ✓
                    </span>

                    <span>
                        <?php echo htmlspecialchars($message); ?>
                    </span>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 ORDER CARD
            ================================================== -->

            <div class="admin-card order-management-card">


                <!-- CARD HEADER -->

                <div class="card-header">

                    <div>

                        <h3>
                            Customer Orders
                        </h3>

                        <p>
                            All orders received from your website.
                        </p>

                    </div>


                    <div class="order-count">

                        <?php echo $total_orders; ?>

                        Order<?php echo ($total_orders != 1) ? "s" : ""; ?>

                    </div>

                </div>



                <!-- =================================================
                     ORDER TABLE
                ================================================== -->

                <div class="table-wrapper">

                    <table class="admin-table order-table">


                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Payment
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if ($total_orders > 0): ?>


                            <?php while ($order = $result->fetch_assoc()): ?>


                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Status Class
                                |--------------------------------------------------------------------------
                                */

                                $status = strtolower(
                                    trim($order["status"])
                                );

                                switch ($status) {

                                    case "completed":
                                        $status_class = "order-completed";
                                        break;

                                    case "preparing":
                                        $status_class = "order-preparing";
                                        break;

                                    case "out for delivery":
                                        $status_class = "order-delivery";
                                        break;

                                    case "cancelled":
                                    case "canceled":
                                        $status_class = "order-cancelled";
                                        break;

                                    default:
                                        $status_class = "order-pending";
                                        break;
                                }

                                ?>


                                <tr>


                                    <!-- ORDER ID -->

                                    <td>

                                        <span class="order-id">

                                            #

                                            <?php
                                            echo $order["id"];
                                            ?>

                                        </span>

                                    </td>



                                    <!-- CUSTOMER -->

                                    <td>

                                        <div class="order-customer">

                                            <div class="order-customer-avatar">

                                                <?php

                                                echo strtoupper(
                                                    substr(
                                                        $order["customer_name"],
                                                        0,
                                                        1
                                                    )
                                                );

                                                ?>

                                            </div>


                                            <div class="order-customer-info">

                                                <strong>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $order["customer_name"]
                                                    );

                                                    ?>

                                                </strong>

                                                <span>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $order["phone"]
                                                    );

                                                    ?>

                                                </span>

                                            </div>

                                        </div>

                                    </td>



                                    <!-- PAYMENT -->

                                    <td>

                                        <?php

                                        $payment =
                                            $order["payment_method"];

                                        ?>

                                        <?php if ($payment === "GCash"): ?>

                                            <span class="payment-badge gcash-payment">
                                                GCash
                                            </span>

                                        <?php else: ?>

                                            <span class="payment-badge cod-payment">
                                                Cash on Delivery
                                            </span>

                                        <?php endif; ?>

                                    </td>



                                    <!-- TOTAL -->

                                    <td>

                                        <strong class="order-price">

                                            ₱<?php

                                            echo number_format(
                                                (float)$order["total"],
                                                2
                                            );

                                            ?>

                                        </strong>

                                    </td>



                                    <!-- STATUS -->

                                    <td>

                                        <span
                                            class="order-status-badge <?php echo $status_class; ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $order["status"]
                                            );

                                            ?>

                                        </span>

                                    </td>



                                    <!-- DATE -->

                                    <td>

                                        <div class="order-date">

                                            <strong>

                                                <?php

                                                echo date(
                                                    "M d, Y",
                                                    strtotime(
                                                        $order["created_at"]
                                                    )
                                                );

                                                ?>

                                            </strong>

                                            <span>

                                                <?php

                                                echo date(
                                                    "h:i A",
                                                    strtotime(
                                                        $order["created_at"]
                                                    )
                                                );

                                                ?>

                                            </span>

                                        </div>

                                    </td>



                                    <!-- ACTION -->

                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="view_order.php?id=<?php echo $order["id"]; ?>"
                                                class="action-button view-button"
                                            >

                                                View

                                            </a>

                                        </div>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <!-- EMPTY STATE -->

                            <tr>

                                <td
                                    colspan="7"
                                    class="empty-table"
                                >

                                    <div class="empty-icon">
                                        📦
                                    </div>

                                    <h3>
                                        No Orders Found
                                    </h3>

                                    <p>
                                        Customer orders will appear here once someone places an order.
                                    </p>

                                    <a
                                        href="../index.php#menu"
                                        class="admin-button"
                                    >

                                        View Website

                                    </a>

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>

                    </table>

                </div>

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


</body>

</html>

<?php

$conn->close();

?>