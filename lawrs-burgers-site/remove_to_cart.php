<?php

session_start();


if (isset($_GET['id'])) {

    $burger_id = (int) $_GET['id'];

    if (isset($_SESSION['cart'][$burger_id])) {

        unset($_SESSION['cart'][$burger_id]);

    }

}


header("Location: cart.php");

exit;

?>