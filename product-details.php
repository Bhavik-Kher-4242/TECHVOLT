<?php
    
    require_once "includes/database.php";
    $product_id = $_GET["id"];
    $sql = "SELECT products.*, categories.category_name FROM products JOIN categories ON products.category_id = categories.category_id WHERE products.product_id = ? AND categories.status = 'active' AND products.status = 'active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    $sql = "SELECT * FROM product_images WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $images = [];
    while($image = mysqli_fetch_assoc($result)){
        $images[] = $image;
    }
    $specifications = [];
    $sql = "SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($spec = mysqli_fetch_assoc($result)){
        $specifications[] = $spec;
    }

    $classStock = "";
    function displayStockCheck($stock){
        if($stock > 5){
        return "In Stock";
        }
        elseif ($stock > 0 && $stock <= 5) {
            return "Limited Stock";
        }
        else{
            return "Out of Stock";
        }
    }
    function classforstock($stock){
        if($stock > 5){
        return "inStock";
        }
        elseif ($stock > 0 && $stock <= 5) {
            return "limitedStock";
        }
        else{
            return "outStock";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product-detail</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/product.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "includes/header.php" ?>
    <main class="product-detail-page">
        <a href="products.php" class="back-link">&larr; Back to Products</a>
        <div class="product-detail-container">
            <div class="image-container-for-product-detail">

    <?php
    $primaryImage = null;

    foreach ($images as $image) {
        if ($image["is_primary"] == 1) {
            $primaryImage = $image;
            break;
        }
    }
    ?>

    <div class="main-image-container">
        <img
            src="<?= htmlspecialchars($primaryImage["image_path"]); ?>"
            alt="<?= htmlspecialchars($primaryImage["alt_text"]); ?>"
            class="main-image">
    </div>

    <div class="thumbnail-container">

        <?php foreach ($images as $image) { ?>

            <img
                src="<?= htmlspecialchars($image["image_path"]); ?>"
                alt="<?= htmlspecialchars($image["alt_text"]); ?>"
                class="<?= ($image["is_primary"] == 1) ? "active-image-product" : "" ?>"
            >

        <?php } ?>

    </div>

</div>
            <div class="product-all-detail-container">
                <div class="main-info-product">
                    <h2 class="product-detail-name"><?php echo $product["product_name"]; ?></h2>
                    <div class="review-container">
                        <p class="review">&starf;&starf;&starf;&starf;&star;<?php echo $product["rating"]; ?></p>
                        <p>(<?php echo htmlspecialchars($product["review_count"]) ?> Reviews)</p>
                    </div>
                    <h3 class="product-detail-price">₹<?php echo htmlspecialchars($product["price"]) ?></h3>
                    <p class="product-detail-desc"><?php echo htmlspecialchars($product["short_description"]); ?> </p>
                    <div class="<?php echo classforstock($product["stock"]) ?>-check-for-product-detail">
                        <p> <span>&#11044; </span><?php echo displayStockCheck($product["stock"]) ?></p>
                        <p class="sku-section"> | SKU: <?php echo htmlspecialchars($product["sku"]); ?></p>
                    </div>
                    <div class="container-for-btn-qun">
                        <p>Quntity:</p>
                        <div class="qun-container">
                            <button class="minus-btn">
                                <img class="minus" src="assets\images\Icons\minus.png" alt="Plus Quntity">
                            </button>
                            <p class="display-Qun">1</p>
                            <button class="plus-btn">
                                <img class="plus" src="assets\images\Icons\plus-symbol-button.png" alt="">
                            </button>
                        </div>
                        <div class="container-for-buttons">
                            <button type="button" class="add-to-cart" data-product-id="<?php echo htmlspecialchars($product["product_id"]); ?>">Add to Cart</button>
                            <button type="button" class=" add-to-wishlist" data-product-id="<?php echo htmlspecialchars($product["product_id"]); ?>"> &#10084; Wishlist</button>
                        </div>
                    </div>
                </div>
                <div class="product-specification-detail">
                    <div class="category-of-product">
                        <img src="assets\images\Icons\menu.png" alt="Categort" class="spec-icon">
                        <p class="first-child">Category:</p>
                        <p><?php echo htmlspecialchars($product["category_name"]); ?></p>
                    </div>
                    <div class="brand-for-product">
                        <img src="assets\images\Icons\badge.png" alt="brand" class="spec-icon">
                        <p class="first-child">Brand: </p>
                        <p><?php echo htmlspecialchars($product["brand"]); ?></p>
                    </div>
                    <div class="tag-section-for-product">
                        <img src="assets\images\Icons\certificate.png" alt="Tag" class="spec-icon">
                        <p class="first-child">Model No: </p>
                        <p><?php echo htmlspecialchars($product['model_number']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <section class="about-product">
            <div class="product-all-description">
                <h2> &bull; Description &bull; </h2>
                <p><?php echo $product["description"] ?></p>
                <h2> &bull; Specification &bull; </h2>
                <div class="specification-of-product">
                    <table class="spec">
                        <?php  foreach ($specifications as $spec) { ?>
                        <tr>
                            <th class="spec-name"><?php echo htmlspecialchars($spec["spec_name"]); ?></th>
                            <td class="spec-value"><?php echo htmlspecialchars($spec["spec_value"]); ?></td>
                        </tr>
                        <?php } ?> 
                    </table>
                </div>
            </div>
        </section>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
    <script src="assets/Js/product-detail.js"></script>
</body>

</html>