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
// Get image information
$sql = "SELECT image_path FROM product_images WHERE image_id = ?";

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
$image_path = $image["image_path"];

$deleteSql = "DELETE FROM product_images WHERE image_id = ?";

$deleteStmt = mysqli_prepare($conn, $deleteSql);
mysqli_stmt_bind_param($deleteStmt, "i", $image_id);

if (!mysqli_stmt_execute($deleteStmt)) {

    echo json_encode([
        "success" => false,
        "message" => "Unable to delete image record."
    ]);

    exit;
}
$file_path = "../" . $image_path;

if (file_exists($file_path)) {
    unlink($file_path);
}

echo json_encode([
    "success" => true,
    "message" => "Image removed successfully."
]);

exit;

?>