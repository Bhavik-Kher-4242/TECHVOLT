<?php

session_start();
require_once "../../includes/database.php";
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}
$categories = [];

$categoryListSql = "
    SELECT category_id, category_name
    FROM categories
    WHERE status = 'active'
    ORDER BY category_name ASC
";

$categoryListResult = mysqli_query($conn, $categoryListSql);

if (!$categoryListResult) {
    die("Category list query failed: " . mysqli_error($conn));
}

while ($categoryRow = mysqli_fetch_assoc($categoryListResult)) {
    $categories[] = $categoryRow;
}
$adminPage = 'products';
$adminBase = '../';
$success_message = "";
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
    ============================================================
    1. GET FORM DATA
    ============================================================
    */

    $product_name = trim($_POST["product_name"] ?? "");
    $category_id = (int) ($_POST["category"] ?? 0);
    $brand = trim($_POST["brand"] ?? "");
    $model_number = trim($_POST["model_number"] ?? "");
    $sku = trim($_POST["sku"] ?? "");
    $short_description = trim($_POST["short_description"] ?? "");
    $price = $_POST["price"] ?? "";
    $stock = $_POST["stock"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "active";

    $spec_names = $_POST["spec_name"] ?? [];
    $spec_values = $_POST["spec_value"] ?? [];

    $primaryNewImage = $_POST["primary_new_image"] ?? "";

    $uploadedFiles = [];
    $category = "";


    /*
    ============================================================
    2. BASIC VALIDATION
    ============================================================
    */

    if (
        $product_name === "" ||
        $category_id <= 0 ||
        $brand === "" ||
        $model_number === "" ||
        $sku === "" ||
        $short_description === "" ||
        $price === "" ||
        $stock === "" ||
        $description === ""
    ) {

        $error_message = "All fields are required.";

    }
    if (
    $error_message === "" &&
    !in_array($status, ["active", "inactive"], true)
    ) {
        $error_message = "Invalid product status selected.";
    }


    /*
    ============================================================
    3. VALIDATE CATEGORY
    ============================================================
    */

    if ($error_message === "") {

        $categorySql = "SELECT category_id, category_name
                FROM categories
                WHERE category_id = ?
                AND status = 'active'";

        $categoryStmt = mysqli_prepare($conn, $categorySql);

        if (!$categoryStmt) {

            $error_message = "Category query failed: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $categoryStmt,
                "i",
                $category_id
            );

            if (!mysqli_stmt_execute($categoryStmt)) {

                $error_message = "Category query failed: "
                    . mysqli_stmt_error($categoryStmt);

            } else {

                $categoryResult = mysqli_stmt_get_result(
                    $categoryStmt
                );

                $categoryRow = mysqli_fetch_assoc(
                    $categoryResult
                );

                if (!$categoryRow) {

                    $error_message = "Invalid category selected.";

                } else {

                    $category = $categoryRow["category_name"];

                }
            }

            mysqli_stmt_close($categoryStmt);
        }
    }


    /*
    ============================================================
    4. VALIDATE PRODUCT IMAGES BEFORE INSERTING ANYTHING
    ============================================================
    */

    if ($error_message === "") {

        if (
            !isset($_FILES["product_images"]) ||
            empty($_FILES["product_images"]["name"][0])
        ) {

            $error_message = "Please upload at least one product image.";

        } elseif ($primaryNewImage === "") {

            $error_message =
                "Please select one image as the primary product image.";

        }
    }


    /*
    ============================================================
    5. VALIDATE PRIMARY IMAGE INDEX
    ============================================================
    */

    if ($error_message === "") {

        $primaryIndex = (int) $primaryNewImage;

        if (
            !isset($_FILES["product_images"]["name"][$primaryIndex]) ||
            $_FILES["product_images"]["error"][$primaryIndex] !== 0
        ) {

            $error_message =
                "The selected primary image is invalid.";

        }
    }


    /*
    ============================================================
    6. START DATABASE TRANSACTION
    ============================================================
    */

    if ($error_message === "") {

        mysqli_begin_transaction($conn);

        try {

            /*
            ====================================================
            7. INSERT PRODUCT
            ====================================================
            */

            $insertSql = "INSERT INTO products
                (
                    product_name,
                    category_id,
                    brand,
                    model_number,
                    sku,
                    short_description,
                    description,
                    price,
                    stock,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertStmt = mysqli_prepare(
                $conn,
                $insertSql
            );

            if (!$insertStmt) {

                throw new Exception(
                    "Product prepare failed: "
                    . mysqli_error($conn)
                );
            }

            mysqli_stmt_bind_param(
                $insertStmt,
                "sisssssdis",
                $product_name,
                $category_id,
                $brand,
                $model_number,
                $sku,
                $short_description,
                $description,
                $price,
                $stock,
                $status
            );

            if (!mysqli_stmt_execute($insertStmt)) {

                throw new Exception(
                    "Product insert failed: "
                    . mysqli_stmt_error($insertStmt)
                );
            }

            $product_id = mysqli_insert_id($conn);

            mysqli_stmt_close($insertStmt);


            /*
            ====================================================
            8. INSERT SPECIFICATIONS
            ====================================================
            */

            foreach ($spec_names as $index => $specName) {

                $specName = trim($specName);

                $specValue = trim(
                    $spec_values[$index] ?? ""
                );

                // Empty specification row skip karo
                if (
                    $specName === "" ||
                    $specValue === ""
                ) {
                    continue;
                }

                $sortOrder = $index + 1;

                $specSql = "INSERT INTO product_specifications
                    (
                        product_id,
                        spec_name,
                        spec_value,
                        sort_order
                    )
                    VALUES (?, ?, ?, ?)";

                $specStmt = mysqli_prepare(
                    $conn,
                    $specSql
                );

                if (!$specStmt) {

                    throw new Exception(
                        "Specification prepare failed: "
                        . mysqli_error($conn)
                    );
                }

                mysqli_stmt_bind_param(
                    $specStmt,
                    "issi",
                    $product_id,
                    $specName,
                    $specValue,
                    $sortOrder
                );

                if (!mysqli_stmt_execute($specStmt)) {

                    throw new Exception(
                        "Specification insert failed: "
                        . mysqli_stmt_error($specStmt)
                    );
                }

                mysqli_stmt_close($specStmt);
            }


            /*
            ====================================================
            9. CREATE PRODUCT IMAGE FOLDER
            ====================================================
            */

            $imageFolder =
                "assets/images/Products/"
                . $category
                . "/"
                . $product_name;

            $imageFolderPath =
                "../../" . $imageFolder;

            if (!is_dir($imageFolderPath)) {

                if (!mkdir(
                    $imageFolderPath,
                    0777,
                    true
                )) {

                    throw new Exception(
                        "Unable to create product image folder."
                    );
                }
            }


            /*
            ====================================================
            10. UPLOAD + INSERT IMAGES
            ====================================================
            */

            foreach (
                $_FILES["product_images"]["name"]
                as $key => $fileName
            ) {

                if (
                    $_FILES["product_images"]["error"][$key] !== 0
                ) {
                    continue;
                }


                $fileExtension = strtolower(
                    pathinfo(
                        $fileName,
                        PATHINFO_EXTENSION
                    )
                );


                /*
                --------------------------------------------
                Allowed image extensions
                --------------------------------------------
                */

                $allowedExtensions = [
                    "jpg",
                    "jpeg",
                    "png"
                ];

                if (
                    !in_array(
                        $fileExtension,
                        $allowedExtensions,
                        true
                    )
                ) {

                    throw new Exception(
                        "Invalid image format."
                    );
                }


                /*
                --------------------------------------------
                Create filename
                --------------------------------------------
                */

                $newFileName =
                    ($key + 1)
                    . "."
                    . $fileExtension;


                $destination =
                    $imageFolderPath
                    . "/"
                    . $newFileName;


                /*
                --------------------------------------------
                Move uploaded file
                --------------------------------------------
                */

                if (!move_uploaded_file(
                    $_FILES["product_images"]["tmp_name"][$key],
                    $destination
                )) {

                    throw new Exception(
                        "Failed to upload image: "
                        . $fileName
                    );
                }


                /*
                --------------------------------------------
                Keep track of uploaded files
                --------------------------------------------
                */

                $uploadedFiles[] = $destination;


                /*
                --------------------------------------------
                Primary image
                --------------------------------------------
                */

                $isPrimary = 0;

                if (
                    $primaryIndex === $key
                ) {

                    $isPrimary = 1;
                }


                /*
                --------------------------------------------
                Image path for database
                --------------------------------------------
                */

                $imagePath =
                    $imageFolder
                    . "/"
                    . $newFileName;

                $altText = $product_name;

                $sortOrder = $key + 1;


                /*
                --------------------------------------------
                Insert image record
                --------------------------------------------
                */

                $imageSql = "INSERT INTO product_images
                    (
                        product_id,
                        image_path,
                        alt_text,
                        is_primary,
                        sort_order
                    )
                    VALUES (?, ?, ?, ?, ?)";

                $imageStmt = mysqli_prepare(
                    $conn,
                    $imageSql
                );

                if (!$imageStmt) {

                    throw new Exception(
                        "Image prepare failed: "
                        . mysqli_error($conn)
                    );
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

                    throw new Exception(
                        "Image insert failed: "
                        . mysqli_stmt_error($imageStmt)
                    );
                }

                mysqli_stmt_close($imageStmt);
            }


            /*
            ====================================================
            11. COMMIT
            ====================================================
            */

            mysqli_commit($conn);

            $success_message =
                "Product has been added successfully.";


        } catch (Throwable $e) {


            /*
            ====================================================
            12. ROLLBACK DATABASE
            ====================================================
            */

            mysqli_rollback($conn);


            /*
            ====================================================
            13. DELETE UPLOADED FILES
            ====================================================
            */

            foreach ($uploadedFiles as $uploadedFile) {

                if (file_exists($uploadedFile)) {

                    unlink($uploadedFile);
                }
            }


            /*
            ====================================================
            14. DELETE EMPTY PRODUCT FOLDER
            ====================================================
            */

            if (
                isset($imageFolderPath) &&
                is_dir($imageFolderPath)
            ) {

                $remainingFiles = scandir(
                    $imageFolderPath
                );

                if (
                    count($remainingFiles) <= 2
                ) {

                    rmdir($imageFolderPath);
                }
            }


            /*
            ====================================================
            15. ERROR MESSAGE
            ====================================================
            */

            $error_message = $e->getMessage();
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
        content="width=device-width, initial-scale=1.0">
    <title>TechVolt Admin - Add Product</title>
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
                    <h2>Add Product</h2>
                    <p>
                        Add a new electronic component to your TechVolt store.
                    </p>
                </div>
                <a
                    href="manage-products.php"
                    class="back-products-btn">
                    ← Back to Products
                </a>
            </div>
            <!-- ================= PRODUCT FORM ================= -->
            <form
                class="add-product-form"
                action="#"
                method="POST"
                enctype="multipart/form-data">
                <!-- ================= BASIC INFORMATION ================= -->
                <div class="product-form-panel">
                    <div class="product-form-panel-header">
                        <h3>Basic Information</h3>
                        <p>
                            Enter the main details of the product.
                        </p>
                    </div>
                    <div class="product-form-body">
                        <div class="form-group full-width">
                            <label for="product-name">
                                Product Name
                                <span>*</span>
                            </label>
                            <input
                                type="text"
                                id="product-name"
                                name="product_name"
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
                                value=""
                                placeholder="Enter short product description">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">
                                    Category
                                    <span>*</span>
                                </label>
                                <select
                                    id="category"
                                    name="category">

                                    <option value="">
                                        Select Category
                                    </option>

                                    <?php foreach ($categories as $categoryOption): ?>
                                        <option value="<?= (int) $categoryOption["category_id"]; ?>">
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
                                    placeholder="Enter SKU"
                                    readonly>
                            </div>

                        </div>
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
                                    placeholder="Enter product price"
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
                                    placeholder="Enter stock quantity"
                                    min="0">
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label for="status">
                                Product Status
                                <span>*</span>
                            </label>

                            <select id="status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="description">
                                Product Description
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                placeholder="Enter product description"></textarea>
                        </div>
                    </div>
                </div>
                <!-- ================= PRODUCT IMAGES ================= -->
                <div class="product-form-panel">
                    <div class="product-form-panel-header">
                        <div>
                            <h3>Product Images</h3>
                            <p>
                                Upload product images and select one as the primary image.
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
                            <div
                                id="new-image-preview-container"
                                class="new-images-grid">
                            </div>
                            <!-- ================= UPLOAD CARD ================= -->
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
                        </div>
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
                            You can upload multiple images. One image must be selected as the primary product image.
                        </small>
                    </div>
                </div>
                <!-- ================= PRODUCT SPECIFICATIONS ================= -->
                <div class="product-form-panel">
                    <div class="product-form-panel-header">
                        <div>
                            <h3>Product Specifications</h3>
                            <p>
                                Add technical specifications of the product.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="add-spec-btn">
                            + Add Specification
                        </button>
                    </div>
                    <div class="product-form-body specification-container">
                        <div class="specification-row">
                            <div class="form-group">
                                <label>
                                    Specification
                                </label>
                                <input
                                    type="text"
                                    name="spec_name[]"
                                    placeholder="Example: Operating Voltage">
                            </div>
                            <div class="form-group">
                                <label>
                                    Value
                                </label>
                                <input
                                    type="text"
                                    name="spec_value[]"
                                    placeholder="Example: 5V">
                            </div>
                            <button
                                type="button"
                                class="remove-spec-btn">
                                ×
                            </button>
                        </div>
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
                        Save Product
                    </button>
                </div>
            </form>
        </section>
    </main>
    <script src="../includes/admin.js"></script>
    <?php if ($success_message !== ""): ?>

        <script>
            Swal.fire({
                icon: "success",
                title: "Product Added Successfully!",
                text: "<?= htmlspecialchars($success_message); ?>",
                background: "#111827",
                color: "#ffffff",
                confirmButtonText: "OK"
            }).then(function() {
                window.location.href = "manage-products.php";
            });
        </script>

    <?php endif; ?>


    <?php if ($error_message !== ""): ?>

        <script>
            Swal.fire({
                icon: "error",
                title: "Failed to Add Product",
                text: "<?= htmlspecialchars($error_message); ?>",
                background: "#111827",
                color: "#ffffff",
                confirmButtonText: "OK"
            });
        </script>

    <?php endif; ?>
</body>

</html>