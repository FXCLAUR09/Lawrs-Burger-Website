<?php
session_start();

require_once "config/db.php";

$sql = "SELECT id, name, description, price, image, category
        FROM burgers
        WHERE available = 1
        ORDER BY id ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Our Menu | Lawr's Burgers</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=Pacifico&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<!-- ================= HEADER ================= -->

<header class="site-header">

    <a href="index.php" class="brand">

        <span class="brand-main">
            LAWR’S
        </span>

        <span class="brand-sub">
            BURGERS
        </span>

    </a>


    <button
        class="menu-toggle"
        aria-label="Open navigation"
        aria-expanded="false">

        <span></span>
        <span></span>
        <span></span>

    </button>


    <nav class="main-nav">

        <a href="index.php">
            HOME
        </a>

        <a href="view_menu.php">
            OUR MENU
        </a>

        <a href="index.php#about">
            ABOUT US
        </a>

        <a href="index.php#identity">
            LOCATIONS
        </a>

        <a href="index.php#reviews">
            REVIEWS
        </a>

        <a href="index.php#contact">
            CONTACT
        </a>

        <a href="cart.php">
            VIEW CART 🛒
        </a>

        <a href="view_menu.php" class="nav-cta">
            ORDER NOW
            <span>→</span>
        </a>

    </nav>

</header>


<!-- ================= MENU ================= -->

<main class="menu-page">

    <div class="menu-container">


        <!-- PAGE INTRO -->

        <div class="menu-heading">

            <p class="script">
                fresh from the grill
            </p>

            <h1>
                LAWR'S BURGER MENU
            </h1>

            <p>
                BIG FLAVOR. JUICY BURGERS. MADE JUST FOR YOU.
            </p>

        </div>


        <!-- ================= BURGER GRID ================= -->

        <div class="menu-grid">


            <?php

            if ($result->num_rows > 0) {

                while ($burger = $result->fetch_assoc()) {

            ?>

                <article class="menu-card">


                    <!-- IMAGE -->

                    <div class="menu-image">

                        <img
                            src="assets/<?php echo htmlspecialchars($burger['image']); ?>"
                            alt="<?php echo htmlspecialchars($burger['name']); ?>">

                    </div>


                    <!-- INFORMATION -->

                    <div class="menu-info">


                        <?php

                        if (!empty($burger['category'])) {

                        ?>

                            <span class="menu-category">

                                <?php
                                echo htmlspecialchars($burger['category']);
                                ?>

                            </span>

                        <?php

                        }

                        ?>


                        <h2>

                            <?php
                            echo htmlspecialchars($burger['name']);
                            ?>

                        </h2>


                        <p>

                            <?php
                            echo htmlspecialchars($burger['description']);
                            ?>

                        </p>


                        <div class="menu-bottom">


                            <span class="menu-price">

                                ₱<?php
                                echo number_format(
                                    (float)$burger['price'],
                                    2
                                );
                                ?>

                            </span>


                            <!-- ADD TO CART -->

                           <form action="./Add_to_cart.php" method="POST">
                            <input
                                type="hidden"
                                name="burger_id"
                                value="<?php echo $burger['id']; ?>">

                            <button
                                type="submit"
                                class="add-cart-btn">

                                ADD TO CART
                                <span>+</span>

                            </button>

                        </form>


                        </div>

                    </div>

                </article>


            <?php

                }

            } else {

            ?>

                <!-- EMPTY MENU -->

                <div class="menu-empty">

                    <h2>
                        NO BURGERS AVAILABLE
                    </h2>

                    <p>
                        Please check back again soon.
                    </p>

                </div>


            <?php

            }

            ?>


        </div>

    </div>

</main>


<script src="js/script.js"></script>

</body>

</html>