<?php

    require_once "../includes/database.php";

    $products = [];
    $sql = "SELECT products.*, categories.category_name, categories.slug AS category_slug, product_images.image_path, product_images.alt_text FROM products JOIN categories ON products.category_id = categories.category_id JOIN product_images ON products.product_id = product_images.product_id AND product_images.is_primary = 1 WHERE categories.status = 'active' AND products.status = 'active'";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Product query failed: " . mysqli_error($conn));
    }
    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }
    echo json_encode($products);
?>