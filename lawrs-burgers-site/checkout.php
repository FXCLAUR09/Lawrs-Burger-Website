<?php

session_start();

require_once "config/db.php";

/*
|--------------------------------------------------------------------------
| Get Logged-In Customer Information
|--------------------------------------------------------------------------
*/

$customer_name = "";

if (isset($_SESSION["customer_id"])) {

    $customer_id = (int)$_SESSION["customer_id"];

    $customer_stmt = $conn->prepare("
        SELECT name, email
        FROM customers
        WHERE id = ?
        LIMIT 1
    ");

    if ($customer_stmt) {

        $customer_stmt->bind_param(
            "i",
            $customer_id
        );

        $customer_stmt->execute();

        $customer_result = $customer_stmt->get_result();

        if ($customer_result->num_rows === 1) {

            $customer = $customer_result->fetch_assoc();

            $customer_name = $customer["name"];
        }

        $customer_stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Make sure the cart exists
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = array();
}

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$error = "";
$order_success = false;
$order_id = null;

/*
|--------------------------------------------------------------------------
| Calculate Cart Total
|--------------------------------------------------------------------------
*/

$total = 0;

foreach ($_SESSION["cart"] as $item) {

    $price = isset($item["price"])
        ? (float)$item["price"]
        : 0;

    $quantity = isset($item["quantity"])
        ? (int)$item["quantity"]
        : 0;

    $total += $price * $quantity;
}

/*
|--------------------------------------------------------------------------
| Process Order
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    |--------------------------------------------------------------------------
    | Get Customer Information
    |--------------------------------------------------------------------------
    */

    $full_name = trim($_POST["full_name"] ?? "");
    $contact_number = trim($_POST["contact_number"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $payment_method = trim($_POST["payment_method"] ?? "");

    /*
    |--------------------------------------------------------------------------
    | Validate Cart and Customer Information
    |--------------------------------------------------------------------------
    */

    if (count($_SESSION["cart"]) === 0) {

        $error = "Your cart is empty. Please add a burger before checking out.";

    } elseif ($full_name === "") {

        $error = "Please enter your full name.";

    } elseif ($contact_number === "") {

        $error = "Please enter your contact number.";

    } elseif ($address === "") {

        $error = "Please enter your delivery address.";

    } elseif (
        $payment_method !== "Cash on Delivery" &&
        $payment_method !== "GCash"
    ) {

        $error = "Please select a valid payment method.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Start Database Transaction
        |--------------------------------------------------------------------------
        */

        $conn->begin_transaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Recalculate Total
            |--------------------------------------------------------------------------
            */

            $order_total = 0;

            foreach ($_SESSION["cart"] as $item) {

                $price = isset($item["price"])
                    ? (float)$item["price"]
                    : 0;

                $quantity = isset($item["quantity"])
                    ? (int)$item["quantity"]
                    : 0;

                if ($quantity <= 0) {

                    throw new Exception(
                        "Invalid quantity found in your cart."
                    );
                }

                $order_total += $price * $quantity;
            }


            /*
            |--------------------------------------------------------------------------
            | Insert Order
            |--------------------------------------------------------------------------
            */

            $status = "Pending";

            $notes = "";

            $stmt = $conn->prepare("
                INSERT INTO orders
                (
                    customer_name,
                    phone,
                    address,
                    notes,
                    payment_method,
                    total,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {

                throw new Exception(
                    "Failed to prepare order."
                );
            }

            $stmt->bind_param(
                "sssssds",
                $full_name,
                $contact_number,
                $address,
                $notes,
                $payment_method,
                $order_total,
                $status
            );

            if (!$stmt->execute()) {

                throw new Exception(
                    "Failed to save order: " . $stmt->error
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Get New Order ID
            |--------------------------------------------------------------------------
            */

            $order_id = $conn->insert_id;

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Prepare Statements
            |--------------------------------------------------------------------------
            */

            $find_burger = $conn->prepare("
                SELECT
                    id,
                    name,
                    price,
                    stock_quantity,
                    available
                FROM burgers
                WHERE id = ?
                FOR UPDATE
            ");

            if (!$find_burger) {

                throw new Exception(
                    "Failed to prepare burger stock check."
                );
            }


            $item_stmt = $conn->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    burger_id,
                    burger_name,
                    price,
                    quantity,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$item_stmt) {

                throw new Exception(
                    "Failed to prepare order item."
                );
            }


            $stock_stmt = $conn->prepare("
                UPDATE burgers
                SET
                    stock_quantity = stock_quantity - ?
                WHERE id = ?
                  AND stock_quantity >= ?
            ");

            if (!$stock_stmt) {

                throw new Exception(
                    "Failed to prepare stock update."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Process Each Cart Item
            |--------------------------------------------------------------------------
            */

            foreach ($_SESSION["cart"] as $item) {

                /*
                |--------------------------------------------------------------------------
                | Get Burger ID
                |--------------------------------------------------------------------------
                */

                if (isset($item["id"])) {

                    $burger_id = (int)$item["id"];

                } elseif (isset($item["burger_id"])) {

                    $burger_id = (int)$item["burger_id"];

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Find Burger ID Using Burger Name
                    |--------------------------------------------------------------------------
                    */

                    $burger_name_search = isset($item["name"])
                        ? $item["name"]
                        : "";

                    $name_stmt = $conn->prepare("
                        SELECT id
                        FROM burgers
                        WHERE name = ?
                        LIMIT 1
                        FOR UPDATE
                    ");

                    if (!$name_stmt) {

                        throw new Exception(
                            "Failed to find burger."
                        );
                    }

                    $name_stmt->bind_param(
                        "s",
                        $burger_name_search
                    );

                    $name_stmt->execute();

                    $name_result = $name_stmt->get_result();

                    if ($name_result->num_rows !== 1) {

                        $name_stmt->close();

                        throw new Exception(
                            "Burger '" .
                            $burger_name_search .
                            "' could not be found."
                        );
                    }

                    $burger_data =
                        $name_result->fetch_assoc();

                    $burger_id =
                        (int)$burger_data["id"];

                    $name_stmt->close();
                }


                /*
                |--------------------------------------------------------------------------
                | Get Cart Information
                |--------------------------------------------------------------------------
                */

                $cart_name = isset($item["name"])
                    ? $item["name"]
                    : "Burger";

                $cart_price = isset($item["price"])
                    ? (float)$item["price"]
                    : 0;

                $quantity = isset($item["quantity"])
                    ? (int)$item["quantity"]
                    : 1;

                if ($quantity <= 0) {

                    throw new Exception(
                        "Invalid quantity for " .
                        $cart_name .
                        "."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Get Current Burger Stock
                |--------------------------------------------------------------------------
                */

                $find_burger->bind_param(
                    "i",
                    $burger_id
                );

                $find_burger->execute();

                $burger_result =
                    $find_burger->get_result();

                if ($burger_result->num_rows !== 1) {

                    throw new Exception(
                        "Burger '" .
                        $cart_name .
                        "' could not be found in the database."
                    );
                }

                $burger = $burger_result->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | Check Availability
                |--------------------------------------------------------------------------
                */

                if ((int)$burger["available"] !== 1) {

                    throw new Exception(
                        $burger["name"] .
                        " is currently unavailable."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Check Stock
                |--------------------------------------------------------------------------
                */

                $current_stock =
                    (int)$burger["stock_quantity"];

                if ($current_stock < $quantity) {

                    throw new Exception(
                        "Not enough stock for " .
                        $burger["name"] .
                        ". Only " .
                        $current_stock .
                        " left in stock."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Use Database Burger Information
                |--------------------------------------------------------------------------
                */

                $burger_name =
                    $burger["name"];

                $price =
                    (float)$burger["price"];

                $subtotal =
                    $price * $quantity;


                /*
                |--------------------------------------------------------------------------
                | Insert Order Item
                |--------------------------------------------------------------------------
                */

                $item_stmt->bind_param(
                    "iisdid",
                    $order_id,
                    $burger_id,
                    $burger_name,
                    $price,
                    $quantity,
                    $subtotal
                );

                if (!$item_stmt->execute()) {

                    throw new Exception(
                        "Failed to save order item: " .
                        $item_stmt->error
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Deduct Stock
                |--------------------------------------------------------------------------
                */

                $stock_stmt->bind_param(
                    "iii",
                    $quantity,
                    $burger_id,
                    $quantity
                );

                if (!$stock_stmt->execute()) {

                    throw new Exception(
                        "Failed to update stock for " .
                        $burger_name .
                        "."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Make Sure Stock Was Actually Updated
                |--------------------------------------------------------------------------
                */

                if ($stock_stmt->affected_rows !== 1) {

                    throw new Exception(
                        "Stock update failed for " .
                        $burger_name .
                        "."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Close Statements
            |--------------------------------------------------------------------------
            */

            $find_burger->close();
            $item_stmt->close();
            $stock_stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Commit Transaction
            |--------------------------------------------------------------------------
            */

            $conn->commit();


            /*
            |--------------------------------------------------------------------------
            | Clear Cart
            |--------------------------------------------------------------------------
            */

            $_SESSION["cart"] = array();


            /*
            |--------------------------------------------------------------------------
            | Redirect to Success Page
            |--------------------------------------------------------------------------
            */

            header(
                "Location: checkout.php?success=1&order_id=" .
                $order_id
            );

            exit;


        } catch (Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Roll Back Everything
            |--------------------------------------------------------------------------
            */

            $conn->rollback();

            $error = $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Successful Order
|--------------------------------------------------------------------------
*/

if (
    isset($_GET["success"]) &&
    $_GET["success"] === "1" &&
    isset($_GET["order_id"])
) {

    $order_success = true;

    $order_id = (int)$_GET["order_id"];
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
        Checkout | Lawr's Burgers
    </title>


    <!-- Google Fonts -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&family=Pacifico&display=swap"
        rel="stylesheet"
    >


    <!-- Main Website CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="checkout-body">


<!-- ================= HEADER ================= -->

<header class="site-header">

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

        <a href="index.php">
            HOME
        </a>

        <a href="index.php#menu">
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

        <a
            class="nav-cta"
            href="index.php#order"
        >

            ORDER NOW

            <span>
                →
            </span>

        </a>

    </nav>

</header>



<!-- ================= CHECKOUT ================= -->

<main class="checkout-page">

    <div class="checkout-container">


        <?php if ($order_success): ?>

            <!-- ================= SUCCESS ================= -->

            <div class="checkout-heading">

                <p class="script">
                    thank you
                </p>

                <h1>
                    ORDER PLACED!
                </h1>

                <p>
                    YOUR BURGER ORDER HAS BEEN RECEIVED.
                </p>

            </div>


            <section class="checkout-card order-success-card">

                <div class="checkout-card-heading">

                    <span>
                        ✓
                    </span>

                    <h2>
                        ORDER CONFIRMED
                    </h2>

                </div>


                <div class="order-success-content">

                    <h3>
                        THANK YOU FOR ORDERING!
                    </h3>

                    <p>
                        Your order has been successfully placed.
                    </p>

                    <div class="order-number">

                        ORDER #

                        <?php echo $order_id; ?>

                    </div>

                    <p>
                        Please keep your order number for reference.
                    </p>


                    <div class="success-actions">

                        <a
                            href="index.php"
                            class="continue-btn"
                        >
                            BACK TO HOME
                        </a>

                        <a
                            href="index.php#menu"
                            class="back-cart-btn"
                        >
                            ORDER MORE
                        </a>

                    </div>

                </div>

            </section>


        <?php else: ?>


            <!-- ================= PAGE TITLE ================= -->

            <div class="checkout-heading">

                <p class="script">
                    almost there
                </p>

                <h1>
                    CHECKOUT
                </h1>

                <p>
                    COMPLETE YOUR ORDER AND GET READY TO TASTE THE DIFFERENCE.
                </p>

            </div>


            <?php if ($error !== ""): ?>

                <div class="checkout-error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <div class="checkout-grid">


                <!-- ================= CUSTOMER INFORMATION ================= -->

                <section class="checkout-card">

                    <div class="checkout-card-heading">

                        <span>
                            01
                        </span>

                        <h2>
                            CUSTOMER INFORMATION
                        </h2>

                    </div>


                    <form
                        action="checkout.php"
                        method="POST"
                        class="checkout-form"
                    >


                        <label for="full_name">
                            FULL NAME
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            value="<?php
                            echo htmlspecialchars(
                                $_POST["full_name"] ?? $customer_name
                            );
                            ?>"
                            required
                        >


                        <label for="contact_number">
                            CONTACT NUMBER
                        </label>

                        <input
                            type="text"
                            id="contact_number"
                            name="contact_number"
                            placeholder="09XXXXXXXXX"
                            value="<?php
                            echo htmlspecialchars(
                                $_POST["contact_number"] ?? ""
                            );
                            ?>"
                            required
                        >


                        <label for="address">
                            DELIVERY ADDRESS
                        </label>

                        <textarea
                            id="address"
                            name="address"
                            placeholder="Enter your complete address"
                            rows="5"
                            required
                        ><?php
                        echo htmlspecialchars(
                            $_POST["address"] ?? ""
                        );
                        ?></textarea>


                        <label for="payment_method">
                            PAYMENT METHOD
                        </label>

                        <select
                            id="payment_method"
                            name="payment_method"
                            required
                        >

                            <option value="">
                                SELECT PAYMENT METHOD
                            </option>

                            <option
                                value="Cash on Delivery"
                                <?php
                                echo (
                                    ($_POST["payment_method"] ?? "")
                                    === "Cash on Delivery"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                CASH ON DELIVERY
                            </option>

                            <option
                                value="GCash"
                                <?php
                                echo (
                                    ($_POST["payment_method"] ?? "")
                                    === "GCash"
                                )
                                    ? "selected"
                                    : "";
                                ?>
                            >
                                GCASH
                            </option>

                        </select>


                        <button
                            type="submit"
                            class="place-order-btn"
                        >

                            PLACE ORDER

                            <span>
                                →
                            </span>

                        </button>

                    </form>

                </section>



                <!-- ================= ORDER SUMMARY ================= -->

                <section class="checkout-card order-summary">

                    <div class="checkout-card-heading">

                        <span>
                            02
                        </span>

                        <h2>
                            YOUR ORDER
                        </h2>

                    </div>


                    <?php if (count($_SESSION["cart"]) > 0): ?>

                        <div class="checkout-items">

                            <?php foreach ($_SESSION["cart"] as $item): ?>

                                <?php

                                $name = isset($item["name"])
                                    ? $item["name"]
                                    : "Burger";

                                $price = isset($item["price"])
                                    ? (float)$item["price"]
                                    : 0;

                                $quantity = isset($item["quantity"])
                                    ? (int)$item["quantity"]
                                    : 1;

                                $subtotal =
                                    $price * $quantity;

                                ?>

                                <div class="checkout-item">

                                    <div class="checkout-item-info">

                                        <h3>
                                            <?php
                                            echo htmlspecialchars(
                                                $name
                                            );
                                            ?>
                                        </h3>

                                        <span>

                                            ₱<?php
                                            echo number_format(
                                                $price,
                                                2
                                            );
                                            ?>

                                            ×

                                            <?php
                                            echo $quantity;
                                            ?>

                                        </span>

                                    </div>


                                    <strong>

                                        ₱<?php
                                        echo number_format(
                                            $subtotal,
                                            2
                                        );
                                        ?>

                                    </strong>

                                </div>

                            <?php endforeach; ?>

                        </div>


                        <div class="checkout-total">

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


                        <a
                            href="cart.php"
                            class="back-cart-btn"
                        >

                            ← EDIT CART

                        </a>


                    <?php else: ?>


                        <div class="checkout-empty">

                            <h3>
                                YOUR CART IS EMPTY
                            </h3>

                            <p>
                                Add some delicious burgers before checking out.
                            </p>

                            <a
                                href="index.php#menu"
                                class="continue-btn"
                            >

                                BROWSE MENU

                            </a>

                        </div>


                    <?php endif; ?>

                </section>

            </div>

        <?php endif; ?>

    </div>

</main>



<!-- ================= JAVASCRIPT ================= -->

<script src="js/script.js"></script>

</body>

</html>

<?php

$conn->close();

?>