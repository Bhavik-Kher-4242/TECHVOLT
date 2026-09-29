<?php
session_start();
require_once "includes/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$deliveryCharges = isset($_POST["deliveryCharges"])
    ? (int) $_POST["deliveryCharges"]
    : 50;
if (!in_array($deliveryCharges, [50, 100, 200])) {
    $deliveryCharges = 50;
}
$user_id = $_SESSION["user_id"];
$cart_products = [];
$subtotal = 0;
$gstRate = 0.07;
$gstAmount = 0;
$SavedAddresses = [];
$sql = "SELECT * FROM addresses WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($saveaddress = mysqli_fetch_assoc($result)) {
    $SavedAddresses[] = $saveaddress;
}
$sql = "SELECT * FROM carts WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    $cart = mysqli_fetch_assoc($result);
    $cart_id = $cart["cart_id"];
    $sql = "SELECT cart_items.*, products.*, product_images.* FROM cart_items JOIN products ON cart_items.product_id = products.product_id JOIN product_images ON products.product_id =  product_images.product_id WHERE cart_items.cart_id = ? AND product_images.is_primary = 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cart_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($product = mysqli_fetch_assoc($result)) {
        $cart_products[] = $product;
        $subtotal += $product["price"] * $product["quantity"];
    }
    if (!(count($cart_products) > 0)) {
        header("Location: cart.php");
        exit;
    }
    $gstAmount = $subtotal * $gstRate;
}
$grandTotal = $subtotal + $deliveryCharges + $gstAmount;
if (isset($_POST["place_order"])) {
    foreach ($cart_products as $product) {
        if ($product["quantity"] > $product["stock"]) {
            echo "Out of Stock";
            exit;
        }
    }
    $fullName = $_POST["full-name"];
    $mobileNo = $_POST["number"];
    $address = $_POST["address"];
    $city = $_POST["city"];
    $state = $_POST["state"];
    $pincode = $_POST["pincode"];
    $paymentMethod = $_POST["pay-method"];
    $selectedAddressId = $_POST["address_id"];
    if (empty($fullName) || empty($mobileNo) || empty($address) || empty($city) || empty($state) || empty($pincode) || empty($paymentMethod)) {
        $errorTitle = "Missing Information";
        $errorText = "All Fields are Required.";
    } else {
        if (!preg_match("/^[0-9]{10}$/", $mobileNo)) {
            $errorTitle = "Mobile No is not Valid";
            $errorText = "Enter Valid Mobile/Phone Number.";
        } elseif (!preg_match("/^[0-9]{6}$/", $pincode)) {
            $errorTitle = "Pincode Not Valid";
            $errorText = "Enter Valid Pincode.";
        } else {
            if (!empty($selectedAddressId)) {
                $sql = "SELECT * FROM addresses WHERE address_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $selectedAddressId, $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result)) {
                    $saveAddress = mysqli_fetch_assoc($result);
                    if (
                        $saveAddress["full_name"] === $fullName &&
                        $saveAddress["phone"] === $mobileNo &&
                        $saveAddress["address_line"] === $address &&
                        $saveAddress["city"] === $city &&
                        $saveAddress["state"] === $state &&
                        $saveAddress["pincode"] === $pincode
                    ) {
                        $address_id = $selectedAddressId;
                    } else {
                        $sql = "INSERT INTO addresses (user_id, full_name, phone, address_line, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "issssss", $user_id, $fullName, $mobileNo, $address, $city, $state, $pincode);
                        mysqli_stmt_execute($stmt);
                        $address_id = mysqli_insert_id($conn);
                    }
                }
            } else {
                $sql = "INSERT INTO addresses (user_id, full_name, phone, address_line, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "issssss", $user_id, $fullName, $mobileNo, $address, $city, $state, $pincode);
                mysqli_stmt_execute($stmt);
                $address_id = mysqli_insert_id($conn);
            }
            $paymentStatus = "pending";
            $orderStatus = "pending";

            try {
                // Start transaction
                mysqli_begin_transaction($conn);

                // 1. Create order
                $sql = "INSERT INTO orders  ( user_id, address_id, subtotal, delivery_charge, gst_amount, total_amount, payment_method, payment_status, order_status, shipping_name, shipping_phone, shipping_address ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);

                if (!$stmt) {
                    throw new Exception("Unable to prepare order query.");
                }

                mysqli_stmt_bind_param($stmt, "iiddddssssss", $user_id, $address_id, $subtotal, $deliveryCharges, $gstAmount, $grandTotal, $paymentMethod, $paymentStatus, $orderStatus, $fullName, $mobileNo, $address);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Unable to place order.");
                }

                $order_id = mysqli_insert_id($conn);


                // 2. Update stock and create order items
                foreach ($cart_products as $product) {

                    $productId = (int) $product["product_id"];
                    $quantity = (int) $product["quantity"];

                    if ($quantity < 1) {
                        throw new Exception(
                            "Invalid quantity for " . $product["product_name"] . "."
                        );
                    }

                    /*
     * Get the latest stock directly from database.
     * FOR UPDATE locks this product row until
     * the transaction is completed.
     */
                    $sql = "SELECT product_name, price, stock, status
            FROM products
            WHERE product_id = ?
            FOR UPDATE";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {
                        throw new Exception("Unable to check product stock.");
                    }

                    mysqli_stmt_bind_param(
                        $stmt,
                        "i",
                        $productId
                    );

                    if (!mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        throw new Exception("Unable to check product stock.");
                    }

                    $result = mysqli_stmt_get_result($stmt);
                    $latestProduct = mysqli_fetch_assoc($result);

                    mysqli_stmt_close($stmt);

                    if (!$latestProduct) {
                        throw new Exception(
                            "Product " . $product["product_name"] . " is no longer available."
                        );
                    }

                    if ($latestProduct["status"] !== "active") {
                        throw new Exception(
                            $latestProduct["product_name"] . " is currently unavailable."
                        );
                    }

                    $availableStock = (int) $latestProduct["stock"];

                    /*
     * FINAL stock validation
     */
                    if ($quantity > $availableStock) {

                        throw new Exception(
                            "Sorry, only " .
                                $availableStock .
                                " item(s) of " .
                                $latestProduct["product_name"] .
                                " are available."
                        );
                    }

                    $price = (float) $latestProduct["price"];
                    $itemSubtotal = $price * $quantity;

                    /*
     * Decrease stock only after fresh stock validation.
     */
                    $sql = "UPDATE products
            SET stock = stock - ?
            WHERE product_id = ?
            AND status = 'active'
            AND stock >= ?";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {
                        throw new Exception("Unable to prepare stock update.");
                    }

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iii",
                        $quantity,
                        $productId,
                        $quantity
                    );

                    if (!mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        throw new Exception(
                            "Unable to update stock for " .
                                $latestProduct["product_name"] . "."
                        );
                    }

                    if (mysqli_stmt_affected_rows($stmt) !== 1) {

                        mysqli_stmt_close($stmt);

                        throw new Exception(
                            "Stock changed while placing the order. Please try again."
                        );
                    }

                    mysqli_stmt_close($stmt);

                    /*
     * Insert order item using the latest database price.
     */
                    $sql = "INSERT INTO order_items
            (
                order_id,
                product_id,
                product_name,
                price,
                quantity,
                subtotal
            )
            VALUES (?, ?, ?, ?, ?, ?)";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {
                        throw new Exception("Unable to prepare order item query.");
                    }

                    $productName = $latestProduct["product_name"];

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iisdid",
                        $order_id,
                        $productId,
                        $productName,
                        $price,
                        $quantity,
                        $itemSubtotal
                    );

                    if (!mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        throw new Exception("Unable to save order item.");
                    }

                    mysqli_stmt_close($stmt);
                }


                // 3. Clear cart only after everything succeeds
                $sql = "DELETE FROM cart_items WHERE cart_id = ?";

                $stmt = mysqli_prepare($conn, $sql);

                if (!$stmt) {
                    throw new Exception("Unable to prepare cart query.");
                }

                mysqli_stmt_bind_param($stmt, "i", $cart_id);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Unable to clear cart.");
                }


                // 4. Everything succeeded
                mysqli_commit($conn);

                header("Location: order-confirm.php?order_id=" . $order_id);
                exit;
            } catch (Exception $e) {

                // Undo everything if any step fails
                mysqli_rollback($conn);

                $errorTitle = "Order Failed";
                $errorText = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/cart.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "includes/header.php"; ?>
    <main class="Checkout-page">
        <section class="header-checkout">
            <h2>Checkout</h2>
            <p>Complete your order securely</p>
        </section>
        <form action="checkout.php" method="POST">
            <input type="hidden" name="address_id" id="selected-address-id">
            <input type="hidden" name="deliveryCharges" value="<?php echo $deliveryCharges; ?>">
            <input type="hidden" name="place_order" value="1">
            <section class="checkout-section">
                <div class="left-section">
                    <h2>Delivery Address</h2>
                    <div class="saved-addresses">
                        <h3>Saved Addresses</h3>
                        <?php foreach ($SavedAddresses as $saveAddress) { ?>
                            <div class="address-card">
                                <input type="radio" name="saved-address" data-address-id="<?php echo $saveAddress["address_id"]; ?>"
                                    data-full-name="<?php echo htmlspecialchars($saveAddress["full_name"]); ?>"
                                    data-phone="<?php echo htmlspecialchars($saveAddress["phone"]); ?>"
                                    data-address="<?php echo htmlspecialchars($saveAddress["address_line"]); ?>"
                                    data-city="<?php echo htmlspecialchars($saveAddress["city"]); ?>"
                                    data-state="<?php echo htmlspecialchars($saveAddress["state"]); ?>"
                                    data-pincode="<?php echo htmlspecialchars($saveAddress["pincode"]); ?>">
                                <div class="address-info">
                                    <h4><?php echo htmlspecialchars($saveAddress["full_name"]); ?></h4>
                                    <p><?php echo htmlspecialchars($saveAddress["phone"]); ?></p>
                                    <p><?php echo htmlspecialchars($saveAddress["address_line"]); ?>, <?php echo htmlspecialchars($saveAddress["city"]); ?>, <?php echo htmlspecialchars($saveAddress["state"]); ?> - <?php echo htmlspecialchars($saveAddress["pincode"]); ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <label for="full-name">Full Name:</label>
                    <input type="text" id="full-name" name="full-name">
                    <label for="number">Mobile Number:</label>
                    <input type="tel" name="number" id="number">
                    <label for="address">Address:</label>
                    <input type="text" name="address" id="address">
                    <div class="city-state-container">
                        <label for="city">City:</label>
                        <input type="text" name="city" id="city">
                        <label for="state">State:</label>
                        <input type="text" name="state" id="state">
                    </div>
                    <label for="pincode">Pincode:</label>
                    <input type="text" name="pincode" id="pincode">
                    <div class="payment-method">
                        <h2>Payment Method</h2>
                        <div class="cod">
                            <input type="radio" name="pay-method" value="cod" checked>
                            <label for="cod">Cash on Delivery</label>
                            <p>Pay when your order arrives</p>
                        </div>
                        <div class="online">
                            <input type="radio" name="pay-method" value="online">
                            <label for="pay-method">Online Payment</label>
                            <p>Pay securely online</p>
                        </div>
                    </div>
                </div>
                <div class="right-section">
                    <div class="container-of-orderSummery">
                        <h4 class="title-of-orderSummery-container">Order Summary</h4>
                        <div class="product-checkout-container">
                            <?php foreach ($cart_products as $product) { ?>
                                <div class="checkout-product">
                                    <div class="chechout-product-right">

                                        <h4 class="product-name-checkout"><?php echo htmlspecialchars($product["product_name"]); ?></h4>
                                        <img src="<?php echo htmlspecialchars($product["image_path"]); ?>" alt="<?php echo htmlspecialchars($product["alt_text"]); ?>" class="product-img-check">
                                    </div>
                                    <table class="container-price-qun-total">
                                        <tr class="price-container">

                                            <th>Price:</th>
                                            <td class="product-price-checkout">₹<?php echo htmlspecialchars($product["price"]); ?></td>
                                        </tr>
                                        <tr class="qun-container">
                                            <th>Quantity:</th>
                                            <td class="quntity-checkout"><?php echo htmlspecialchars($product["quantity"]); ?></td>
                                        </tr>
                                        <tr class="total-container">
                                            <th>Total:</th>
                                            <td class="total-checkout">₹<?php echo htmlspecialchars($product["price"]) * htmlspecialchars($product["quantity"]); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                        <table class="orderSummery">
                            <tr>
                                <th>Subtotal:</th>
                                <td>₹<?php echo $subtotal; ?></td>
                            </tr>
                            <tr>
                                <th>Delivary:</th>
                                <td>₹<?php echo $deliveryCharges; ?></td>
                            </tr>
                            <tr>
                                <th> GST(7%):</th>
                                <td>₹<?php echo $gstAmount; ?></td>
                            </tr>
                            <tr class="grand-total">
                                <th>Grand Total:</th>
                                <td>₹<?php echo $grandTotal; ?></td>
                            </tr>
                        </table>
                        <button type="submit" class="checkout-btn">Place Order &#10132;</button>
                    </div>
                </div>
            </section>
        </form>
    </main>
    <?php include "includes/footer.php" ?>
    <?php if (isset($errorTitle) || isset($errorText)) { ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: '<?php echo $errorTitle; ?>',
                text: '<?php echo $errorText; ?>',
                theme: 'dark'
            });
        </script>
    <?php } ?>
    <script src="assets/Js/checkout.js"></script>
</body>

</html>