<?php

session_start();

/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin-login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once "../includes/database.php";


/*
|--------------------------------------------------------------------------
| ADMIN PAGE SETTINGS
|--------------------------------------------------------------------------
*/

$adminPage = "reports";
$adminBase = "./";


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$totalSales = 0;
$totalOrders = 0;
$totalCustomers = 0;

$returnCount = 0;
$returnRate = 0;

$pendingOrders = 0;
$confirmedOrders = 0;
$shippedOrders = 0;
$deliveredOrders = 0;
$cancelledOrders = 0;

$newCustomers = 0;
$usersWithOrders = 0;
$usersWithoutOrders = 0;

$codOrders = 0;
$onlineOrders = 0;

$codPercentage = 0;
$onlinePercentage = 0;

$topProducts = [];

$salesChartData = [];


/*
|--------------------------------------------------------------------------
| TOTAL SALES & TOTAL ORDERS
|--------------------------------------------------------------------------
|
| Cancelled orders are excluded from sales.
|
*/

$totalSalesSql = "
    SELECT
        COALESCE(SUM(total_amount), 0) AS total_sales,
        COUNT(*) AS total_orders
    FROM orders
    WHERE order_status != 'cancelled'
";

$totalSalesStmt = mysqli_prepare(
    $conn,
    $totalSalesSql
);

if ($totalSalesStmt) {

    mysqli_stmt_execute($totalSalesStmt);

    $totalSalesResult = mysqli_stmt_get_result(
        $totalSalesStmt
    );

    if ($totalSalesResult) {

        $salesRow = mysqli_fetch_assoc(
            $totalSalesResult
        );

        $totalSales = (float) (
            $salesRow["total_sales"] ?? 0
        );

        $totalOrders = (int) (
            $salesRow["total_orders"] ?? 0
        );
    }

    mysqli_stmt_close($totalSalesStmt);
}


/*
|--------------------------------------------------------------------------
| TOTAL CUSTOMERS
|--------------------------------------------------------------------------
|
| Only customer accounts are counted.
|
*/

$totalCustomersSql = "
    SELECT COUNT(*) AS total_customers
    FROM users
    WHERE role = 'customer'
";

$totalCustomersStmt = mysqli_prepare(
    $conn,
    $totalCustomersSql
);

if ($totalCustomersStmt) {

    mysqli_stmt_execute(
        $totalCustomersStmt
    );

    $totalCustomersResult = mysqli_stmt_get_result(
        $totalCustomersStmt
    );

    if ($totalCustomersResult) {

        $customerRow = mysqli_fetch_assoc(
            $totalCustomersResult
        );

        $totalCustomers = (int) (
            $customerRow["total_customers"] ?? 0
        );
    }

    mysqli_stmt_close(
        $totalCustomersStmt
    );
}


/*
|--------------------------------------------------------------------------
| RETURN RATE
|--------------------------------------------------------------------------
|
| Return rate =
| Total return requests / Total non-cancelled orders × 100
|
*/

$returnSql = "
    SELECT COUNT(*) AS return_count
    FROM returns
";

$returnStmt = mysqli_prepare(
    $conn,
    $returnSql
);

if ($returnStmt) {

    mysqli_stmt_execute(
        $returnStmt
    );

    $returnResult = mysqli_stmt_get_result(
        $returnStmt
    );

    if ($returnResult) {

        $returnRow = mysqli_fetch_assoc(
            $returnResult
        );

        $returnCount = (int) (
            $returnRow["return_count"] ?? 0
        );
    }

    mysqli_stmt_close(
        $returnStmt
    );
}

if ($totalOrders > 0) {

    $returnRate = (
        $returnCount / $totalOrders
    ) * 100;
}


/*
|--------------------------------------------------------------------------
| ORDER STATUS
|--------------------------------------------------------------------------
*/

$orderStatusSql = "
    SELECT
        order_status,
        COUNT(*) AS status_count
    FROM orders
    GROUP BY order_status
";

$orderStatusStmt = mysqli_prepare(
    $conn,
    $orderStatusSql
);

if ($orderStatusStmt) {

    mysqli_stmt_execute(
        $orderStatusStmt
    );

    $orderStatusResult = mysqli_stmt_get_result(
        $orderStatusStmt
    );

    if ($orderStatusResult) {

        while (
            $statusRow = mysqli_fetch_assoc(
                $orderStatusResult
            )
        ) {

            $status = $statusRow["order_status"];
            $count = (int) $statusRow["status_count"];

            if ($status === "pending") {
                $pendingOrders = $count;
            }
            elseif ($status === "confirmed") {
                $confirmedOrders = $count;
            }
            elseif ($status === "shipped") {
                $shippedOrders = $count;
            }
            elseif ($status === "delivered") {
                $deliveredOrders = $count;
            }
            elseif ($status === "cancelled") {
                $cancelledOrders = $count;
            }
        }
    }

    mysqli_stmt_close(
        $orderStatusStmt
    );
}


/*
|--------------------------------------------------------------------------
| CUSTOMER OVERVIEW
|--------------------------------------------------------------------------
|
| New users = users created during current month.
|
*/

$newCustomersSql = "
    SELECT COUNT(*) AS new_customers
    FROM users
    WHERE role = 'customer'
    AND created_at >= DATE_FORMAT(
        CURDATE(),
        '%Y-%m-01'
    )
";

$newCustomersStmt = mysqli_prepare(
    $conn,
    $newCustomersSql
);

if ($newCustomersStmt) {

    mysqli_stmt_execute(
        $newCustomersStmt
    );

    $newCustomersResult = mysqli_stmt_get_result(
        $newCustomersStmt
    );

    if ($newCustomersResult) {

        $newCustomerRow = mysqli_fetch_assoc(
            $newCustomersResult
        );

        $newCustomers = (int) (
            $newCustomerRow["new_customers"] ?? 0
        );
    }

    mysqli_stmt_close(
        $newCustomersStmt
    );
}


/*
|--------------------------------------------------------------------------
| USERS WITH ORDERS
|--------------------------------------------------------------------------
*/

$usersWithOrdersSql = "
    SELECT COUNT(DISTINCT user_id) AS users_with_orders
    FROM orders
    WHERE order_status != 'cancelled'
";

$usersWithOrdersStmt = mysqli_prepare(
    $conn,
    $usersWithOrdersSql
);

if ($usersWithOrdersStmt) {

    mysqli_stmt_execute(
        $usersWithOrdersStmt
    );

    $usersWithOrdersResult = mysqli_stmt_get_result(
        $usersWithOrdersStmt
    );

    if ($usersWithOrdersResult) {

        $usersWithOrdersRow = mysqli_fetch_assoc(
            $usersWithOrdersResult
        );

        $usersWithOrders = (int) (
            $usersWithOrdersRow["users_with_orders"] ?? 0
        );
    }

    mysqli_stmt_close(
        $usersWithOrdersStmt
    );
}


/*
|--------------------------------------------------------------------------
| USERS WITHOUT ORDERS
|--------------------------------------------------------------------------
*/

$usersWithoutOrders = max(
    0,
    $totalCustomers - $usersWithOrders
);


/*
|--------------------------------------------------------------------------
| PAYMENT METHOD SUMMARY
|--------------------------------------------------------------------------
*/

$paymentSql = "
    SELECT
        payment_method,
        COUNT(*) AS payment_count
    FROM orders
    WHERE order_status != 'cancelled'
    GROUP BY payment_method
";

$paymentStmt = mysqli_prepare(
    $conn,
    $paymentSql
);

if ($paymentStmt) {

    mysqli_stmt_execute(
        $paymentStmt
    );

    $paymentResult = mysqli_stmt_get_result(
        $paymentStmt
    );

    if ($paymentResult) {

        while (
            $paymentRow = mysqli_fetch_assoc(
                $paymentResult
            )
        ) {

            $paymentMethod =
                strtolower(
                    trim(
                        $paymentRow["payment_method"]
                    )
                );

            $paymentCount = (int) (
                $paymentRow["payment_count"]
            );

            if (
                $paymentMethod === "cod"
                ||
                $paymentMethod === "cash on delivery"
            ) {

                $codOrders += $paymentCount;
            }
            elseif (
                $paymentMethod === "online"
                ||
                $paymentMethod === "online payment"
            ) {

                $onlineOrders += $paymentCount;
            }
        }
    }

    mysqli_stmt_close(
        $paymentStmt
    );
}


/*
|--------------------------------------------------------------------------
| PAYMENT PERCENTAGES
|--------------------------------------------------------------------------
*/

$paymentTotal =
    $codOrders + $onlineOrders;

if ($paymentTotal > 0) {

    $codPercentage = (
        $codOrders / $paymentTotal
    ) * 100;

    $onlinePercentage = (
        $onlineOrders / $paymentTotal
    ) * 100;
}


/*
|--------------------------------------------------------------------------
| TOP SELLING PRODUCTS
|--------------------------------------------------------------------------
|
| Product name and price are taken from order_items
| because order_items stores historical order data.
|
*/

$topProductsSql = "
    SELECT
        oi.product_id,
        oi.product_name,
        SUM(oi.quantity) AS total_sold,
        SUM(oi.subtotal) AS total_sales,
        p.category_id,
        c.category_name
    FROM order_items oi

    INNER JOIN orders o
        ON oi.order_id = o.order_id

    LEFT JOIN products p
        ON oi.product_id = p.product_id

    LEFT JOIN categories c
        ON p.category_id = c.category_id

    WHERE o.order_status != 'cancelled'

    GROUP BY
        oi.product_id,
        oi.product_name,
        p.category_id,
        c.category_name

    ORDER BY
        total_sales DESC

    LIMIT 5
";

$topProductsStmt = mysqli_prepare(
    $conn,
    $topProductsSql
);

if ($topProductsStmt) {

    mysqli_stmt_execute(
        $topProductsStmt
    );

    $topProductsResult = mysqli_stmt_get_result(
        $topProductsStmt
    );

    if ($topProductsResult) {

        while (
            $productRow = mysqli_fetch_assoc(
                $topProductsResult
            )
        ) {

            $topProducts[] = $productRow;
        }
    }

    mysqli_stmt_close(
        $topProductsStmt
    );
}


/*
|--------------------------------------------------------------------------
| SALES OVERVIEW
|--------------------------------------------------------------------------
|
| Last 7 months are prepared for the chart.
| Summary cards above remain overall/all-time values.
|
*/

$salesChartSql = "
    SELECT
        DATE_FORMAT(
            created_at,
            '%Y-%m'
        ) AS sale_month,

        DATE_FORMAT(
            created_at,
            '%b %Y'
        ) AS month_label,

        COALESCE(
            SUM(total_amount),
            0
        ) AS monthly_sales

    FROM orders

    WHERE order_status != 'cancelled'

    AND created_at >= DATE_SUB(
        DATE_FORMAT(
            CURDATE(),
            '%Y-%m-01'
        ),
        INTERVAL 6 MONTH
    )

    GROUP BY
        sale_month,
        month_label

    ORDER BY
        sale_month ASC
";

$salesChartStmt = mysqli_prepare(
    $conn,
    $salesChartSql
);

if ($salesChartStmt) {

    mysqli_stmt_execute(
        $salesChartStmt
    );

    $salesChartResult = mysqli_stmt_get_result(
        $salesChartStmt
    );

    if ($salesChartResult) {

        while (
            $chartRow = mysqli_fetch_assoc(
                $salesChartResult
            )
        ) {

            $salesChartData[] = [
                "label" => $chartRow["month_label"],
                "sales" => (float) $chartRow["monthly_sales"]
            ];
        }
    }

    mysqli_stmt_close(
        $salesChartStmt
    );
}


/*
|--------------------------------------------------------------------------
| FILL MISSING MONTHS IN SALES CHART
|--------------------------------------------------------------------------
|
| This makes sure the chart always has 7 months,
| even if there were no orders in a particular month.
|
*/

$completeSalesChart = [];

for ($i = 6; $i >= 0; $i--) {

    $monthTimestamp = strtotime(
        "-$i months"
    );

    $monthKey = date(
        "Y-m",
        $monthTimestamp
    );

    $monthLabel = date(
        "M Y",
        $monthTimestamp
    );

    $monthSales = 0;

    foreach ($salesChartData as $chartData) {

        if (
            date(
                "Y-m",
                strtotime(
                    $chartData["label"]
                )
            ) === $monthKey
        ) {
            $monthSales = $chartData["sales"];
            break;
        }
    }

    $completeSalesChart[] = [
        "label" => $monthLabel,
        "sales" => $monthSales
    ];
}

$salesChartData = $completeSalesChart;

$maxSales = 0;

foreach ($salesChartData as $chartData) {
    if ($chartData["sales"] > $maxSales) {
        $maxSales = $chartData["sales"];
    }
}


/*
|--------------------------------------------------------------------------
| DISPLAY VALUES
|--------------------------------------------------------------------------
*/

$totalSalesFormatted =
    number_format(
        $totalSales,
        2
    );

$returnRateFormatted =
    number_format(
        $returnRate,
        1
    );

$codPercentageFormatted =
    number_format(
        $codPercentage,
        1
    );

$onlinePercentageFormatted =
    number_format(
        $onlinePercentage,
        1
    );


/*
|--------------------------------------------------------------------------
| ORDER STATUS PERCENTAGES
|--------------------------------------------------------------------------
*/

$pendingPercentage = 0;
$confirmedPercentage = 0;
$shippedPercentage = 0;
$deliveredPercentage = 0;
$cancelledPercentage = 0;

/*
 * Order Status Chart Total
 *
 * Here cancelled orders are also included,
 * because the circle represents ALL orders.
 */
$totalStatusOrders =
    $pendingOrders +
    $confirmedOrders +
    $shippedOrders +
    $deliveredOrders +
    $cancelledOrders;

if ($totalStatusOrders > 0) {

    $pendingPercentage =
        ($pendingOrders / $totalStatusOrders) * 100;

    $confirmedPercentage =
        ($confirmedOrders / $totalStatusOrders) * 100;

    $shippedPercentage =
        ($shippedOrders / $totalStatusOrders) * 100;

    $deliveredPercentage =
        ($deliveredOrders / $totalStatusOrders) * 100;

    $cancelledPercentage =
        ($cancelledOrders / $totalStatusOrders) * 100;
}


/*
|--------------------------------------------------------------------------
| STATUS LABELS
|--------------------------------------------------------------------------
|
| These will be used later in HTML.
|
*/

$orderStatusLabels = [
    "pending" => "Pending",
    "confirmed" => "Confirmed",
    "shipped" => "Shipped",
    "delivered" => "Delivered",
    "cancelled" => "Cancelled"
];

$pendingDegree = $pendingPercentage * 3.6;
$confirmedDegree = $confirmedPercentage * 3.6;
$shippedDegree = $shippedPercentage * 3.6;
$deliveredDegree = $deliveredPercentage * 3.6;
$cancelledDegree = $cancelledPercentage * 3.6;

$pendingEnd = $pendingDegree;
$confirmedEnd = $pendingEnd + $confirmedDegree;
$shippedEnd = $confirmedEnd + $shippedDegree;
$deliveredEnd = $shippedEnd + $deliveredDegree;
$cancelledEnd = $deliveredEnd + $cancelledDegree;

$statusCircleStyle = "
    background: conic-gradient(
        #F4B000 0deg {$pendingEnd}deg,
        #0056F9 {$pendingEnd}deg {$confirmedEnd}deg,
        #30AEFB {$confirmedEnd}deg {$shippedEnd}deg,
        #219653 {$shippedEnd}deg {$deliveredEnd}deg,
        #D64545 {$deliveredEnd}deg {$cancelledEnd}deg
    );
";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TechVolt Admin - Reports</title>

    <link
        rel="stylesheet"
        href="includes/admin.css"
    >

</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <?php include 'includes/header.php'; ?>


    <main class="admin-main">

        <section class="reports-page">


            <!-- ================= PAGE HEADER ================= -->

            <div class="reports-page-header">

                <div>

                    <h2>Reports & Analytics</h2>

                    <p>
                        View sales, orders and customer performance.
                    </p>

                </div>

            </div>



            <!-- ================= REPORT SUMMARY ================= -->

            <div class="reports-summary">


                <div class="report-summary-card">

                    <div class="report-summary-icon">
                        ₹
                    </div>

                    <div>

                        <small>Total Sales</small>

                        <strong>₹<?= number_format($totalSales, 2); ?></strong>

                    </div>

                </div>



                <div class="report-summary-card">

                    <div class="report-summary-icon">
                        ◫
                    </div>

                    <div>

                        <small>Total Orders</small>

                        <strong><?= $totalOrders; ?></strong>

                    </div>

                </div>



                <div class="report-summary-card">

                    <div class="report-summary-icon">
                        ♙
                    </div>

                    <div>

                        <small>Total Customers</small>

                        <strong><?= $totalCustomers; ?></strong>

                    </div>

                </div>



                <div class="report-summary-card">

                    <div class="report-summary-icon">
                        ↩
                    </div>

                    <div>

                        <small>Return Rate</small>

                        <strong><?= $returnRateFormatted; ?>%</strong>

                    </div>

                </div>


            </div>



            <!-- ================= FIRST REPORT ROW ================= -->

            <div class="report-grid">


                <!-- SALES OVERVIEW -->

                <div class="report-panel sales-report-panel">

                    <div class="report-panel-header">

                        <div>

                            <h2>Sales Overview</h2>

                            <p>
                                Sales performance for the selected period.
                            </p>

                        </div>


                        <span class="report-period">
                            Last 7 Months
                        </span>

                    </div>



                    <div class="sales-chart">


                        <div class="chart-y-axis">
    <span>
        ₹<?= number_format($maxSales, 0); ?>
    </span>

    <span>
        ₹<?= number_format($maxSales * 0.75, 0); ?>
    </span>

    <span>
        ₹<?= number_format($maxSales * 0.50, 0); ?>
    </span>

    <span>
        ₹<?= number_format($maxSales * 0.25, 0); ?>
    </span>

    <span>₹0</span>
</div>



                        <div class="chart-area">


                            <div class="chart-grid-lines">

                                <span></span>

                                <span></span>

                                <span></span>

                                <span></span>

                                <span></span>

                            </div>



                            <div class="chart-bars">

    <?php foreach ($salesChartData as $chartData): ?>

        <?php
        $salesAmount = (float) $chartData["sales"];

        if ($maxSales > 0) {
            $barHeight = ($salesAmount / $maxSales) * 100;
        } else {
            $barHeight = 0;
        }
        ?>

        <div class="chart-bar-group">

            <div
                class="chart-bar"
                style="height: <?= $barHeight; ?>%;"
                title="₹<?= number_format($salesAmount, 2); ?>"
            ></div>

            <small>
                <?= htmlspecialchars($chartData["label"]); ?>
            </small>

        </div>

    <?php endforeach; ?>

</div>

                        </div>

                    </div>

                </div>



                <!-- ORDER STATUS -->

                <div class="report-panel order-status-panel">


                    <div class="report-panel-header">

                        <div>

                            <h2>Order Status</h2>

                            <p>
                                Current order distribution.
                            </p>

                        </div>

                    </div>



                    <div class="order-status-content">


                        <div class="status-circle" style="<?= htmlspecialchars($statusCircleStyle); ?>">

                            <div>

                                <strong><?= $totalStatusOrders; ?></strong>

                                <span>Orders</span>

                            </div>

                        </div>



                        <div class="status-list">


                            <div class="status-item">

                                <span class="status-dot pending-dot"></span>

                                <div>

                                    <strong>Pending</strong>

                                    <small><?= $pendingOrders; ?> Orders</small>

                                </div>
                                    <b><?= number_format($pendingPercentage, 0); ?>% </b>

                            </div>



                            <div class="status-item">

                                <span class="status-dot processing-dot"></span>

                                <div>

                                    <strong>Confirmed</strong>

<small>
    <?= $confirmedOrders; ?> Orders
</small>

                                </div>

                                <?= number_format($confirmedPercentage, 0); ?>%

                            </div>



                            <div class="status-item">

                                <span class="status-dot shipped-dot"></span>

                                <div>

                                    <strong>Shipped</strong>

<small>
    <?= $shippedOrders; ?> Orders
</small>

                                </div>

                                <?= number_format($shippedPercentage, 0); ?>%

                            </div>



                            <div class="status-item">

                                <span class="status-dot delivered-dot"></span>

                                <div>

                                    <strong>Delivered</strong>

                                    <small>
                                        <?= $deliveredOrders; ?> Orders
                                    </small>


                                </div>

                                <?= number_format($deliveredPercentage, 0); ?>%

                            </div>
                            <div class="status-item">

    <span class="status-dot cancelled-dot"></span>

    <div>
        <strong>Cancelled</strong>

        <small>
            <?= $cancelledOrders; ?> Orders
        </small>
    </div>

    <b>
        <?= number_format($cancelledPercentage, 0); ?>%
    </b>

</div>

                        </div>

                    </div>

                </div>


            </div>



            <!-- ================= SECOND REPORT ROW ================= -->

            <div class="report-grid">


                <!-- TOP PRODUCTS -->

                <div class="report-panel">


                    <div class="report-panel-header">

                        <div>

                            <h2>Top Selling Products</h2>

                            <p>
                                Products generating the most sales.
                            </p>

                        </div>

                    </div>



                    <div class="top-products-list">

    <?php if (!empty($topProducts)): ?>

        <?php foreach ($topProducts as $index => $product): ?>

            <div class="top-product">

                <span class="product-rank">
                    <?= str_pad(
                        $index + 1,
                        2,
                        "0",
                        STR_PAD_LEFT
                    ); ?>
                </span>

                <div class="top-product-info">

                    <strong>
                        <?= htmlspecialchars(
                            $product["product_name"]
                        ); ?>
                    </strong>

                    <small>
                        <?= htmlspecialchars(
                            $product["category_name"]
                            ?? "Uncategorized"
                        ); ?>
                    </small>

                </div>

                <div class="top-product-sales">

                    <strong>
                        ₹<?= number_format(
                            (float) $product["total_sales"],
                            2
                        ); ?>
                    </strong>

                    <small>
                        <?= (int) $product["total_sold"]; ?>
                        Sold
                    </small>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <p class="no-report-data">
            No sales data available.
        </p>

    <?php endif; ?>

</div>

                </div>



                <!-- CUSTOMER OVERVIEW -->

                <div class="report-panel customer-report-panel">


                    <div class="report-panel-header">

                        <div>

                            <h2>Customer Overview</h2>

                            <p>
                                Customer activity summary.
                            </p>

                        </div>

                    </div>



                    <div class="customer-report-list">


                        <div class="customer-report-item">

                            <div class="customer-report-icon">
                                ♙
                            </div>

                            <div>

                                <strong><?= $totalCustomers; ?></strong>

                                <small>
                                    Registered Users
                                </small>

                            </div>

                        </div>



                        <div class="customer-report-item">

                            <div class="customer-report-icon">
                                +
                            </div>

                            <div>

                                <strong><?= $newCustomers; ?></strong>

                                <small>
                                    New Users
                                </small>

                            </div>

                        </div>



                        <div class="customer-report-item">

                            <div class="customer-report-icon">
                                ◫
                            </div>

                            <div>

                                <strong><?= $usersWithOrders; ?></strong>

                                <small>
                                    Users With Orders
                                </small>

                            </div>

                        </div>



                        <div class="customer-report-item">

                            <div class="customer-report-icon">
                                □
                            </div>

                            <div>

                                <strong><?= $usersWithoutOrders; ?></strong>

                                <small>
                                    Users Without Orders
                                </small>

                            </div>

                        </div>


                    </div>

                </div>


            </div>



            <!-- ================= PAYMENT SUMMARY ================= -->

            <div class="report-panel payment-report-panel">


                <div class="report-panel-header">

                    <div>

                        <h2>Payment Summary</h2>

                        <p>
                            Order payment method distribution.
                        </p>

                    </div>

                </div>



                <div class="payment-summary">


                    <div class="payment-method">

                        <div class="payment-method-icon">
                            ₹
                        </div>

                        <div>

                            <strong>
                                Cash on Delivery
                            </strong>

                            <small>
                                <?= $codOrders; ?> Orders
                            </small>

                        </div>

                        <span>
                            <?= $codPercentageFormatted; ?>%
                        </span>

                    </div>



                    <div class="payment-method">

                        <div class="payment-method-icon">
                            ▣
                        </div>

                        <div>

                            <strong>
                                Online Payment
                            </strong>

                            <small>
                                <?= $onlineOrders; ?> Orders
                            </small>

                        </div>

                        <span>
                            <?= $onlinePercentageFormatted; ?>%
                        </span>

                    </div>


                </div>

            </div>



            <!-- ================= REPORT NOTE ================= -->

            <div class="report-note">

                <span>ⓘ</span>

                <p>
                    Report figures are calculated from current TechVolt order, product and customer data.
                </p>

            </div>


        </section>

    </main>

</body>

</html>