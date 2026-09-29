<?php

session_start();

require_once "../includes/database.php";


/* Check Login */

if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}

$user_id = $_SESSION["user_id"];


/* Check File */

if (!isset($_FILES["profile_image"])) {
    echo "no_file";
    exit;
}

$file = $_FILES["profile_image"];


/* Check Upload Error */

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo "upload_error";
    exit;
}


/* Check File Size */

$maxFileSize = 2 * 1024 * 1024; // 2 MB

if ($file["size"] > $maxFileSize) {
    echo "file_too_large";
    exit;
}


/* Check Image */

$imageInfo = getimagesize($file["tmp_name"]);

if ($imageInfo === false) {
    echo "invalid_image";
    exit;
}


/* Check Image Type */

$allowedTypes = [
    IMAGETYPE_JPEG,
    IMAGETYPE_PNG,
    IMAGETYPE_WEBP
];

if (!in_array($imageInfo[2], $allowedTypes)) {
    echo "invalid_type";
    exit;
}


/* Create File Extension */

$extension = image_type_to_extension($imageInfo[2], false);


/* Create Unique File Name */

$fileName = "user_" . $user_id . "_" . time() . "." . $extension;


/* Upload Directory */

$uploadDirectory = "../assets/images/Profile/";


/* Create Full File Path */

$filePath = $uploadDirectory . $fileName;


/* Move Image */

if (!move_uploaded_file($file["tmp_name"], $filePath)) {
    echo "upload_failed";
    exit;
}


/* Database Path */

$databasePath = "assets/images/Profile/" . $fileName;

/* user fetch */
$sql = "SELECT profile_image
        FROM users
        WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

/* Update Database */

$sql = "UPDATE users
        SET profile_image = ?
        WHERE user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $databasePath,
    $user_id
);

if (mysqli_stmt_execute($stmt)) {
    echo "success";
    if (
        !empty($user["profile_image"]) &&
        $user["profile_image"] !== "assets/images/Profile/default-user.jpg"
    ) {
        $oldImagePath = "../" . $user["profile_image"];

        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }
} else {
    echo "database_error";
}

?>