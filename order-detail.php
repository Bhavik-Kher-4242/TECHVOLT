<?php
    session_start();
    require_once "includes/database.php";
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
    $order_id = $_GET["order_id"];
    $sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    $orderItems = [];
    $sql = "SELECT order_items.*, product_images.image_path, product_images.alt_text, categories.category_name, returns.return_status FROM order_items JOIN products ON order_items.product_id = products.product_id JOIN categories ON products.category_id = categories.category_id JOIN product_images ON order_items.product_id = product_images.product_id AND product_images.is_primary = 1 LEFT JOIN returns ON order_items.order_item_id = returns.order_item_id WHERE order_items.order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($item = mysqli_fetch_assoc($result)) {
        $orderItems[] = $item;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include "includes/header.php";?>
    <main class="order-detail-page">
        <section class="order-info">
            <button class="back-to-my-order-btn" onclick="window.location.href='my-orders.php'"> &larr; Back to My Orders</button>
            <p>Order <span><?php echo "#TV2026" . str_pad(htmlspecialchars($order["order_id"]), 4, "0", STR_PAD_LEFT) ?></span></p>
            <p>Placed on <span><?php echo date("d F Y", strtotime(htmlspecialchars($order["created_at"]))); ?></span></p>
            <?php
                if ($order["order_status"] == "pending") {
                    $orderClass = "order-stutas-pen";
                    $orderIcon = "&#128309;";
                    $currentStep = 1;
                } elseif ($order["order_status"] == "confirmed") {
                    $orderClass = "order-stutas-confirmed";
                    $orderIcon = "&#10004;";
                    $currentStep = 2;
                } elseif ($order["order_status"] == "cancelled") {
                    $orderClass = "order-stutas-can";
                    $orderIcon = "&#10060;";
                    $currentStep = 0;
                } elseif ($order["order_status"] == "shipped") {
                    $orderClass = "order-stutas-ship";
                    $orderIcon = "&#128666;";
                    $currentStep = 3;
                } elseif ($order["order_status"] == "delivered") {
                    $orderClass = "order-stutas-del";
                    $orderIcon = "&#9989;";
                    $currentStep = 4;
                }
            ?>
            <p class="<?php echo $orderClass ?> status-of-order"><?php echo $orderIcon ?> <?php echo htmlspecialchars(ucfirst($order["order_status"])); ?></p>
        </section>
        <section class="order-detail-status">
            <h2>Order Status</h2>
            <?php if ($order["order_status"] === "cancelled") { ?>
                <div class="cancelled-tracker">
                    <div class="cancelled-circle">&#10008;</div>
                    <div class="cancelled-info">
                        <h3>Order Cancelled</h3>
                        <p>This order has been cancelled.</p>
                    </div>
                </div>
            <?php } else { ?>
            <div class="order-tracker">

                <div class="tracker-step <?php echo ($currentStep >= 1) ? 'completed' : ''; ?>">
                    <div class="circle">
                        <?php echo ($currentStep >= 1) ? '✓' : '○'; ?>
                    </div>
                    <p>Ordered</p>
                </div>

                <div class="tracker-line <?php echo ($currentStep >= 2) ? 'completed' : ''; ?>"></div>

                <div class="tracker-step <?php echo ($currentStep >= 2) ? 'completed' : ''; ?>">
                    <div class="circle">
                        <?php echo ($currentStep >= 2) ? '✓' : '○'; ?>
                        </div>
                    <p>Processing</p>
                </div>

                <div class="tracker-line <?php echo ($currentStep >= 3) ? 'completed' : ''; ?>"></div>

                <div class="tracker-step <?php echo ($currentStep >= 3) ? 'completed' : ''; ?>">
                    <div class="circle">
                        <?php echo ($currentStep >= 3) ? '✓' : '○'; ?>
                        </div>
                    <p>Shipped</p>
                </div>

                <div class="tracker-line <?php echo ($currentStep >= 4) ? 'completed' : ''; ?>"></div>

                <div class="tracker-step <?php echo ($currentStep >= 4) ? 'completed' : ''; ?>">
                    <div class="circle">
                        <?php echo ($currentStep >= 4) ? '✓' : '○'; ?>
                        </div>
                    <p>Delivered</p>
                </div>

            </div>
            <?php } ?>
        </section>
        <div class="return-modal">
            <div class="return-form">
                <div class="return-form-header">
                    <h3>Return Product</h3>
                    <button type="button" class="close-return-form">&times;</button>
                </div>
                <form>
                    <input type="hidden" name="order_item_id" id="return-order-item-id">
                    <label for="return-reason">Reason</label>
                    <select id="return-reason" name="reason">
                        <option value="">Select reason</option>
                        <option value="damaged">Product damaged</option>
                        <option value="wrong-product">Wrong product received</option>
                        <option value="not-working">Product not working</option>
                        <option value="missing-parts">Missing parts/accessories</option>
                        <option value="not-as-described">Product does not match description</option>
                        <option value="other">Other</option>
                    </select>
                    <label for="return-description">Additional Details</label>
                    <textarea
                        id="return-description"
                        name="description"
                        placeholder="Tell us more about the issue..."></textarea>
                    <div class="return-form-actions">
                        <button type="button" class="cancel-return-btn">
                            Cancel
                        </button>
                        <button type="submit" class="submit-return-btn">
                            Submit Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <section class="order-detail-items">
            <h2>Order Items</h2>
            <table class="ordered-items">
                <thead>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name & Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orderItems as $items){ ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($items["image_path"]) ?>" alt="<?php echo htmlspecialchars($items["alt_text"]) ?>" class="img-ordered-item">
                        </td>
                        <td>
                            <div class="name-and-category">

                                <p class="product-name-in-ordered-item"><?php echo htmlspecialchars($items["product_name"]) ?></p>
                                <p class="product-category-in-ordered-item"><?php echo htmlspecialchars($items["category_name"]) ?></p>
                            </div>
                        </td>
                        <td>₹<?php echo htmlspecialchars($items["price"]) ?></td>
                        <td><?php echo htmlspecialchars($items["quantity"]) ?></td>
                        <td>₹<?php echo htmlspecialchars($items["subtotal"]) ?></td>
                        <td>
                            <?php if ($items["return_status"]) { ?>
                                <span class="return-status">
                                    Return 
                                    <?php echo htmlspecialchars(ucfirst($items["return_status"])); ?>
                                </span>
                            <?php } elseif ($order["order_status"] === "delivered") { ?>
                                <button type="button"
                                        class="return-order-btn"
                                        data-order-item-id="<?php echo htmlspecialchars($items["order_item_id"]); ?>">
                                    Return Item
                                </button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
        <?php 
            $sql = "SELECT * FROM addresses WHERE address_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $order["address_id"]);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $address = mysqli_fetch_assoc($result);
        ?>
        <section class="container-ship-add-and-orderSummery">
            <div class="container-for-shipping-address">
                <h2>Shipping Address</h2>
                <h3 class="customername"><?php echo htmlspecialchars($address["full_name"]); ?></h3>
                <p>Address:</p>
                <h4><?php echo $address["address_line"]; ?>, <?php echo htmlspecialchars($address["city"]); ?>, <?php echo htmlspecialchars($address["state"]); ?></h4>
                <h4><?php echo htmlspecialchars($address["phone"]); ?></h4>
                <p>Pincode: <span><?php echo htmlspecialchars($address["pincode"]); ?></span></p>
            </div>
            <div class="container-for-orderSummery">
                <h2>Order Summary</h2>
                <table class="order-summery-table">
                    <tr>
                        <th>Subtotal:</th>
                        <td>₹<?php echo htmlspecialchars($order["subtotal"]); ?></td>
                    </tr>
                    <tr>
                        <th>Shipping:</th>
                        <td>₹<?php echo htmlspecialchars($order["delivery_charge"]); ?></td>
                    </tr>
                    <tr>
                        <th>GST(7%)</th>
                        <td>₹<?php echo htmlspecialchars($order["gst_amount"]); ?></td>
                    </tr>
                    <tr>
                        <th>Grand Total:</th>
                        <td>₹<?php echo htmlspecialchars($order["total_amount"]); ?></td>
                    </tr>
                </table>
            </div>
        </section>
        <section class="payment-detail-container">
            <h2>Order & Payment Information</h2>
            <table class="payment-table">
                <tr>
                    
                    <th>Payment Method</th>
                    <td>
                        <?php
                        if($order["payment_method"] == "cod"){
                            echo "Cash on Delivery";
                        }
                        elseif ($order["payment_method"] == "online") {
                            echo "Online Payment";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Payment Status</th>
                    <td><?php echo htmlspecialchars(ucfirst($order["payment_status"])) ?></td>
                </tr>
                <tr>
                    <th>Order Status</th>
                    <td><?php echo htmlspecialchars(ucfirst($order["order_status"])) ?></td>
                </tr>
                <tr>
                    
                        <?php if ($order["order_status"] === "pending" || $order["order_status"] === "confirmed") { ?>
                        <th>Cancel Order</th>
                        <td>
                            <button type="button" class="cancel-order-btn"
                            data-order-id="<?php echo $order_id; ?>">
                                Cancel Order
                            </button>
                        </td>
                        <?php } ?>
                </tr>
            </table>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
    <script src="assets/Js/order-detail.js"></script>
</body>
</html>