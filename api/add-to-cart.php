<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}

require_once "../includes/database.php";

$user_id = (int) $_SESSION["user_id"];
$product_id = (int) ($_POST["product_id"] ?? 0);
$quantity = (int) ($_POST["quantity"] ?? 0);

if ($product_id <= 0 || $quantity < 1) {
    echo "invalid_quantity";
    exit;
}

/*
 * Check product and current stock.
 * Only active products can be added to cart.
 */
$sql = "SELECT product_id, stock, status
        FROM products
        WHERE product_id = ?";

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

if ($product["status"] !== "active") {
    echo "product_not_found";
    exit;
}

$stock = (int) $product["stock"];

if ($stock <= 0 || $quantity > $stock) {
    echo "out_of_stock";
    exit;
}

/*
 * Find user's cart.
 */
$sql = "SELECT cart_id
        FROM carts
        WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$cart = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*
 * Create cart if user does not have one.
 */
if (!$cart) {

    $sql = "INSERT INTO carts (user_id)
            VALUES (?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "error";
        exit;
    }

    $cart_id = mysqli_insert_id($conn);

    mysqli_stmt_close($stmt);

} else {

    $cart_id = (int) $cart["cart_id"];
}

/*
 * Check whether product already exists in cart.
 */
$sql = "SELECT cart_item_id, quantity
        FROM cart_items
        WHERE cart_id = ?
        AND product_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $cart_id, $product_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$cartItem = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if ($cartItem) {

    $newQuantity =
        (int) $cartItem["quantity"] + $quantity;

    if ($newQuantity > $stock) {
        echo "out_of_stock";
        exit;
    }

    $sql = "UPDATE cart_items
            SET quantity = ?
            WHERE cart_item_id = ?
            AND cart_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $newQuantity,
        $cartItem["cart_item_id"],
        $cart_id
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "update";
    } else {
        echo "error";
    }

    mysqli_stmt_close($stmt);

} else {

    $sql = "INSERT INTO cart_items
            (cart_id, product_id, quantity)
            VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $cart_id,
        $product_id,
        $quantity
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "error";
    }

    mysqli_stmt_close($stmt);
}
?>