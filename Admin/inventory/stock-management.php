<?php

session_start();

require_once "../../includes/database.php";

/* =========================================================
   ADMIN LOGIN PROTECTION
========================================================= */

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}


/* =========================================================
   ADMIN PAGE SETTINGS
========================================================= */

$adminPage = 'inventory';
$adminBase = '../';


/* =========================================================
   FILTER VALUES
========================================================= */

$search = trim($_GET["search"] ?? "");
$categoryFilter = trim($_GET["category"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");
$stockUpdated = isset($_GET["stock_updated"]) && $_GET["stock_updated"] === "1";

// =========================================================
// PAGINATION
// =========================================================

$currentPage = isset($_GET["page"]) ? (int) $_GET["page"] : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

$productsPerPage = 5;

/* =========================================================
   STOCK STATUS RULE
========================================================= */

function getStockStatus($stock)
{
    $stock = (int) $stock;

    if ($stock <= 0) {
        return "out-stock";
    }

    if ($stock < 10) {
        return "low-stock";
    }

    return "in-stock";
}


/* =========================================================
   STOCK STATUS TEXT
========================================================= */

function getStockStatusText($stock)
{
    $stock = (int) $stock;

    if ($stock <= 0) {
        return "Out of Stock";
    }

    if ($stock < 10) {
        return "Low Stock";
    }

    return "In Stock";
}


/* =========================================================
   STOCK SUMMARY
========================================================= */

$totalProducts = 0;
$inStockProducts = 0;
$lowStockProducts = 0;
$outOfStockProducts = 0;


/* Total Products */

$totalSql = "
    SELECT COUNT(*) AS total_products
    FROM products
";

$totalResult = mysqli_query($conn, $totalSql);

if ($totalResult) {

    $totalRow = mysqli_fetch_assoc($totalResult);

    $totalProducts = (int) $totalRow["total_products"];
}


/* In Stock */

$inStockSql = "
    SELECT COUNT(*) AS in_stock
    FROM products
    WHERE stock >= 10
";

$inStockResult = mysqli_query($conn, $inStockSql);

if ($inStockResult) {

    $inStockRow = mysqli_fetch_assoc($inStockResult);

    $inStockProducts = (int) $inStockRow["in_stock"];
}


/* Low Stock */

$lowStockSql = "
    SELECT COUNT(*) AS low_stock
    FROM products
    WHERE stock > 0
    AND stock < 10
";

$lowStockResult = mysqli_query($conn, $lowStockSql);

if ($lowStockResult) {

    $lowStockRow = mysqli_fetch_assoc($lowStockResult);

    $lowStockProducts = (int) $lowStockRow["low_stock"];
}


/* Out of Stock */

$outOfStockSql = "
    SELECT COUNT(*) AS out_of_stock
    FROM products
    WHERE stock <= 0
";

$outOfStockResult = mysqli_query($conn, $outOfStockSql);

if ($outOfStockResult) {

    $outOfStockRow = mysqli_fetch_assoc($outOfStockResult);

    $outOfStockProducts = (int) $outOfStockRow["out_of_stock"];
}


/* =========================================================
   ACTIVE CATEGORIES
========================================================= */

$categories = [];

$categorySql = "
    SELECT category_id, category_name, slug
    FROM categories
    WHERE status = 'active'
    ORDER BY category_name ASC
";

$categoryResult = mysqli_query($conn, $categorySql);

if ($categoryResult) {

    while ($categoryRow = mysqli_fetch_assoc($categoryResult)) {

        $categories[] = $categoryRow;
    }
}


/* =========================================================
   PRODUCT QUERY
========================================================= */

$products = [];

$productSql = "
    SELECT
        p.product_id,
        p.product_name,
        p.category_id,
        p.brand,
        p.price,
        p.stock,
        p.created_at,
        c.category_name,
        c.slug AS category_slug,
        pi.image_path,
        pi.alt_text

    FROM products p

    INNER JOIN categories c
        ON p.category_id = c.category_id

    LEFT JOIN product_images pi
        ON p.product_id = pi.product_id
        AND pi.is_primary = 1
";


/* =========================================================
   FILTER CONDITIONS
========================================================= */
$whereConditions = ["c.status = 'active'"];

$searchParam = "";
$categoryParam = "";


/* Search */

if ($search !== "") {

    $whereConditions[] = "
        (
            p.product_name LIKE ?
            OR p.brand LIKE ?
            OR p.product_id LIKE ?
        )
    ";
}


/* Category */

if ($categoryFilter !== "") {

    $whereConditions[] = "
        c.slug = ?
    ";
}


/* Stock Status */

if ($statusFilter === "in-stock") {

    $whereConditions[] = "
        p.stock >= 10
    ";
}

elseif ($statusFilter === "low-stock") {

    $whereConditions[] = "
        p.stock > 0
        AND p.stock < 10
    ";
}

elseif ($statusFilter === "out-stock") {

    $whereConditions[] = "
        p.stock <= 0
    ";
}


/* Add WHERE */

if (!empty($whereConditions)) {

    $productSql .= "
        WHERE
        " . implode(" AND ", $whereConditions);
}

/* =========================================================
   COUNT FILTERED PRODUCTS
========================================================= */

$countSql = "
    SELECT COUNT(*) AS total_filtered
    FROM products p
    INNER JOIN categories c
        ON p.category_id = c.category_id
";

if (!empty($whereConditions)) {
    $countSql .= "
        WHERE
        " . implode(" AND ", $whereConditions);
}

$countStmt = mysqli_prepare($conn, $countSql);

$totalFilteredProducts = 0;

if ($countStmt) {

    $countTypes = "";
    $countParams = [];

    /* Search Parameters */
    if ($search !== "") {

        $searchParam = "%" . $search . "%";

        $countTypes .= "sss";

        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
    }

    /* Category Parameter */
    if ($categoryFilter !== "") {

        $categoryParam = $categoryFilter;

        $countTypes .= "s";

        $countParams[] = $categoryParam;
    }

    /* Bind Parameters */
    if ($countTypes !== "") {

        mysqli_stmt_bind_param(
            $countStmt,
            $countTypes,
            ...$countParams
        );
    }

    /* Execute */
    mysqli_stmt_execute($countStmt);

    /* Get Result */
    $countResult = mysqli_stmt_get_result($countStmt);

    if ($countResult) {

        $countRow = mysqli_fetch_assoc($countResult);

        $totalFilteredProducts = (int) $countRow["total_filtered"];
    }

    mysqli_stmt_close($countStmt);
}


/* =========================================================
   PAGINATION CALCULATION
========================================================= */

$totalPages = (int) ceil($totalFilteredProducts / $productsPerPage);

if ($totalPages < 1) {
    $totalPages = 1;
}

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $productsPerPage;


/* =========================================================
   Sorting + Pagination
========================================================= */

$productSql .= "
    ORDER BY p.product_id ASC
    LIMIT ? OFFSET ?
";


/* =========================================================
   PREPARE PRODUCT QUERY
========================================================= */

$productStmt = mysqli_prepare($conn, $productSql);

if ($productStmt) {

    $types = "";
    $params = [];


    /* Search Parameters */

    if ($search !== "") {

        $searchParam = "%" . $search . "%";

        $types .= "sss";

        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }


    /* Category Parameter */

    if ($categoryFilter !== "") {

        $categoryParam = $categoryFilter;

        $types .= "s";

        $params[] = $categoryParam;
    }

        /* Pagination Parameters */

            $types .= "ii";

            $params[] = $productsPerPage;
            $params[] = $offset;


    /* Bind Parameters */

    if ($types !== "") {

        mysqli_stmt_bind_param(
            $productStmt,
            $types,
            ...$params
        );
    }


    /* Execute */

    mysqli_stmt_execute($productStmt);


    /* Get Result */

    $productResult = mysqli_stmt_get_result($productStmt);


    if ($productResult) {

        while ($productRow = mysqli_fetch_assoc($productResult)) {

            $products[] = $productRow;
        }
    }


    mysqli_stmt_close($productStmt);
}


/* =========================================================
   PAGINATION DISPLAY RANGE
========================================================= */

if ($totalFilteredProducts > 0) {

    $showingFrom = $offset + 1;

    $showingTo = min(
        $offset + $productsPerPage,
        $totalFilteredProducts
    );

} else {

    $showingFrom = 0;
    $showingTo = 0;
}

/* =========================================================
   UPDATE STOCK VARIABLES
========================================================= */

$stockUpdateSuccess = false;
$stockUpdateError = false;
$stockUpdateMessage = "";


/* =========================================================
   UPDATE STOCK
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_stock"])
) {

    $productId = (int) ($_POST["product_id"] ?? 0);

    $newStock = $_POST["new_stock"] ?? "";

    if ($productId <= 0) {

        $stockUpdateError = true;

        $stockUpdateMessage = "Invalid product selected.";
    }

    elseif ($newStock === "" || !is_numeric($newStock)) {

        $stockUpdateError = true;

        $stockUpdateMessage = "Please enter a valid stock quantity.";
    }

    elseif ((int) $newStock < 0) {

        $stockUpdateError = true;

        $stockUpdateMessage = "Stock quantity cannot be negative.";
    }

    else {

        $newStock = (int) $newStock;


        /* Check Product */

        $checkProductSql = "
            SELECT product_id, product_name
            FROM products
            WHERE product_id = ?
        ";

        $checkProductStmt = mysqli_prepare(
            $conn,
            $checkProductSql
        );

        mysqli_stmt_bind_param(
            $checkProductStmt,
            "i",
            $productId
        );

        mysqli_stmt_execute($checkProductStmt);

        $checkProductResult = mysqli_stmt_get_result(
            $checkProductStmt
        );

        $existingProduct = mysqli_fetch_assoc(
            $checkProductResult
        );

        mysqli_stmt_close($checkProductStmt);


        if (!$existingProduct) {

            $stockUpdateError = true;

            $stockUpdateMessage = "Product not found.";
        }

        else {

            /* Update Stock */

            $updateStockSql = "
                UPDATE products
                SET stock = ?
                WHERE product_id = ?
            ";

            $updateStockStmt = mysqli_prepare(
                $conn,
                $updateStockSql
            );

            mysqli_stmt_bind_param(
                $updateStockStmt,
                "ii",
                $newStock,
                $productId
            );

            $updateStockSuccess = mysqli_stmt_execute(
                $updateStockStmt
            );

            mysqli_stmt_close($updateStockStmt);


            if ($updateStockSuccess) {

                header("Location: stock-management.php?stock_updated=1");
                exit;
            }

            else {

                $stockUpdateError = true;

                $stockUpdateMessage =
                    "Unable to update stock.";
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

    <title>TechVolt Admin - Stock Management</title>

    <link rel="stylesheet" href="../includes/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="stock-management">

            <!-- Page Header -->
            <div class="stock-page-header">

                <div>
                    <h2>Stock Management</h2>
                    <p>Monitor and manage product stock levels.</p>
                </div>

                <button type="button" class="refresh-stock-btn" onclick="window.location.reload();">
                    <span>↻</span>
                    Refresh Stock
                </button>

            </div>


            <!-- Stock Summary -->
            <div class="stock-summary">

                <div class="stock-summary-card">

                    <div class="stock-summary-icon total">
                        📦
                    </div>

                    <div class="stock-summary-info">
                        <span>Total Products</span>
                        <strong><?= $totalProducts; ?></strong>
                    </div>

                </div>


                <div class="stock-summary-card">

                    <div class="stock-summary-icon in-stock">
                        ✓
                    </div>

                    <div class="stock-summary-info">
                        <span>In Stock</span>
                        <strong><?= $inStockProducts; ?></strong>
                    </div>

                </div>


                <div class="stock-summary-card">

                    <div class="stock-summary-icon low-stock">
                        !
                    </div>

                    <div class="stock-summary-info">
                        <span>Low Stock</span>
                        <strong><?= $lowStockProducts; ?></strong>
                    </div>

                </div>


                <div class="stock-summary-card">

                    <div class="stock-summary-icon out-stock">
                        ×
                    </div>

                    <div class="stock-summary-info">
                        <span>Out of Stock</span>
                        <strong><?= $outOfStockProducts; ?></strong>
                    </div>

                </div>

            </div>


            <!-- Product Inventory -->
            <div class="stock-panel">

                <div class="stock-panel-header">

                    <div>
                        <h3>Product Stock</h3>
                        <p>Check current stock levels and update quantities.</p>
                    </div>

                </div>


                <!-- Filters -->
                <form method="get" action="" class="stock-toolbar">
                    

                    <div class="stock-search">

                        <span>⌕</span>

                        <input
                            type="search"
                            id="stock-search"
                            name="search"
                            value="<?= htmlspecialchars($search); ?>"
                            placeholder="Search products..."
                        >

                    </div>


                    <select id="stock-category" name="category">
                        <option value="">All Categories</option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?= htmlspecialchars($category["slug"]); ?>"
                                <?= $categoryFilter === $category["slug"] ? "selected" : ""; ?>
                            >
                                <?= htmlspecialchars($category["category_name"]); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>


                    <select id="stock-status" name="status">
                        <option value="">All Stock Status</option>

                        <option
                            value="in-stock"
                            <?= $statusFilter === "in-stock" ? "selected" : ""; ?>
                        >
                            In Stock
                        </option>

                        <option
                            value="low-stock"
                            <?= $statusFilter === "low-stock" ? "selected" : ""; ?>
                        >
                            Low Stock
                        </option>

                        <option
                            value="out-stock"
                            <?= $statusFilter === "out-stock" ? "selected" : ""; ?>
                        >
                            Out of Stock
                        </option>

                    </select>


                    <button type="submit" class="filter-stock-btn">
                        Filter
                    </button>

                </form>


                <!-- Stock Table -->
                <div class="stock-table-wrapper">

                    <table class="stock-management-table">

                        <thead>

                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>

                        </thead>


                        <tbody>

                            <?php if (empty($products)): ?>

                                <tr>
                                    <td colspan="7" style="text-align: center;">
                                        No products found.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($products as $product): ?>

                                    <?php
                                    $stockStatus = getStockStatus($product["stock"]);
                                    $stockStatusText = getStockStatusText($product["stock"]);
                                    ?>

                                    <tr>

                                        <!-- Product -->

                                        <td>

                                            <div class="stock-product">

                                                <div class="stock-product-image">

                                                    <img
                                                        src="../../<?= htmlspecialchars($product["image_path"] ?? "assets/images/placeholder.jpg"); ?>"
                                                        alt="<?= htmlspecialchars($product["alt_text"] ?? $product["product_name"]); ?>"
                                                    >

                                                </div>

                                                <div class="stock-product-info">

                                                    <strong>
                                                        <?= htmlspecialchars($product["product_name"]); ?>
                                                    </strong>

                                                    <small>
                                                        Product ID: <?= (int) $product["product_id"]; ?>
                                                    </small>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- Category -->

                                        <td>

                                            <span class="stock-category-badge">

                                                <?= htmlspecialchars($product["category_name"]); ?>

                                            </span>

                                        </td>


                                        <!-- Price -->

                                        <td>

                                            <span class="stock-price">

                                                ₹<?= number_format((float) $product["price"], 0); ?>

                                            </span>

                                        </td>


                                        <!-- Current Stock -->

                                        <td>

                                            <span class="stock-quantity <?= $stockStatus === "in-stock" ? "normal" : ($stockStatus === "low-stock" ? "low" : "out"); ?>">

                                                <?= (int) $product["stock"]; ?>

                                            </span>

                                        </td>


                                        <!-- Status -->

                                        <td>

                                            <span class="stock-status-badge <?= $stockStatus === "in-stock" ? "in" : ($stockStatus === "low-stock" ? "low" : "out"); ?>">

                                                <?= $stockStatusText; ?>

                                            </span>

                                        </td>


                                        <!-- Last Updated -->

                                        <td>

                                            <span class="stock-date">

                                                <?= date("d M Y", strtotime($product["created_at"])); ?>

                                            </span>

                                        </td>


                                        <!-- Action -->

                                        <td>

                                            <button
                                                type="button"
                                                class="update-stock-btn"
                                                onclick='openStockModal(
                                                    <?= json_encode($product["product_name"]); ?>,
                                                    <?= (int) $product["stock"]; ?>,
                                                    <?= (int) $product["product_id"]; ?>
                                                )'
                                            >
                                                Update
                                            </button>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <?php 
                    /* =========================================================
                    PAGINATION URL
                    ========================================================= */

                    $paginationParams = [];

                    if ($search !== "") {
                        $paginationParams["search"] = $search;
                    }

                    if ($categoryFilter !== "") {
                        $paginationParams["category"] = $categoryFilter;
                    }

                    if ($statusFilter !== "") {
                        $paginationParams["status"] = $statusFilter;
                    }

                    function getPaginationUrl($page, $paginationParams)
                    {
                        $paginationParams["page"] = $page;

                        return "?" . http_build_query($paginationParams);
                    }
                ?>
                <!-- Footer -->
                <div class="stock-table-footer">

                    <span>
                        <?php if ($totalFilteredProducts > 0): ?>

                            Showing
                            <strong><?= $showingFrom; ?></strong>
                            -
                            <strong><?= $showingTo; ?></strong>

                            of

                            <strong><?= $totalFilteredProducts; ?></strong>
                            products

                        <?php else: ?>

                            Showing <strong>0</strong> products

                        <?php endif; ?>
                    </span>


                    <?php if ($totalPages > 1): ?>

                        <div class="stock-pagination">

                            <!-- Previous -->
                            <?php if ($currentPage > 1): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        getPaginationUrl(
                                            $currentPage - 1,
                                            $paginationParams
                                        )
                                    ); ?>"
                                    class="pagination-btn"
                                >
                                    ‹
                                </a>

                            <?php else: ?>

                                <button
                                    type="button"
                                    disabled
                                >
                                    ‹
                                </button>

                            <?php endif; ?>


                            <!-- Page Numbers -->
                            <?php for ($page = 1; $page <= $totalPages; $page++): ?>

                                <?php if ($page == $currentPage): ?>

                                    <button
                                        type="button"
                                        class="active-page"
                                    >
                                        <?= $page; ?>
                                    </button>

                                <?php else: ?>

                                    <a
                                        href="<?= htmlspecialchars(
                                            getPaginationUrl(
                                                $page,
                                                $paginationParams
                                            )
                                        ); ?>"
                                        class="pagination-btn"
                                    >
                                        <?= $page; ?>
                                    </a>

                                <?php endif; ?>

                            <?php endfor; ?>


                            <!-- Next -->
                            <?php if ($currentPage < $totalPages): ?>

                                <a
                                    href="<?= htmlspecialchars(
                                        getPaginationUrl(
                                            $currentPage + 1,
                                            $paginationParams
                                        )
                                    ); ?>"
                                    class="pagination-btn"
                                >
                                    ›
                                </a>

                            <?php else: ?>

                                <button
                                    type="button"
                                    disabled
                                >
                                    ›
                                </button>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>
            <!-- ================= STOCK UPDATE MODAL ================= -->

            <div
                class="stock-modal-overlay"
                id="stock-modal">

                <div class="stock-modal">

                    <div class="stock-modal-header">

                        <div>
                            <h3>Update Stock</h3>

                            <p>
                                Update the available stock quantity.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="stock-modal-close"
                            onclick="closeStockModal()"
                        >
                            ×
                        </button>

                    </div>


                    <div class="stock-modal-body">
                        <input type="hidden" id="stock-product-id" value="">

                        <div class="stock-modal-product">

                            <span>Product</span>

                            <strong id="stock-product-name"></strong>

                        </div>


                        <div class="stock-quantity-info">

                            <div>

                                <span>Current Stock</span>

                                <strong id="current-stock">0</strong>

                            </div>

                        </div>


                        <div class="stock-form-group">

                            <label for="new-stock">
                                New Stock Quantity
                            </label>

                            <input
                                type="number"
                                id="new-stock"
                                min="0"
                                value="0"
                                placeholder="Enter stock quantity"
                            >

                            <small>
                                Enter the total quantity currently available.
                            </small>

                        </div>

                    </div>


                    <div class="stock-modal-footer">

                        <button
                            type="button"
                            class="stock-modal-cancel"
                            onclick="closeStockModal()"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="stock-modal-update"
                            onclick="updateStock()"
                        >
                            Update Stock
                        </button>

                    </div>

                </div>

            </div>
        </section>

    </main>
    <script src="../includes/admin.js"></script>
    <?php if ($stockUpdateError): ?>

        <script>
            Swal.fire({
                icon: "error",
                title: "Stock Update Failed",
                text: <?= json_encode($stockUpdateMessage); ?>,
                confirmButtonText: "OK"
            });
        </script>

    <?php endif; ?>
    <?php if ($stockUpdated): ?>

        <script>
            Swal.fire({
                icon: "success",
                title: "Stock Updated",
                text: "Product stock has been updated successfully.",
                confirmButtonText: "OK"
            });
        </script>

    <?php endif; ?>
</body>
</html>