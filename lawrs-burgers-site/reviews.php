<?php

session_start();

require_once "config/db.php";

/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "";

$customer_name = "";
$rating = 0;
$review_text = "";


/*
|--------------------------------------------------------------------------
| SUBMIT REVIEW
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customer_name = trim($_POST["customer_name"] ?? "");
    $rating = (int)($_POST["rating"] ?? 0);
    $review_text = trim($_POST["review_text"] ?? "");

    /*
     * Validate name
     */
    if ($customer_name === "") {

        $message = "Please enter your name.";
        $message_type = "error";

    }

    /*
     * Validate rating
     */
    elseif ($rating < 1 || $rating > 5) {

        $message = "Please select a rating between 1 and 5 stars.";
        $message_type = "error";

    }

    /*
     * Validate review
     */
    elseif ($review_text === "") {

        $message = "Please write a review.";
        $message_type = "error";

    }

    /*
     * Insert review
     */
    else {

        $stmt = $conn->prepare("
            INSERT INTO reviews
            (customer_name, rating, review_text)
            VALUES (?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sis",
                $customer_name,
                $rating,
                $review_text
            );

            if ($stmt->execute()) {

                $message = "Thank you for your review! 🍔⭐";
                $message_type = "success";

                $customer_name = "";
                $rating = 0;
                $review_text = "";

            } else {

                $message = "Something went wrong while submitting your review.";
                $message_type = "error";

            }

            $stmt->close();

        } else {

            $message = "Unable to submit your review.";
            $message_type = "error";

        }
    }
}


/*
|--------------------------------------------------------------------------
| GET REVIEW STATISTICS
|--------------------------------------------------------------------------
*/

$average_rating = 0;
$total_reviews = 0;

$stats_sql = "
    SELECT
        COUNT(*) AS total_reviews,
        COALESCE(AVG(rating), 0) AS average_rating
    FROM reviews
";

$stats_result = $conn->query($stats_sql);

if ($stats_result) {

    $stats = $stats_result->fetch_assoc();

    if ($stats) {

        $total_reviews = (int)$stats["total_reviews"];
        $average_rating = (float)$stats["average_rating"];

    }
}


/*
|--------------------------------------------------------------------------
| GET STAR COUNTS
|--------------------------------------------------------------------------
*/

$star_counts = [
    5 => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0
];

$star_sql = "
    SELECT
        rating,
        COUNT(*) AS total
    FROM reviews
    GROUP BY rating
";

$star_result = $conn->query($star_sql);

if ($star_result) {

    while ($row = $star_result->fetch_assoc()) {

        $star = (int)$row["rating"];

        if (isset($star_counts[$star])) {

            $star_counts[$star] = (int)$row["total"];

        }
    }
}


/*
|--------------------------------------------------------------------------
| GET REVIEWS
|--------------------------------------------------------------------------
*/

$reviews = [];

$reviews_sql = "
    SELECT
        id,
        customer_name,
        rating,
        review_text,
        created_at
    FROM reviews
    ORDER BY created_at DESC
";

$reviews_result = $conn->query($reviews_sql);

if ($reviews_result) {

    while ($row = $reviews_result->fetch_assoc()) {

        $reviews[] = $row;

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

    <meta
        name="description"
        content="Lawr's Burgers customer reviews."
    >

    <title>Reviews | Lawr's Burgers</title>


    <!-- GOOGLE FONTS -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700;800&family=Pacifico&display=swap"
        rel="stylesheet"
    >


    <!-- MAIN WEBSITE CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- REVIEWS PAGE CSS -->

    <style>

        /* =====================================================
           REVIEWS PAGE
        ===================================================== */

        .reviews-page {

            min-height: 100vh;

            background:
                linear-gradient(
                    rgba(20, 10, 5, 0.72),
                    rgba(20, 10, 5, 0.78)
                ),
                url("assets/about-burger.jpg");

            background-size: cover;
            background-position: center;
            background-attachment: scroll;

            padding: 120px 7% 80px;

        }


        /* =====================================================
           PAGE HEADER
        ===================================================== */

        .reviews-header {

            text-align: center;

            max-width: 750px;

            margin: 0 auto 45px;

        }


        .reviews-header .small-title {

            font-family: "Pacifico", cursive;

            color: #FF9100;

            font-size: 20px;

            margin-bottom: 8px;

        }


        .reviews-header h1 {

            margin: 0;

            font-family: "Bebas Neue", sans-serif;

            font-size: clamp(48px, 7vw, 82px);

            line-height: 0.95;

            color: #ffffff;

            letter-spacing: 1px;

        }


        .reviews-header p {

            margin-top: 18px;

            color: rgba(255, 255, 255, 0.82);

            font-family: "DM Sans", sans-serif;

            font-size: 15px;

            line-height: 1.7;

        }


        /* =====================================================
           MESSAGE
        ===================================================== */

        .review-message {

            max-width: 1050px;

            margin: 0 auto 25px;

            padding: 13px 17px;

            border-radius: 10px;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 700;

        }


        .review-message.success {

            background: #eaf8ee;

            color: #18733a;

            border: 1px solid #c8e8d1;

        }


        .review-message.error {

            background: #fff0f0;

            color: #b42318;

            border: 1px solid #f0caca;

        }


        /* =====================================================
           RATING SUMMARY
        ===================================================== */

        .reviews-summary {

            max-width: 1050px;

            margin: 0 auto 45px;

            display: grid;

            grid-template-columns: 280px 1fr;

            gap: 25px;

            background: rgba(255, 255, 255, 0.96);

            border-radius: 18px;

            padding: 30px;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);

        }


        .overall-rating {

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            text-align: center;

            padding: 15px;

            border-right: 1px solid #eeeeee;

        }


        .rating-number {

            font-family: "Bebas Neue", sans-serif;

            font-size: 72px;

            line-height: 0.9;

            color: #8B1E1E;

        }


        .rating-stars {

            margin: 10px 0;

            color: #FF9100;

            font-size: 23px;

            letter-spacing: 3px;

        }


        .rating-count {

            color: #777;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 600;

        }


        /* =====================================================
           STAR BREAKDOWN
        ===================================================== */

        .rating-breakdown {

            display: flex;

            flex-direction: column;

            justify-content: center;

            gap: 10px;

            padding: 10px 15px;

        }


        .rating-row {

            display: grid;

            grid-template-columns: 55px 1fr 40px;

            align-items: center;

            gap: 10px;

        }


        .rating-label {

            color: #555;

            font-family: "DM Sans", sans-serif;

            font-size: 12px;

            font-weight: 700;

            text-align: right;

        }


        .rating-bar {

            height: 9px;

            background: #eeeeee;

            border-radius: 20px;

            overflow: hidden;

        }


        .rating-bar-fill {

            height: 100%;

            background: #FF9100;

            border-radius: 20px;

            transition: width 0.3s ease;

        }


        .rating-total {

            color: #777;

            font-family: "DM Sans", sans-serif;

            font-size: 12px;

            font-weight: 700;

        }


        /* =====================================================
           MAIN CONTENT
        ===================================================== */

        .reviews-layout {

            max-width: 1050px;

            margin: 0 auto;

            display: grid;

            grid-template-columns: 1.4fr 0.8fr;

            gap: 25px;

            align-items: start;

        }


        /* =====================================================
           REVIEW LIST
        ===================================================== */

        .reviews-list {

            display: flex;

            flex-direction: column;

            gap: 20px;

        }


        /* =====================================================
           FLOATING MESSAGE REVIEW CARDS
        ===================================================== */

        .review-card {

            position: relative;

            background: rgba(255, 255, 255, 0.97);

            border-radius: 18px;

            padding: 24px;

            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.18);

            animation:
                floatingReview 4.5s ease-in-out infinite;

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;

            will-change: transform;

        }


        @keyframes floatingReview {

            0% {

                transform:
                    translateY(0)
                    rotate(0deg);

            }

            50% {

                transform:
                    translateY(-8px)
                    rotate(0.3deg);

            }

            100% {

                transform:
                    translateY(0)
                    rotate(0deg);

            }

        }


        .review-card:nth-child(1) {

            animation-delay: 0s;

        }


        .review-card:nth-child(2) {

            animation-delay: -1.2s;

        }


        .review-card:nth-child(3) {

            animation-delay: -2.4s;

        }


        .review-card:nth-child(4) {

            animation-delay: -0.8s;

        }


        .review-card:nth-child(5) {

            animation-delay: -1.8s;

        }


        .review-card:nth-child(6) {

            animation-delay: -2.8s;

        }


        .review-card:hover {

            animation-play-state: paused;

            transform:
                translateY(-12px)
                scale(1.02);

            box-shadow:
                0 20px 45px rgba(0, 0, 0, 0.25);

        }


        /* =====================================================
           SPEECH BUBBLE TAIL
        ===================================================== */

        .review-card::after {

            content: "";

            position: absolute;

            bottom: -9px;

            left: 28px;

            width: 20px;

            height: 20px;

            background: #ffffff;

            transform: rotate(45deg);

            border-radius: 3px;

            z-index: 0;

        }


        /* =====================================================
           FLOATING STAR BADGE
        ===================================================== */

        .review-card::before {

            content: "★";

            position: absolute;

            top: -10px;

            right: 20px;

            width: 30px;

            height: 30px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #FF9100;

            color: #ffffff;

            border-radius: 50%;

            font-size: 13px;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.18);

            animation:
                floatingStar 3s ease-in-out infinite;

            z-index: 2;

        }


        @keyframes floatingStar {

            0% {

                transform:
                    translateY(0)
                    rotate(0deg);

            }

            50% {

                transform:
                    translateY(-5px)
                    rotate(8deg);

            }

            100% {

                transform:
                    translateY(0)
                    rotate(0deg);

            }

        }


        /* =====================================================
           REVIEW CONTENT
        ===================================================== */

        .review-top {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 15px;

            margin-bottom: 13px;

            position: relative;

            z-index: 1;

        }


        .reviewer-name {

            color: #241717;

            font-family: "DM Sans", sans-serif;

            font-size: 15px;

            font-weight: 800;

        }


        .review-date {

            color: #999;

            font-family: "DM Sans", sans-serif;

            font-size: 11px;

            margin-top: 3px;

        }


        .review-stars {

            color: #FF9100;

            font-size: 16px;

            letter-spacing: 1px;

            white-space: nowrap;

        }


        .review-text {

            margin: 0;

            color: #555;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            line-height: 1.7;

            position: relative;

            z-index: 1;

        }


        /* =====================================================
           WRITE REVIEW CARD
        ===================================================== */

        .review-form-card {

            background: #ffffff;

            border-radius: 18px;

            padding: 28px;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.22);

            position: sticky;

            top: 100px;

        }


        .review-form-card h2 {

            margin: 0;

            color: #241717;

            font-family: "Bebas Neue", sans-serif;

            font-size: 34px;

            letter-spacing: 0.5px;

        }


        .review-form-card > p {

            margin: 6px 0 22px;

            color: #888;

            font-family: "DM Sans", sans-serif;

            font-size: 12px;

            line-height: 1.6;

        }


        /* =====================================================
           FORM
        ===================================================== */

        .review-form-group {

            margin-bottom: 17px;

        }


        .review-form-group label {

            display: block;

            margin-bottom: 7px;

            color: #444;

            font-family: "DM Sans", sans-serif;

            font-size: 12px;

            font-weight: 800;

        }


        .review-form-group input,
        .review-form-group textarea {

            width: 100%;

            box-sizing: border-box;

            border: 1px solid #dddddd;

            border-radius: 9px;

            padding: 12px 13px;

            background: #fafafa;

            color: #333;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            outline: none;

            transition:
                border-color 0.2s ease,
                background 0.2s ease;

        }


        .review-form-group input:focus,
        .review-form-group textarea:focus {

            border-color: #FF9100;

            background: #ffffff;

        }


        .review-form-group textarea {

            min-height: 120px;

            resize: vertical;

        }


        /* =====================================================
           STAR SELECTOR
        ===================================================== */

        .star-selector {

            display: flex;

            flex-direction: row-reverse;

            justify-content: flex-end;

            gap: 3px;

        }


        .star-selector input {

            display: none;

        }


        .star-selector label {

            margin: 0;

            color: #cccccc;

            font-size: 28px;

            cursor: pointer;

            transition:
                color 0.15s ease;

        }


        .star-selector label:hover,
        .star-selector label:hover ~ label,
        .star-selector input:checked ~ label {

            color: #FF9100;

        }


        /* =====================================================
           SUBMIT BUTTON
        ===================================================== */

        .submit-review-btn {

            width: 100%;

            border: none;

            border-radius: 10px;

            padding: 13px 18px;

            background: #f19602;

            color: #ffffff;

            font-family: "DM Sans", sans-serif;

            font-size: 12px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.5px;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease;

        }


        .submit-review-btn:hover {

            background: #b37006;

            transform: translateY(-2px);

        }


        /* =====================================================
           EMPTY REVIEWS
        ===================================================== */

        .no-reviews {

            background: rgba(255, 255, 255, 0.96);

            border-radius: 16px;

            padding: 45px 25px;

            text-align: center;

        }


        .no-reviews-icon {

            font-size: 40px;

            margin-bottom: 10px;

        }


        .no-reviews h3 {

            margin: 0 0 6px;

            color: #333;

            font-family: "Bebas Neue", sans-serif;

            font-size: 28px;

        }


        .no-reviews p {

            margin: 0;

            color: #888;

            font-size: 13px;

        }


        /* =====================================================
           RESPONSIVE - TABLET
        ===================================================== */

        @media (max-width: 900px) {

            .reviews-layout {

                grid-template-columns: 1fr;

            }


            .review-form-card {

                position: static;

            }

        }


        /* =====================================================
           RESPONSIVE - MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            .reviews-page {

                padding: 100px 5% 60px;

                background-attachment: scroll;

            }


            .reviews-summary {

                grid-template-columns: 1fr;

                padding: 22px;

            }


            .overall-rating {

                border-right: none;

                border-bottom: 1px solid #eeeeee;

                padding-bottom: 25px;

            }


            .rating-breakdown {

                padding-top: 15px;

            }

        }


        /* =====================================================
           SMALL MOBILE
        ===================================================== */

        @media (max-width: 500px) {

            .reviews-header h1 {

                font-size: 50px;

            }


            .review-card {

                padding: 20px;

            }


            .review-top {

                flex-direction: column;

                gap: 7px;

            }


            .review-stars {

                font-size: 15px;

            }


            .review-form-card {

                padding: 22px;

            }


            .rating-number {

                font-size: 62px;

            }

        }


        /* =====================================================
           REDUCE ANIMATION
        ===================================================== */

        @media (prefers-reduced-motion: reduce) {

            .review-card,
            .review-card::before {

                animation: none;

            }


            .review-card {

                transition: none;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     SAME HEADER AS MAIN WEBSITE
========================================================= -->

<header class="site-header" id="siteHeader">

    <a
        class="brand"
        href="index.php"
        aria-label="Lawr's Burgers home"
    >

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
        aria-expanded="false"
    >

        <span></span>
        <span></span>
        <span></span>

    </button>


    <nav
        class="main-nav"
        aria-label="Primary navigation"
    >

        <a href="index.php#home">
            HOME
        </a>

        <a href="view_menu.php">
            OUR MENU
        </a>

        <a href="index.php#about">
            ABOUT US
        </a>

        <a href="index.php#identity">
            GALLERY
        </a>

        <a href="reviews.php" class="active">
            REVIEWS
        </a>

        <a href="index.php#contact">
            CONTACT
        </a>

        <a href="cart.php">
            VIEW CART 🛒
        </a>

        <a
            class="nav-cta"
            href="view_menu.php"
        >

            ORDER NOW
            <span>→</span>

        </a>

    </nav>

</header>



<!-- =========================================================
     REVIEWS PAGE
========================================================= -->

<section class="reviews-page">


    <!-- PAGE HEADER -->

    <div class="reviews-header">

        <div class="small-title">
            What Our Customers Say
        </div>


        <h1>
            CUSTOMER REVIEWS
        </h1>


        <p>
            Every burger is made with care, and every review helps us
            become better. See what our customers think about
            Lawr's Burgers.
        </p>

    </div>



    <!-- SUCCESS / ERROR MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="review-message <?php echo htmlspecialchars($message_type); ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>



    <!-- RATING SUMMARY -->

    <div class="reviews-summary">


        <!-- OVERALL RATING -->

        <div class="overall-rating">

            <div class="rating-number">

                <?php echo number_format($average_rating, 1); ?>

            </div>


            <div class="rating-stars">

                <?php

                $rounded_rating = round($average_rating);

                for ($i = 1; $i <= 5; $i++) {

                    echo ($i <= $rounded_rating)
                        ? "★"
                        : "☆";

                }

                ?>

            </div>


            <div class="rating-count">

                Based on

                <?php echo $total_reviews; ?>

                review<?php echo ($total_reviews == 1) ? "" : "s"; ?>

            </div>

        </div>



        <!-- STAR BREAKDOWN -->

        <div class="rating-breakdown">

            <?php for ($star = 5; $star >= 1; $star--): ?>

                <?php

                $count = $star_counts[$star];

                $percentage = 0;

                if ($total_reviews > 0) {

                    $percentage =
                        ($count / $total_reviews) * 100;

                }

                ?>


                <div class="rating-row">

                    <div class="rating-label">

                        <?php echo $star; ?> ★

                    </div>


                    <div class="rating-bar">

                        <div
                            class="rating-bar-fill"
                            style="width: <?php echo $percentage; ?>%;"
                        ></div>

                    </div>


                    <div class="rating-total">

                        <?php echo $count; ?>

                    </div>

                </div>

            <?php endfor; ?>

        </div>

    </div>



    <!-- REVIEWS + FORM -->

    <div class="reviews-layout">


        <!-- CUSTOMER REVIEWS -->

        <div class="reviews-list">

            <?php if (count($reviews) > 0): ?>


                <?php foreach ($reviews as $review): ?>


                    <article class="review-card">


                        <div class="review-top">


                            <div>

                                <div class="reviewer-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $review["customer_name"]
                                    );

                                    ?>

                                </div>


                                <div class="review-date">

                                    <?php

                                    echo date(
                                        "F j, Y",
                                        strtotime(
                                            $review["created_at"]
                                        )
                                    );

                                    ?>

                                </div>

                            </div>


                            <div class="review-stars">

                                <?php

                                $review_rating =
                                    (int)$review["rating"];

                                for ($i = 1; $i <= 5; $i++) {

                                    echo ($i <= $review_rating)
                                        ? "★"
                                        : "☆";

                                }

                                ?>

                            </div>


                        </div>


                        <p class="review-text">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $review["review_text"]
                                )
                            );

                            ?>

                        </p>


                    </article>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="no-reviews">

                    <div class="no-reviews-icon">
                        🍔
                    </div>


                    <h3>
                        NO REVIEWS YET
                    </h3>


                    <p>
                        Be the first customer to leave a review!
                    </p>

                </div>


            <?php endif; ?>

        </div>



        <!-- WRITE A REVIEW -->

        <div class="review-form-card">


            <h2>
                WRITE A REVIEW
            </h2>


            <p>
                Had a great burger? Tell us what you think!
            </p>


            <form
                method="POST"
                action=""
            >


                <!-- NAME -->

                <div class="review-form-group">

                    <label for="customer_name">
                        YOUR NAME
                    </label>


                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        placeholder="Enter your name"
                        maxlength="100"
                        value="<?php
                            echo htmlspecialchars(
                                $customer_name
                            );
                        ?>"
                        required
                    >

                </div>



                <!-- RATING -->

                <div class="review-form-group">

                    <label>
                        YOUR RATING
                    </label>


                    <div class="star-selector">


                        <input
                            type="radio"
                            id="star5"
                            name="rating"
                            value="5"
                            <?php
                            echo ($rating == 5)
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star5">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star4"
                            name="rating"
                            value="4"
                            <?php
                            echo ($rating == 4)
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star4">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star3"
                            name="rating"
                            value="3"
                            <?php
                            echo ($rating == 3)
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star3">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star2"
                            name="rating"
                            value="2"
                            <?php
                            echo ($rating == 2)
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star2">
                            ★
                        </label>


                        <input
                            type="radio"
                            id="star1"
                            name="rating"
                            value="1"
                            <?php
                            echo ($rating == 1)
                                ? "checked"
                                : "";
                            ?>
                        >

                        <label for="star1">
                            ★
                        </label>


                    </div>

                </div>



                <!-- REVIEW -->

                <div class="review-form-group">

                    <label for="review_text">
                        YOUR REVIEW
                    </label>


                    <textarea
                        id="review_text"
                        name="review_text"
                        placeholder="Tell us about your burger..."
                        maxlength="1000"
                        required
                    ><?php
                        echo htmlspecialchars(
                            $review_text
                        );
                    ?></textarea>

                </div>



                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="submit-review-btn"
                >

                    SUBMIT REVIEW

                </button>


            </form>

        </div>


    </div>


</section>



<!-- =========================================================
     SAME FOOTER AS MAIN WEBSITE
========================================================= -->

<footer class="site-footer" id="contact">


    <div class="footer-main">


        <!-- BRAND -->

        <div class="footer-brand-area">

            <a
                class="footer-logo"
                href="index.php#home"
            >

                <span class="footer-logo-main">
                    LAWR’S
                </span>

                <span class="footer-logo-sub">
                    BURGERS
                </span>

            </a>


            <p class="footer-slogan">

                GOOD BURGERS.<br>
                GOOD MOOD.

            </p>


            <p class="footer-description">

                Fresh burgers, bold flavors,<br>
                and good food made for everyone.

            </p>


            <div class="footer-socials">

                <a
                    href="#"
                    aria-label="Facebook"
                >

                    <img
                        src="assets/facebook.png"
                        alt="Facebook"
                    >

                </a>


                <a
                    href="#"
                    aria-label="Instagram"
                >

                    <img
                        src="assets/instagram.png"
                        alt="Instagram"
                    >

                </a>


                <a
                    href="#"
                    aria-label="X"
                >

                    <img
                        src="assets/x-icon.png"
                        alt="X"
                    >

                </a>

            </div>

        </div>



        <!-- QUICK LINKS -->

        <div class="footer-links-area">

            <h3>
                QUICK LINKS
            </h3>


            <a href="index.php#home">
                Home
            </a>


            <a href="view_menu.php">
                Our Menu
            </a>


            <a href="index.php#about">
                About Us
            </a>


            <a href="index.php#identity">
                Gallery
            </a>


            <a href="reviews.php">
                Reviews
            </a>


            <a href="index.php#contact">
                Contact
            </a>

        </div>



        <!-- HOURS -->

        <div class="footer-hours-area">

            <h3>
                HOURS
            </h3>


            <div class="footer-hour-group">

                <strong>
                    MON - FRI
                </strong>

                <span>
                    10:00 AM - 9:00 PM
                </span>

            </div>


            <div class="footer-hour-group">

                <strong>
                    SATURDAY
                </strong>

                <span>
                    10:00 AM - 10:00 PM
                </span>

            </div>


            <div class="footer-hour-group">

                <strong>
                    SUNDAY
                </strong>

                <span>
                    10:00 AM - 8:00 PM
                </span>

            </div>

        </div>



        <!-- CONTACT -->

        <div class="footer-contact-area">


            <p class="footer-big-slogan">

                SAVOR THE<br>
                ULTIMATE <span>BURGER.</span>

            </p>


            <h3>
                CONTACT US
            </h3>


            <div class="footer-contact-item">

                <span class="footer-contact-icon">
                    ☎
                </span>

                <span>
                    +63 994 072 5885
                </span>

            </div>


            <div class="footer-contact-item">

                <span class="footer-contact-icon">
                    ✉
                </span>

                <span>
                    lawrsburgers@gmail.com
                </span>

            </div>


            <div class="footer-contact-item">

                <span class="footer-contact-icon">
                    📍
                </span>

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

            ©
            <span id="year">
                2026
            </span>

            Lawr’s Burgers.
            All Rights Reserved.

        </p>

    </div>


</footer>



<!-- MAIN WEBSITE JAVASCRIPT -->

<script src="js/script.js"></script>


</body>

</html>