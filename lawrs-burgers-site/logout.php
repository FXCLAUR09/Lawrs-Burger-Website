<?php

session_start();

/*
|--------------------------------------------------------------------------
| Save the current customer's cart
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["customer_id"])) {

    $customer_id = (int)$_SESSION["customer_id"];

    if (!isset($_SESSION["customer_carts"])) {
        $_SESSION["customer_carts"] = array();
    }

    $_SESSION["customer_carts"][$customer_id] =
        $_SESSION["cart"] ?? array();
}

/*
|--------------------------------------------------------------------------
| Remove account login information
|--------------------------------------------------------------------------
*/

unset(
    $_SESSION["admin_id"],
    $_SESSION["admin_username"],
    $_SESSION["customer_id"],
    $_SESSION["customer_name"],
    $_SESSION["customer_email"]
);

/*
|--------------------------------------------------------------------------
| Remove the active cart
|--------------------------------------------------------------------------
|
| The cart has already been saved above under the
| customer's own ID.
|
*/

unset($_SESSION["cart"]);

/*
|--------------------------------------------------------------------------
| Return to login
|--------------------------------------------------------------------------
*/

header("Location: login.php");
exit;

?>