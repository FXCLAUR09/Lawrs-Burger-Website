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
 * Get all burgers.
 */
$sql = "
    SELECT
        id,
        name,
        description,
        price,
        image,
        category,
        available,
        stock_quantity,
        created_at
    FROM burgers
    ORDER BY id DESC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Burger Management | Lawr's Burgers</title>

    <link rel="stylesheet" href="../css/admin.css">

</head>

<body>

<div class="admin-layout">

    <!-- =========================================
         SIDEBAR
    ========================================== -->

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

            <a href="dashboard.php" class="nav-item">

                <span class="nav-icon">📊</span>

                <span>
                    Dashboard
                </span>

            </a>


            <a href="burgers.php" class="nav-item active">

                <span class="nav-icon">🍔</span>

                <span>
                    Burgers
                </span>

            </a>


            <a href="orders.php" class="nav-item">

                <span class="nav-icon">📦</span>

                <span>
                    Orders
                </span>

            </a>


            <a href="reports.php" class="nav-item">

                <span class="nav-icon">📈</span>

                <span>
                    Reports
                </span>

            </a>


            <div class="sidebar-divider"></div>


            <a href="../index.php" class="nav-item">

                <span class="nav-icon">🌐</span>

                <span>
                    View Website
                </span>

            </a>


            <a href="../logout.php" class="nav-item logout-link">

                <span class="nav-icon">🚪</span>

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


    <!-- =========================================
         MAIN CONTENT
    ========================================== -->

    <main class="admin-main">


        <!-- TOP BAR -->

        <header class="admin-topbar">

            <div>

                <span class="page-label">
                    ADMIN PANEL
                </span>

                <h1>
                    Burger Management
                </h1>

            </div>


            <div class="admin-user">

                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION["admin_username"], 0, 1)); ?>
                </div>

                <div class="user-info">

                    <strong>
                        <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </header>


        <!-- =========================================
             PAGE CONTENT
        ========================================== -->

        <section class="admin-content">


            <!-- PAGE HEADER -->

            <div class="content-header">

                <div>

                    <h2>
                        Burgers
                    </h2>

                    <p>
                        Manage your burger menu, prices, stock, and availability.
                    </p>

                </div>


                <a href="add_burger.php" class="admin-button">

                    <span>+</span>

                    Add Burger

                </a>

            </div>


            <!-- =====================================
                 BURGER TABLE
            ====================================== -->

            <div class="admin-card burger-management-card">

                <div class="card-header">

                    <div>

                        <h3>
                            Burger Menu
                        </h3>

                        <p>
                            All burgers currently stored in the database.
                        </p>

                    </div>

                    <div class="burger-count">

                        <?php echo $result->num_rows; ?>

                        Burger<?php echo ($result->num_rows != 1) ? "s" : ""; ?>

                    </div>

                </div>


                <div class="table-wrapper">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Burger
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Stock
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($result->num_rows > 0): ?>

                            <?php while ($burger = $result->fetch_assoc()): ?>

                                <tr>

                                    <!-- ID -->

                                    <td>

                                        <span class="burger-id">

                                            #<?php echo $burger["id"]; ?>

                                        </span>

                                    </td>


                                    <!-- BURGER -->

                                    <td>

                                        <div class="burger-table-info">

                                            <div class="burger-table-image">

                                                <?php if (!empty($burger["image"])): ?>

                                                    <img
                                                        src="../assets/<?php echo htmlspecialchars($burger["image"]); ?>"
                                                        alt="<?php echo htmlspecialchars($burger["name"]); ?>"
                                                        onerror="this.style.display='none';"
                                                    >

                                                <?php else: ?>

                                                    <span>
                                                        🍔
                                                    </span>

                                                <?php endif; ?>

                                            </div>


                                            <div class="burger-table-details">

                                                <strong>

                                                    <?php echo htmlspecialchars($burger["name"]); ?>

                                                </strong>

                                                <span>

                                                    <?php

                                                    $description = $burger["description"] ?? "";

                                                    if (strlen($description) > 55) {

                                                        echo htmlspecialchars(
                                                            substr($description, 0, 55)
                                                        ) . "...";

                                                    } else {

                                                        echo htmlspecialchars($description);

                                                    }

                                                    ?>

                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- CATEGORY -->

                                    <td>

                                        <span class="category-badge">

                                            <?php echo htmlspecialchars($burger["category"]); ?>

                                        </span>

                                    </td>


                                    <!-- PRICE -->

                                    <td>

                                        <strong class="burger-price">

                                            ₱<?php echo number_format((float)$burger["price"], 2); ?>

                                        </strong>

                                    </td>


                                    <!-- STOCK -->

                                    <td>

                                        <?php

                                        $stock = (int)$burger["stock_quantity"];

                                        if ($stock <= 0) {

                                            $stock_class = "stock-out";

                                        } elseif ($stock <= 10) {

                                            $stock_class = "stock-low";

                                        } else {

                                            $stock_class = "stock-good";

                                        }

                                        ?>

                                        <span class="stock-badge <?php echo $stock_class; ?>">

                                            <?php echo $stock; ?>

                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php if ((int)$burger["available"] === 1): ?>

                                            <span class="status-badge status-available">

                                                Available

                                            </span>

                                        <?php else: ?>

                                            <span class="status-badge status-unavailable">

                                                Unavailable

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td>

                                        <div class="table-actions">

                                            <a
                                                href="edit_burger.php?id=<?php echo $burger["id"]; ?>"
                                                class="action-button edit-button"
                                            >

                                                Edit

                                            </a>


                                            <a
                                                href="delete_burger.php?id=<?php echo $burger["id"]; ?>"
                                                class="action-button delete-button"
                                                onclick="return confirm('Are you sure you want to delete this burger?');"
                                            >

                                                Delete

                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="7" class="empty-table">

                                    <div class="empty-icon">
                                        🍔
                                    </div>

                                    <h3>
                                        No Burgers Found
                                    </h3>

                                    <p>
                                        You haven't added any burgers yet.
                                    </p>

                                    <a href="add_burger.php" class="admin-button">

                                        Add Your First Burger

                                    </a>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


        </section>


        <!-- FOOTER -->

        <footer class="admin-footer">

            <p>
                © <?php echo date("Y"); ?> Lawr's Burgers. Admin Panel.
            </p>

        </footer>


    </main>

</div>

</body>

</html>

<?php

$conn->close();

?>