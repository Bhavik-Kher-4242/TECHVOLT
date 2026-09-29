<?php
    session_start();
    require_once "../includes/database.php";
    if (!isset($_SESSION["user_id"])) {
        echo "login_required";
        exit;
    }
    $user_id = $_SESSION["user_id"];
    $full_name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    if (empty($full_name) || empty($username) || empty($email)) {
        echo "empty_fields";
        exit;
    }
    $sql = "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo "already_exists";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "invalid_email";
    }
    else{
    $sql = "UPDATE users SET full_name = ?, username = ?, email = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $full_name, $username, $email, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "error";
    }
    }
    
?>