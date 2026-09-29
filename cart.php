<?php
    session_start();
    require_once "includes/database.php";

    if(!isset($_SESSION["user_id"])){
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION["user_id"];
    $cart_products = [];
    $subtotal = 0;
    $sql = "SELECT * FROM carts WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $cart = mysqli_fetch_assoc($result);
        $cart_id = $cart["cart_id"];
        $sql = "SELECT cart_items.*, products.*, categories.category_name, product_images.* FROM cart_items  JOIN products ON products.product_id = cart_items.product_id JOIN categories ON products.category_id = categories.category_id JOIN product_images ON products.product_id = product_images.product_id WHERE cart_items.cart_id = ? AND product_images.is_primary = 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($product = mysqli_fetch_assoc($result)){
            $cart_products[] = $product;
        }
    }
    $gstRate = 0.07;
    $gstAmount = $subtotal * $gstRate;
    $deliveryCharge = 50;
    $grandTotal = $subtotal + $deliveryCharge + $gstAmount;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechVolt-Cart</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/product.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "includes/header.php" ?>
    <main class="cart-body">
        <section class="header-of-cart">
            <h2>Your Cart</h2>
            <p class="quantity-of-items"><span class="item-count"><?php echo count($cart_products); ?></span> items in your Cart</p>
        </section>
        <section class="conatiner-of-product-delivery-orderSummery">
            <div class="container-of-product <?php echo count($cart_products) == 0 ? 'empty-cart-product' : ''; ?>">
                <div class="empty-cart <?php echo count($cart_products) > 0 ? 'hide-empty-cart' : ''; ?> ">
                    <div class="empty-cart-icon">
                        <img src="assets/images/Icons/empty-cart.png" alt="Empty Cart">
                    </div>

                    <h3>Your Cart is Empty</h3>

                    <p>
                        Looks like you haven't added anything to your cart yet.
                    </p>

                    <button class="continue-shopping-btn"
                            onclick="window.location.href='products.php'">
                        Continue Shopping &#10132;
                    </button>
                </div>
                <?php if(count($cart_products) > 0){ ?>
                <h4 class="title-of-product-container all-products-title">All Products</h4>
                    <?php foreach($cart_products as $product){ ?>
                    <?php $subtotal += (htmlspecialchars($product["price"]) * htmlspecialchars($product["quantity"])); ?>
                    <div class="cart-product" data-cart-item-id="<?php echo htmlspecialchars($product["cart_item_id"]); ?>" data-price="<?php echo htmlspecialchars($product["price"]); ?>" data-stock="<?php echo htmlspecialchars($product["stock"]); ?>">
                        <img class="product-cart-img" src="<?php echo htmlspecialchars($product["image_path"]); ?>" alt="<?php echo htmlspecialchars($product["alt_text"]); ?>">
                        <div class="product-info">
                            <h3 class="cart-product-title"><?php echo htmlspecialchars($product["product_name"]); ?></h3>
                            <p class="cart-product-category"><?php echo htmlspecialchars($product["category_name"]); ?></p>
                            <p class="cart-product-price">Price: ₹<?php echo htmlspecialchars($product["price"]); ?></p>
                            <p class="stock-check">&check; In Stock</p>
                            <div class="cart-action">
                                <div class="cart-Quantity-container">
                                <button class="minus-btn">
                                    <img src="assets/images/Icons/minus.png" alt="-" class="images">
                                </button>
                                <p class="Quantity-in-cart"><?php echo $product["quantity"] ?></p>
                                <button class="plus-btn">
                                    <img src="assets/images/Icons/plus-symbol-button.png" alt="-" class="images">
                                </button>
                                </div>
                                <button class="cart-remove-btn"><img src="assets/images/Icons/rubbish-bin (1).png" alt="" class="images">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="container-of-deliveryOptions">
                <h4 class="title-of-deliveryOptions-container">Delivery Method</h4>
                <div class="delivery-options">
                    <div class="options">
                        <div class="option">
                            <input type="radio" name="deliveryOption" class="radio-for-delivery">
                        </div>
                        <div class="option-info">
                            <p>1 - day Delivery</p>
                            <p data-price="200" class="delivery-price">₹200</p>
                            <p>&#8226; Within 1 working day</p>
                        </div>
                    </div>
                    <div class="options">
                        <div class="option">
                            <input type="radio" name="deliveryOption" class="radio-for-delivery">
                        </div>
                        <div class="option-info">
                            <p>3 - day Delivery</p>
                            <p data-price="100" class="delivery-price">₹100</p>
                            <p>&#8226; Within 3 working days</p>
                        </div>
                    </div>
                    <div class="options">
                        <div class="option">
                            <input type="radio" name="deliveryOption" class="radio-for-delivery" checked>
                        </div>
                        <div class="option-info">
                            <p>7 - day Delivery</p>
                            <p data-price="50" class="delivery-price">₹50</p>
                            <p>&#8226; Within 7 working days</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-of-orderSummery">
                <h4 class="title-of-orderSummery-container">Order Summary</h4>
                <table class="orderSummery">
                    <tr>
                        <th>Subtotal:</th>
                        <td class="subtotal-amount">₹<?php echo $subtotal; ?></td>
                    </tr>
                    <tr>
                        <th>Delivery:</th>
                        <td class="delivery-amount">₹<?= $deliveryCharge; ?></td>
                    </tr>
                    <tr>
                        <th> GST(7%):</th>
                        <td class="gst-amount">₹<?= $gstAmount; ?></td>
                    </tr>
                    <tr class="grand-total">
                        <th>Grand Total:</th>
                        <td class="grand-total-amount">₹<?= $grandTotal; ?></td>
                    </tr>
                </table>
                <form action="checkout.php" method="post">
                    <input type="hidden" name="deliveryCharges" value="50" class="deliveryChargesAmount">
                    <button class="checkout-btn" type="submit">Proceed to Checkout &#10132;</button>
                </form>
            </div>
            <?php } ?>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
    <script src="assets/Js/cart.js?v=2"></script>
</body>

</html>