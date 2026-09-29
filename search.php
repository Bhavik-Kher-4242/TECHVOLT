<?php
    session_start();
    $search = $_GET["search"] ?? "";
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | TechVolt</title>
    <link rel="stylesheet" href="assets/css/product.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include "includes/header.php";?>
    <main>
        <section class="not-found" id="not-found">
            <h2>Search Results </h2>
            <p>No Products Found</p>
            <p>We couldn't find any products matching "<span><?php echo htmlspecialchars($search); ?></span>".</p>
            <div class="container-shop-btn">
                <button class="shop-btn" onclick="window.location.href='products.php'">View Products &#10132;</button>
            </div>
        </section>
        <section class="top-search" id="search-results">
            <h2>Search Results</h2>
            <p>Results for: <span><?php echo htmlspecialchars($search); ?></span></p>
            <p><span id="product-count">0</span> products found</p>
            <hr>
            <div class="all-products">
            </div>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
    <script src="assets/Js/search.js"></script>
</body>
</html>