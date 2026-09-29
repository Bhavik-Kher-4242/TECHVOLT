<?php
    session_start();
    $deleted = isset($_GET["deleted"]) && $_GET["deleted"] == "1";
    require_once "../../includes/database.php";
    if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
    }
    $search = trim($_GET["search"] ?? "");
    $category = trim($_GET["category"] ?? "");
    $searchPattern = "%$search%";
    $search1 = $searchPattern;
    $search2 = $searchPattern;
    $search3 = $searchPattern;

    $page = $_GET["page"] ?? 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;
    $adminPage = 'products';
    $adminBase = '../';
    $countSql = "SELECT COUNT(*) AS total FROM products p JOIN categories c ON p.category_id = c.category_id WHERE ( p.product_name LIKE ? OR c.category_name LIKE ? OR p.brand LIKE ?) AND (? = '' OR c.category_name = ?)";

    $countStmt = mysqli_prepare($conn, $countSql);

    mysqli_stmt_bind_param($countStmt, "sssss", $search1, $search2, $search3, $category, $category);

    mysqli_stmt_execute($countStmt);

    $countResult = mysqli_stmt_get_result($countStmt);
    $countRow = mysqli_fetch_assoc($countResult);


    $totalProducts = $countRow["total"];

    $startProduct = $offset + 1;
    $endProduct = min($limit * $page, $totalProducts);
    $totalPages = ceil($totalProducts / $limit);

    $sql = "SELECT p.product_id, p.product_name, p.category_id, c.category_name, p.price, p.stock, p.status, pi.image_path, pi.alt_text FROM products p JOIN categories c ON p.category_id = c.category_id LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 WHERE ( p.product_name LIKE ? OR c.category_name LIKE ? OR p.brand LIKE ?) AND (? = '' OR c.category_name = ?) ORDER BY p.product_id ASC LIMIT $limit OFFSET $offset";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $search1, $search2, $search3, $category, $category);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    function checkStock($stock){
        if($stock <= 0){
            echo "Out of Stock";
        }
        elseif ($stock < 10 && $stock > 0) {
            echo "Limited Stock";
        }
        else{
            echo "In Stock";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TechVolt Admin - Products</title>

    <link rel="stylesheet" href="../includes/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="product-management">


            <!-- =========================
                 PAGE HEADER
            ========================== -->

            <div class="product-page-header">

                <div>

                    <h2>Products</h2>

                    <p>
                        View, edit and manage all products
                    </p>

                </div>


                <a
                    href="add-product.php"
                    class="add-product-btn"
                >
                    <span>+</span>
                    Add Product
                </a>

            </div>



            <!-- =========================
                 FILTER BAR
            ========================== -->

            <form method="GET" action="manage-products.php">
            <div class="product-filter-panel">

                <div class="product-search">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        name="search"
                        type="search"
                        id="product-search"
                        placeholder="Search products..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                </div>
                

                <div class="product-filter">

                    <select id="category-filter" name="category">

                        <option value="">
                            All Categories
                        </option>

                        <option value="Accessories"
                        <?= $category === "Accessories" ? "selected" : ""; ?>>
                            Accessories
                        </option>

                        <option value="Displays"
                        <?= $category === "Displays" ? "selected" : ""; ?>>
                            Displays
                        </option>

                        <option value="ICs"
                        <?= $category === "ICs" ? "selected" : ""; ?>>
                            ICs
                        </option>

                        <option value="Microcontrollers"
                        <?= $category === "Microcontrollers" ? "selected" : ""; ?>>
                            Microcontrollers
                        </option>

                        <option value="Motors & Actuators"
                        <?= $category === "Motors & Actuators" ? "selected" : ""; ?>>
                            Motors & Actuators
                        </option>

                        <option value="Sensors"
                        <?= $category === "Sensors" ? "selected" : ""; ?>>
                            Sensors
                        </option>

                        <option value="Power Module"
                        <?= $category === "Power Module" ? "selected" : ""; ?>>
                        
                            Power Module
                        </option>

                        <option value="Passive Components"
                        <?= $category === "Passive Components" ? "selected" : ""; ?>>
                        
                            Passive Components
                        </option>

                        <option value="Single Board Computers"
                        <?= $category === "Single Board Computers" ? "selected" : ""; ?>>
                        
                            Single Board Computers
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    class="filter-btn"
                >
                    Filter
                </button>

            </div>
            </form>


            <!-- =========================
                 PRODUCT TABLE PANEL
            ========================== -->

            <div class="product-table-panel">


                <div class="product-table-header">

                    <div>

                        <h3>
                            All Products
                        </h3>

                        <p>
                            <?= $totalProducts; ?> products found
                        </p>

                    </div>


                    <div class="product-table-actions">

                        <button
                            type="button"
                            class="table-action-btn"
                            onclick="window.location.href='manage-products.php';"
                        >
                            ↻ Refresh
                        </button>

                    </div>

                </div>



                <!-- =========================
                     TABLE
                ========================== -->

                <div class="product-table-wrapper">

                    <table class="product-management-table">

                        <thead>

                            <tr>

                                <th>
                                    Product
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Stock
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <!-- PRODUCTS -->
                        <?php while($row = mysqli_fetch_assoc($result)){ ?>
                            <tr>

                                <td>

                                    <div class="product-info">

                                        <div class="product-table-image">

                                            <img
                                                src="<?= "../". $adminBase. $row["image_path"]; ?>"
                                                alt="<?= $row["alt_text"]; ?>"
                                            >

                                        </div>


                                        <div class="product-name">

                                            <strong>
                                                <?= $row["product_name"]; ?>
                                            </strong>

                                            <span>
                                                ID: <?= "#" . str_pad($row["product_id"], 3, "0" ,STR_PAD_LEFT); ?>
                                            </span>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <span class="category-badge">
                                        <?= $row["category_name"]; ?>
                                    </span>

                                </td>


                                <td>

                                    <strong class="product-price">
                                        ₹<?= $row["price"]; ?>
                                    </strong>

                                </td>


                                <td>

                                    <span class="stock-number">
                                        <?= $row["stock"]; ?>
                                    </span>

                                </td>


                                <td>

                                    <span class="product-status <?= ($row["stock"] > 10) ? "in-stock" : (($row["stock"] > 0 && $row["stock"] <= 10) ? "low-stock" : "out-of-stock") ?>">
                                        <?= checkStock($row["stock"]); ?>
                                    </span>

                                </td>


                                <td>

                                    <div class="product-actions">

                                        <a
                                            href="edit-product.php?id=<?= $row['product_id']; ?>"
                                            class="edit-btn"
                                        >
                                            Edit
                                        </a>

                                        <a
                                            href="delete-product.php?id=<?= $row['product_id']; ?>"
                                            class="delete-btn"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>
                        <?php } ?>

                        </tbody>

                    </table>

                </div>



                <!-- =========================
                     TABLE FOOTER
                ========================== -->

                <div class="product-table-footer">

                    <span>
                        Showing
                        <strong><?= $startProduct; ?>–<?= $endProduct; ?></strong>
                        of
                        <strong><?= $totalProducts; ?></strong>
                        products
                    </span>


                    <div class="pagination">

                        <button
                            type="button"
                            onclick="window.location.href='manage-products.php?page=<?= $page-1; ?>&search=<?= urlencode($search); ?>'"
                            <?=  ($page == 1) ? "disabled" : ""; ?>
                        >
                            ‹
                        </button>

                        <?php for($i = 1; $i <= $totalPages; $i++){ ?>

                            <button
                                type="button"
                                class="<?= $page == $i ? 'active-page' : ''; ?>"
                                onclick="window.location.href='manage-products.php?page=<?= $i; ?>&search=<?= urlencode($search); ?>'"
                            >
                                <?= $i; ?>
                            </button>

                        <?php } ?>

                        

                        <button type="button"
                        onclick="window.location.href='manage-products.php?page=<?= $page+1; ?>&search=<?= urlencode($search); ?>'"
                        <?= ($page == $totalPages) ? "disabled" : ""; ?> >
                            ›
                        </button>

                    </div>

                </div>

            </div>

        </section>

    </main>
    <?php if ($deleted): ?>

        <script>
            Swal.fire({
                icon: "success",
                title: "Product Deleted!",
                text: "The product has been deleted successfully.",
                background: "#111827",
                color: "#ffffff",
                confirmButtonText: "OK"
            }).then(() => {
                window.history.replaceState(
                    null,
                    "",
                    "manage-products.php"
                );
            });
        </script>

    <?php endif; ?>
</body>

</html>