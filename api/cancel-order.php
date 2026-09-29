<?php
session_start();
require_once "../includes/database.php";
if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}
$user_id = $_SESSION["user_id"];
$order_id = $_POST["order_id"] ?? "";
if (empty($order_id)) {
    echo "invalid_order";
    exit;
}
$sql = "SELECT order_status FROM orders
        WHERE order_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
if (!$order) {
    echo "order_not_found";
    exit;
}
if ($order["order_status"] !== "pending" &&
    $order["order_status"] !== "confirmed") {
    echo "cannot_cancel";
    exit;
}
$sql = "UPDATE orders SET order_status = ? WHERE order_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
$status = "cancelled";
mysqli_stmt_bind_param( $stmt, "sii", $status, $order_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    echo "success";
} else {
    echo "error";
}
?>