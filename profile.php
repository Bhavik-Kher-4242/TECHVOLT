<?php
    session_start();
    require_once "includes/database.php";
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
    $sql = "SELECT full_name, username, profile_image, email FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    if (!$user) {
    echo "User not found.";
    exit;
    }
    $sql = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orderCount = mysqli_fetch_assoc($result);
    $totalOrders = $orderCount["total_orders"];

    $sql = "SELECT COUNT(*) as wishlist_items FROM wishlist WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $wishlistItems = mysqli_fetch_assoc($result);
    $totalWishlistItems = $wishlistItems["wishlist_items"];

    $sql = "SELECT cart_id FROM carts WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $totalCartItems = 0;
    if (mysqli_num_rows($result) > 0) {
        $cart = mysqli_fetch_assoc($result);
        $cart_id = $cart["cart_id"];
    
        $sql = "SELECT COUNT(*) as Cart_all_items FROM cart_items WHERE cart_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $CartItems = mysqli_fetch_assoc($result);
        $totalCartItems = $CartItems["Cart_all_items"];
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "includes/header.php" ?>
    <main class="profile-page">
        <div class="hero-of-profile">
            <h2 class="title-profile">My Profile</h2>
            <p>Manage your TechVolt account information and preferences.</p>
        </div>
        <div class="profile-information">
            <h3 class="main-profile-heading">Profile Information</h3>
            <div class="image-profile-container">
                <div class="image-profile">
                    <img src="
                    <?php 
                        if($user["profile_image"] !== NULL){
                            echo htmlspecialchars($user["profile_image"]);
                        }
                        else{
                            echo "assets/images/profile/default-user.jpg";
                        }
                    ?>
                    " alt="Profile Pic" class="profile-image">
                </div>
                <button class="change-profile-pic">Change Profile Picture</button>
                <input type="file" id="profile-image-input" accept="image/*">
                <button type="button" class="upload-profile-pic"> Upload Picture </button>
            </div>
            <div class="basic-info-container">
                <table class="info-table">
                    <tr>
                        <th class="info-heading">Full Name</th>
                        <td class="user-info"><?php echo htmlspecialchars($user["full_name"]); ?></td>
                    </tr>
                    <tr>
                        <th class="info-heading">Username</th>
                        <td class="user-info"><?php echo htmlspecialchars($user["username"]); ?></td>
                    </tr>
                    <tr>
                        <th class="info-heading">Email</th>
                        <td class="user-info"><?php echo htmlspecialchars($user["email"]); ?></td>
                    </tr>
                </table>
                <button class="update-profile">Update Profile</button>
            </div>
        </div>
        <h2>Account Overview</h2>
        <section class="account-overview">
            <div class="order-overview">
                <img src="assets\images\Icons\box.png" alt="Product">
                <h4>
                    Total Orders
                </h4>
                <p class="total-order-for-profilePage"><?php echo str_pad($totalOrders, 2, "0",  STR_PAD_LEFT); ?></p>
                <button onclick="window.location.href = 'my-orders.php'">View Orders</button>
            </div>
            <div class="cart-overview">
                <img src="assets\images\Icons\Cart-black.png" alt="Cart">
                <h4>
                    Cart Item
                </h4>
                <p class="total-product-in-cart-for-profilePage"><?php echo str_pad($totalCartItems, 2, "0",  STR_PAD_LEFT); ?></p>
                <button onclick="window.location.href = 'cart.php'">Your Cart</button>
            </div>
            <div class="wishlist-overview">
                <img src="assets/images/Icons/heart.png" alt="Product">
                <h4>
                    WishList
                </h4>
                <p class="total-order-for-profilePage"><?php echo str_pad($totalWishlistItems, 2, "0",  STR_PAD_LEFT); ?></p>
                <button onclick="window.location.href = 'wishlist.php'">Your WishList</button>
            </div>
        </section>
        <div class="update-profile-modal">
            <div class="update-profile-form">
                <div class="update-profile-header">
                    <h3>Update Profile</h3>
                    <button type="button" class="close-update-profile"> &times; </button>
                </div>
                <form>
                    <label for="profile-full-name"> Full Name </label>
                    <input type="text" id="profile-full-name" name="full_name" value="" placeholder="Enter Full Name" >
                    <label for="profile-username"> Username </label>
                    <input type="text" id="profile-username" name="username" value="" placeholder="Enter UserName" >
                    <label for="profile-email"> Email </label>
                    <input type="email" id="profile-email" name="email" placeholder="Enter Email" >
                    <div class="update-profile-actions">
                        <button type="button" class="cancel-update-profile"> Cancel </button>
                        <button type="submit" class="submit-update-profile"> Update Profile </button>
                    </div>
                </form>
            </div>
        </div>
        <section class="logout-section">
            <h2>Logout</h2>
            <button class="logout-btn">Logout</button>
        </section>
    </main>
    <script src="assets/Js/main.js"></script>
    <script src="assets/Js/profile.js"></script>
    <?php include "includes/footer.php" ?>
</body>

</html>