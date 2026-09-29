<?php
session_start();
require_once "includes/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$MyOrders = [];
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($order = mysqli_fetch_assoc($result)) {
    $MyOrders[] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders and Returns</title>
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include "includes/header.php"; ?>
    <main>

        <section class="header-MyOrders">
            <h2>My Orders & Returns</h2>
            <p>Track and manage your orders</p>
        </section>
        <section class="all-my-orders">
            <?php foreach ($MyOrders as $order) { ?>
                <div class="my-order">
                    <div class="order-top">
                        <p>Order <span><?php echo "#TV2026" . str_pad($order["order_id"], 4, "0", STR_PAD_LEFT) ?></span></p>
                        <?php
                        if ($order["order_status"] == "pending") {
                            $orderClass = "order-stutas-pen";
                            $orderIcon = "&#128309;";
                        } elseif ($order["order_status"] == "confirmed") {
                            $orderClass = "order-stutas-confirmed";
                            $orderIcon = "&#10004;";
                        } elseif ($order["order_status"] == "cancelled") {
                            $orderClass = "order-stutas-can";
                            $orderIcon = "&#10060;";
                        } elseif ($order["order_status"] == "shipped") {
                            $orderClass = "order-stutas-ship";
                            $orderIcon = "&#128666;";
                        } elseif ($order["order_status"] == "delivered") {
                            $orderClass = "order-stutas-del";
                            $orderIcon = "&#9989;";
                        }
                        ?>
                        <p class="<?php echo $orderClass; ?>"><?php echo $orderIcon; ?> <span><?php echo ucfirst($order["order_status"]); ?></span></p>
                    </div>
                    <p>Placed on: <span><?php echo date("d M Y", strtotime(htmlspecialchars($order["created_at"]))); ?></span></p>
                    <?php
                    $orderProducts = [];
                    $sql = "SELECT order_items.*, product_images.image_path, product_images.alt_text FROM order_items JOIN product_images ON order_items.product_id = product_images.product_id AND product_images.is_primary = 1 WHERE order_items.order_id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $order["order_id"]);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    while ($product = mysqli_fetch_assoc($result)) {
                        $orderProducts[] = $product;
                    }
                    ?>
                    <div class="order-product-section">
                        <img src="<?php echo htmlspecialchars($orderProducts[0]["image_path"]); ?>" alt="<?php echo htmlspecialchars($orderProducts[0]["alt_text"]); ?>" class="order-product-img">
                        <div class="order-product-info-section">
                            <h4><?php echo htmlspecialchars($orderProducts[0]["product_name"]); ?></h4>
                            <p>Qty: <span><?php echo htmlspecialchars($orderProducts[0]["quantity"]); ?></span></p>
                            <p>₹<?php echo htmlspecialchars($orderProducts[0]["subtotal"]); ?></p>
                            <?php if (count($orderProducts) > 1) { ?>
                                <p class="order-item-qty"><span><?php echo count($orderProducts) - 1; ?></span> more item<?php if ((count($orderProducts) - 1) > 1) {
                                    echo "s";
                                                                                                                                } ?></p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="order-detail-last-section">
                        <p>Total: ₹<span><?php echo htmlspecialchars($order["total_amount"]); ?></span></p>
                        <div class="order-actions">
                            <button class="view-detail-for-order" onclick="window.location.href='order-detail.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>'">
                                View Order Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
</body>

</html>