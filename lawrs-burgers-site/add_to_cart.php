<?php

session_start();

require_once "config/db.php";


/*
 * Make sure burger_id was sent.
 */
if (!isset($_POST['burger_id'])) {
    die("Error: No burger ID was received.");
}


$burger_id = (int)$_POST['burger_id'];


/*
 * Get burger from database.
 */
$sql = "SELECT id, name, price, image
        FROM burgers
        WHERE id = ?
        AND available = 1
        LIMIT 1";


$stmt = $conn->prepare($sql);


if (!$stmt) {
    die("Database prepare error: " . $conn->error);
}


$stmt->bind_param("i", $burger_id);

$stmt->execute();


$result = $stmt->get_result();


/*
 * Check if burger exists.
 */
if ($result->num_rows === 0) {
    die("Error: Burger not found.");
}


$burger = $result->fetch_assoc();


/*
 * Create cart if it does not exist.
 */
if (!isset($_SESSION['cart'])) {

    $_SESSION['cart'] = array();

}


/*
 * Check if burger is already in cart.
 */
if (isset($_SESSION['cart'][$burger_id])) {

    $_SESSION['cart'][$burger_id]['quantity']++;

} else {

    $_SESSION['cart'][$burger_id] = array(

        'id' => $burger['id'],

        'name' => $burger['name'],

        'price' => $burger['price'],

        'image' => $burger['image'],

        'quantity' => 1

    );

}


/*
 * Go to cart.
 */
header("Location: cart.php");

exit;

?>