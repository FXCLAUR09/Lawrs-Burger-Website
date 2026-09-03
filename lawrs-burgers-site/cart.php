<?php
session_start();

// Get cart
$cart = isset($_SESSION['cart'])
    ? $_SESSION['cart']
    : array();

// Calculate total
$total = 0;

foreach ($cart as $item) {
    $price = isset($item['price']) ? (float)$item['price'] : 0;
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;

    $total += $price * $quantity;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Your shopping cart at Lawr's Burgers.">

    <title>Your Cart - Lawr's Burgers</title>

    <!-- Google Fonts -->
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=Pacifico&display=swap"
        rel="stylesheet">

    <!-- Main CSS -->
    <link
        rel="stylesheet"
        href="css/style.css">

</head>

<body>

    <!-- =====================================================
         HEADER
         ===================================================== -->

    <header class="site-header" id="siteHeader">

        <a
            class="brand"
            href="index.php"
            aria-label="Lawr's Burgers home">

            <span class="brand-main">LAWR’S</span>
            <span class="brand-sub">BURGERS</span>

        </a>


        <!-- Mobile Menu Button -->

        <button
            class="menu-toggle"
            aria-label="Open navigation"
            aria-expanded="false">

            <span></span>
            <span></span>
            <span></span>

        </button>


        <!-- Navigation -->

        <nav class="main-nav" aria-label="Primary navigation">

    <a href="index.php">HOME</a>
    <a href="view_menu.php">OUR MENU</a>
    <a href="#about">ABOUT US</a>
    <a href="#identity">GALLERY</a>
    <a href="reviews.php">REVIEWS</a>
    <a href="#contact">CONTACT</a>

    <a href="cart.php">VIEW CART 🛒</a>

    <?php if (isset($_SESSION['admin_id'])): ?>

        <a href="admin/dashboard.php">
            ADMIN PANEL
        </a>

        <a href="logout.php">
            LOGOUT
        </a>

    <?php elseif (isset($_SESSION['customer_id'])): ?>

        <a href="logout.php">
            LOGOUT
        </a>

    <?php else: ?>

        <a href="login.php">
            LOGIN
        </a>

        <a href="register.php">
            REGISTER
        </a>

    <?php endif; ?>

    <a class="nav-cta" href="view_menu.php">
        ORDER NOW <span>→</span>
    </a>

</nav>

        </nav>

    </header>


    <!-- =====================================================
         CART PAGE
         ===================================================== -->

    <main>

        <section class="cart-page">

            <div class="cart-container">


                <!-- PAGE HEADING -->

                <div class="cart-heading">

                    <div>

                        <p class="script">
                            your order
                        </p>

                        <h1>
                            SHOPPING CART
                        </h1>

                        <p class="cart-subtitle">
                            Review your delicious choices before checkout.
                        </p>

                    </div>


                    <a
                        href="view_menu.php"
                        class="continue-btn">

                        ← CONTINUE SHOPPING

                    </a>

                </div>


                <?php if (empty($cart)): ?>

                    <!-- =================================================
                         EMPTY CART
                         ================================================= -->

                    <div class="empty-cart">

                        <div class="empty-cart-icon">
                            🛒
                        </div>

                        <h2>
                            YOUR CART IS EMPTY
                        </h2>

                        <p>
                            Looks like you haven't added any burgers yet.
                        </p>

                        <a
                            href="view_menu.php"
                            class="checkout-btn">

                            VIEW OUR MENU

                        </a>

                    </div>


                <?php else: ?>

                    <!-- =================================================
                         CART ITEMS
                         ================================================= -->

                    <div class="cart-items">


                        <?php foreach ($cart as $item): ?>

                            <?php

                            $itemId = isset($item['id'])
                                ? (int)$item['id']
                                : 0;

                            $itemName = isset($item['name'])
                                ? $item['name']
                                : 'Burger';

                            $itemPrice = isset($item['price'])
                                ? (float)$item['price']
                                : 0;

                            $itemQuantity = isset($item['quantity'])
                                ? (int)$item['quantity']
                                : 1;

                            $itemImage = isset($item['image'])
                                ? $item['image']
                                : '';

                            $subtotal = $itemPrice * $itemQuantity;

                            ?>


                            <article class="cart-item">


                                <!-- BURGER IMAGE -->

                                <div class="cart-item-image">

                                    <?php if (!empty($itemImage)): ?>

                                        <img
                                            src="assets/<?php echo htmlspecialchars($itemImage); ?>"
                                            alt="<?php echo htmlspecialchars($itemName); ?>">

                                    <?php else: ?>

                                        <div class="cart-no-image">
                                            🍔
                                        </div>

                                    <?php endif; ?>

                                </div>


                                <!-- BURGER INFORMATION -->

                                <div class="cart-item-info">

                                    <p class="cart-item-label">
                                        LAWR'S BURGERS
                                    </p>

                                    <h2>
                                        <?php
                                        echo htmlspecialchars($itemName);
                                        ?>
                                    </h2>

                                    <p class="cart-price">
                                        ₱<?php
                                        echo number_format(
                                            $itemPrice,
                                            2
                                        );
                                        ?>
                                        each
                                    </p>

                                </div>


                                <!-- QUANTITY -->

                                <div class="cart-quantity">

                                    <span class="cart-label">
                                        QUANTITY
                                    </span>

                                    <form
                                        action="update_cart.php"
                                        method="POST"
                                        class="quantity-form">

                                        <input
                                            type="hidden"
                                            name="burger_id"
                                            value="<?php echo $itemId; ?>">

                                        <input
                                            type="number"
                                            name="quantity"
                                            value="<?php echo $itemQuantity; ?>"
                                            min="1"
                                            required
                                            aria-label="Quantity of <?php echo htmlspecialchars($itemName); ?>">

                                        <button
                                            type="submit"
                                            class="update-btn">

                                            UPDATE

                                        </button>

                                    </form>

                                </div>


                                <!-- SUBTOTAL -->

                                <div class="cart-item-total">

                                    <span class="cart-label">
                                        SUBTOTAL
                                    </span>

                                    <strong>
                                        ₱<?php
                                        echo number_format(
                                            $subtotal,
                                            2
                                        );
                                        ?>
                                    </strong>

                                </div>


                                <!-- REMOVE -->

                                <div class="cart-remove">

                                    <a
                                        href="remove_to_cart.php?id=<?php echo $itemId; ?>"
                                        class="remove-btn"
                                        onclick="return confirm('Remove this burger from your cart?');">

                                        REMOVE

                                    </a>

                                </div>


                            </article>

                        <?php endforeach; ?>


                    </div>


                    <!-- =================================================
                         CART SUMMARY
                         ================================================= -->

                    <div class="cart-summary">


                        <div class="cart-summary-left">

                            <p>
                                <?php echo count($cart); ?>
                                item<?php echo count($cart) != 1 ? 's' : ''; ?>
                                in your cart
                            </p>

                        </div>


                        <div class="cart-summary-right">

                            <div class="cart-total">

                                <span>
                                    TOTAL
                                </span>

                                <strong>
                                    ₱<?php
                                    echo number_format(
                                        $total,
                                        2
                                    );
                                    ?>
                                </strong>

                            </div>


                            <div class="cart-actions">

                                <a
                                    href="view_menu.php"
                                    class="continue-btn">

                                    CONTINUE SHOPPING

                                </a>

                                <a
                                    href="checkout.php"
                                    class="checkout-btn">

                                    CHECKOUT →

                                </a>

                            </div>

                        </div>


                    </div>

                <?php endif; ?>


            </div>

        </section>

    </main>


    <!-- =====================================================
         FOOTER
         ===================================================== -->

    <footer>

        <div class="footer-brand">

            <div class="brand">

                <span class="brand-main">
                    LAWR’S
                </span>

                <span class="brand-sub">
                    BURGERS
                </span>

            </div>

        </div>


        <div class="footer-center">

            <span>
                Made with love & burgers
            </span>

        </div>


        <div class="socials">

            <a href="#" aria-label="Facebook">
                Facebook
            </a>

            <a href="#" aria-label="Instagram">
                Instagram
            </a>

        </div>

    </footer>


    <!-- Main JavaScript -->

    <script src="js/script.js"></script>

</body>

</html>