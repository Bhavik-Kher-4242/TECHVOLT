<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}

require_once "../includes/database.php";

$user_id = (int) $_SESSION["user_id"];
$cart_item_id = (int) ($_POST["cart_item_id"] ?? 0);
$quantity = (int) ($_POST["quantity"] ?? 0);

if ($cart_item_id <= 0 || $quantity < 1) {
    echo "invalid_quantity";
    exit;
}

/*
 * Check cart item ownership and get latest product stock
 */
$sql = "SELECT
            ci.cart_item_id,
            ci.product_id,
            p.product_name,
            p.stock,
            p.status
        FROM cart_items ci
        INNER JOIN carts c
            ON ci.cart_id = c.cart_id
        INNER JOIN products p
            ON ci.product_id = p.product_id
        WHERE ci.cart_item_id = ?
        AND c.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo "error";
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $cart_item_id,
    $user_id
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo "error";
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$cartItem = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$cartItem) {
    echo "invalid_cart_item";
    exit;
}

if ($cartItem["status"] !== "active") {
    echo "product_inactive";
    exit;
}

$availableStock = (int) $cartItem["stock"];

/*
 * FINAL quantity check
 */
if ($quantity > $availableStock) {
    echo "out_of_stock";
    exit;
}

/*
 * Update only this user's cart item
 */
$sql = "UPDATE cart_items ci
        INNER JOIN carts c
            ON ci.cart_id = c.cart_id
        SET ci.quantity = ?
        WHERE ci.cart_item_id = ?
        AND c.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo "error";
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iii",
    $quantity,
    $cart_item_id,
    $user_id
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo "error";
    exit;
}

mysqli_stmt_close($stmt);

echo "quantity";
?>