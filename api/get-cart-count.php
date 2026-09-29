<?php

session_start();

require_once "../includes/database.php";


/* Check Login */

if (!isset($_SESSION["user_id"])) {
    echo 0;
    exit;
}


$user_id = $_SESSION["user_id"];


/* Get Cart Quantity */

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


/* Display Quantity */

echo $cart["total_quantity"] ?? 0;

?>