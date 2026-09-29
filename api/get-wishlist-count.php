<?php

session_start();

require_once "../includes/database.php";


/* Check Login */

if (!isset($_SESSION["user_id"])) {
    echo 0;
    exit;
}

$user_id = $_SESSION["user_id"];


/* Get Wishlist Count */

$sql = "SELECT COUNT(*) AS total_wishlist
        FROM wishlist
        WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$wishlist = mysqli_fetch_assoc($result);


/* Display Count */

echo $wishlist["total_wishlist"] ?? 0;

?>