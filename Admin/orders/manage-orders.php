<?php

session_start();

require_once "../../includes/database.php";

/* =========================================================
   ADMIN LOGIN PROTECTION
========================================================= */

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}


/* =========================================================
   ADMIN PAGE SETTINGS
========================================================= */

$adminPage = 'orders';
$adminBase = '../';


/* =========================================================
   FILTER VALUES
========================================================= */

$search = trim($_GET["search"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");
$paymentFilter = trim($_GET["payment"] ?? "");

$orderUpdated = isset($_GET["order_updated"])
    && $_GET["order_updated"] === "1";


/* =========================================================
   PAGINATION
========================================================= */

$currentPage = isset($_GET["page"])
    ? (int) $_GET["page"]
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

$ordersPerPage = 5;


/* =========================================================
   ORDER STATUS UPDATE
========================================================= */

$orderUpdateError = false;
$orderUpdateMessage = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_order_status"])
) {

    $orderId = (int) ($_POST["order_id"] ?? 0);
    $newStatus = trim($_POST["order_status"] ?? "");

    $allowedStatuses = [
        "pending",
        "confirmed",
        "shipped",
        "delivered",
        "cancelled"
    ];

    if ($orderId <= 0) {

        $orderUpdateError = true;
        $orderUpdateMessage = "Invalid order selected.";

    } elseif (!in_array($newStatus, $allowedStatuses, true)) {

        $orderUpdateError = true;
        $orderUpdateMessage = "Invalid order status.";

    } else {

        $checkOrderSql = "
            SELECT order_id
            FROM orders
            WHERE order_id = ?
        ";

        $checkOrderStmt = mysqli_prepare(
            $conn,
            $checkOrderSql
        );

        if ($checkOrderStmt) {

            mysqli_stmt_bind_param(
                $checkOrderStmt,
                "i",
                $orderId
            );

            mysqli_stmt_execute($checkOrderStmt);

            $checkOrderResult = mysqli_stmt_get_result(
                $checkOrderStmt
            );

            $existingOrder = mysqli_fetch_assoc(
                $checkOrderResult
            );

            mysqli_stmt_close($checkOrderStmt);

            if (!$existingOrder) {

                $orderUpdateError = true;
                $orderUpdateMessage = "Order not found.";

            } else {

                $updateOrderSql = "
                    UPDATE orders
                    SET order_status = ?
                    WHERE order_id = ?
                ";

                $updateOrderStmt = mysqli_prepare(
                    $conn,
                    $updateOrderSql
                );

                if ($updateOrderStmt) {

                    mysqli_stmt_bind_param(
                        $updateOrderStmt,
                        "si",
                        $newStatus,
                        $orderId
                    );

                    $updateSuccess = mysqli_stmt_execute(
                        $updateOrderStmt
                    );

                    mysqli_stmt_close(
                        $updateOrderStmt
                    );

                    if ($updateSuccess) {

                        header(
                            "Location: manage-orders.php?order_updated=1"
                        );

                        exit;

                    } else {

                        $orderUpdateError = true;
                        $orderUpdateMessage =
                            "Unable to update order status.";
                    }

                } else {

                    $orderUpdateError = true;
                    $orderUpdateMessage =
                        "Unable to prepare order update.";
                }
            }

        } else {

            $orderUpdateError = true;
            $orderUpdateMessage =
                "Unable to check order.";
        }
    }
}


/* =========================================================
   ORDER STATISTICS
========================================================= */

$totalOrders = 0;
$pendingOrders = 0;
$processingOrders = 0;
$deliveredOrders = 0;


/* Total Orders */

$totalOrdersSql = "
    SELECT COUNT(*) AS total_orders
    FROM orders
";

$totalOrdersResult = mysqli_query(
    $conn,
    $totalOrdersSql
);

if ($totalOrdersResult) {

    $totalOrdersRow = mysqli_fetch_assoc(
        $totalOrdersResult
    );

    $totalOrders = (int) $totalOrdersRow["total_orders"];
}


/* Pending */

$pendingOrdersSql = "
    SELECT COUNT(*) AS pending_orders
    FROM orders
    WHERE order_status = 'pending'
";

$pendingOrdersResult = mysqli_query(
    $conn,
    $pendingOrdersSql
);

if ($pendingOrdersResult) {

    $pendingOrdersRow = mysqli_fetch_assoc(
        $pendingOrdersResult
    );

    $pendingOrders = (int) $pendingOrdersRow["pending_orders"];
}


/* Processing */

$processingOrdersSql = "
    SELECT COUNT(*) AS processing_orders
    FROM orders
    WHERE order_status = 'confirmed'
";

$processingOrdersResult = mysqli_query(
    $conn,
    $processingOrdersSql
);

if ($processingOrdersResult) {

    $processingOrdersRow = mysqli_fetch_assoc(
        $processingOrdersResult
    );

    $processingOrders =
        (int) $processingOrdersRow["processing_orders"];
}


/* Delivered */

$deliveredOrdersSql = "
    SELECT COUNT(*) AS delivered_orders
    FROM orders
    WHERE order_status = 'delivered'
";

$deliveredOrdersResult = mysqli_query(
    $conn,
    $deliveredOrdersSql
);

if ($deliveredOrdersResult) {

    $deliveredOrdersRow = mysqli_fetch_assoc(
        $deliveredOrdersResult
    );

    $deliveredOrders =
        (int) $deliveredOrdersRow["delivered_orders"];
}


/* =========================================================
   ORDER CONDITIONS
========================================================= */

$whereConditions = [];

$searchParam = "";
$statusParam = "";
$paymentParam = "";


/* Search */

if ($search !== "") {

    $whereConditions[] = "
        (
            CAST(o.order_id AS CHAR) LIKE ?
            OR o.shipping_name LIKE ?
            OR o.shipping_phone LIKE ?
            OR u.full_name LIKE ?
            OR u.email LIKE ?
        )
    ";
}


/* Status */

if ($statusFilter !== "") {

    $whereConditions[] = "
        o.order_status = ?
    ";
}


/* Payment */

if ($paymentFilter !== "") {

    $whereConditions[] = "
        o.payment_method = ?
    ";
}


/* =========================================================
   COUNT FILTERED ORDERS
========================================================= */

$countSql = "
    SELECT COUNT(*) AS total_filtered
    FROM orders o
    LEFT JOIN users u
        ON o.user_id = u.user_id
";

if (!empty($whereConditions)) {

    $countSql .= "
        WHERE
        " . implode(
            " AND ",
            $whereConditions
        );
}

$countStmt = mysqli_prepare(
    $conn,
    $countSql
);

$totalFilteredOrders = 0;

if ($countStmt) {

    $countTypes = "";
    $countParams = [];


    /* Search Parameters */

    if ($search !== "") {

        $searchParam = "%" . $search . "%";

        $countTypes .= "sssss";

        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
    }


    /* Status Parameter */

    if ($statusFilter !== "") {

        $statusParam = $statusFilter;

        $countTypes .= "s";

        $countParams[] = $statusParam;
    }


    /* Payment Parameter */

    if ($paymentFilter !== "") {

        $paymentParam = $paymentFilter;

        $countTypes .= "s";

        $countParams[] = $paymentParam;
    }


    /* Bind */

    if ($countTypes !== "") {

        mysqli_stmt_bind_param(
            $countStmt,
            $countTypes,
            ...$countParams
        );
    }


    /* Execute */

    mysqli_stmt_execute(
        $countStmt
    );


    /* Result */

    $countResult = mysqli_stmt_get_result(
        $countStmt
    );

    if ($countResult) {

        $countRow = mysqli_fetch_assoc(
            $countResult
        );

        $totalFilteredOrders =
            (int) $countRow["total_filtered"];
    }

    mysqli_stmt_close(
        $countStmt
    );
}


/* =========================================================
   PAGINATION CALCULATION
========================================================= */

$totalPages = (int) ceil(
    $totalFilteredOrders / $ordersPerPage
);

if ($totalPages < 1) {
    $totalPages = 1;
}

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset =
    ($currentPage - 1) * $ordersPerPage;


/* =========================================================
   ORDERS QUERY
========================================================= */

$orders = [];

$orderSql = "
    SELECT
        o.order_id,
        o.user_id,
        o.subtotal,
        o.delivery_charge,
        o.gst_amount,
        o.total_amount,
        o.payment_method,
        o.payment_status,
        o.order_status,
        o.shipping_name,
        o.shipping_phone,
        o.shipping_address,
        o.created_at,

        u.full_name,
        u.email,

        COUNT(oi.order_item_id) AS item_count

    FROM orders o

    LEFT JOIN users u
        ON o.user_id = u.user_id

    LEFT JOIN order_items oi
        ON o.order_id = oi.order_id
";


if (!empty($whereConditions)) {

    $orderSql .= "
        WHERE
        " . implode(
            " AND ",
            $whereConditions
        );
}


$orderSql .= "
    GROUP BY
        o.order_id,
        o.user_id,
        o.subtotal,
        o.delivery_charge,
        o.gst_amount,
        o.total_amount,
        o.payment_method,
        o.payment_status,
        o.order_status,
        o.shipping_name,
        o.shipping_phone,
        o.shipping_address,
        o.created_at,
        u.full_name,
        u.email

    ORDER BY o.created_at DESC

    LIMIT ? OFFSET ?
";


/* =========================================================
   PREPARE ORDERS QUERY
========================================================= */

$orderStmt = mysqli_prepare(
    $conn,
    $orderSql
);

if ($orderStmt) {

    $types = "";
    $params = [];


    /* Search Parameters */

    if ($search !== "") {

        $searchParam = "%" . $search . "%";

        $types .= "sssss";

        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }


    /* Status Parameter */

    if ($statusFilter !== "") {

        $statusParam = $statusFilter;

        $types .= "s";

        $params[] = $statusParam;
    }


    /* Payment Parameter */

    if ($paymentFilter !== "") {

        $paymentParam = $paymentFilter;

        $types .= "s";

        $params[] = $paymentParam;
    }


    /* Pagination */

    $types .= "ii";

    $params[] = $ordersPerPage;
    $params[] = $offset;


    /* Bind */

    mysqli_stmt_bind_param(
        $orderStmt,
        $types,
        ...$params
    );


    /* Execute */

    mysqli_stmt_execute(
        $orderStmt
    );


    /* Result */

    $orderResult = mysqli_stmt_get_result(
        $orderStmt
    );

    if ($orderResult) {

        while (
            $orderRow =
            mysqli_fetch_assoc($orderResult)
        ) {

            $orders[] = $orderRow;
        }
    }

    mysqli_stmt_close(
        $orderStmt
    );
}


/* =========================================================
   SHOWING RANGE
========================================================= */

if ($totalFilteredOrders > 0) {

    $showingFrom =
        $offset + 1;

    $showingTo = min(
        $offset + $ordersPerPage,
        $totalFilteredOrders
    );

} else {

    $showingFrom = 0;
    $showingTo = 0;
}


/* =========================================================
   PAGINATION URL
========================================================= */

$paginationParams = [];

if ($search !== "") {
    $paginationParams["search"] = $search;
}

if ($statusFilter !== "") {
    $paginationParams["status"] = $statusFilter;
}

if ($paymentFilter !== "") {
    $paginationParams["payment"] = $paymentFilter;
}


function getPaginationUrl(
    $page,
    $paginationParams
) {

    $paginationParams["page"] = $page;

    return "?" .
        http_build_query(
            $paginationParams
        );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        TechVolt Admin - Manage Orders
    </title>

    <link
        rel="stylesheet"
        href="../includes/admin.css"
    >

    <script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
    ></script>

</head>

<body>

<?php include '../includes/sidebar.php'; ?>

<?php include '../includes/header.php'; ?>


<main class="admin-main">

<section class="order-management">


    <!-- Page Header -->

    <div class="order-page-header">

        <div>

            <h2>Orders</h2>

            <p>
                Monitor customer orders and update their status.
            </p>

        </div>

    </div>


    <!-- Order Statistics -->

    <div class="order-stats">


        <!-- Total -->

        <div class="order-stat-card">

            <div class="order-stat-icon blue">
                ◫
            </div>

            <div class="order-stat-info">

                <span>Total Orders</span>

                <strong>
                    <?= $totalOrders; ?>
                </strong>

            </div>

        </div>


        <!-- Pending -->

        <div class="order-stat-card">

            <div class="order-stat-icon orange">
                ◷
            </div>

            <div class="order-stat-info">

                <span>Pending</span>

                <strong>
                    <?= $pendingOrders; ?>
                </strong>

            </div>

        </div>


        <!-- Processing -->

        <div class="order-stat-card">

            <div class="order-stat-icon purple">
                ◌
            </div>

            <div class="order-stat-info">

                <span>Processing</span>

                <strong>
                    <?= $processingOrders; ?>
                </strong>

            </div>

        </div>


        <!-- Delivered -->

        <div class="order-stat-card">

            <div class="order-stat-icon green">
                ✓
            </div>

            <div class="order-stat-info">

                <span>Delivered</span>

                <strong>
                    <?= $deliveredOrders; ?>
                </strong>

            </div>

        </div>


    </div>


    <!-- Orders Panel -->

    <div class="orders-table-panel">


        <div class="orders-table-header">

            <div>

                <h3>All Orders</h3>

                <p>
                    <?= $totalFilteredOrders; ?>
                    orders found
                </p>

            </div>

        </div>


        <!-- Toolbar -->

        <form
            method="get"
            action=""
            class="order-toolbar"
        >


            <!-- Search -->

            <div class="order-search">

                <span>⌕</span>

                <input
                    type="search"
                    name="search"
                    value="<?= htmlspecialchars($search); ?>"
                    placeholder="Search order ID or customer..."
                >

            </div>


            <!-- Status -->

            <select
                class="order-status-filter"
                name="status"
            >

                <option value="">
                    All Status
                </option>

                <option
                    value="pending"
                    <?= $statusFilter === "pending"
                        ? "selected"
                        : ""; ?>
                >
                    Pending
                </option>

                <option
                    value="confirmed"
                    <?= $statusFilter === "confirmed"
                        ? "selected"
                        : ""; ?>
                >
                    Confirmed
                </option>

                <option
                    value="shipped"
                    <?= $statusFilter === "shipped"
                        ? "selected"
                        : ""; ?>
                >
                    Shipped
                </option>

                <option
                    value="delivered"
                    <?= $statusFilter === "delivered"
                        ? "selected"
                        : ""; ?>
                >
                    Delivered
                </option>

                <option
                    value="cancelled"
                    <?= $statusFilter === "cancelled"
                        ? "selected"
                        : ""; ?>
                >
                    Cancelled
                </option>

            </select>


            <!-- Payment -->

            <select
                class="order-payment-filter"
                name="payment"
            >

                <option value="">
                    All Payments
                </option>

                <option
                    value="cod"
                    <?= $paymentFilter === "cod"
                        ? "selected"
                        : ""; ?>
                >
                    Cash on Delivery
                </option>

                <option
                    value="online"
                    <?= $paymentFilter === "online"
                        ? "selected"
                        : ""; ?>
                >
                    Online Payment
                </option>

            </select>


            <button
                type="submit"
                class="order-filter-btn"
            >
                Filter
            </button>


            <button
                type="button"
                class="order-refresh-btn"
                onclick="window.location.href='manage-orders.php';"
            >
                ↻
            </button>

        </form>


        <!-- Orders Table -->

        <div class="orders-table-wrapper">

            <table class="orders-management-table">

                <thead>

                    <tr>

                        <th>Order</th>

                        <th>Customer</th>

                        <th>Date</th>

                        <th>Items</th>

                        <th>Total</th>

                        <th>Payment</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (empty($orders)): ?>

                    <tr>

                        <td
                            colspan="8"
                            style="text-align: center;"
                        >
                            No orders found.
                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($orders as $order): ?>


                        <?php

                        $displayOrderId =
                            "#TV" .
                            date(
                                "Y",
                                strtotime(
                                    $order["created_at"]
                                )
                            ) .
                            str_pad(
                                $order["order_id"],
                                4,
                                "0",
                                STR_PAD_LEFT
                            );


                        $customerName =
                            $order["full_name"]
                            ?: $order["shipping_name"];


                        $customerEmail =
                            $order["email"]
                            ?: "No email";


                        $customerInitials =
                            strtoupper(
                                substr(
                                    $customerName,
                                    0,
                                    1
                                )
                            );


                        $nameParts =
                            explode(
                                " ",
                                trim($customerName)
                            );


                        if (count($nameParts) > 1) {

                            $customerInitials =
                                strtoupper(
                                    substr(
                                        $nameParts[0],
                                        0,
                                        1
                                    ) .
                                    substr(
                                        $nameParts[
                                            count($nameParts) - 1
                                        ],
                                        0,
                                        1
                                    )
                                );
                        }


                        $orderStatus =
                            $order["order_status"];


                        $orderStatusText =
                            ucfirst(
                                $orderStatus
                            );


                        $paymentMethod =
                            strtolower(
                                $order["payment_method"]
                            );

                        ?>



                        <tr>


                            <!-- Order -->

                            <td>

                                <strong class="order-id">

                                    <?= htmlspecialchars(
                                        $displayOrderId
                                    ); ?>

                                </strong>

                            </td>



                            <!-- Customer -->

                            <td>

                                <div class="customer-info">

                                    <div class="customer-avatar">

                                        <?= htmlspecialchars(
                                            $customerInitials
                                        ); ?>

                                    </div>


                                    <div class="customer-details">

                                        <strong>

                                            <?= htmlspecialchars(
                                                $customerName
                                            ); ?>

                                        </strong>

                                        <span>

                                            <?= htmlspecialchars(
                                                $customerEmail
                                            ); ?>

                                        </span>

                                    </div>

                                </div>

                            </td>



                            <!-- Date -->

                            <td>

                                <?= date(
                                    "d M Y",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                ); ?>

                            </td>



                            <!-- Items -->

                            <td>

                                <?= (int)
                                    $order["item_count"]; ?>

                                <?= (int)
                                    $order["item_count"] === 1
                                    ? "Item"
                                    : "Items"; ?>

                            </td>



                            <!-- Total -->

                            <td>

                                <strong class="order-total">

                                    ₹<?= number_format(
                                        (float)
                                        $order["total_amount"],
                                        0
                                    ); ?>

                                </strong>

                            </td>



                            <!-- Payment -->

                            <td>

                                <?php if ($paymentMethod === "cod"): ?>

                                    <span class="payment-badge cod">
                                        COD
                                    </span>

                                <?php elseif ($paymentMethod === "online"): ?>

                                    <span class="payment-badge online">
                                        Online
                                    </span>

                                <?php else: ?>

                                    <span class="payment-badge">
                                        <?= htmlspecialchars(
                                            ucfirst($paymentMethod)
                                        ); ?>
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- Status -->

                            <td>

                                <span
                                    class="order-status <?= htmlspecialchars(
                                        $orderStatus
                                    ); ?>"
                                >

                                    <?= htmlspecialchars(
                                        $orderStatusText
                                    ); ?>

                                </span>

                            </td>



                            <!-- Action -->

                            <td>

                                <a
                                    href="order-details.php?id=<?= (int) $order["order_id"]; ?>"
                                    class="view-order-btn"
                                >
                                    View
                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>

            </table>

        </div>



        <!-- Table Footer -->

        <div class="orders-table-footer">


            <span>

                <?php if ($totalFilteredOrders > 0): ?>

                    Showing

                    <strong>
                        <?= $showingFrom; ?>
                    </strong>

                    –

                    <strong>
                        <?= $showingTo; ?>
                    </strong>

                    of

                    <strong>
                        <?= $totalFilteredOrders; ?>
                    </strong>

                    orders

                <?php else: ?>

                    Showing
                    <strong>0</strong>
                    orders

                <?php endif; ?>

            </span>



            <?php if ($totalPages > 1): ?>

                <div class="orders-pagination">


                    <!-- Previous -->

                    <?php if ($currentPage > 1): ?>

    <a
        href="<?= htmlspecialchars(
            getPaginationUrl(
                $currentPage - 1,
                $paginationParams
            )
        ); ?>"
        class="pagination-btn"
    >
        ‹
    </a>

<?php else: ?>

    <button
        type="button"
        class="pagination-btn"
        disabled
    >
        ‹
    </button>

<?php endif; ?>



                    <!-- Page Numbers -->

                    <?php for (
                        $page = 1;
                        $page <= $totalPages;
                        $page++
                    ): ?>


                        <?php if (
                            $page === $currentPage
                        ): ?>

                            <button
                                type="button"
                                class="pagination-btn active-page"
                            >
                                <?= $page; ?>
                            </button>

                        <?php else: ?>

                            <a
                                href="<?= htmlspecialchars(
                                    getPaginationUrl(
                                        $page,
                                        $paginationParams
                                    )
                                ); ?>"
                                class="pagination-btn"
                            >
                                <?= $page; ?>
                            </a>

                        <?php endif; ?>


                    <?php endfor; ?>



                    <!-- Next -->

                    <?php if ($currentPage < $totalPages): ?>

    <a
        href="<?= htmlspecialchars(
            getPaginationUrl(
                $currentPage + 1,
                $paginationParams
            )
        ); ?>"
        class="pagination-btn"
    >
        ›
    </a>

<?php else: ?>

    <button
        type="button"
        class="pagination-btn"
        disabled
    >
        ›
    </button>

<?php endif; ?>


                </div>

            <?php endif; ?>


        </div>


    </div>

</section>

</main>



<?php if ($orderUpdateError): ?>

<script>

Swal.fire({

    icon: "error",

    title: "Order Update Failed",

    text: <?= json_encode(
        $orderUpdateMessage
    ); ?>,

    confirmButtonText: "OK"

});

</script>

<?php endif; ?>



<?php if ($orderUpdated): ?>

<script>

Swal.fire({

    icon: "success",

    title: "Order Updated",

    text: "Order status has been updated successfully.",

    confirmButtonText: "OK"

});

</script>

<?php endif; ?>


</body>

</html>