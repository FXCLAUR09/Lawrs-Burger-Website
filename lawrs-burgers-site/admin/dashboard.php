<?php

session_start();
require_once "../config/db.php";

/*
 * Protect admin dashboard.
 * Only logged-in administrators can access this page.
 */
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/*
 * Admin username.
 */
$admin_username = $_SESSION["admin_username"] ?? "Admin";

/*
 * ==========================================
 * DASHBOARD STATISTICS
 * ==========================================
 */

/* Total burgers */
$total_burgers = 0;

$sql = "SELECT COUNT(*) AS total FROM burgers";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_burgers = (int)$row["total"];
}


/* Available burgers */
$available_burgers = 0;

$sql = "SELECT COUNT(*) AS total FROM burgers WHERE available = 1";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $available_burgers = (int)$row["total"];
}


/* Total orders */
$total_orders = 0;

$sql = "SELECT COUNT(*) AS total FROM orders";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_orders = (int)$row["total"];
}


/* Pending orders */
$pending_orders = 0;

$sql = "SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $pending_orders = (int)$row["total"];
}


/* Total sales */
$total_sales = 0;

$sql = "SELECT COALESCE(SUM(total), 0) AS total_sales FROM orders";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $total_sales = (float)$row["total_sales"];
}


/* Today's sales */
$today_sales = 0;

$sql = "
    SELECT COALESCE(SUM(total), 0) AS today_sales
    FROM orders
    WHERE DATE(created_at) = CURDATE()
";

$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $today_sales = (float)$row["today_sales"];
}


/*
 * ==========================================
 * RECENT ORDERS
 * ==========================================
 */

$recent_orders = [];

$sql = "
    SELECT
        id,
        customer_name,
        phone,
        total,
        status,
        created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 5
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = $row;
    }

}


/*
 * ==========================================
 * LOW STOCK BURGERS
 * ==========================================
 */

$low_stock_burgers = [];

$sql = "
    SELECT
        id,
        name,
        stock_quantity,
        available
    FROM burgers
    ORDER BY stock_quantity ASC
    LIMIT 5
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $low_stock_burgers[] = $row;
    }

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

    <title>Dashboard | Lawr's Burgers Admin</title>

    <link rel="stylesheet" href="../css/admin.css">

</head>

<body>

<div class="admin-layout">

    <!-- ======================================
         SIDEBAR
    ======================================= -->

    <aside class="admin-sidebar">

        <div class="sidebar-brand">

            <div class="brand-main">
                LAWR'S
            </div>

            <div class="brand-sub">
                BURGERS
            </div>

            <div class="sidebar-admin-label">
                ADMIN PANEL
            </div>

        </div>


        <nav class="admin-nav">

            <a href="dashboard.php" class="nav-item active">

                <span class="nav-icon">▣</span>

                <span>
                    Dashboard
                </span>

            </a>


             <a href="burgers.php" class="nav-item">

                <span class="nav-icon">🍔</span>

                <span>
                    Burgers
                </span>

            </a>


            <a href="orders.php" class="nav-item">

                <span class="nav-icon">🛒</span>

                <span>
                    Orders
                </span>

            </a>


            <a href="reports.php" class="nav-item">

                <span class="nav-icon">📊</span>

                <span>
                    Reports
                </span>

            </a>


            <div class="nav-divider"></div>


            <a
                href="../index.php"
                class="nav-item"
                target="_blank"
            >

                <span class="nav-icon">↗</span>

                <span>
                    View Website
                </span>

            </a>


            <a href="../logout.php" class="nav-item logout-link">

                <span class="nav-icon">⇥</span>

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


    <!-- ======================================
         MAIN CONTENT
    ======================================= -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <div class="breadcrumb">
                    ADMIN / DASHBOARD
                </div>

                <h1>
                    Dashboard
                </h1>

            </div>


            <div class="admin-user">

                <div class="user-avatar">
                    <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                </div>

                <div class="user-info">

                    <strong>
                        <?php echo htmlspecialchars($admin_username); ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </header>


        <!-- ======================================
             WELCOME BANNER
        ======================================= -->

        <section class="welcome-banner">

            <div class="welcome-content">

                <p class="welcome-small">
                    WELCOME BACK
                </p>

                <h2>
                    Good day, <?php echo htmlspecialchars($admin_username); ?>!
                </h2>

                <p>
                    Here's what's happening with Lawr's Burgers today.
                </p>

            </div>


            <div class="welcome-burger">

                🍔

            </div>

        </section>


        <!-- ======================================
             STATISTICS
        ======================================= -->

        <section class="stats-grid">


            <!-- BURGERS -->

            <div class="stat-card">

                <div class="stat-icon orange">
                    🍔
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        TOTAL BURGERS
                    </span>

                    <strong class="stat-number">
                        <?php echo $total_burgers; ?>
                    </strong>

                    <span class="stat-description">
                        <?php echo $available_burgers; ?> currently available
                    </span>

                </div>

            </div>


            <!-- ORDERS -->

            <div class="stat-card">

                <div class="stat-icon burgundy">
                    🛒
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        TOTAL ORDERS
                    </span>

                    <strong class="stat-number">
                        <?php echo $total_orders; ?>
                    </strong>

                    <span class="stat-description">
                        <?php echo $pending_orders; ?> pending
                    </span>

                </div>

            </div>


            <!-- SALES -->

            <div class="stat-card">

                <div class="stat-icon green">
                    ₱
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        TOTAL SALES
                    </span>

                    <strong class="stat-number">
                        ₱<?php echo number_format($total_sales, 2); ?>
                    </strong>

                    <span class="stat-description">
                        All recorded orders
                    </span>

                </div>

            </div>


            <!-- TODAY -->

            <div class="stat-card">

                <div class="stat-icon dark">
                    ★
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        TODAY'S SALES
                    </span>

                    <strong class="stat-number">
                        ₱<?php echo number_format($today_sales, 2); ?>
                    </strong>

                    <span class="stat-description">
                        Sales today
                    </span>

                </div>

            </div>

        </section>


        <!-- ======================================
             CONTENT GRID
        ======================================= -->

        <section class="dashboard-grid">


            <!-- RECENT ORDERS -->

            <div class="dashboard-card orders-card">

                <div class="card-header">

                    <div>

                        <span class="card-label">
                            ORDER MANAGEMENT
                        </span>

                        <h2>
                            Recent Orders
                        </h2>

                    </div>

                    <a href="#" class="card-link">
                        View All →
                    </a>

                </div>


                <?php if (count($recent_orders) > 0): ?>

                    <div class="table-wrapper">

                        <table class="admin-table">

                            <thead>

                                <tr>

                                    <th>
                                        ORDER
                                    </th>

                                    <th>
                                        CUSTOMER
                                    </th>

                                    <th>
                                        TOTAL
                                    </th>

                                    <th>
                                        STATUS
                                    </th>

                                    <th>
                                        DATE
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php foreach ($recent_orders as $order): ?>

                                <tr>

                                    <td>

                                        <strong class="order-id">
                                            #<?php echo (int)$order["id"]; ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <div class="customer-cell">

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

                                    </td>


                                    <td>

                                        <strong>
                                            ₱<?php
                                            echo number_format(
                                                (float)$order["total"],
                                                2
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        $status_class = strtolower(
                                            str_replace(
                                                " ",
                                                "-",
                                                $order["status"]
                                            )
                                        );

                                        ?>

                                        <span
                                            class="status-badge <?php
                                            echo htmlspecialchars(
                                                $status_class
                                            );
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $order["status"]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="date-cell">

                                            <?php
                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $order["created_at"]
                                                )
                                            );
                                            ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <div class="empty-icon">
                            🛒
                        </div>

                        <h3>
                            No orders yet
                        </h3>

                        <p>
                            Customer orders will appear here.
                        </p>

                    </div>

                <?php endif; ?>

            </div>


            <!-- STOCK -->

            <div class="dashboard-card stock-card">

                <div class="card-header">

                    <div>

                        <span class="card-label">
                            INVENTORY
                        </span>

                        <h2>
                            Burger Stock
                        </h2>

                    </div>

                    <span class="stock-count">
                        <?php echo $total_burgers; ?> items
                    </span>

                </div>


                <?php if (count($low_stock_burgers) > 0): ?>

                    <div class="stock-list">

                    <?php foreach ($low_stock_burgers as $burger): ?>

                        <div class="stock-item">

                            <div class="stock-info">

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $burger["name"]
                                    );
                                    ?>
                                </strong>

                                <span>

                                    <?php
                                    if ((int)$burger["available"] === 1) {
                                        echo "Available";
                                    } else {
                                        echo "Unavailable";
                                    }
                                    ?>

                                </span>

                            </div>


                            <div class="stock-number
                                <?php
                                if ((int)$burger["stock_quantity"] <= 10) {
                                    echo "low";
                                } elseif ((int)$burger["stock_quantity"] <= 20) {
                                    echo "medium";
                                } else {
                                    echo "good";
                                }
                                ?>
                            ">

                                <?php
                                echo (int)$burger["stock_quantity"];
                                ?>

                                <small>
                                    left
                                </small>

                            </div>

                        </div>

                    <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="empty-state">

                        <div class="empty-icon">
                            🍔
                        </div>

                        <h3>
                            No burgers found
                        </h3>

                    </div>

                <?php endif; ?>

            </div>


        </section>


        <!-- ======================================
             QUICK ACTIONS
        ======================================= -->

        <section class="quick-actions">

            <div class="section-heading">

                <span class="card-label">
                    MANAGEMENT
                </span>

                <h2>
                    Quick Actions
                </h2>

            </div>


            <div class="action-grid">


                <a href="burgers.php" class="action-card">

                    <div class="action-icon">
                        🍔
                    </div>

                    <div>

                        <strong>
                            Manage Burgers
                        </strong>

                        <span>
                            Add, edit, or remove burgers
                        </span>

                    </div>

                    <span class="action-arrow">
                        →
                    </span>

                </a>


                <a href="orders.php" class="action-card">

                    <div class="action-icon">
                        🛒
                    </div>

                    <div>

                        <strong>
                            Manage Orders
                        </strong>

                        <span>
                            View and update customer orders
                        </span>

                    </div>

                    <span class="action-arrow">
                        →
                    </span>

                </a>


                <a href="reports.php" class="action-card">

                    <div class="action-icon">
                        📊
                    </div>

                    <div>

                        <strong>
                            View Reports
                        </strong>

                        <span>
                            Check sales and order reports
                        </span>

                    </div>

                    <span class="action-arrow">
                        →
                    </span>

                </a>


            </div>

        </section>


        <!-- FOOTER -->

        <footer class="admin-footer">

            <span>
                © <?php echo date("Y"); ?> Lawr's Burgers
            </span>

            <span>
                Admin Panel
            </span>

        </footer>


    </main>

</div>

</body>

</html>

<?php

$conn->close();

?>