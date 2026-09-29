<?php
session_start();
require_once "includes/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION["user_id"];
if (!isset($_GET["order_id"])) {
    header("Location: my-orders.php");
    exit;
}
$order_id = (int) $_GET["order_id"];
$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) == 0) {
    header("Location: my-orders.php");
    exit;
}
$order = mysqli_fetch_assoc($result);
$order_items = [];
$sql = "SELECT order_items.*, product_images.image_path, product_images.alt_text
        FROM order_items
        LEFT JOIN product_images
        ON order_items.product_id = product_images.product_id
        AND product_images.is_primary = 1
        WHERE order_items.order_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($item = mysqli_fetch_assoc($result)) {
    $order_items[] = $item;
}
$display_order_id = "TV2026" . str_pad($order_id, 4, "0", STR_PAD_LEFT);
$delivery_charge = (float) $order["delivery_charge"];
if ($delivery_charge == 200) {
    $delivery_text = "1-Day Delivery";
    $delivery_days = 1;
}
elseif ($delivery_charge == 100) {
    $delivery_text = "3-Day Delivery";
    $delivery_days = 3;
}
else {
    $delivery_text = "7-Day Delivery";
    $delivery_days = 7;
}
$order_date = new DateTime($order["created_at"]);
$order_date->modify("+" . $delivery_days . " days");
$estimated_delivery = $order_date->format("j M Y");
if ($order["payment_method"] == "cod") {
    $payment_method = "Cash on Delivery";
}
else {
    $payment_method = "Online Payment";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include "includes/header.php"; ?>
<main class="order-confirm-page">
    <section class="confirm-detail">
        <div class="circle-icon">
            <h2>&check;</h2>
        </div>
        <p>Order Placed Successfully!</p>
        <p>Your order has been confirmed.</p>
        <p>Thank you for shopping with TechVolt.</p>
        <p>
            Order ID: #<?php echo $display_order_id; ?>
        </p>
        <p>
            Estimated Delivery: <?php echo $estimated_delivery; ?>
        </p>
    </section>
    <section class="order-detail">
        <h2>Order Detail</h2>
        <?php foreach ($order_items as $item) { ?>
            <div class="orders">
                <div class="product-order-info">
                    <div class="order-product-image">
                        <?php if (!empty($item["image_path"])) { ?>
                            <img
                                src="<?php echo htmlspecialchars($item["image_path"]); ?>"
                                alt="<?php echo htmlspecialchars($item["alt_text"] ?? $item["product_name"]); ?>"
                            >
                        <?php } ?>
                    </div>
                    <div class="order-product-name">
                        <h4>
                            <?php echo htmlspecialchars($item["product_name"]); ?>
                        </h4>
                        <p>
                            Quantity: <?php echo htmlspecialchars($item["quantity"]); ?>
                        </p>
                    </div>
                </div>
                <div class="order-product-price">
                    <p>
                        ₹<?php echo number_format($item["subtotal"], 2); ?>
                    </p>
                </div>
            </div>
        <?php } ?>
        <table class="orderSummery order-confirm-summary">
            <tr>
                <th>Subtotal:</th>
                <td>
                    ₹<?php echo number_format($order["subtotal"], 2); ?>
                </td>
            </tr>
            <tr>
                <th>Delivery:</th>
                <td>
                    ₹<?php echo number_format($order["delivery_charge"], 2); ?>
                </td>
            </tr>
            <tr>
                <th>GST(7%):</th>
                <td>
                    ₹<?php echo number_format($order["gst_amount"], 2); ?>
                </td>
            </tr>
            <tr class="grand-total">
                <th>Grand Total:</th>
                <td>
                    ₹<?php echo number_format($order["total_amount"], 2); ?>
                </td>
            </tr>
        </table>
    </section>
    <section class="delivery-info">
        <h2>Delivery Information</h2>
        <table>
            <tr>
                <th>Name:</th>
                <td>
                    <?php echo htmlspecialchars($order["shipping_name"]); ?>
                </td>
            </tr>
            <tr>
                <th>Mobile No.</th>
                <td>
                    <?php echo htmlspecialchars($order["shipping_phone"]); ?>
                </td>
            </tr>
            <tr>
                <th>Address</th>
                <td>
                    <?php echo htmlspecialchars($order["shipping_address"]); ?>
                </td>
            </tr>
            <tr>
                <th>Payment:</th>
                <td>
                    <?php echo $payment_method; ?>
                </td>
            </tr>
            <tr>
                <th>Delivery:</th>
                <td>
                    <?php echo $delivery_text; ?>
                </td>
            </tr>
            <tr>
                <th>Payment Status:</th>
                <td>
                    <?php echo ucfirst($order["payment_status"]); ?>
                </td>
            </tr>
        </table>
    </section>
    <section class="confirm-btn-section">
        <button
            class="con-shop"
            onclick="window.location.href='products.php'"
        >
            &#10502; Continue Shopping
        </button>
        <button
            class="view-order"
            onclick="window.location.href='my-orders.php'"
        >
            View My Orders &#10503;
        </button>
    </section>
</main>
<?php include "includes/footer.php"; ?>
    <script src="assets/Js/main.js"></script>
</body>
</html>