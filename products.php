<?php
session_start();
require_once "includes/database.php";
if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
$category_sql = "
    SELECT category_id, category_name, slug
    FROM categories
    WHERE status = 'active'
    ORDER BY category_name ASC
";

$category_result = mysqli_query($conn, $category_sql);

if (!$category_result) {
    die("Category query failed: " . mysqli_error($conn));
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TechVolt</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/product.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include "includes/header.php" ?>

    <main>
        <h2 class="text-product">&bull; Products &bull;</h2>
        <section class="category">
            <h4> Sort by Categories </h4>
            <div class="select-category">
            <label for="category">Category: </label>
            <select name="category" id="category">
                <option value="all" selected>All Categories</option>

                <?php while ($category = mysqli_fetch_assoc($category_result)): ?>
                    <option
                        value="<?= htmlspecialchars($category['slug']); ?>"
                    >
                        <?= htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            </div>
        </section>
        <hr>
        <div class="all-products">
        </div>
    </main>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
    <script src="assets/Js/products.js"></script>
</body>

</html>