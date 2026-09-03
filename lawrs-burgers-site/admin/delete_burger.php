<?php

session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Burger ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: burgers.php");
    exit;
}

$burger_id = (int)$_GET["id"];

/*
|--------------------------------------------------------------------------
| Get Burger Image Before Deleting
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT image
    FROM burgers
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $burger_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: burgers.php");
    exit;
}

$burger = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Delete Burger
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM burgers
    WHERE id = ?
");

$stmt->bind_param("i", $burger_id);

if ($stmt->execute()) {

    /*
    |--------------------------------------------------------------------------
    | Delete Image File
    |--------------------------------------------------------------------------
    */

    if (!empty($burger["image"])) {

        $image_path = "../assets/" . $burger["image"];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $stmt->close();
    $conn->close();

    header("Location: burgers.php?deleted=1");
    exit;

} else {

    $error = $stmt->error;

    $stmt->close();
    $conn->close();

    die("Failed to delete burger: " . htmlspecialchars($error));
}

?>