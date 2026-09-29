<?php
	session_start();
	require_once "includes/database.php";
	if(!isset($_SESSION["user_id"])){
		header("Location: login.php");
		exit;
	}
	$featuredProduct = [];
	$sql = "SELECT products.*, categories.category_name, product_images.image_path, product_images.alt_text FROM products JOIN categories ON products.category_id = categories.category_id JOIN product_images ON products.product_id = product_images.product_id AND product_images.is_primary = 1 WHERE products.featured = 1 AND products.stock > 0";
	$result = mysqli_query($conn, $sql);
    while ($product = mysqli_fetch_assoc($result)) {
        $featuredProduct[] = $product;
    }

?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>TechVolt</title>
	<link rel="stylesheet" href="assets/css/style.css" />
	<link rel="stylesheet" href="assets/css/product.css">
</head>

<body>
	<?php include "includes/header.php" ?>
	<main>
		<section class="hero">
			<div class="hero-container">
				<h1>Electronic Components</h1>
				<p>Build Your Next Project</p>
				<button class="shop-btn" onclick="window.location.href='products.php'">Shop Now &#10132;</button>
			</div>
		</section>
		<h2 class="new-product-title">&bull; Featured Products &bull;</h2>
		<section class="new-products-container">
			<?php foreach($featuredProduct as $product){ ?>
			<div id="<?php echo htmlspecialchars($product["product_id"]); ?>" class="products" data-product-id="<?php echo htmlspecialchars($product["product_id"]); ?>">
				<img src="<?php echo htmlspecialchars($product["image_path"]); ?>" alt="<?php echo htmlspecialchars($product["alt_text"]); ?>">
				<h4 class="category"><?php echo htmlspecialchars($product["category_name"]); ?></h4>

				<h3 class="product-name"><?php echo htmlspecialchars($product["product_name"]); ?></h3>
				<div class="rating"><?php echo generateStar($product["rating"]); ?>(<?php echo $product["review_count"]; ?>)</div>
				<p class="product-short-discription"><?php echo htmlspecialchars($product["short_description"]); ?></p>
				<div class="container-for-price-stock">
					<p class="product-price">Price: ₹<?php echo htmlspecialchars($product["price"]); ?></p>
					<p class="product-stock">&#9679; <?php echo CheckStock($product["stock"]); ?></p>
				</div>
				<div class="container-for-buttons">
					<button  class="add-to-cart" data-product-id="<?php echo $product["product_id"]; ?>"> Add to Cart </button>

				<button  class="add-to-wishlist" data-product-id="<?php echo $product["product_id"]; ?>"> &#10084; Wishlist </button>
				</div>
			</div>
			<?php } ?>
		</section>
		<section class="techvolt-specification">
			<h2 class="techvolt-specification-title">&bull;Why Choose TechVolt?</h2>
			<p class="techvolt-specification-text">Quality Components for Your projects</p>
			<div class="techvolt-specification-container">
				<div class="specification">
					<div class="icon-circle">
					<img src="assets\images\Icons\quality-assurance.png" alt="Quality Components ">
					</div>
					<div class="specification-text-container">
						<h2>Quality Components</h2>
						<p>&bull; Reliable and quality electronic components for your projects.</p>
					</div>
				</div>
				<div class="specification">
					<div class="icon-circle">
					<img src="assets\images\Icons\tag 1.png" alt="Price-tag-image">
					</div>
					<div class="specification-text-container">
						<h2>Best Prices</h2>
						<p>&bull; Get the components you need at competitive and affordable prices.</p>
					</div>
				</div>
				<div class="specification">
					<div class="icon-circle">
						<img src="assets\images\Icons\delivery-truck (2).png" alt="Fast-delivery-image">
					</div>
					<div class="specification-text-container">
						<h2>Fast Delivery</h2>
						<p>&bull; Get your electronic components delivered quickly and safely.</p>
					</div>
				</div>
				<div class="specification">
					<div class="icon-circle">
						<img src="assets\images\Icons\shopping-bag.png" alt="Secure Shopping">
					</div>
					<div class="specification-text-container">
						<h2>Secure Shopping</h2>
						<p>&bull; Shop with confidence with a safe and secure online experience.</p>
					</div>
				</div>
			</div>
		</section>
	</main>
	<?php include "includes/footer.php" ?>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="assets/Js/main.js"></script>
	<script src="assets/Js/index.js"></script>
</body>

</html>
<?php 
function generateStar($rating) {

    $product_rating = round($rating);

    $string_rating = "";

    for ($i = 0; $i < 5; $i++) {

        if ($i < $product_rating) {

            $string_rating .= "&#9733;";

        }
        else {

            $string_rating .= "&#9734;";

        }
    }

    return $string_rating;
}
function CheckStock($stock) {

    if ($stock > 0) {
        return "In Stock";
    }
    else {
        return "Out of Stock";
    }
}
?>