<?php

session_start();

require_once "../../includes/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

$adminPage = 'products';
$adminBase = '../';

$product_id = (int) ($_GET["id"] ?? 0);

if ($product_id <= 0) {
    header("Location: manage-products.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $deleteProductId = (int) ($_POST["product_id"] ?? 0);

    if ($deleteProductId !== $product_id) {
        die("Invalid product request.");
    }

    mysqli_begin_transaction($conn);

    try {

        // Check whether product is already used in an order
        $orderSql = "
            SELECT COUNT(*) AS total
            FROM order_items
            WHERE product_id = ?
        ";

        $orderStmt = mysqli_prepare($conn, $orderSql);

        if (!$orderStmt) {
            throw new Exception(
                "Order check failed: " . mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $orderStmt,
            "i",
            $deleteProductId
        );

        mysqli_stmt_execute($orderStmt);

        $orderResult = mysqli_stmt_get_result($orderStmt);
        $orderRow = mysqli_fetch_assoc($orderResult);

        mysqli_stmt_close($orderStmt);

        if ($orderRow["total"] > 0) {
            throw new Exception(
                "This product cannot be deleted because it is already associated with an order."
            );
        }


        // Get product images before deleting database record
        $imageSql = "
            SELECT image_path
            FROM product_images
            WHERE product_id = ?
        ";

        $imageStmt = mysqli_prepare($conn, $imageSql);

        if (!$imageStmt) {
            throw new Exception(
                "Image query failed: " . mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $imageStmt,
            "i",
            $deleteProductId
        );

        mysqli_stmt_execute($imageStmt);

        $imageResult = mysqli_stmt_get_result($imageStmt);

        $imagePaths = [];

        while ($imageRow = mysqli_fetch_assoc($imageResult)) {
            $imagePaths[] = $imageRow["image_path"];
        }

        mysqli_stmt_close($imageStmt);


        // Delete product
        $deleteSql = "
            DELETE FROM products
            WHERE product_id = ?
        ";

        $deleteStmt = mysqli_prepare($conn, $deleteSql);

        if (!$deleteStmt) {
            throw new Exception(
                "Delete prepare failed: " . mysqli_error($conn)
            );
        }

        mysqli_stmt_bind_param(
            $deleteStmt,
            "i",
            $deleteProductId
        );

        if (!mysqli_stmt_execute($deleteStmt)) {
            throw new Exception(
                "Product deletion failed: "
                . mysqli_stmt_error($deleteStmt)
            );
        }

        if (mysqli_stmt_affected_rows($deleteStmt) !== 1) {
            throw new Exception("Product could not be deleted.");
        }

        mysqli_stmt_close($deleteStmt);


        // Commit database deletion
        mysqli_commit($conn);


        // Delete physical image files
        foreach ($imagePaths as $imagePath) {

            $fullPath = "../../" . $imagePath;

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }


        // Delete empty product folder
        if (!empty($imagePaths)) {

            $productFolder = dirname("../../" . $imagePaths[0]);

            if (is_dir($productFolder)) {

                $files = scandir($productFolder);

                if (count($files) <= 2) {
                    rmdir($productFolder);
                }
            }
        }


        header("Location: manage-products.php?deleted=1");
        exit;

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        $delete_error = $e->getMessage();
    }
}

$sql = "
    SELECT
        p.product_id,
        p.product_name,
        p.brand,
        p.price,
        p.stock,
        p.status,
        c.category_name,
        pi.image_path,
        pi.alt_text
    FROM products p
    JOIN categories c
        ON p.category_id = c.category_id
    LEFT JOIN product_images pi
        ON p.product_id = pi.product_id
        AND pi.is_primary = 1
    WHERE p.product_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $product_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    header("Location: manage-products.php");
    exit;
}

function checkStock($stock)
{
    if ($stock <= 0) {
        return "Out of Stock";
    }

    if ($stock < 10) {
        return "Limited Stock";
    }

    return "In Stock";
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

    <title>TechVolt Admin - Delete Product</title>

    <link
        rel="stylesheet"
        href="../includes/admin.css"
    >

</head>


<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="delete-product-page">


            <!-- ================= PAGE HEADER ================= -->

            <div class="delete-product-page-header">

                <div>

                    <h2>Delete Product</h2>

                    <p>
                        Review the product before permanently deleting it.
                    </p>

                </div>


                <a
                    href="manage-products.php"
                    class="back-products-btn"
                >
                    ← Back to Products
                </a>

            </div>



            <!-- ================= DELETE PANEL ================= -->

            <div class="delete-product-panel">


                <!-- ================= WARNING ================= -->

                <div class="delete-warning">

                    <div class="delete-warning-icon">
                        !
                    </div>

                    <div>

                        <h3>
                            Are you sure you want to delete this product?
                        </h3>

                        <p>
                            This action will permanently remove the product
                            from your TechVolt store.
                        </p>

                    </div>

                </div>



                <!-- ================= PRODUCT INFORMATION ================= -->

                <div class="delete-product-content">


                    <!-- PRODUCT IMAGE -->

                    <div class="delete-product-image">

                        <img
                            src="../../<?= htmlspecialchars($product["image_path"]); ?>"
                        >

                    </div>



                    <!-- PRODUCT DETAILS -->

                    <div class="delete-product-details">

                        <span class="delete-product-label">
                            Product
                        </span>

                        <h3>
                            <?= htmlspecialchars($product["product_name"]); ?>
                        </h3>


                        <div class="delete-product-info-grid">

                            <div>

                                <span>
                                    Product ID
                                </span>

                                <strong>
                                    ID: <?= "#" . str_pad($product["product_id"], 3, "0" ,STR_PAD_LEFT); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Category
                                </span>

                                <strong>
                                    <?= htmlspecialchars($product["category_name"]); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Brand
                                </span>

                                <strong>
                                    <?= htmlspecialchars($product["brand"]); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Price
                                </span>

                                <strong>
                                    ₹<?= htmlspecialchars($product["price"]); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Stock
                                </span>

                                <strong>
                                    <?= htmlspecialchars($product["stock"]); ?> Units
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Status
                                </span>

                                <strong class="product-<?= ($product["stock"] > 10) ? "In" : (($product["stock"] > 0 && $product["stock"] <= 10) ? "Limited" : "Out") ?>-stock-status">
                                    <?= checkStock($product["stock"]); ?>
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- ================= DELETE NOTE ================= -->

                <div class="delete-product-note">

                    <strong>
                        Important:
                    </strong>

                    <span>
                        Deleting this product cannot be undone.
                        Make sure you have selected the correct product.
                    </span>

                </div>



                <!-- ================= ACTIONS ================= -->

                <div class="delete-product-actions">

                    <a
                        href="manage-products.php"
                        class="cancel-delete-btn"
                    >
                        Cancel
                    </a>


                    <form method="POST">

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= $product["product_id"]; ?>"
                        >

                        <button
                            type="submit"
                            class="confirm-delete-btn"
                        >
                            Delete Product
                        </button>

                    </form>

                </div>


            </div>

        </section>

    </main>


</body>

</html>