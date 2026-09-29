<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}

require_once "../includes/database.php";

$user_id = $_SESSION["user_id"];
$wishlist_id = $_POST["wishlist_id"] ?? "";

if (empty($wishlist_id)) {
    echo "invalid_data";
    exit;
}


/* Check wishlist item belongs to logged-in user */

$sql = "SELECT wishlist_id
        FROM wishlist
        WHERE wishlist_id = ?
        AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "ii", $wishlist_id, $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!mysqli_fetch_assoc($result)) {
    echo "not_found";
    exit;
}


/* Remove product from wishlist */

$sql = "DELETE FROM wishlist
        WHERE wishlist_id = ?
        AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "ii", $wishlist_id, $user_id);

if (mysqli_stmt_execute($stmt)) {
    echo "success";
}
else {
    echo "error";
}

?>