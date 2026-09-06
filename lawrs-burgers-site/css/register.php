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

    <style>

        /*
         * REGISTER PAGE
         */

        .register-page {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px 20px;

            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.72),
                    rgba(0, 0, 0, 0.72)
                ),
                url("assets/about-burger.jpg");

            background-size: cover;

            background-position: center;

            background-attachment: fixed;
        }



        .register-card {

            width: 100%;

            max-width: 480px;

            background: #fffaf0;

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.35);
        }



        /*
         * BRAND
         */

        .register-brand {

            text-align: center;

            padding: 30px 25px 24px;

            background: #111;
        }



        .register-brand .brand-main {

            font-family: "Bebas Neue", sans-serif;

            font-size: 52px;

            line-height: 0.85;

            color: #ff9100;

            letter-spacing: 2px;
        }



        .register-brand .brand-sub {

            font-family: "Bebas Neue", sans-serif;

            font-size: 25px;

            line-height: 1;

            color: #ffffff;

            letter-spacing: 5px;
        }



        .customer-label {

            margin: 12px 0 0;

            font-family: "DM Sans", sans-serif;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: 2px;

            color: #ff9100;
        }



        /*
         * CONTENT
         */

        .register-content {

            padding: 32px 35px 30px;
        }



        .register-content h1 {

            margin: 0;

            font-family: "Bebas Neue", sans-serif;

            font-size: 38px;

            line-height: 1;

            color: #111;

            letter-spacing: 1px;
        }



        .register-description {

            margin: 8px 0 25px;

            font-family: "DM Sans", sans-serif;

            font-size: 14px;

            color: #666;

            line-height: 1.5;
        }



        /*
         * ERROR / SUCCESS
         */

        .register-error {

            margin-bottom: 20px;

            padding: 12px 14px;

            border-radius: 8px;

            background: #f8d7da;

            border: 1px solid #f1aeb5;

            color: #842029;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            line-height: 1.4;
        }



        .register-success {

            margin-bottom: 20px;

            padding: 12px 14px;

            border-radius: 8px;

            background: #d1e7dd;

            border: 1px solid #a3cfbb;

            color: #0f5132;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            line-height: 1.4;
        }



        /*
         * FORM
         */

        .register-form-group {

            margin-bottom: 18px;
        }



        .register-form-group label {

            display: block;

            margin-bottom: 7px;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 700;

            color: #222;
        }



        .register-form-group input {

            width: 100%;

            box-sizing: border-box;

            padding: 13px 14px;

            border: 1px solid #d6d0c5;

            border-radius: 8px;

            background: #ffffff;

            color: #222;

            font-family: "DM Sans", sans-serif;

            font-size: 14px;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }



        .register-form-group input:focus {

            border-color: #ff9100;

            box-shadow:
                0 0 0 3px rgba(255, 145, 0, 0.15);
        }



        .register-form-group input::placeholder {

            color: #999;
        }



        .password-note {

            margin-top: 6px;

            font-family: "DM Sans", sans-serif;

            font-size: 11px;

            color: #777;
        }



        /*
         * BUTTON
         */

        .register-button {

            width: 100%;

            margin-top: 5px;

            padding: 14px 20px;

            border: none;

            border-radius: 8px;

            background: #ff9100;

            color: #111;

            font-family: "Bebas Neue", sans-serif;

            font-size: 22px;

            letter-spacing: 1px;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                background 0.2s ease,
                box-shadow 0.2s ease;
        }



        .register-button:hover {

            background: #e67f00;

            transform: translateY(-2px);

            box-shadow:
                0 8px 18px rgba(255, 145, 0, 0.25);
        }



        .register-button:active {

            transform: translateY(0);
        }



        /*
         * FOOTER
         */

        .register-footer {

            padding: 18px 25px 25px;

            text-align: center;

            border-top: 1px solid #e7e0d5;
        }



        .register-footer p {

            margin: 0 0 10px;

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            color: #777;
        }



        .register-footer a {

            font-family: "DM Sans", sans-serif;

            font-size: 13px;

            font-weight: 700;

            color: #8b1e1e;

            text-decoration: none;

            transition: color 0.2s ease;
        }



        .register-footer a:hover {

            color: #ff9100;
        }



        .back-website {

            display: inline-block;

            margin-top: 5px;

            color: #555 !important;

            font-weight: 500 !important;
        }



        .back-website:hover {

            color: #ff9100 !important;
        }



        /*
         * MOBILE
         */

        @media (max-width: 600px) {

            .register-page {

                padding: 20px 14px;

                background-attachment: scroll;
            }



            .register-card {

                max-width: 100%;
            }



            .register-brand {

                padding: 25px 20px 20px;
            }



            .register-brand .brand-main {

                font-size: 46px;
            }



            .register-brand .brand-sub {

                font-size: 22px;
            }



            .register-content {

                padding: 28px 24px 25px;
            }



            .register-content h1 {

                font-size: 34px;
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
```
