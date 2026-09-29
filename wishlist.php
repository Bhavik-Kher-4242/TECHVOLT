<?php
session_start();
require_once "includes/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$sql = "SELECT wishlist.wishlist_id, products.product_id, products.product_name, products.price, products.stock, products.rating, products.review_count, categories.category_name, product_images.image_path, product_images.alt_text FROM wishlist JOIN products ON wishlist.product_id = products.product_id JOIN categories ON products.category_id = categories.category_id JOIN product_images ON products.product_id = product_images.product_id AND product_images.is_primary = 1 WHERE wishlist.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$wishlistProducts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $wishlistProducts[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WishList</title>
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<?php include "includes/header.php"; ?>
<main class="wishlist-page">
    <section class="header-of-wishlist">
        <h2>My WishList</h2>
        <p>Your Saved Products</p>
    </section>
    <section class="product-item-container">
        <div class="header-of-product-item-container">
            <h2 class="quantity-in-wishlist">
                <?php echo count($wishlistProducts); ?>
                Items In Your WishList
            </h2>
        </div>
        <div class="product-item-container-wishlist">
            <div class="all-products">
                <?php if (count($wishlistProducts) > 0) { ?>
                    <?php foreach ($wishlistProducts as $product) { ?>
                        <div id="<?php echo htmlspecialchars($product["product_id"]); ?>" class="products" data-wishlist-id="<?php echo htmlspecialchars($product["wishlist_id"]); ?>"
                        >
                            <img src="<?php echo htmlspecialchars($product["image_path"]); ?>" alt="<?php echo htmlspecialchars($product["alt_text"]); ?>"
                            >
                            <h4 class="category">
                                <?php echo htmlspecialchars($product["category_name"]); ?>
                            </h4>
                            <h3 class="product-name">
                                <?php echo htmlspecialchars($product["product_name"]); ?>
                            </h3>
                            <div class="rating">
                                <?php
                                $rating = round($product["rating"]);
                                for ($i = 0; $i < 5; $i++) {
                                    if ($i < $rating) {
                                        echo "&starf;";
                                    }
                                    else {
                                        echo "&star;";
                                    }
                                }
                                ?>
                                (<?php echo $product["review_count"]; ?>)
                            </div>
                            <div class="container-for-price-stock">
                                <p class="product-price">
                                    Price: ₹<?php echo $product["price"]; ?>
                                </p>
                                <p class="product-stock">
                                    <?php if ($product["stock"] > 0) { ?>
                                        &check; In Stock
                                    <?php } else { ?>
                                        &#10060; Out of Stock
                                    <?php } ?>
                                </p>
                            </div>
                            <div class="container-for-buttons-wishlist">
                                <button class="add-to-cart" data-product-id="<?php echo htmlspecialchars($product["product_id"]); ?>">
                                    Add to Cart
                                </button>
                                <button class="wishlist-remove-btn" data-wishlist-id="<?php echo htmlspecialchars($product["wishlist_id"]); ?>">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="empty-wishlist">
                        <h2>Your Wishlist is Empty</h2>
                        <p>
                            You haven't added any products to your wishlist yet.
                        </p>
                        <a href="products.php">
                            Browse Products
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</main>
<?php include "includes/footer.php"; ?>
<script src="assets/Js/main.js"></script>
<script src="assets/Js/wishlist.js"></script>
</body>
</html>