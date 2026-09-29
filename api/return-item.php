<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    echo "login_required";
    exit;
}

require_once "../includes/database.php";

$user_id = $_SESSION["user_id"];

$order_item_id = $_POST["order_item_id"] ?? "";
$reason = $_POST["reason"] ?? "";
$description = $_POST["description"] ?? "";

if (empty($order_item_id) || empty($reason)) {
    echo "invalid_data";
    exit;
}


/* Check that this order item belongs to
   the logged-in user and the order is delivered */

$sql = "SELECT order_items.order_item_id, orders.order_id, orders.order_status
        FROM order_items
        JOIN orders
        ON order_items.order_id = orders.order_id
        WHERE order_items.order_item_id = ?
        AND orders.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $order_item_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$orderItem = mysqli_fetch_assoc($result);


if (!$orderItem) {
    echo "item_not_found";
    exit;
}


/* Return allowed only for delivered orders */

if ($orderItem["order_status"] !== "delivered") {
    echo "return_not_allowed";
    exit;
}


/* Check whether this item has already been returned */

$sql = "SELECT return_id
        FROM returns
        WHERE order_item_id = ?
        AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $order_item_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_fetch_assoc($result)) {
    echo "already_returned";
    exit;
}


/* Insert return request */

$return_status = "requested";

$sql = "INSERT INTO returns
        (order_item_id, user_id, reason, description, return_status)
        VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iisss",
    $order_item_id,
    $user_id,
    $reason,
    $description,
    $return_status
);


if (mysqli_stmt_execute($stmt)) {
    echo "success";
} else {
    echo "error";
}

?>