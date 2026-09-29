<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "database.php";

$itemQuantity = 0;

if (isset($_SESSION["user_id"])) {

    $user_id = $_SESSION["user_id"];

    $sql = "SELECT SUM(cart_items.quantity) AS total_quantity
            FROM cart_items
            JOIN carts
                ON cart_items.cart_id = carts.cart_id
            WHERE carts.user_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $cart = mysqli_fetch_assoc($result);

    $CartItemQuantity = $cart["total_quantity"] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
	<link rel="stylesheet" href="assets/css/navbar.css">
	<link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <header>
		<div class="header">
			<div class="logo-section">
				<img id="logo" src="assets/images/header logo.png" alt="TechVolt" />
			</div>
			<div class="search-section">
				<input type="text" name="search" id="search" placeholder="Search">
				<button class="search-btn">
						<img class="search-img" src="assets\images\Icons\search.svg" alt="Search"
							title="Search" />
						</button>
			</div>
			<div class="last-section">
				<button class="cart" onclick="window.location.href='cart.php'">
					<img src="assets\images\Icons\Cart-blue.png" alt="Cart">
					<p class="itemQuantity"><?php echo $CartItemQuantity; ?></p>
				</button>
				<button class="wishlist" onclick="window.location.href='wishlist.php'">
					<img src="assets/images/Icons/heart.png" alt="wishlist">
					<p class="itemSavedQuantity">0</p>
				</button>
				<button class="orders-returns" onclick="window.location.href='my-orders.php'">
					<p>My Orders</p>
					<p> & Returns</p>
				</button>
				<button class="account" onclick="window.location.href='profile.php'">
					<img src="assets\images\Icons\account-Photoroom.png" alt="Account">
				</button>
			</div>
		</div>
		<nav class="nav">
			<a href="index.php">
				<img src="assets\images\Icons\home (2).png" alt="Home">
				<p>Home</p>
			</a>


			<a href="products.php">
				<img src="assets\images\Icons\box (1).png" alt="Products">
				<p>Products</p>
			</a>
			<!-- <a href="category.php">
				<img src="assets\images\Icons\list (1).png" alt="">
				<p>Categories</p>
			</a> -->
			<a href="contact.php">
				<img src="assets\images\Icons\chat (1).png" alt="">
				<p>Contact Us</p>
			</a>
			<a href="about.php">
				<img src="assets\images\Icons\information (1).png" alt="">
				<p>About Us</p>
			</a>
		</nav>
</header>
<script>
    const SearchInput = document.querySelector("#search");
    const SearchButton = document.querySelector(".search-btn");
    function performSearch() {
        const searchValue = SearchInput.value.trim();
        if (searchValue !== "") {
            window.location.href =
                `search.php?search=${encodeURIComponent(searchValue)}`;
        }
    }
    SearchButton.addEventListener("click", function() {
        performSearch();
    });
    SearchInput.addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            performSearch();
        }
    });
</script>
</body>
</html>
