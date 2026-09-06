<?php

session_start();

require_once "config/db.php";

$error = "";
$success = "";

$name = "";
$email = "";

/*
 * Process customer registration.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    /*
     * Validate required fields.
     */
    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {

        $error = "Please fill in all fields.";

    /*
     * Validate name using Regex.
     *
     * Allows letters, spaces, periods,
     * apostrophes, and hyphens.
     */
    } elseif (!preg_match("/^[a-zA-Z .'-]+$/", $name)) {

        $error = "Name can only contain letters, spaces, periods, apostrophes, and hyphens.";

    /*
     * Validate email.
     */
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    /*
     * Check password length.
     */
    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters long.";

    /*
     * Check maximum password length.
     */
    } elseif (strlen($password) > 255) {

        $error = "Password is too long.";

    /*
     * Check password confirmation.
     */
    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        /*
         * Check if email already exists.
         */

        $stmt = $conn->prepare("
            SELECT id
            FROM customers
            WHERE email = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $error = "An account with this email already exists.";

            } else {

                /*
                 * Securely hash the password.
                 */
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /*
                 * Insert customer account.
                 */
                $insert = $conn->prepare("
                    INSERT INTO customers
                    (name, email, password)
                    VALUES (?, ?, ?)
                ");

                if ($insert) {

                    $insert->bind_param(
                        "sss",
                        $name,
                        $email,
                        $hashed_password
                    );

                    if ($insert->execute()) {

                        /*
                         * Registration successful.
                         */
                        $success = "Account created successfully! You can now log in.";

                        /*
                         * Clear form values.
                         */
                        $name = "";
                        $email = "";

                    } else {

                        $error = "Something went wrong while creating your account. Please try again.";
                    }

                    $insert->close();

                } else {

                    $error = "Unable to create your account. Please try again.";
                }
            }

            $stmt->close();

        } else {

            $error = "Unable to process registration. Please try again.";
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
        content="Create your Lawr's Burgers customer account."
    >

    <title>Create Account | Lawr's Burgers</title>

    <!-- Google Fonts -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
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

    <style>

        /*
         * ==========================================================
         * LAWR'S BURGERS - REGISTER PAGE
         * Glassmorphism design matching login.php
         * ==========================================================
         */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: "DM Sans", sans-serif;
        }


        /*
         * ==========================================================
         * PAGE BACKGROUND
         * ==========================================================
         */

        .register-page {
            position: relative;

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 50px 20px;

            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.72),
                    rgba(0, 0, 0, 0.82)
                ),
                url("assets/about-burger.jpg");

            background-size: cover;
            background-position: center;
            background-attachment: fixed;

            overflow-y: auto;
        }


        /*
         * ==========================================================
         * GLASS CARD
         * ==========================================================
         */

        .register-card {
            position: relative;

            width: 100%;
            max-width: 460px;

            background: rgba(255, 255, 255, 0.10);

            border: 1px solid rgba(255, 255, 255, 0.20);

            border-radius: 20px;

            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.55),
                inset 0 1px 0 rgba(255, 255, 255, 0.10);

            overflow: hidden;
        }


        /*
         * ==========================================================
         * BRAND
         * ==========================================================
         */

        .register-brand {
            text-align: center;

            padding: 32px 25px 25px;

            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .register-brand .brand-main {
            margin: 0;

            font-family: "Bebas Neue", sans-serif;

            font-size: 62px;

            line-height: 0.82;

            color: #ff9700;

            letter-spacing: 3px;

            text-shadow:
                0 4px 15px rgba(255, 151, 0, 0.20);
        }

        .register-brand .brand-sub {
            margin-top: 5px;

            font-family: "Bebas Neue", sans-serif;

            font-size: 26px;

            line-height: 1;

            color: #ffffff;

            letter-spacing: 6px;
        }

        .customer-label {
            margin: 13px 0 0;

            font-family: "DM Sans", sans-serif;

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 2.5px;

            color: #ff9700;

            text-transform: uppercase;
        }


        /*
         * ==========================================================
         * CONTENT
         * ==========================================================
         */

        .register-content {
            padding: 30px 35px 28px;
        }

        .register-content h1 {
            margin: 0;

            font-family: "Bebas Neue", sans-serif;

            font-size: 38px;

            line-height: 1;

            color: #ffffff;

            letter-spacing: 1px;
        }

        .register-description {
            margin: 8px 0 25px;

            font-family: "DM Sans", sans-serif;

            font-size: 14px;

            color: rgba(255, 255, 255, 0.70);

            line-height: 1.5;
        }


        /*
         * ==========================================================
         * ERROR
         * ==========================================================
         */

        .register-error {
            margin-bottom: 20px;

            padding: 12px 14px;

            border-radius: 10px;

            background: rgba(220, 53, 69, 0.16);

            border: 1px solid rgba(255, 100, 110, 0.35);

            color: #ffb5bb;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            line-height: 1.4;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.12);
        }


        /*
         * ==========================================================
         * SUCCESS
         * ==========================================================
         */

        .register-success {
            margin-bottom: 20px;

            padding: 12px 14px;

            border-radius: 10px;

            background: rgba(25, 135, 84, 0.16);

            border: 1px solid rgba(80, 220, 145, 0.30);

            color: #a9f5cc;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            line-height: 1.4;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.12);
        }


        /*
         * ==========================================================
         * FORM
         * ==========================================================
         */

        .register-form-group {
            margin-bottom: 17px;
        }

        .register-form-group label {
            display: block;

            margin-bottom: 7px;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 700;

            color: rgba(255, 255, 255, 0.90);
        }

        .register-form-group input {
            width: 100%;

            padding: 13px 14px;

            border: 1px solid rgba(255, 255, 255, 0.20);

            border-radius: 9px;

            background: rgba(0, 0, 0, 0.30);

            color: #ffffff;

            font-family: "DM Sans", sans-serif;

            font-size: 14px;

            outline: none;

            transition:
                border-color 0.2s ease,
                background 0.2s ease,
                box-shadow 0.2s ease,
                transform 0.2s ease;
        }

        .register-form-group input:hover {
            background: rgba(0, 0, 0, 0.38);

            border-color: rgba(255, 255, 255, 0.28);
        }

        .register-form-group input:focus {
            border-color: #ff9700;

            background: rgba(0, 0, 0, 0.42);

            box-shadow:
                0 0 0 3px rgba(255, 151, 0, 0.14),
                0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .register-form-group input::placeholder {
            color: rgba(255, 255, 255, 0.42);
        }

        .register-form-group input:-webkit-autofill,
        .register-form-group input:-webkit-autofill:hover,
        .register-form-group input:-webkit-autofill:focus {
            -webkit-text-fill-color: #ffffff;

            transition:
                background-color 9999s ease-in-out 0s;
        }


        /*
         * ==========================================================
         * PASSWORD NOTE
         * ==========================================================
         */

        .password-note {
            margin-top: 6px;

            font-family: "DM Sans", sans-serif;

            font-size: 11px;

            color: rgba(255, 255, 255, 0.50);
        }


        /*
         * ==========================================================
         * BUTTON
         * ==========================================================
         */

        .register-button {
            width: 100%;

            margin-top: 5px;

            padding: 14px 20px;

            border: none;

            border-radius: 9px;

            background: #ff9700;

            color: #080808;

            font-family: "Bebas Neue", sans-serif;

            font-size: 22px;

            letter-spacing: 1.5px;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                background 0.2s ease,
                color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .register-button:hover {
            background: #ffffff;

            color: #111111;

            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.30);
        }

        .register-button:active {
            transform: translateY(0);

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.20);
        }


        /*
         * ==========================================================
         * FOOTER
         * ==========================================================
         */

        .register-footer {
            padding: 20px 25px 25px;

            text-align: center;

            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .register-footer p {
            margin: 0 0 10px;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            color: rgba(255, 255, 255, 0.60);
        }

        .register-footer a {
            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 700;

            color: #ff9700;

            text-decoration: none;

            transition:
                color 0.2s ease,
                text-shadow 0.2s ease;
        }

        .register-footer a:hover {
            color: #ffffff;

            text-shadow:
                0 0 10px rgba(255, 255, 255, 0.30);
        }

        .back-website {
            display: inline-block;

            margin-top: 7px;

            color: rgba(255, 255, 255, 0.55) !important;

            font-weight: 500 !important;
        }

        .back-website:hover {
            color: #ff9700 !important;

            text-shadow: none !important;
        }


        /*
         * ==========================================================
         * MOBILE
         * ==========================================================
         */

        @media (max-width: 600px) {

            .register-page {
                padding: 25px 14px;

                background-attachment: scroll;
            }

            .register-card {
                max-width: 100%;

                border-radius: 17px;
            }

            .register-brand {
                padding: 27px 20px 22px;
            }

            .register-brand .brand-main {
                font-size: 50px;
            }

            .register-brand .brand-sub {
                font-size: 22px;

                letter-spacing: 5px;
            }

            .register-content {
                padding: 28px 23px 25px;
            }

            .register-content h1 {
                font-size: 34px;
            }

            .register-description {
                font-size: 13px;
            }

            .register-footer {
                padding: 18px 20px 23px;
            }
        }


        @media (max-width: 380px) {

            .register-brand .brand-main {
                font-size: 45px;
            }

            .register-brand .brand-sub {
                font-size: 20px;
            }

            .register-content {
                padding-left: 19px;
                padding-right: 19px;
            }
        }

    </style>

</head>


<body>

    <div class="register-page">

        <div class="register-card">


            <!-- BRAND -->

            <div class="register-brand">

                <div class="brand-main">
                    LAWR'S
                </div>

                <div class="brand-sub">
                    BURGERS
                </div>

                <p class="customer-label">
                    CUSTOMER ACCOUNT
                </p>

            </div>


            <!-- CONTENT -->

            <div class="register-content">

                <h1>
                    CREATE ACCOUNT
                </h1>

                <p class="register-description">
                    Join Lawr's Burgers and make ordering your favorite burgers easier.
                </p>


                <!-- ERROR -->

                <?php if ($error !== ""): ?>

                    <div class="register-error">

                        <?php
                        echo htmlspecialchars($error);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- SUCCESS -->

                <?php if ($success !== ""): ?>

                    <div class="register-success">

                        <?php
                        echo htmlspecialchars($success);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- REGISTRATION FORM -->

                <form
                    method="POST"
                    action=""
                    autocomplete="on"
                >


                    <!-- NAME -->

                    <div class="register-form-group">

                        <label for="name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            autocomplete="name"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="register-form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            autocomplete="email"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="register-form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                        <div class="password-note">
                            Password must be at least 6 characters.
                        </div>

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="register-form-group">

                        <label for="confirm_password">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="register-button"
                    >
                        CREATE ACCOUNT
                    </button>


                </form>

            </div>


            <!-- FOOTER -->

            <div class="register-footer">

                <p>
                    Already have an account?

                    <a href="login.php">
                        Login here
                    </a>
                </p>

                <a
                    href="index.php"
                    class="back-website"
                >
                    ← Back to Website
                </a>

            </div>


        </div>

    </div>

</body>

</html>