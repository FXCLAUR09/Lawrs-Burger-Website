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
| Get Existing Burger
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        description,
        price,
        image,
        category,
        available,
        stock_quantity
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
| Default Values
|--------------------------------------------------------------------------
*/

$name = $burger["name"];
$description = $burger["description"];
$price = $burger["price"];
$category = $burger["category"];
$stock_quantity = $burger["stock_quantity"];
$available = (int)$burger["available"];

$error = "";

/*
|--------------------------------------------------------------------------
| Update Burger
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $stock_quantity = trim($_POST["stock_quantity"] ?? "");

    $available = isset($_POST["available"]) ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | Validate Form
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $error = "Please enter a burger name.";

    } elseif ($description === "") {

        $error = "Please enter a burger description.";

    } elseif ($price === "" || !is_numeric($price) || (float)$price < 0) {

        $error = "Please enter a valid price.";

    } elseif ($category === "") {

        $error = "Please select a category.";

    } elseif (
        $stock_quantity === "" ||
        !is_numeric($stock_quantity) ||
        (int)$stock_quantity < 0
    ) {

        $error = "Please enter a valid stock quantity.";

    } else {

        $price = (float)$price;
        $stock_quantity = (int)$stock_quantity;

        /*
        |--------------------------------------------------------------------------
        | Check if New Image Was Uploaded
        |--------------------------------------------------------------------------
        */

        $new_image_uploaded = false;
        $new_file_name = $burger["image"];
        $new_upload_path = "";

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            $image = $_FILES["image"];

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $max_size = 5 * 1024 * 1024;

            if (!in_array($image["type"], $allowed_types)) {

                $error = "Invalid image type. Please use JPG, JPEG, PNG, or WEBP.";

            } elseif ($image["size"] > $max_size) {

                $error = "Image is too large. Maximum file size is 5 MB.";

            } elseif ($image["error"] !== UPLOAD_ERR_OK) {

                $error = "There was a problem uploading the image.";

            } else {

                $file_extension = strtolower(
                    pathinfo($image["name"], PATHINFO_EXTENSION)
                );

                $safe_name = preg_replace(
                    "/[^a-zA-Z0-9-_]/",
                    "-",
                    strtolower($name)
                );

                $new_file_name =
                    $safe_name . "-" . time() . "." . $file_extension;

                $upload_directory = "../assets/";
                $new_upload_path = $upload_directory . $new_file_name;

                if (!move_uploaded_file(
                    $image["tmp_name"],
                    $new_upload_path
                )) {

                    $error = "Failed to save the uploaded image.";

                } else {

                    $new_image_uploaded = true;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        if ($error === "") {

            $stmt = $conn->prepare("
                UPDATE burgers
                SET
                    name = ?,
                    description = ?,
                    price = ?,
                    image = ?,
                    category = ?,
                    available = ?,
                    stock_quantity = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssdssiii",
                $name,
                $description,
                $price,
                $new_file_name,
                $category,
                $available,
                $stock_quantity,
                $burger_id
            );

            if ($stmt->execute()) {

                /*
                |--------------------------------------------------------------------------
                | Delete Old Image
                |--------------------------------------------------------------------------
                */

                if (
                    $new_image_uploaded &&
                    !empty($burger["image"])
                ) {

                    $old_image_path = "../assets/" . $burger["image"];

                    if (
                        file_exists($old_image_path) &&
                        $burger["image"] !== $new_file_name
                    ) {
                        unlink($old_image_path);
                    }
                }

                $stmt->close();

                header("Location: burgers.php?updated=1");
                exit;

            } else {

                /*
                |--------------------------------------------------------------------------
                | Remove New Image If Database Update Failed
                |--------------------------------------------------------------------------
                */

                if (
                    $new_image_uploaded &&
                    file_exists($new_upload_path)
                ) {
                    unlink($new_upload_path);
                }

                $error = "Failed to update burger: " . $stmt->error;
            }

            $stmt->close();
        }
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

    <title>Edit Burger | Lawr's Burgers</title>

    <link rel="stylesheet" href="../css/admin.css">

</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->

    <aside class="admin-sidebar">

        <div class="sidebar-brand">

            <div class="brand-main">
                LAWR'S
            </div>

            <div class="brand-sub">
                BURGERS
            </div>

            <div class="sidebar-label">
                ADMIN PANEL
            </div>

        </div>

        <nav class="sidebar-nav">

            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>

            <a href="burgers.php" class="nav-item active">
                <span class="nav-icon">🍔</span>
                <span>Burgers</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">📦</span>
                <span>Orders</span>
            </a>

            <a href="#" class="nav-item">
                <span class="nav-icon">📈</span>
                <span>Reports</span>
            </a>

            <div class="sidebar-divider"></div>

            <a href="../index.php" class="nav-item">
                <span class="nav-icon">🌐</span>
                <span>View Website</span>
            </a>

            <a href="logout.php" class="nav-item logout-link">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>

        </nav>

        <div class="sidebar-footer">

            <p>Lawr's Burgers</p>

            <span>
                Admin Management System
            </span>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div>

                <span class="page-label">
                    ADMIN PANEL
                </span>

                <h1>
                    Edit Burger
                </h1>

            </div>

            <div class="admin-user">

                <div class="user-avatar">

                    <?php
                    echo strtoupper(
                        substr(
                            $_SESSION["admin_username"],
                            0,
                            1
                        )
                    );
                    ?>

                </div>

                <div class="user-info">

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $_SESSION["admin_username"]
                        );
                        ?>
                    </strong>

                    <span>
                        Administrator
                    </span>

                </div>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="admin-content">

            <div class="content-header">

                <div>

                    <h2>
                        Edit Burger
                    </h2>

                    <p>
                        Update the information, price, stock, or image of this burger.
                    </p>

                </div>

            </div>


            <?php if ($error !== ""): ?>

                <div class="login-error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <div class="admin-card">

                <form
                    method="POST"
                    action=""
                    enctype="multipart/form-data"
                    class="burger-form"
                >

                    <!-- BURGER NAME -->

                    <div class="form-group">

                        <label for="name">
                            Burger Name
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            required
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            required
                        ><?php echo htmlspecialchars($description); ?></textarea>

                    </div>


                    <!-- PRICE + CATEGORY -->

                    <div class="form-row">

                        <div class="form-group">

                            <label for="price">
                                Price
                            </label>

                            <input
                                type="number"
                                id="price"
                                name="price"
                                step="0.01"
                                min="0"
                                value="<?php echo htmlspecialchars($price); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="category">
                                Category
                            </label>

                            <select
                                id="category"
                                name="category"
                                required
                            >

                                <option
                                    value="Signature Burgers"
                                    <?php
                                    echo ($category === "Signature Burgers")
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Signature Burgers
                                </option>

                                <option
                                    value="Classic Burgers"
                                    <?php
                                    echo ($category === "Classic Burgers")
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Classic Burgers
                                </option>

                                <option
                                    value="Special Burgers"
                                    <?php
                                    echo ($category === "Special Burgers")
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Special Burgers
                                </option>

                                <option
                                    value="Chicken Burgers"
                                    <?php
                                    echo ($category === "Chicken Burgers")
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Chicken Burgers
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- STOCK -->

                    <div class="form-group">

                        <label for="stock_quantity">
                            Stock Quantity
                        </label>

                        <input
                            type="number"
                            id="stock_quantity"
                            name="stock_quantity"
                            min="0"
                            value="<?php echo htmlspecialchars($stock_quantity); ?>"
                            required
                        >

                    </div>


                    <!-- CURRENT IMAGE -->

                    <div class="form-group">

                        <label>
                            Current Burger Image
                        </label>

                        <div style="
                            width: 180px;
                            height: 180px;
                            border: 1px solid #ddd;
                            border-radius: 10px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            overflow: hidden;
                            background: #f8f8f8;
                        ">

                            <?php if (!empty($burger["image"])): ?>

                                <img
                                    src="../assets/<?php echo htmlspecialchars($burger["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($burger["name"]); ?>"
                                    style="
                                        width: 100%;
                                        height: 100%;
                                        object-fit: contain;
                                    "
                                >

                            <?php else: ?>

                                <span style="font-size: 50px;">
                                    🍔
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- NEW IMAGE -->

                    <div class="form-group">

                        <label for="image">
                            Replace Image
                        </label>

                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >

                        <small>
                            Leave this empty if you want to keep the current image.
                        </small>

                    </div>


                    <!-- AVAILABILITY -->

                    <div class="form-group">

                        <label class="checkbox-label">

                            <input
                                type="checkbox"
                                name="available"
                                value="1"
                                <?php
                                echo ($available === 1)
                                    ? "checked"
                                    : "";
                                ?>
                            >

                            <span>
                                Burger is available for customers
                            </span>

                        </label>

                    </div>


                    <!-- BUTTONS -->

                    <div class="form-actions">

                        <a
                            href="burgers.php"
                            class="cancel-button"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="save-button"
                        >
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </section>


        <footer class="admin-footer">

            <p>
                © <?php echo date("Y"); ?> Lawr's Burgers. Admin Panel.
            </p>

        </footer>

    </main>

</div>

</body>

</html>

<?php

$conn->close();

?>