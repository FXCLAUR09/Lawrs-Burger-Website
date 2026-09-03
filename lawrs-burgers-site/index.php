<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/db.php";

$sql = "SELECT id, name, description, price, image
        FROM burgers
        WHERE available = 1
        AND category = 'Signature Burgers'
        ORDER BY id ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description"
        content="Lawr's Burgers — juicy burgers, real ingredients, super B taste.">

    <title>Lawr's Burgers</title>

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=Pacifico&display=swap"
        rel="stylesheet">

  
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header class="site-header" id="siteHeader">

        <a class="brand" href="#home" aria-label="Lawr's Burgers home">
            <span class="brand-main">LAWR’S</span>
            <span class="brand-sub">BURGERS</span>
        </a>

        <button
            class="menu-toggle"
            aria-label="Open navigation"
            aria-expanded="false">

            <span></span>
            <span></span>
            <span></span>

        </button>

      <nav class="main-nav" aria-label="Primary navigation">

    <a href="#home">HOME</a>

    <a href="view_menu.php">OUR MENU</a>

    <a href="#about">ABOUT US</a>

    <a href="#identity">GALLERY</a>

    <a href="reviews.php">REVIEWS</a>

    <a href="#contact">CONTACT</a>

    <a href="cart.php">VIEW CART</a>


    <!-- USER ACCOUNT -->

    <?php if (isset($_SESSION["customer_id"])): ?>

        <!-- CUSTOMER LOGGED IN -->

        <div class="user-menu">

            <button
                type="button"
                class="user-menu-button"
                aria-expanded="false"
            >

                <span class="user-icon">👤</span>

                <span class="user-name">
                    <?php
                    echo htmlspecialchars($_SESSION["customer_name"]);
                    ?>
                </span>

                <span class="user-arrow">▾</span>

            </button>


            <div class="user-dropdown">

                <div class="user-dropdown-header">

                    <span class="user-dropdown-icon">👤</span>

                    <div>

                        <strong>
                            <?php
                            echo htmlspecialchars($_SESSION["customer_name"]);
                            ?>
                        </strong>

                        <small>
                            <?php
                            echo htmlspecialchars($_SESSION["customer_email"]);
                            ?>
                        </small>

                    </div>

                </div>


                <div class="user-dropdown-divider"></div>


                <a href="cart.php">
                    <span>📦</span>
                    My Orders
                </a>


                <a href="logout.php" class="logout-link">
                    <span>↪</span>
                    Logout
                </a>

            </div>

        </div>


    <?php elseif (isset($_SESSION["admin_id"])): ?>

        <!-- ADMIN LOGGED IN -->

        <div class="user-menu">

            <button
                type="button"
                class="user-menu-button"
                aria-expanded="false"
            >

                <span class="user-icon">👤</span>

                <span class="user-name">
                    <?php
                    echo htmlspecialchars($_SESSION["admin_username"]);
                    ?>
                </span>

                <span class="user-arrow">▾</span>

            </button>


            <div class="user-dropdown">

                <div class="user-dropdown-header">

                    <span class="user-dropdown-icon">👤</span>

                    <div>

                        <strong>
                            <?php
                            echo htmlspecialchars($_SESSION["admin_username"]);
                            ?>
                        </strong>

                        <small>
                            Administrator
                        </small>

                    </div>

                </div>


                <div class="user-dropdown-divider"></div>


                <a href="admin/dashboard.php">
                    <span>⚙</span>
                    Admin Dashboard
                </a>


                <a href="logout.php" class="logout-link">
                    <span>↪</span>
                    Logout
                </a>

            </div>

        </div>


    <?php else: ?>

        <!-- NOT LOGGED IN -->

        <a href="login.php" class="user-login-link">

            <span class="user-login-icon">👤</span>

            <span>LOGIN</span>

        </a>

    <?php endif; ?>

</nav>

    </header>


    <main>

        <section class="hero section-dark" id="home">

            <div class="hero-copy">

                <p class="eyebrow">
                     SAVOR THE ULTIMATE BURGER.
                </p>

                <h1>
                    JUICY BURGERS<br>
                    THAT ACTUALLY<br>
                    <span>TASTE GOOD!!</span>
                </h1>

                <p class="hero-description">
                    A mouthwatering burgers using 100%<br>
                    premium ingredients for a flavor that hits<br>
                    every time.
                </p>

                <a class="outline-btn" href="view_menu.php">
                    EXPLORE MENU
                </a>

            </div>

        </section>

        <section
            class="stats section-dark"
            aria-label="Lawr's Burgers statistics">

            <div class="stat">

                <img src="assets/burger-icon.png" alt="">

                <div>
                    <strong>25+</strong>
                    <span>BURGER OPTIONS</span>
                </div>

            </div>


            <div class="stat">

                <img src="assets/fresh-ingredients.png" alt="">

                <div>
                    <strong>100%</strong>
                    <span>FRESH INGREDIENTS</span>
                </div>

            </div>


            <div class="stat">

                <img src="assets/star-reviews.png" alt="">

                <div>
                    <strong>1000+</strong>
                    <span>5-STAR REVIEWS</span>
                </div>

            </div>


            <div class="stat">

                <img src="assets/happy-customers.png" alt="">

                <div>
                    <strong>5600+</strong>
                    <span>HAPPY CUSTOMERS</span>
                </div>

            </div>

        </section>

        <section
            class="menu-section section-dark"
            id="menu">

            <div class="section-heading-row">

                <div>

                    <p class="script">
                        try our
                    </p>

                    <h2>
                        SIGNATURE BURGERS
                    </h2>

                </div>

                <a
                    class="outline-btn small"
                    href="view_menu.php">

                    OUR MENU

                </a>

            </div>

            <div class="burger-grid">

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($burger = $result->fetch_assoc()): ?>

                        <article
    class="burger-card"
    data-burger-id="<?php echo (int)$burger['id']; ?>">

    <div class="burger-image">

        <img
            src="assets/<?php echo htmlspecialchars($burger['image']); ?>"
            alt="<?php echo htmlspecialchars($burger['name']); ?> burger">

    </div>

    <h3>
        <?php echo htmlspecialchars($burger['name']); ?>
    </h3>

    <p>
        ₱<?php echo number_format((float)$burger['price'], 2); ?>
    </p>

    <form action="add_to_cart.php" method="POST">

        <input
            type="hidden"
            name="burger_id"
            value="<?php echo (int)$burger['id']; ?>">

        <button
            type="submit"
            class="add-to-cart-btn">

            ADD TO CART

        </button>

    </form>

</article>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="no-burgers">

                        <p>
                            No signature burgers are currently available.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </section>

        <section
            class="about section-dark"
            id="about">

            <div class="about-copy">

                <p class="script">
                    About Lawr’s Burgers
                </p>

                <h2>
                    REAL INGREDIENTS.<br>
                    <span>SUPER’B TASTE!!</span>
                </h2>

                <p>
                    At Lawr’s Burger, we believe simple is better<br>
                    thats why we use only real, high-quality<br>
                    ingredients to deliver bold flavors in<br>
                    every single bite.
                </p>

                <a
                    class="outline-btn small"
                    href="#identity">

                    LEARN MORE

                </a>

            </div>


            <div class="fresh-badge">

                <span>★</span>

                <b>FRESH</b>

                <small>NEVER FROZEN</small>

                <span>★ ★ ★</span>

            </div>

        </section>

        <section
            class="order-strip"
            id="order">

            <img
                src="assets/burger-icon.png"
                alt="">

            <div>

                <h2>
                    CRAVING SOMETHING DELICIOUS?
                </h2>

                <p>
                    ORDER NOW AND TASTE THE DIFFERENCE!
                </p>

            </div>

            <a
                class="dark-btn"
                href="view_menu.php">

                ORDER NOW

            </a>

        </section>

        <section
            class="identity section-dark"
            id="identity">

            <div class="identity-intro">

                <div class="identity-title">

                    <h2>
                        OUR <span>BURGER</span><br>
                        <em>OUR </em>
                        <em>IDENTITY</em>
                    </h2>

                </div>


                <div class="identity-copy">

                    <p>
                        At Lawr’s Burgers, we believe great taste<br>
                        starts with great details. Every packaging,<br>
                        apparels, and touchpoint reflects the quality<br>
                        and passion we put into every burger.
                    </p>

                </div>

            </div>


            <div class="gallery-title">

                <span>★</span>

                <h2>GALLERY</h2>

                <span>★</span>

            </div>

            <div class="gallery-group">

                <h3>
                    <img
                        src="assets/section-burger-icon.png"
                        alt="">

                    PACKAGING
                </h3>


                <div class="gallery-grid">

                    <div class="gallery-card">
                        <img
                            src="assets/packaging-box-alt.png"
                            loading="lazy" decoding="async" alt="Lawr's burger packaging">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/packaging-stack.png"
                            loading="lazy" decoding="async" alt="Stacked Lawr's burger boxes">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/packaging-diamond.png"
                            loading="lazy" decoding="async" alt="Lawr's burger box">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/packaging-box.png"
                            loading="lazy" decoding="async" alt="Lawr's premium burger box">
                    </div>

                </div>

            </div>

            <div class="gallery-group">

                <h3>

                    <img
                        src="assets/section-burger-icon.png"
                        alt="">

                    APPAREL

                </h3>


                <div class="gallery-grid">

                    <div class="gallery-card">
                        <img
                            src="assets/apparel-shirt.png"
                            loading="lazy" decoding="async" alt="Lawr's burger shirt">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/apparel-hoodie.png"
                            loading="lazy" decoding="async" alt="Lawr's burger hoodie">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/apparel-apron.png"
                            loading="lazy" decoding="async" alt="Lawr's burger apron">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/apparel-hat.png"
                            loading="lazy" decoding="async" alt="Lawr's burger bucket hat">
                    </div>

                </div>

            </div>

            <div class="gallery-group">

                <h3>

                    <img
                        src="assets/section-burger-icon.png"
                        alt="">

                    DRINKWARE &amp; ACCESSORIES

                </h3>


                <div class="gallery-grid">

                    <div class="gallery-card">
                        <img
                            src="assets/drinkware-cup.png"
                            loading="lazy" decoding="async" alt="Lawr's burger cup">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/drinkware-bag.png"
                            loading="lazy" decoding="async" alt="Lawr's burger coffee carrier">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/drinkware-coffee.png"
                            loading="lazy" decoding="async" alt="Lawr's burger coffee cup">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/identity-building.png"
                            loading="lazy" decoding="async" alt="Lawr's burger identity building">
                    </div>

                </div>

            </div>


            <!-- DELIVERY -->

            <div class="gallery-group">

                <h3>

                    <img
                        src="assets/section-burger-icon.png"
                        alt="">

                    DELIVERY &amp; MORE

                </h3>


                <div class="gallery-grid">

                    <div class="gallery-card">
                        <img
                            src="assets/delivery-backpack.png"
                            loading="lazy" decoding="async" alt="Lawr's delivery backpack">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/identity-card.png"
                            loading="lazy" decoding="async" alt="Lawr's identity card">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/delivery-tote.png"
                            loading="lazy" decoding="async" alt="Lawr's delivery tote">
                    </div>

                    <div class="gallery-card">
                        <img
                            src="assets/identity-poster.png"
                            loading="lazy" decoding="async" alt="Lawr's burger identity poster">
                    </div>

                </div>

            </div>

        </section>

    </main>

  <footer class="site-footer" id="contact">

    <div class="footer-main">

        <!-- BRAND -->  
        <div class="footer-brand-area">

            <a class="footer-logo" href="#home">
                <span class="footer-logo-main">LAWR’S</span>
                <span class="footer-logo-sub">BURGERS</span>
            </a>

            <p class="footer-slogan">
                <br><br>GOOD BURGERS.<br>
                GOOD MOOD.
            </p>

            <p class="footer-description">
                Fresh burgers, bold flavors,<br>
                and good food made for everyone.
            </p>

            <div class="footer-socials">

                <a href="#" aria-label="Facebook">
                    <img src="assets/facebook.png" alt="Facebook">
                </a>

                <a href="#" aria-label="Instagram">
                    <img src="assets/instagram.png" alt="Instagram">
                </a>

                <a href="#" aria-label="X">
                    <img src="assets/x-icon.png" alt="X">
                </a>

            </div>

        </div>


        <!-- QUICK LINKS -->
        <div class="footer-links-area">

            <h3>QUICK LINKS</h3>

            <a href="#home">Home</a>
            <a href="view_menu.php">Our Menu</a>
            <a href="#about">About Us</a>
            <a href="#identity">Gallery</a>
            <a href="reviews.php">Reviews</a>
            <a href="#contact">Contact</a>

        </div>


        <!-- HOURS -->
        <div class="footer-hours-area">

            <h3>HOURS</h3>

            <div class="footer-hour-group">
                <strong>MON - FRI</strong>
                <span>10:00 AM - 9:00 PM</span>
            </div>

            <div class="footer-hour-group">
                <strong>SATURDAY</strong>
                <span>10:00 AM - 10:00 PM</span>
            </div>

            <div class="footer-hour-group">
                <strong>SUNDAY</strong>
                <span>10:00 AM - 8:00 PM</span>
            </div>

        </div>


       <div class="footer-contact-area">

    <p class="footer-big-slogan">
        SAVOR THE<br>
        ULTIMATE <span>BURGER.</span>
    </p>

    <h3>CONTACT US</h3>

            <div class="footer-contact-item">

                <span class="footer-contact-icon">☎</span>

                <span>
                    +63 994 072 5885
                </span>

            </div>

            <div class="footer-contact-item">

                <span class="footer-contact-icon">✉</span>

                <span>
                    lawrsburgers@gmail.com
                </span>

            </div>

            <div class="footer-contact-item">

                <span class="footer-contact-icon">📍</span>

                <span>
                    Dumaguete City,<br>
                    Negros Oriental, Philippines
                </span>

            </div>

        </div>

    </div>


    <!-- COPYRIGHT -->
    <div class="footer-bottom">

        <div class="footer-divider"></div>

        <p>
            © <span id="year">2026</span>
            Lawr’s Burgers. All Rights Reserved.
        </p>

    </div>

</footer>
    <script src="js/script.js"></script>

    <script>
document.addEventListener("DOMContentLoaded", function () {

    const userMenus = document.querySelectorAll(".user-menu");

    userMenus.forEach(function (menu) {

        const button = menu.querySelector(".user-menu-button");

        if (!button) {
            return;
        }

        button.addEventListener("click", function (event) {

            event.stopPropagation();

            const isOpen = menu.classList.contains("active");

            // Close other user menus
            userMenus.forEach(function (otherMenu) {

                otherMenu.classList.remove("active");

                const otherButton =
                    otherMenu.querySelector(".user-menu-button");

                if (otherButton) {
                    otherButton.setAttribute(
                        "aria-expanded",
                        "false"
                    );
                }

            });

            // Open clicked menu
            if (!isOpen) {

                menu.classList.add("active");

                button.setAttribute(
                    "aria-expanded",
                    "true"
                );

            }

        });

    });


    // Close when clicking outside
    document.addEventListener("click", function () {

        userMenus.forEach(function (menu) {

            menu.classList.remove("active");

            const button =
                menu.querySelector(".user-menu-button");

            if (button) {

                button.setAttribute(
                    "aria-expanded",
                    "false"
                );

            }

        });

    });

});
</script>
</body>
</html>