<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}

require_once "../includes/database.php";

$user_id = (int) $_SESSION["user_id"];
$product_id = (int) ($_POST["product_id"] ?? 0);

if ($product_id <= 0) {
    echo "invalid_product";
    exit;
}

/*
 * Check whether product exists and is active.
 */
$sql = "SELECT product_id
        FROM products
        WHERE product_id = ?
        AND status = 'active'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$product) {
    echo "product_not_found";
    exit;
}

/*
 * Check whether product is already in wishlist.
 */
$sql = "SELECT wishlist_id
        FROM wishlist
        WHERE user_id = ?
        AND product_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$wishlistItem = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if ($wishlistItem) {
    echo "already_exists";
    exit;
}

/*
 * Add product to wishlist.
 */
$sql = "INSERT INTO wishlist
        (user_id, product_id)
        VALUES (?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $product_id
);

if (mysqli_stmt_execute($stmt)) {
    echo "success";
} else {
    echo "error";
}

mysqli_stmt_close($stmt);
?>