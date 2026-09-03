<?php

session_start();
require_once "config/db.php";

/*
 * If already logged in as admin,
 * go directly to the admin dashboard.
 */
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

/*
 * If already logged in as customer,
 * go directly to the customer homepage.
 */
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

/*
 * Process login.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
     * The same input is used for:
     * - Admin username
     * - Customer email
     */
    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login === "" || $password === "") {

        $error = "Please enter your username/email and password.";

    } else {

        /*
         * =====================================================
         * CHECK ADMIN ACCOUNT FIRST
         * =====================================================
         */

        $stmt = $conn->prepare("
            SELECT id, username, password
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param("s", $login);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $admin = $result->fetch_assoc();

                /*
                 * Verify admin password.
                 */
                if (password_verify($password, $admin["password"])) {

                    /*
                     * Prevent session fixation.
                     */
                    session_regenerate_id(true);

                    /*
                     * Remove any customer session.
                     */
                    unset(
                        $_SESSION["customer_id"],
                        $_SESSION["customer_name"],
                        $_SESSION["customer_email"]
                    );

                    /*
                     * Create admin session.
                     */
                    $_SESSION["admin_id"] = $admin["id"];
                    $_SESSION["admin_username"] = $admin["username"];

                    /*
                     * Send admin to dashboard.
                     */
                    header("Location: admin/dashboard.php");
                    exit;

                } else {

                    $error = "Invalid username/email or password.";

                }

            } else {

                /*
                 * Admin account was not found.
                 * We will now check the customer table.
                 */

                $stmt->close();

                /*
                 * =====================================================
                 * CHECK CUSTOMER ACCOUNT
                 * =====================================================
                 */

                $customer_stmt = $conn->prepare("
                    SELECT id, name, email, password
                    FROM customers
                    WHERE email = ?
                    LIMIT 1
                ");

                if ($customer_stmt) {

                    $customer_stmt->bind_param("s", $login);
                    $customer_stmt->execute();

                    $customer_result = $customer_stmt->get_result();

                    if ($customer_result->num_rows === 1) {

                        $customer = $customer_result->fetch_assoc();

                        /*
                         * Verify customer password.
                         */
                        if (password_verify(
                            $password,
                            $customer["password"]
                        )) {

                            /*
                             * Prevent session fixation.
                             */
                            session_regenerate_id(true);

                            /*
                             * Remove any admin session.
                             */
                            unset(
                                $_SESSION["admin_id"],
                                $_SESSION["admin_username"]
                            );

                            $_SESSION["customer_id"] = $customer["id"];
                            $_SESSION["customer_name"] = $customer["name"];
                            $_SESSION["customer_email"] = $customer["email"];

                            /*
                            |--------------------------------------------------------------------------
                            | Load this customer's own cart
                            |--------------------------------------------------------------------------
                            */

                            if (!isset($_SESSION["customer_carts"])) {
                                $_SESSION["customer_carts"] = array();
                            }

                            $customer_id = (int)$customer["id"];

                            if (isset($_SESSION["customer_carts"][$customer_id])) {

                                $_SESSION["cart"] = $_SESSION["customer_carts"][$customer_id];

                            } else {

                                $_SESSION["cart"] = array();
                            }

                            header("Location: index.php");
                            exit;

                        } else {

                            $error = "Invalid username/email or password.";

                        }

                    } else {

                        $error = "Invalid username/email or password.";

                    }

                    $customer_stmt->close();

                } else {

                    $error = "Unable to process login. Please try again.";

                }

            }

        } else {

            $error = "Unable to process login. Please try again.";

        }
    }
}

$conn->close();

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
        content="Login to Lawr's Burgers."
    >

    <title>Login | Lawr's Burgers</title>

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

    <!-- Admin/Login CSS -->
    <link rel="stylesheet" href="css/admin.css">

</head>


<body>

    <div class="login-page">

        <div class="login-card">


            <!-- BRAND -->

            <div class="login-brand">

                <div class="brand-main">
                    LAWR'S
                </div>

                <div class="brand-sub">
                    BURGERS
                </div>

                <p class="admin-label">
                    ACCOUNT LOGIN
                </p>

            </div>


            <!-- LOGIN CONTENT -->

            <div class="login-content">

                <h1>
                    Welcome Back
                </h1>

                <p class="login-description">
                    Sign in to your Lawr's Burgers account.
                </p>


                <!-- ERROR MESSAGE -->

                <?php if ($error !== ""): ?>

                    <div class="login-error">

                        <?php
                        echo htmlspecialchars($error);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- LOGIN FORM -->

                <form
                    method="POST"
                    action=""
                >


                    <!-- USERNAME / EMAIL -->

                    <div class="form-group">

                        <label for="login">
                            Username or Email
                        </label>

                        <input
                            type="text"
                            id="login"
                            name="login"
                            placeholder="Enter username or email"
                            autocomplete="username"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter password"
                            autocomplete="current-password"
                            required
                        >

                    </div>


                    <!-- LOGIN BUTTON -->

                    <button
                        type="submit"
                        class="login-button"
                    >
                        LOGIN
                    </button>


                </form>


            </div>


            <!-- FOOTER -->

            <div class="login-footer">

                <p>
                    Don't have an account?
                </p>

                <a href="register.php" class="register-link">
                    CREATE AN ACCOUNT
                </a>

                <a href="index.php" class="back-link">
                    ← Back to Website
                </a>

            </div>

        </div>

    </div>

</body>

</html>