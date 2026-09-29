<?php

session_start();

require_once "../includes/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access."
    ]);

    exit;
}

$image_id = $_POST["image_id"] ?? "";

if (empty($image_id)) {

    echo json_encode([
        "success" => false,
        "message" => "Image ID is missing."
    ]);

    exit;
}

$sql = "SELECT product_id
        FROM product_images
        WHERE image_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $image_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    echo json_encode([
        "success" => false,
        "message" => "Image not found."
    ]);

    exit;
}

$image = mysqli_fetch_assoc($result);

$product_id = $image["product_id"];

$resetSql = "UPDATE product_images
             SET is_primary = 0
             WHERE product_id = ?";

$resetStmt = mysqli_prepare($conn, $resetSql);

mysqli_stmt_bind_param(
    $resetStmt,
    "i",
    $product_id
);

mysqli_stmt_execute($resetStmt);

$primarySql = "UPDATE product_images
               SET is_primary = 1
               WHERE image_id = ?
               AND product_id = ?";

$primaryStmt = mysqli_prepare($conn, $primarySql);

mysqli_stmt_bind_param(
    $primaryStmt,
    "ii",
    $image_id,
    $product_id
);

if (mysqli_stmt_execute($primaryStmt)) {

    echo json_encode([
        "success" => true,
        "message" => "Primary image updated successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Unable to set primary image."
    ]);

}

exit;

?>