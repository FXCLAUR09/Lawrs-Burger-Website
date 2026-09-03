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
| REPORT STATISTICS
|--------------------------------------------------------------------------
*/

/*
 * Total Sales
 * Cancelled orders are excluded.
 */
$sales_query = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS total_sales
    FROM orders
    WHERE status != 'Cancelled'
");

$sales_data = $sales_query->fetch_assoc();
$total_sales = (float)$sales_data["total_sales"];


/*
 * Total Orders
 */
$orders_query = $conn->query("
    SELECT COUNT(*) AS total_orders
    FROM orders
");

$orders_data = $orders_query->fetch_assoc();
$total_orders = (int)$orders_data["total_orders"];


/*
 * Completed Orders
 */
$completed_query = $conn->query("
    SELECT COUNT(*) AS completed_orders
    FROM orders
    WHERE status = 'Completed'
");

$completed_data = $completed_query->fetch_assoc();
$completed_orders = (int)$completed_data["completed_orders"];


/*
 * Cancelled Orders
 */
$cancelled_query = $conn->query("
    SELECT COUNT(*) AS cancelled_orders
    FROM orders
    WHERE status = 'Cancelled'
");

$cancelled_data = $cancelled_query->fetch_assoc();
$cancelled_orders = (int)$cancelled_data["cancelled_orders"];


/*
 * Active Orders
 */
$active_query = $conn->query("
    SELECT COUNT(*) AS active_orders
    FROM orders
    WHERE status NOT IN ('Completed', 'Cancelled')
");

$active_data = $active_query->fetch_assoc();
$active_orders = (int)$active_data["active_orders"];


/*
|--------------------------------------------------------------------------
| BEST-SELLING BURGERS
|--------------------------------------------------------------------------
*/

$best_sellers_query = $conn->query("
    SELECT
        burger_name,
        SUM(quantity) AS total_quantity,
        SUM(subtotal) AS total_sales
    FROM order_items oi
    INNER JOIN orders o
        ON oi.order_id = o.id
    WHERE o.status != 'Cancelled'
    GROUP BY burger_id, burger_name
    ORDER BY total_quantity DESC
    LIMIT 5
");


/*
|--------------------------------------------------------------------------
| SALES BY DATE
|--------------------------------------------------------------------------
*/

$daily_sales_query = $conn->query("
    SELECT
        DATE(created_at) AS sale_date,
        COUNT(*) AS order_count,
        SUM(total) AS daily_sales
    FROM orders
    WHERE status != 'Cancelled'
    GROUP BY DATE(created_at)
    ORDER BY sale_date DESC
    LIMIT 7
");


/*
|--------------------------------------------------------------------------
| SALES BY MONTH
|--------------------------------------------------------------------------
*/

$monthly_sales_query = $conn->query("
    SELECT
        DATE_FORMAT(created_at, '%Y-%m') AS sale_month,
        COUNT(*) AS order_count,
        SUM(total) AS monthly_sales
    FROM orders
    WHERE status != 'Cancelled'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY sale_month DESC
    LIMIT 6
");


/*
|--------------------------------------------------------------------------
| STOCK REPORT
|--------------------------------------------------------------------------
*/

$stock_query = $conn->query("
    SELECT
        id,
        name,
        category,
        stock_quantity,
        available
    FROM burgers
    ORDER BY stock_quantity ASC, name ASC
");


/*
|--------------------------------------------------------------------------
| LOW STOCK COUNT
|--------------------------------------------------------------------------
*/

$low_stock_query = $conn->query("
    SELECT COUNT(*) AS low_stock
    FROM burgers
    WHERE stock_quantity <= 10
");

$low_stock_data = $low_stock_query->fetch_assoc();
$low_stock = (int)$low_stock_data["low_stock"];

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
        Reports | Lawr's Burgers
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
                class="nav-item"
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
                class="nav-item active"
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
         MAIN
    ====================================================== -->

    <main class="admin-main">


        <!-- =================================================
             TOPBAR
        ================================================== -->

        <header class="admin-topbar">

            <div>

                <span class="page-label">
                    BUSINESS REPORTS
                </span>

                <h1>
                    Reports
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
                        Business Reports
                    </h2>

                    <p>
                        Monitor sales, orders, burger performance, and inventory.
                    </p>

                </div>

            </div>



            <!-- =================================================
                 SUMMARY CARDS
            ================================================== -->

            <div class="stats-grid">


                <!-- TOTAL SALES -->

                <div class="stat-card">

                    <div class="stat-icon">
                        💰
                    </div>

                    <div class="stat-info">

                        <span>
                            Total Sales
                        </span>

                        <strong>
                            ₱<?php echo number_format($total_sales, 2); ?>
                        </strong>

                    </div>

                </div>



                <!-- TOTAL ORDERS -->

                <div class="stat-card">

                    <div class="stat-icon">
                        📦
                    </div>

                    <div class="stat-info">

                        <span>
                            Total Orders
                        </span>

                        <strong>
                            <?php echo number_format($total_orders); ?>
                        </strong>

                    </div>

                </div>



                <!-- COMPLETED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        ✅
                    </div>

                    <div class="stat-info">

                        <span>
                            Completed
                        </span>

                        <strong>
                            <?php echo number_format($completed_orders); ?>
                        </strong>

                    </div>

                </div>



                <!-- ACTIVE -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🔄
                    </div>

                    <div class="stat-info">

                        <span>
                            Active Orders
                        </span>

                        <strong>
                            <?php echo number_format($active_orders); ?>
                        </strong>

                    </div>

                </div>



                <!-- CANCELLED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        ❌
                    </div>

                    <div class="stat-info">

                        <span>
                            Cancelled
                        </span>

                        <strong>
                            <?php echo number_format($cancelled_orders); ?>
                        </strong>

                    </div>

                </div>



                <!-- LOW STOCK -->

                <div class="stat-card">

                    <div class="stat-icon">
                        ⚠️
                    </div>

                    <div class="stat-info">

                        <span>
                            Low Stock
                        </span>

                        <strong>
                            <?php echo number_format($low_stock); ?>
                        </strong>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 BEST SELLING BURGERS
            ================================================== -->

            <div class="admin-card report-card">

                <div class="card-header">

                    <div>

                        <h3>
                            🍔 Best-Selling Burgers
                        </h3>

                        <p>
                            Top burgers based on quantity sold.
                        </p>

                    </div>

                </div>


                <div class="table-wrapper">

                    <table class="admin-table report-table">

                        <thead>

                            <tr>

                                <th>
                                    Rank
                                </th>

                                <th>
                                    Burger
                                </th>

                                <th>
                                    Quantity Sold
                                </th>

                                <th>
                                    Sales
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php

                        $rank = 1;

                        if ($best_sellers_query->num_rows > 0):

                        ?>

                            <?php while ($burger = $best_sellers_query->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <span class="report-rank">
                                            #<?php echo $rank; ?>
                                        </span>

                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $burger["burger_name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <span class="quantity-badge">

                                            <?php
                                            echo number_format(
                                                (int)$burger["total_quantity"]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <strong class="report-price">

                                            ₱<?php

                                            echo number_format(
                                                (float)$burger["total_sales"],
                                                2
                                            );

                                            ?>

                                        </strong>

                                    </td>

                                </tr>

                                <?php $rank++; ?>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="empty-table"
                                >

                                    <div class="empty-icon">
                                        🍔
                                    </div>

                                    <h3>
                                        No Sales Yet
                                    </h3>

                                    <p>
                                        Burger sales will appear here after customers place orders.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>



            <!-- =================================================
                 DAILY + MONTHLY SALES
            ================================================== -->

            <div class="report-two-column">


                <!-- DAILY SALES -->

                <div class="admin-card report-card">

                    <div class="card-header">

                        <div>

                            <h3>
                                📅 Recent Daily Sales
                            </h3>

                            <p>
                                Sales from the latest 7 days with orders.
                            </p>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="admin-table report-table">

                            <thead>

                                <tr>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Orders
                                    </th>

                                    <th>
                                        Sales
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php if ($daily_sales_query->num_rows > 0): ?>

                                <?php while ($day = $daily_sales_query->fetch_assoc()): ?>

                                    <tr>

                                        <td>

                                            <?php

                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $day["sale_date"]
                                                )
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php
                                            echo number_format(
                                                (int)$day["order_count"]
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <strong class="report-price">

                                                ₱<?php

                                                echo number_format(
                                                    (float)$day["daily_sales"],
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
                                        colspan="3"
                                        class="empty-table"
                                    >
                                        No sales data yet.
                                    </td>

                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>



                <!-- MONTHLY SALES -->

                <div class="admin-card report-card">

                    <div class="card-header">

                        <div>

                            <h3>
                                📈 Monthly Sales
                            </h3>

                            <p>
                                Sales summary for recent months.
                            </p>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="admin-table report-table">

                            <thead>

                                <tr>

                                    <th>
                                        Month
                                    </th>

                                    <th>
                                        Orders
                                    </th>

                                    <th>
                                        Sales
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php if ($monthly_sales_query->num_rows > 0): ?>

                                <?php while ($month = $monthly_sales_query->fetch_assoc()): ?>

                                    <tr>

                                        <td>

                                            <?php

                                            echo date(
                                                "F Y",
                                                strtotime(
                                                    $month["sale_month"] . "-01"
                                                )
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php
                                            echo number_format(
                                                (int)$month["order_count"]
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <strong class="report-price">

                                                ₱<?php

                                                echo number_format(
                                                    (float)$month["monthly_sales"],
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
                                        colspan="3"
                                        class="empty-table"
                                    >
                                        No monthly data yet.
                                    </td>

                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 STOCK REPORT
            ================================================== -->

            <div class="admin-card report-card">

                <div class="card-header">

                    <div>

                        <h3>
                            📦 Burger Stock Report
                        </h3>

                        <p>
                            Current inventory levels for all burgers.
                        </p>

                    </div>

                </div>


                <div class="table-wrapper">

                    <table class="admin-table report-table">

                        <thead>

                            <tr>

                                <th>
                                    Burger
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Stock
                                </th>

                                <th>
                                    Availability
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($stock_query->num_rows > 0): ?>

                            <?php while ($stock = $stock_query->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $stock["name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <span class="category-badge">

                                            <?php
                                            echo htmlspecialchars(
                                                $stock["category"]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?php

                                        $stock_quantity =
                                            (int)$stock["stock_quantity"];

                                        if ($stock_quantity <= 10):

                                        ?>

                                            <span class="stock-badge low-stock">
                                                <?php echo $stock_quantity; ?> left
                                            </span>

                                        <?php elseif ($stock_quantity <= 25): ?>

                                            <span class="stock-badge medium-stock">
                                                <?php echo $stock_quantity; ?> left
                                            </span>

                                        <?php else: ?>

                                            <span class="stock-badge good-stock">
                                                <?php echo $stock_quantity; ?> left
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php if ((int)$stock["available"] === 1): ?>

                                            <span class="status-badge status-available">
                                                Available
                                            </span>

                                        <?php else: ?>

                                            <span class="status-badge status-unavailable">
                                                Unavailable
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="4"
                                    class="empty-table"
                                >
                                    No burgers found.
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
```
