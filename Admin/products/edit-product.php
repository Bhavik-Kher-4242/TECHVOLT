<?php

session_start();
require_once "../../includes/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}
$adminPage = 'products';
$adminBase = '../';
$updateSuccess = false;
$updateError = false;
$product_id = $_GET["id"] ?? "";
if (empty($product_id)) {
    die("Product ID is missing.");
}
$sql = "SELECT p.*, c.category_name, c.status AS category_status FROM products p JOIN categories c ON c.category_id = p.category_id WHERE p.product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) == 0) {
    die("Product not found.");
}
$product = mysqli_fetch_assoc($result);

$categories = [];

$categoryListSql = " SELECT category_id, category_name, status FROM categories WHERE status = 'active' ORDER BY category_name ASC ";

$categoryListResult = mysqli_query($conn, $categoryListSql);

if (!$categoryListResult) {
    die("Category list query failed: " . mysqli_error($conn));
}

while ($categoryRow = mysqli_fetch_assoc($categoryListResult)) {
    $categories[] = $categoryRow;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_id = $_POST["product_id"];
    $product_name = trim($_POST["product_name"]);
    $short_description = trim($_POST["short_description"]);
    $category_id = (int) ($_POST["category"] ?? 0);
    $brand = trim($_POST["brand"]);
    $model_number = trim($_POST["model_number"]);
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    $description = trim($_POST["description"]);
    $status = $_POST["status"] ?? "active";
    $spec_names = $_POST["spec_name"] ?? [];
    $spec_values = $_POST["spec_value"] ?? [];
    $primaryNewImage = $_POST["primary_new_image"] ?? "";

    $categorySql = "
    SELECT category_id, category_name
    FROM categories
    WHERE category_id = ?
    AND status = 'active'
";
    $categoryStmt = mysqli_prepare($conn, $categorySql);
    mysqli_stmt_bind_param($categoryStmt, "i", $category_id);
    mysqli_stmt_execute($categoryStmt);
    $categoryResult = mysqli_stmt_get_result($categoryStmt);
    $categoryRow = mysqli_fetch_assoc($categoryResult);
    if (!$categoryRow) {
        die("Invalid or inactive category selected.");
    }
    $category = $categoryRow["category_name"];
    if (!in_array($status, ["active", "inactive"], true)) {
        die("Invalid product status selected.");
    }

    $updateSql = "UPDATE products SET product_name = ?, category_id = ?, brand = ?, model_number = ?, short_description = ?, description = ?, price = ?, stock = ?, status = ? WHERE product_id = ?";
    $updatestmt = mysqli_prepare($conn, $updateSql);
    if (!$updatestmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($updatestmt, "sissssdisi", $product_name, $category_id, $brand, $model_number, $short_description, $description, $price, $stock, $status, $product_id);


    if (mysqli_stmt_execute($updatestmt)) {

        $updateSuccess = true;
    } else {

        $updateError = true;
    }

    $deleteSpecSql = "DELETE FROM product_specifications WHERE product_id = ?";

    $deleteSpecStmt = mysqli_prepare($conn, $deleteSpecSql);

    mysqli_stmt_bind_param(
        $deleteSpecStmt,
        "i",
        $product_id
    );

    mysqli_stmt_execute($deleteSpecStmt);
    foreach ($spec_names as $index => $specName) {

        $specName = trim($specName);
        $specValue = trim($spec_values[$index] ?? "");

        if ($specName === "" || $specValue === "") {
            continue;
        }

        $sortOrder = $index + 1;

        $specSql = "INSERT INTO product_specifications
                (product_id, spec_name, spec_value, sort_order)
                VALUES (?, ?, ?, ?)";

        $specStmt = mysqli_prepare($conn, $specSql);

        mysqli_stmt_bind_param(
            $specStmt,
            "issi",
            $product_id,
            $specName,
            $specValue,
            $sortOrder
        );

        mysqli_stmt_execute($specStmt);
    }

    $existingImageSql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY image_id ASC LIMIT 1";

    $existingImageStmt = mysqli_prepare($conn, $existingImageSql);

    mysqli_stmt_bind_param(
        $existingImageStmt,
        "i",
        $product_id
    );

    mysqli_stmt_execute($existingImageStmt);

    $existingImageResult = mysqli_stmt_get_result($existingImageStmt);

    $existingImage = mysqli_fetch_assoc($existingImageResult);

    if ($existingImage) {
        $existingImagePath = $existingImage["image_path"];

        $existingImagePath = str_replace("\\", "/", $existingImagePath);

        $imageFolder = dirname($existingImagePath);

        $imageFolderPath = "../../" . $imageFolder;
    } else {
        $imageFolder = "assets/images/Products/" . $category . "/" . $product_name;

        $imageFolderPath = "../../" . $imageFolder;
        if (!is_dir($imageFolderPath)) {
            mkdir($imageFolderPath, 0777, true);
        }
    }

    $nextImageNumber = 1;

    $existingFiles = scandir($imageFolderPath);

    foreach ($existingFiles as $file) {

        $fileName = pathinfo($file, PATHINFO_FILENAME);

        if (is_numeric($fileName)) {

            $number = (int)$fileName;

            if ($number >= $nextImageNumber) {
                $nextImageNumber = $number + 1;
            }
        }
    }
    if (isset($_FILES["product_images"])) {

        foreach ($_FILES["product_images"]["name"] as $key => $fileName) {

            if ($_FILES["product_images"]["error"][$key] !== 0) {
                continue;
            }

            $isPrimary = 0;

            if ($primaryNewImage !== "" && (int)$primaryNewImage === $key) {

                $isPrimary = 1;

                $resetPrimarySql = "UPDATE product_images
                        SET is_primary = 0
                        WHERE product_id = ?";

                $resetPrimaryStmt = mysqli_prepare($conn, $resetPrimarySql);

                mysqli_stmt_bind_param(
                    $resetPrimaryStmt,
                    "i",
                    $product_id
                );

                mysqli_stmt_execute($resetPrimaryStmt);
            }

            $fileExtension = strtolower(
                pathinfo($fileName, PATHINFO_EXTENSION)
            );

            $imageNumber = $nextImageNumber;

            $newFileName = $imageNumber . "." . $fileExtension;

            $destination = $imageFolderPath . "/" . $newFileName;

            if (move_uploaded_file(
                $_FILES["product_images"]["tmp_name"][$key],
                $destination
            )) {

                $imagePath = $imageFolder . "/" . $newFileName;

                $altText = $product_name;
                $sortOrder = $imageNumber;

                $imageSql = "INSERT INTO product_images
                         (product_id, image_path, alt_text, is_primary, sort_order)
                         VALUES (?, ?, ?, ?, ?)";

                $imageStmt = mysqli_prepare($conn, $imageSql);

                if (!$imageStmt) {
                    die("Image prepare failed: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param(
                    $imageStmt,
                    "issii",
                    $product_id,
                    $imagePath,
                    $altText,
                    $isPrimary,
                    $sortOrder
                );

                if (!mysqli_stmt_execute($imageStmt)) {
                    die("Image insert failed: " . mysqli_stmt_error($imageStmt));
                }

                $nextImageNumber++;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechVolt Admin - Edit Product</title>
    <link rel="stylesheet" href="../includes/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>
    <main class="admin-main">
        <section class="add-product-page">
            <!-- ================= PAGE HEADER ================= -->
            <div class="add-product-page-header">
                <div>
                    <h2>Edit Product</h2>
                    <p>
                        Update product information and specifications.
                    </p>
                </div>
                <a
                    href="manage-products.php"
                    class="back-products-btn">
                    ← Back to Products
                </a>
            </div>
            <!-- ================= EDIT FORM ================= -->
            <form
                class="add-product-form"
                action="edit-product.php?id=<?= $product_id; ?>"
                method="POST"
                enctype="multipart/form-data">
                <!-- Product ID -->
                <input
                    type="hidden"
                    name="product_id"
                    value="<?= $product["product_id"]; ?>">
                <!-- ================= BASIC INFORMATION ================= -->
                <div class="product-form-panel">
                    <div class="product-form-panel-header">
                        <div>
                            <h3>Basic Information</h3>
                            <p>
                                Update the main details of the product.
                            </p>
                        </div>
                    </div>
                    <div class="product-form-body">
                        <!-- PRODUCT NAME -->
                        <div class="form-group full-width">
                            <label for="product-name">
                                Product Name
                                <span>*</span>
                            </label>
                            <input
                                type="text"
                                id="product-name"
                                name="product_name"
                                value="<?= $product["product_name"]; ?>"
                                placeholder="Enter product name">
                        </div>
                        <!-- Short descripton -->
                        <div class="form-group full-width">
                            <label for="short-description">
                                Short Description
                            </label>

                            <input
                                type="text"
                                id="short-description"
                                name="short_description"
                                value="<?= $product["short_description"]; ?>"
                                placeholder="Enter short product description">
                        </div>
                        <!-- CATEGORY + BRAND + Model-Number -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">
                                    Category
                                    <span>*</span>
                                </label>

                                <select id="category" name="category">

                                    <?php if ($product["category_status"] === "inactive"): ?>

                                        <option
                                            value="<?= (int) $product["category_id"]; ?>"
                                            selected>
                                            <?= htmlspecialchars($product["category_name"]); ?>
                                            (Inactive)
                                        </option>

                                    <?php endif; ?>

                                    <?php foreach ($categories as $categoryOption): ?>

                                        <option
                                            value="<?= (int) $categoryOption["category_id"]; ?>"
                                            <?= ((int) $product["category_id"] === (int) $categoryOption["category_id"]) ? "selected" : ""; ?>>
                                            <?= htmlspecialchars($categoryOption["category_name"]); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label for="brand">
                                    Brand
                                </label>

                                <input
                                    type="text"
                                    id="brand"
                                    name="brand"
                                    value="<?= $product["brand"]; ?>"
                                    placeholder="Enter brand name">
                            </div>

                        </div>
                        <div class="form-row">

                            <div class="form-group">
                                <label for="model-number">
                                    Model Number
                                </label>

                                <input
                                    type="text"
                                    id="model-number"
                                    name="model_number"
                                    value="<?= $product["model_number"]; ?>"
                                    placeholder="Enter model number">
                            </div>

                            <div class="form-group">
                                <label for="sku">
                                    SKU
                                </label>

                                <input
                                    type="text"
                                    id="sku"
                                    name="sku"
                                    value="<?= $product["sku"]; ?>"
                                    placeholder="Enter SKU" disabled style="background-color: #eeeeee;">
                            </div>

                        </div>
                        <!-- PRICE + STOCK -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">
                                    Price (₹)
                                    <span>*</span>
                                </label>
                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    value="<?= $product["price"]; ?>"
                                    min="0"
                                    step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="stock">
                                    Stock Quantity
                                    <span>*</span>
                                </label>
                                <input
                                    type="number"
                                    id="stock"
                                    name="stock"
                                    value="<?= $product["stock"]; ?>"
                                    min="0">
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label for="status">
                                Product Status
                                <span>*</span>
                            </label>

                            <select id="status" name="status">
                                <option
                                    value="active"
                                    <?= $product["status"] === "active" ? "selected" : ""; ?>>
                                    Active
                                </option>

                                <option
                                    value="inactive"
                                    <?= $product["status"] === "inactive" ? "selected" : ""; ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>
                        <!-- DESCRIPTION -->
                        <div class="form-group full-width">
                            <label for="description">
                                Product Description
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="5"><?= $product["description"]; ?></textarea>
                        </div>
                    </div>
                </div>
                <!-- ================= PRODUCT IMAGES ================= -->
                <div class="product-form-panel">
                    <div class="product-form-panel-header">
                        <div>
                            <h3>Product Images</h3>
                            <p>
                                Manage existing images or upload new product images.
                            </p>
                        </div>
                        <label
                            for="product-images"
                            class="add-images-btn">
                            + Add Images
                        </label>
                    </div>
                    <div class="product-form-body">
                        <div class="product-images-grid">
                            <?php
                            $sql = "SELECT * FROM product_images WHERE product_id = ?";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "i", $product_id);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            while ($image = mysqli_fetch_assoc($result)) { ?>
                                <!-- ================= EXISTING IMAGES ================= -->
                                <!-- ================= PRIMARY IMAGE ================= -->
                                <div class="product-image-card <?= $image["is_primary"] == 1 ? "primary" : ""; ?>">
                                    <div class="product-image-preview">
                                        <?php if ($image["is_primary"] == 1) { ?>
                                            <span class="primary-badge">
                                                ★ Primary
                                            </span>
                                        <?php } ?>
                                        <img
                                            alt="<?= $image["alt_text"] ?>"
                                            src="<?= "../../" . $image["image_path"]; ?>">
                                    </div>
                                    <div class="product-image-card-footer">

                                        <span class="product-image-name">
                                            <?= basename(str_replace("\\", "/", $image["image_path"])); ?>
                                        </span>

                                        <?php if ($image["is_primary"] != 1) { ?>

                                            <button
                                                type="button"
                                                class="set-primary-btn" data-image-id="<?= $image["image_id"]; ?>">
                                                Set Primary
                                            </button>

                                        <?php } ?>

                                        <button
                                            type="button"
                                            class="remove-image-btn" data-image-id="<?= $image["image_id"]; ?>">
                                            ×
                                        </button>

                                    </div>
                                </div>

                            <?php } ?>
                            <div id="new-image-preview-container" class="new-images-grid"></div>
                        </div>
                    </div>
                    <!-- ================= ADD NEW IMAGE ================= -->
                    <label
                        for="product-images"
                        class="product-image-upload-card">

                        <div class="upload-card-icon">
                            +
                        </div>
                        <strong>
                            Add Images
                        </strong>
                        <small>
                            JPG, JPEG or PNG
                        </small>
                    </label>

                    <!-- ================= NEW IMAGE INPUT ================= -->
                    <input
                        type="file"
                        id="product-images"
                        name="product_images[]"
                        accept=".jpg,.jpeg,.png"
                        multiple
                        hidden>
                    <input
                        type="hidden"
                        name="primary_new_image"
                        id="primary-new-image"
                        value="">
                    <small class="product-image-help">
                        Select one image as the primary product image. You can also add or remove images.
                    </small>
                </div>
                <!-- ================= SPECIFICATIONS ================= -->
                <div class="product-form-panel">
                    <div class="product-form-panel-header">
                        <div>
                            <h3>Product Specifications</h3>
                            <p>
                                Update the technical specifications.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="add-spec-btn">
                            + Add Specification
                        </button>
                    </div>
                    <?php
                    $sql = "SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $product_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt); ?>
                    <div class="product-form-body specification-container">
                        <!-- SPECIFICATION 1 -->
                        <?php while ($spec = mysqli_fetch_assoc($result)) {
                        ?>
                            <div class="specification-row">
                                <div class="form-group">
                                    <label>
                                        Specification
                                    </label>
                                    <input
                                        type="text"
                                        name="spec_name[]"
                                        value="<?= $spec["spec_name"]; ?>">
                                </div>
                                <div class="form-group">
                                    <label>
                                        Value
                                    </label>
                                    <input
                                        type="text"
                                        name="spec_value[]"
                                        value="<?= $spec["spec_value"]; ?>">
                                </div>
                                <button
                                    type="button"
                                    class="remove-spec-btn">
                                    ×
                                </button>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <!-- ================= FORM ACTIONS ================= -->
                <div class="product-form-actions">
                    <a
                        href="manage-products.php"
                        class="cancel-product-btn">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="save-product-btn">
                        Update Product
                    </button>
                </div>
            </form>
        </section>
    </main>
    <?php if ($updateSuccess == true) { ?>

        <script>
            Swal.fire({
                icon: 'success',
                title: 'Product Updated',
                text: 'Product details have been updated successfully.',
                confirmButtonText: 'OK'
            }).then(() => {

                window.location.href = 'manage-products.php';

            });
        </script>

    <?php } ?>


    <?php if ($updateError == true) { ?>

        <script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Unable to update the product.',
                confirmButtonText: 'OK'
            });
        </script>

    <?php } ?>
    <script src="../includes/admin.js"></script>
</body>

</html>