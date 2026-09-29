<?php

session_start();

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin-login.php");

    exit;

}

$admin_id = $_SESSION["admin_id"];

$adminPage = "dashboard";
$adminBase = "./";

require_once "../includes/database.php";


// ========================================
// DASHBOARD STATISTICS
// ========================================

// Total Orders
$totalOrders = 0;

$orderQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_orders
     FROM orders
     WHERE order_status != 'cancelled'"
);

if ($orderQuery) {

    $orderData = mysqli_fetch_assoc($orderQuery);

    $totalOrders = (int) $orderData["total_orders"];

}


// Total Sales
$totalSales = 0;

$salesQuery = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_amount), 0) AS total_sales
     FROM orders
     WHERE order_status != 'cancelled'"
);

if ($salesQuery) {

    $salesData = mysqli_fetch_assoc($salesQuery);

    $totalSales = (float) $salesData["total_sales"];

}


// Total Products
$totalProducts = 0;

$productQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_products
     FROM products"
);

if ($productQuery) {

    $productData = mysqli_fetch_assoc($productQuery);

    $totalProducts = (int) $productData["total_products"];

}


// Low Stock Items
$lowStockItems = 0;

$lowStockQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS low_stock_items
     FROM products
     WHERE stock <= 10
     AND status = 'active'"
);

if ($lowStockQuery) {

    $lowStockData = mysqli_fetch_assoc($lowStockQuery);

    $lowStockItems = (int) $lowStockData["low_stock_items"];

}


// ========================================
// RECENT ORDERS
// ========================================

$recentOrders = [];

$recentOrdersQuery = mysqli_query(
    $conn,
    "SELECT
        o.order_id,
        o.total_amount,
        o.order_status,
        o.created_at,
        u.full_name
     FROM orders o
     LEFT JOIN users u
        ON o.user_id = u.user_id
     ORDER BY o.created_at DESC
     LIMIT 4"
);

if ($recentOrdersQuery) {

    while ($row = mysqli_fetch_assoc($recentOrdersQuery)) {

        $recentOrders[] = $row;

    }

}


// ========================================
// LOW STOCK PRODUCTS
// ========================================

$lowStockProducts = [];

$lowStockProductsQuery = mysqli_query(
    $conn,
    "SELECT
        p.product_id,
        p.product_name,
        p.stock,
        c.category_name
     FROM products p
     LEFT JOIN categories c
        ON p.category_id = c.category_id
     WHERE p.stock <= 10
     AND p.status = 'active'
     ORDER BY p.stock ASC
     LIMIT 4"
);

if ($lowStockProductsQuery) {

    while ($row = mysqli_fetch_assoc($lowStockProductsQuery)) {

        $lowStockProducts[] = $row;

    }

}


// ========================================
// ORDER STATUS CLASS
// ========================================

function getOrderStatusClass($status)
{
    switch ($status) {

        case "delivered":
            return "delivered";

        case "shipped":
            return "shipped";

        case "confirmed":
            return "processing";

        case "pending":
            return "pending";

        case "cancelled":
            return "cancelled";

        default:
            return "pending";

    }
}


// ========================================
// ORDER STATUS LABEL
// ========================================

function getOrderStatusLabel($status)
{
    switch ($status) {

        case "delivered":
            return "Delivered";

        case "shipped":
            return "Shipped";

        case "confirmed":
            return "Confirmed";

        case "pending":
            return "Pending";

        case "cancelled":
            return "Cancelled";

        default:
            return ucfirst($status);

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TechVolt Admin - Dashboard</title>

    <link rel="stylesheet" href="includes/admin.css">

</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <?php include 'includes/header.php'; ?>

    <main class="admin-main">

    <section class="dashboard-content">

        <!-- =========================
             STAT CARDS
        ========================== -->

        <div class="dashboard-stats">

            <div class="stat-card">

                <div class="stat-card-icon blue">
                    ◫
                </div>

                <div class="stat-card-content">

                    <span>Total Orders</span>

                    <h2><?= $totalOrders; ?></h2>

                    <p class="positive">
                        Current database total
                    </p>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-card-icon green">
                    ₹
                </div>

                <div class="stat-card-content">

                    <span>Total Sales</span>

                    <h2>₹<?= number_format($totalSales, 0); ?></h2>

                    <p class="positive">
                        Current database total
                    </p>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-card-icon purple">
                    ▣
                </div>

                <div class="stat-card-content">

                    <span>Total Products</span>

                    <h2><?= $totalProducts; ?></h2>

                    <p class="positive">
                        Current active inventory
                    </p>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-card-icon orange">
                    !
                </div>

                <div class="stat-card-content">

                    <span>Low Stock Items</span>

                    <h2><?= $lowStockItems; ?></h2>

                    <p class="warning">
                        Needs attention
                    </p>

                </div>

            </div>

        </div>


        <!-- =========================
             ORDERS + LOW STOCK
        ========================== -->

        <div class="dashboard-grid">

            <!-- Recent Orders -->

            <div class="dashboard-panel orders-panel">

                <div class="panel-header">

                    <div>
                        <h3>Recent Orders</h3>

                        <p>
                            Latest customer orders
                        </p>
                    </div>

                    <a href="orders/manage-orders.php">
                        View All
                    </a>

                </div>


                <div class="orders-table-container">

                    <table class="admin-table">

                        <thead>

                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>

                        </thead>


                        <tbody>

<?php if (!empty($recentOrders)): ?>

    <?php foreach ($recentOrders as $order): ?>

        <?php
        $statusClass =
            getOrderStatusClass($order["order_status"]);

        $statusLabel =
            getOrderStatusLabel($order["order_status"]);
        ?>

        <tr>

            <td>
                <strong>
                    #TV2026<?= str_pad( $order["order_id"], 4, "0", STR_PAD_LEFT ); ?>
                </strong>
            </td>

            <td>
                <?= htmlspecialchars(
                    $order["full_name"] ?? "Unknown Customer"
                ); ?>
            </td>

            <td>
                ₹<?= number_format(
                    (float) $order["total_amount"],
                    2
                ); ?>
            </td>

            <td>

                <span class="status <?= $statusClass; ?>">
                    <?= htmlspecialchars($statusLabel); ?>
                </span>

            </td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <tr>

        <td colspan="4" style="text-align:center;">
            No orders found.
        </td>

    </tr>

<?php endif; ?>

</tbody>

                    </table>

                </div>

            </div>


            <!-- Low Stock -->

            <div class="dashboard-panel stock-panel">

                <div class="panel-header">

                    <div>

                        <h3>Low Stock</h3>

                        <p>
                            Products running low
                        </p>

                    </div>

                    <a href="inventory/stock-management.php">
                        Manage
                    </a>

                </div>


                <div class="stock-list">

<?php if (!empty($lowStockProducts)): ?>

    <?php foreach ($lowStockProducts as $product): ?>

        <?php
        $stock = (int) $product["stock"];

        $stockClass =
            ($stock <= 5)
                ? "danger"
                : "warning-stock";
        ?>

        <div class="stock-item">

            <div class="stock-product">

                <div class="stock-image">
                    📦
                </div>

                <div>

                    <strong>
                        <?= htmlspecialchars(
                            $product["product_name"]
                        ); ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            $product["category_name"]
                            ?? "Uncategorized"
                        ); ?>
                    </span>

                </div>

            </div>

            <div class="stock-count <?= $stockClass; ?>">

                <?= $stock; ?> left

            </div>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div style="text-align:center; padding:25px;">

        <strong>
            All products are sufficiently stocked.
        </strong>

    </div>

<?php endif; ?>

</div>

            </div>

        </div>


        <!-- =========================
             QUICK ACTIONS
        ========================== -->

        <div class="dashboard-panel quick-actions-panel">

            <div class="panel-header">

                <div>

                    <h3>Quick Actions</h3>

                    <p>
                        Frequently used admin tools
                    </p>

                </div>

            </div>


            <div class="quick-actions">

                <a
                    href="products/add-product.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        +
                    </div>

                    <div>

                        <strong>
                            Add Product
                        </strong>

                        <span>
                            Add a new electronic component
                        </span>

                    </div>

                </a>


                <a
                    href="categories/add-category.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        +
                    </div>

                    <div>

                        <strong>
                            Add Category
                        </strong>

                        <span>
                            Create a new product category
                        </span>

                    </div>

                </a>


                <a
                    href="orders/manage-orders.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        ◫
                    </div>

                    <div>

                        <strong>
                            Manage Orders
                        </strong>

                        <span>
                            View and update customer orders
                        </span>

                    </div>

                </a>


                <a
                    href="reports.php"
                    class="quick-action"
                >

                    <div class="quick-action-icon">
                        ◭
                    </div>

                    <div>

                        <strong>
                            View Reports
                        </strong>

                        <span>
                            Check sales and business reports
                        </span>

                    </div>

                </a>

            </div>

        </div>

    </section>

</main>
</body>

</html>