<?php

session_start();
require_once "../config/db.php";

/*
|--------------------------------------------------------------------------
| ADMIN LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";

/*
|--------------------------------------------------------------------------
| ADD BURGER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = $_POST["price"] ?? "";
    $category = trim($_POST["category"] ?? "");
    $stock_quantity = $_POST["stock_quantity"] ?? "";
    $available = isset($_POST["available"]) ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $error = "Please enter the burger name.";

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

    } elseif (!isset($_FILES["image"]) || $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE) {

        $error = "Please select a burger image.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | IMAGE UPLOAD
        |--------------------------------------------------------------------------
        */

        $image = $_FILES["image"];

        $allowed_types = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        $max_size = 5 * 1024 * 1024; // 5 MB

        /*
        |--------------------------------------------------------------------------
        | CHECK IMAGE TYPE
        |--------------------------------------------------------------------------
        */

        if (!in_array($image["type"], $allowed_types)) {

            $error = "Invalid image type. Please use JPG, JPEG, PNG, or WEBP.";

        } elseif ($image["size"] > $max_size) {

            $error = "Image is too large. Maximum file size is 5 MB.";

        } elseif ($image["error"] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | CREATE UNIQUE FILE NAME
            |--------------------------------------------------------------------------
            */

            $file_extension = strtolower(
                pathinfo($image["name"], PATHINFO_EXTENSION)
            );

            $safe_name = preg_replace(
                "/[^a-zA-Z0-9-_]/",
                "-",
                strtolower($name)
            );

            $file_name = $safe_name . "-" . time() . "." . $file_extension;

            /*
            |--------------------------------------------------------------------------
            | UPLOAD LOCATION
            |--------------------------------------------------------------------------
            */

            $upload_directory = "../assets/";

            $upload_path = $upload_directory . $file_name;

            /*
            |--------------------------------------------------------------------------
            | MOVE IMAGE
            |--------------------------------------------------------------------------
            */

            if (!move_uploaded_file($image["tmp_name"], $upload_path)) {

                $error = "Failed to save the uploaded image.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | INSERT BURGER INTO DATABASE
                |--------------------------------------------------------------------------
                */

                $price = (float)$price;
                $stock_quantity = (int)$stock_quantity;

                $stmt = $conn->prepare("
                    INSERT INTO burgers
                    (
                        name,
                        description,
                        price,
                        image,
                        category,
                        available,
                        stock_quantity
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$stmt) {

                    /*
                    | If database insert preparation fails,
                    | remove the uploaded image.
                    */

                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }

                    $error = "Database error: " . $conn->error;

                } else {

                    $stmt->bind_param(
                        "ssdssii",
                        $name,
                        $description,
                        $price,
                        $file_name,
                        $category,
                        $available,
                        $stock_quantity
                    );

                    if ($stmt->execute()) {

                        header("Location: burgers.php?added=1");
                        exit;

                    } else {

                        /*
                        | Remove image if database insert fails.
                        */

                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }

                        $error = "Failed to add burger: " . $stmt->error;
                    }

                    $stmt->close();
                }
            }
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

    <title>Add Burger | Lawr's Burgers</title>

    <link
        rel="stylesheet"
        href="../css/admin.css"
    >

    <style>

        /* =====================================================
           ADD BURGER PAGE
        ===================================================== */

        .add-burger-page {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* -----------------------------------------------------
           PAGE HEADER
        ----------------------------------------------------- */

        .add-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .add-page-title h2 {
            margin: 0 0 6px;
            font-size: 32px;
            font-weight: 900;
            color: var(--black);
        }

        .add-page-title p {
            margin: 0;
            color: #888;
            font-size: 14px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 11px 17px;

            background: #ffffff;
            color: #555;

            border: 1px solid #e3dfd8;
            border-radius: 9px;

            text-decoration: none;

            font-size: 13px;
            font-weight: 800;

            transition: all 0.2s ease;
        }

        .back-button:hover {
            background: #f7f3ed;
            color: var(--orange);
            border-color: #ffd39b;
            transform: translateY(-1px);
        }

        /* -----------------------------------------------------
           FORM CARD
        ----------------------------------------------------- */

        .add-burger-card {
            background: #ffffff;

            border: 1px solid #e8e3db;
            border-radius: 18px;

            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);

            overflow: hidden;
        }

        .form-card-header {
            padding: 24px 28px;

            background:
                linear-gradient(
                    135deg,
                    #fff8ed,
                    #ffffff
                );

            border-bottom: 1px solid #eee7dc;
        }

        .form-card-header-inner {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .form-header-icon {
            width: 50px;
            height: 50px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #fff0d8;

            border: 1px solid #ffd9a3;
            border-radius: 13px;

            font-size: 25px;
        }

        .form-card-header h3 {
            margin: 0 0 4px;

            font-size: 19px;
            font-weight: 900;

            color: #252525;
        }

        .form-card-header p {
            margin: 0;

            color: #999;

            font-size: 12px;
        }

        /* -----------------------------------------------------
           FORM BODY
        ----------------------------------------------------- */

        .burger-form {
            padding: 30px;
        }

        .form-grid {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 22px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 12px;

            font-weight: 900;

            color: #333;

            text-transform: uppercase;

            letter-spacing: 0.5px;
        }

        .required {
            color: var(--orange);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {

            width: 100%;

            box-sizing: border-box;

            padding: 13px 14px;

            background: #faf9f7;

            border: 1px solid #ded9d1;

            border-radius: 9px;

            color: #222;

            font-family: inherit;

            font-size: 14px;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .form-group input,
        .form-group select {
            height: 48px;
        }

        .form-group textarea {
            min-height: 120px;

            resize: vertical;

            line-height: 1.5;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {

            background: #ffffff;

            border-color: var(--orange);

            box-shadow:
                0 0 0 3px
                rgba(255, 145, 0, 0.10);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #b4b0aa;
        }

        .input-hint {
            font-size: 11px;
            color: #999;
        }

        /* -----------------------------------------------------
           IMAGE UPLOAD
        ----------------------------------------------------- */

        .image-upload-area {

            display: flex;
            align-items: center;
            gap: 20px;

            padding: 20px;

            background: #faf8f4;

            border: 2px dashed #ded7cd;

            border-radius: 12px;

            transition: all 0.2s ease;
        }

        .image-upload-area:hover {

            border-color: var(--orange);

            background: #fffaf3;
        }

        .image-preview {

            width: 100px;
            height: 100px;

            min-width: 100px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            background: #f1ece4;

            border: 1px solid #e2dcd2;

            border-radius: 12px;

            color: #aaa;

            font-size: 35px;
        }

        .image-preview img {

            width: 100%;
            height: 100%;

            object-fit: contain;

            display: none;
        }

        .image-upload-content {
            flex: 1;
        }

        .image-upload-content strong {

            display: block;

            margin-bottom: 5px;

            color: #333;

            font-size: 14px;

            font-weight: 900;
        }

        .image-upload-content p {

            margin: 0 0 12px;

            color: #999;

            font-size: 11px;

            line-height: 1.5;
        }

        .choose-image-button {

            display: inline-flex;

            align-items: center;
            justify-content: center;

            padding: 9px 15px;

            background: #ffffff;

            color: var(--orange);

            border: 1px solid #ffd29a;

            border-radius: 8px;

            font-size: 12px;

            font-weight: 900;

            cursor: pointer;

            transition: all 0.2s ease;
        }

        .choose-image-button:hover {

            background: var(--orange);

            color: #ffffff;

            transform: translateY(-1px);

            box-shadow:
                0 5px 12px
                rgba(255, 145, 0, 0.20);
        }

        .image-file-name {

            display: block;

            margin-top: 7px;

            color: #777;

            font-size: 11px;

            word-break: break-all;
        }

        #image {
            display: none;
        }

        /* -----------------------------------------------------
           AVAILABILITY
        ----------------------------------------------------- */

        .availability-box {

            grid-column: 1 / -1;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 17px 18px;

            background: #faf8f4;

            border: 1px solid #e8e2d9;

            border-radius: 11px;
        }

        .availability-info {

            display: flex;

            align-items: center;

            gap: 12px;
        }

        .availability-icon {

            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            background: #edf5df;

            border-radius: 9px;

            font-size: 18px;
        }

        .availability-text strong {

            display: block;

            margin-bottom: 3px;

            color: #333;

            font-size: 13px;

            font-weight: 900;
        }

        .availability-text span {

            color: #999;

            font-size: 11px;
        }

        /* -----------------------------------------------------
           TOGGLE
        ----------------------------------------------------- */

        .toggle {

            position: relative;

            width: 52px;
            height: 28px;
        }

        .toggle input {

            opacity: 0;

            width: 0;
            height: 0;
        }

        .toggle-slider {

            position: absolute;

            inset: 0;

            cursor: pointer;

            background: #d7d3cd;

            border-radius: 30px;

            transition: 0.2s ease;
        }

        .toggle-slider::before {

            content: "";

            position: absolute;

            width: 22px;
            height: 22px;

            left: 3px;
            top: 3px;

            background: #ffffff;

            border-radius: 50%;

            box-shadow:
                0 2px 5px
                rgba(0, 0, 0, 0.15);

            transition: 0.2s ease;
        }

        .toggle input:checked + .toggle-slider {
            background: var(--orange);
        }

        .toggle input:checked + .toggle-slider::before {
            transform: translateX(24px);
        }

        /* -----------------------------------------------------
           ERROR
        ----------------------------------------------------- */

        .form-error {

            margin-bottom: 25px;

            padding: 13px 16px;

            background: #fff0f0;

            border: 1px solid #f0cccc;

            border-left: 4px solid #a43838;

            border-radius: 8px;

            color: #a43838;

            font-size: 13px;

            font-weight: 700;
        }

        /* -----------------------------------------------------
           FORM FOOTER
        ----------------------------------------------------- */

        .form-footer {

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 10px;

            margin-top: 30px;

            padding-top: 24px;

            border-top: 1px solid #eee9e2;
        }

        .cancel-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 12px 20px;

            background: #f5f3ef;

            color: #666;

            border: 1px solid #dedad3;

            border-radius: 9px;

            text-decoration: none;

            font-size: 13px;

            font-weight: 800;

            transition: all 0.2s ease;
        }

        .cancel-button:hover {

            background: #ebe8e2;

            color: #333;

            transform: translateY(-1px);
        }

        .save-button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 13px 23px;

            background: var(--orange);

            color: #ffffff;

            border: none;

            border-radius: 9px;

            font-size: 13px;

            font-weight: 900;

            cursor: pointer;

            box-shadow:
                0 5px 15px
                rgba(255, 145, 0, 0.22);

            transition: all 0.2s ease;
        }

        .save-button:hover {

            background: var(--orange-dark);

            transform: translateY(-2px);

            box-shadow:
                0 8px 20px
                rgba(255, 145, 0, 0.30);
        }

        .save-button:active {
            transform: translateY(0);
        }

        /* -----------------------------------------------------
           RESPONSIVE
        ----------------------------------------------------- */

        @media (max-width: 700px) {

            .add-page-header {

                align-items: flex-start;

                flex-direction: column;
            }

            .form-grid {

                grid-template-columns: 1fr;
            }

            .form-group.full-width {

                grid-column: auto;
            }

            .availability-box {

                grid-column: auto;
            }

            .burger-form {

                padding: 20px;
            }

            .image-upload-area {

                align-items: flex-start;

                flex-direction: column;
            }

            .form-footer {

                flex-direction: column-reverse;
            }

            .cancel-button,
            .save-button {

                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="admin-layout">

    <!-- =====================================================
         SIDEBAR
    ====================================================== -->

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


    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="admin-main">

        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <span class="page-label">
                    BURGER MANAGEMENT
                </span>

                <h1>
                    Add Burger
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

            <div class="add-burger-page">

                <!-- PAGE HEADER -->

                <div class="add-page-header">

                    <div class="add-page-title">

                        <h2>
                            Create New Burger
                        </h2>

                        <p>
                            Add a new burger to your menu and manage its inventory.
                        </p>

                    </div>

                    <a
                        href="burgers.php"
                        class="back-button"
                    >
                        ← Back to Burgers
                    </a>

                </div>


                <!-- FORM -->

                <div class="add-burger-card">

                    <div class="form-card-header">

                        <div class="form-card-header-inner">

                            <div class="form-header-icon">
                                🍔
                            </div>

                            <div>

                                <h3>
                                    Burger Information
                                </h3>

                                <p>
                                    Enter the details of the burger you want to add.
                                </p>

                            </div>

                        </div>

                    </div>


                    <form
                        method="POST"
                        action=""
                        enctype="multipart/form-data"
                        class="burger-form"
                    >

                        <?php if ($error !== ""): ?>

                            <div class="form-error">

                                <?php
                                echo htmlspecialchars($error);
                                ?>

                            </div>

                        <?php endif; ?>


                        <div class="form-grid">

                            <!-- NAME -->

                            <div class="form-group">

                                <label for="name">

                                    Burger Name

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    placeholder="e.g. Classic Smash"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["name"] ?? ""
                                    );
                                    ?>"
                                    required
                                >

                            </div>


                            <!-- CATEGORY -->

                            <div class="form-group">

                                <label for="category">

                                    Category

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <select
                                    id="category"
                                    name="category"
                                    required
                                >

                                    <option value="">
                                        Select category
                                    </option>

                                    <option
                                        value="Signature Burgers"
                                        <?php
                                        echo (
                                            ($_POST["category"] ?? "") ===
                                            "Signature Burgers"
                                        )
                                        ? "selected"
                                        : "";
                                        ?>
                                    >
                                        Signature Burgers
                                    </option>

                                    <option
                                        value="Classic Burgers"
                                        <?php
                                        echo (
                                            ($_POST["category"] ?? "") ===
                                            "Classic Burgers"
                                        )
                                        ? "selected"
                                        : "";
                                        ?>
                                    >
                                        Classic Burgers
                                    </option>

                                    <option
                                        value="Premium Burgers"
                                        <?php
                                        echo (
                                            ($_POST["category"] ?? "") ===
                                            "Premium Burgers"
                                        )
                                        ? "selected"
                                        : "";
                                        ?>
                                    >
                                        Premium Burgers
                                    </option>

                                    <option
                                        value="Special Burgers"
                                        <?php
                                        echo (
                                            ($_POST["category"] ?? "") ===
                                            "Special Burgers"
                                        )
                                        ? "selected"
                                        : "";
                                        ?>
                                    >
                                        Special Burgers
                                    </option>

                                </select>

                            </div>


                            <!-- DESCRIPTION -->

                            <div class="form-group full-width">

                                <label for="description">

                                    Description

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    placeholder="Describe the burger, ingredients, flavor, etc."
                                    required
                                ><?php
                                echo htmlspecialchars(
                                    $_POST["description"] ?? ""
                                );
                                ?></textarea>

                                <span class="input-hint">
                                    Keep the description short and appealing to customers.
                                </span>

                            </div>


                            <!-- PRICE -->

                            <div class="form-group">

                                <label for="price">

                                    Price

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    placeholder="149.00"
                                    min="0"
                                    step="0.01"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["price"] ?? ""
                                    );
                                    ?>"
                                    required
                                >

                                <span class="input-hint">
                                    Enter the price in Philippine pesos.
                                </span>

                            </div>


                            <!-- STOCK -->

                            <div class="form-group">

                                <label for="stock_quantity">

                                    Stock Quantity

                                    <span class="required">
                                        *
                                    </span>

                                </label>

                                <input
                                    type="number"
                                    id="stock_quantity"
                                    name="stock_quantity"
                                    placeholder="50"
                                    min="0"
                                    step="1"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $_POST["stock_quantity"] ?? ""
                                    );
                                    ?>"
                                    required
                                >

                                <span class="input-hint">
                                    Number of burgers currently available.
                                </span>

                            </div>


                            <!-- IMAGE UPLOAD -->

                            <div class="form-group full-width">

                                <label>
                                    Burger Image
                                    <span class="required">*</span>
                                </label>

                                <div class="image-upload-area">

                                    <div
                                        class="image-preview"
                                        id="imagePreview"
                                    >

                                        🍔

                                        <img
                                            id="previewImage"
                                            src=""
                                            alt="Burger Preview"
                                        >

                                    </div>

                                    <div class="image-upload-content">

                                        <strong>
                                            Upload Burger Photo
                                        </strong>

                                        <p>
                                            Choose a clear burger image from your computer.
                                            JPG, PNG, and WEBP files are supported.
                                            Maximum size: 5 MB.
                                        </p>

                                        <label
                                            for="image"
                                            class="choose-image-button"
                                        >
                                            Choose Image
                                        </label>

                                        <input
                                            type="file"
                                            id="image"
                                            name="image"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            required
                                        >

                                        <span
                                            class="image-file-name"
                                            id="imageFileName"
                                        >
                                            No image selected
                                        </span>

                                    </div>

                                </div>

                            </div>


                            <!-- AVAILABILITY -->

                            <div class="availability-box">

                                <div class="availability-info">

                                    <div class="availability-icon">
                                        ✓
                                    </div>

                                    <div class="availability-text">

                                        <strong>
                                            Burger Available
                                        </strong>

                                        <span>
                                            Make this burger visible and orderable on the website.
                                        </span>

                                    </div>

                                </div>

                                <label class="toggle">

                                    <input
                                        type="checkbox"
                                        name="available"
                                        value="1"
                                        <?php
                                        echo (
                                            !isset($_POST["available"]) ||
                                            $_POST["available"] == "1"
                                        )
                                        ? "checked"
                                        : "";
                                        ?>
                                    >

                                    <span class="toggle-slider"></span>

                                </label>

                            </div>

                        </div>


                        <!-- BUTTONS -->

                        <div class="form-footer">

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
                                <span>＋</span>
                                Add Burger
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </section>


        <!-- FOOTER -->

        <footer class="admin-footer">

            <p>
                © <?php echo date("Y"); ?> Lawr's Burgers. Admin Panel.
            </p>

        </footer>

    </main>

</div>


<!-- =========================================================
     IMAGE PREVIEW JAVASCRIPT
========================================================= -->

<script>

const imageInput = document.getElementById("image");
const previewImage = document.getElementById("previewImage");
const imagePreview = document.getElementById("imagePreview");
const imageFileName = document.getElementById("imageFileName");

imageInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {

        previewImage.style.display = "none";

        imageFileName.textContent = "No image selected";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW FILE NAME
    |--------------------------------------------------------------------------
    */

    imageFileName.textContent = file.name;


    /*
    |--------------------------------------------------------------------------
    | SHOW IMAGE PREVIEW
    |--------------------------------------------------------------------------
    */

    const reader = new FileReader();

    reader.onload = function (event) {

        previewImage.src = event.target.result;

        previewImage.style.display = "block";

    };

    reader.readAsDataURL(file);

});

</script>

</body>

</html>

<?php

$conn->close();

?>