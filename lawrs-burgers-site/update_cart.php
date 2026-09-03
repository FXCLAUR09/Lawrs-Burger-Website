<?php

session_start();

/*
 * Make sure the cart exists.
 */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

/*
 * Make sure the required values were submitted.
 */
if (
    !isset($_POST['burger_id']) ||
    !isset($_POST['quantity'])
) {
    header("Location: cart.php");
    exit;
}

/*
 * Get submitted values.
 */
$burger_id = (int) $_POST['burger_id'];
$quantity = (int) $_POST['quantity'];

/*
 * Quantity must be at least 1.
 */
if ($quantity < 1) {
    $quantity = 1;
}

/*
 * Find the burger inside the session cart.
 *
 * Your cart uses an array of burger information,
 * so we search for the matching burger ID.
 */
foreach ($_SESSION['cart'] as $key => $item) {

    if (
        isset($item['id']) &&
        (int)$item['id'] === $burger_id
    ) {

        $_SESSION['cart'][$key]['quantity'] = $quantity;

        break;
    }
}

/*
 * Return to the cart.
 */
header("Location: cart.php");
exit;

?>