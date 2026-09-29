<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

require_once "../../includes/database.php";

$adminPage = 'orders';
$adminBase = '../';

/*
|--------------------------------------------------------------------------
| Get Order ID
|--------------------------------------------------------------------------
*/

$orderId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($orderId <= 0) {
    header("Location: manage-orders.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Update Order Status
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newStatus = $_POST["order_status"] ?? "";

    $allowedStatuses = [
        "pending",
        "confirmed",
        "shipped",
        "delivered",
        "cancelled"
    ];

    if (!in_array($newStatus, $allowedStatuses, true)) {
        header("Location: order-details.php?id=" . $orderId . "&error=invalid_status");
        exit;
    }

    $updateSql = "
        UPDATE orders
        SET order_status = ?
        WHERE order_id = ?
    ";

    $updateStmt = mysqli_prepare($conn, $updateSql);

    if ($updateStmt) {

        mysqli_stmt_bind_param(
            $updateStmt,
            "si",
            $newStatus,
            $orderId
        );

        if (mysqli_stmt_execute($updateStmt)) {

            mysqli_stmt_close($updateStmt);

            header(
                "Location: order-details.php?id=" .
                $orderId .
                "&updated=1"
            );

            exit;
        }

        mysqli_stmt_close($updateStmt);
    }

    header("Location: order-details.php?id=" . $orderId . "&error=update_failed");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Order + Customer
|--------------------------------------------------------------------------
*/

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
        u.email

    FROM orders o

    LEFT JOIN users u
        ON o.user_id = u.user_id

    WHERE o.order_id = ?
    LIMIT 1
";

$orderStmt = mysqli_prepare($conn, $orderSql);

if (!$orderStmt) {
    die("Order query preparation failed.");
}

mysqli_stmt_bind_param(
    $orderStmt,
    "i",
    $orderId
);

mysqli_stmt_execute($orderStmt);

$orderResult = mysqli_stmt_get_result($orderStmt);

$order = mysqli_fetch_assoc($orderResult);

mysqli_stmt_close($orderStmt);


/*
|--------------------------------------------------------------------------
| Order Not Found
|--------------------------------------------------------------------------
*/

if (!$order) {
    header("Location: manage-orders.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Ordered Products
|--------------------------------------------------------------------------
|
| order_items already stores historical product_name, price,
| quantity and subtotal.
|
| product_images is used only to get the primary image.
|
*/

$itemsSql = "
    SELECT
        oi.order_item_id,
        oi.product_id,
        oi.product_name,
        oi.price,
        oi.quantity,
        oi.subtotal,
        pi.image_path

    FROM order_items oi

    LEFT JOIN product_images pi
        ON oi.product_id = pi.product_id
        AND pi.is_primary = 1

    WHERE oi.order_id = ?

    ORDER BY oi.order_item_id ASC
";

$itemsStmt = mysqli_prepare($conn, $itemsSql);

if (!$itemsStmt) {
    die("Order items query preparation failed.");
}

mysqli_stmt_bind_param(
    $itemsStmt,
    "i",
    $orderId
);

mysqli_stmt_execute($itemsStmt);

$itemsResult = mysqli_stmt_get_result($itemsStmt);

$orderItems = [];

while ($item = mysqli_fetch_assoc($itemsResult)) {
    $orderItems[] = $item;
}

mysqli_stmt_close($itemsStmt);


/*
|--------------------------------------------------------------------------
| Helper Values
|--------------------------------------------------------------------------
*/

$orderStatus = strtolower($order["order_status"]);
$paymentMethod = strtolower($order["payment_method"]);
$paymentStatus = strtolower($order["payment_status"]);



/*
|--------------------------------------------------------------------------
| Order Status Rank
|--------------------------------------------------------------------------
*/

$statusRank = [
    "pending" => 1,
    "confirmed" => 2,
    "shipped" => 3,
    "delivered" => 4
];

$currentRank = $statusRank[$orderStatus] ?? 0;


/*
|--------------------------------------------------------------------------
| Format Order Date
|--------------------------------------------------------------------------
*/

$orderDate = date(
    "d F Y",
    strtotime($order["created_at"])
);

$orderTime = date(
    "h:i A",
    strtotime($order["created_at"])
);


/*
|--------------------------------------------------------------------------
| Customer Initials
|--------------------------------------------------------------------------
*/

$customerName = $order["full_name"] ?: $order["shipping_name"];

$nameParts = explode(" ", trim($customerName));

$initials = "";

foreach ($nameParts as $part) {

    if ($part !== "") {
        $initials .= strtoupper(substr($part, 0, 1));
    }

    if (strlen($initials) >= 2) {
        break;
    }
}


/*
|--------------------------------------------------------------------------
| Payment Method Display
|--------------------------------------------------------------------------
*/

if ($paymentMethod === "cod") {

    $paymentMethodText = "Cash on Delivery";

} elseif ($paymentMethod === "online") {

    $paymentMethodText = "Online Payment";

} else {

    $paymentMethodText = ucfirst(
        str_replace("_", " ", $paymentMethod)
    );
}


/*
|--------------------------------------------------------------------------
| Payment Status Display
|--------------------------------------------------------------------------
*/

$paymentStatusText = ucfirst(
    str_replace("_", " ", $paymentStatus)
);


/*
|--------------------------------------------------------------------------
| Order Status Display
|--------------------------------------------------------------------------
*/

$orderStatusText = ucfirst($orderStatus);
$isCancelled = ($orderStatus === "cancelled");


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
        TechVolt Admin - Order Details
    </title>

    <link
        rel="stylesheet"
        href="../includes/admin.css"
    >

</head>

<body>

<?php include '../includes/sidebar.php'; ?>

<?php include '../includes/header.php'; ?>


<main class="admin-main">

<section class="order-details-page">


    <!-- Order Header -->

    <div class="order-details-header">

        <div class="order-details-heading">

            <a
                href="manage-orders.php"
                class="back-orders-btn"
            >
                ← Back to Orders
            </a>


            <div class="order-details-title">

                <div>

                    <h2>
                        Order #<?= (int) $order["order_id"]; ?>
                    </h2>

                    <p>
                        Placed on
                        <?= htmlspecialchars($orderDate); ?>
                        at
                        <?= htmlspecialchars($orderTime); ?>
                    </p>

                </div>


                <span
                    class="order-status <?= htmlspecialchars($orderStatus); ?>"
                >
                    <?= htmlspecialchars($orderStatusText); ?>
                </span>

            </div>

        </div>

    </div>


    <!-- Customer + Order Information -->

    <div class="order-info-grid">


        <!-- Customer Information -->

        <div class="order-info-card">

            <div class="order-info-card-header">

                <div class="order-info-icon">
                    ♙
                </div>

                <div>

                    <h3>
                        Customer Information
                    </h3>

                    <p>
                        Customer details
                    </p>

                </div>

            </div>


            <div class="order-info-content">

                <div class="customer-detail-profile">

                    <div class="order-customer-avatar">
                        <?= htmlspecialchars($initials); ?>
                    </div>

                    <div>

                        <strong>
                            <?= htmlspecialchars($customerName); ?>
                        </strong>

                        <span>
                            <?= htmlspecialchars($order["email"] ?? ""); ?>
                        </span>

                    </div>

                </div>


                <div class="order-detail-row">

                    <span>
                        Phone
                    </span>

                    <strong>
                        <?= htmlspecialchars($order["shipping_phone"]); ?>
                    </strong>

                </div>


                <div class="order-detail-row">

                    <span>
                        Customer ID
                    </span>

                    <strong>
                        <?= "#CUS". str_pad((int) $order["user_id"], 4, "0", STR_PAD_LEFT) ; ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- Shipping Information -->

        <div class="order-info-card">

            <div class="order-info-card-header">

                <div class="order-info-icon">
                    ⌖
                </div>

                <div>

                    <h3>
                        Shipping Information
                    </h3>

                    <p>
                        Delivery address
                    </p>

                </div>

            </div>


            <div class="order-info-content">

                <div class="shipping-address">

                    <strong>
                        <?= htmlspecialchars($order["shipping_name"]); ?>
                    </strong>

                    <p>
                        <?= nl2br(
                            htmlspecialchars($order["shipping_address"])
                        ); ?>
                    </p>

                </div>


                <div class="order-detail-row">

                    <span>
                        Delivery Charge
                    </span>

                    <strong>
                        ₹<?= number_format(
                            (float) $order["delivery_charge"],
                            2
                        ); ?>
                    </strong>

                </div>

            </div>

        </div>


        <!-- Payment Information -->

        <div class="order-info-card">

            <div class="order-info-card-header">

                <div class="order-info-icon">
                    ₹
                </div>

                <div>

                    <h3>
                        Payment Information
                    </h3>

                    <p>
                        Payment details
                    </p>

                </div>

            </div>


            <div class="order-info-content">

                <div class="payment-detail-line">

                    <span>
                        Payment Method
                    </span>


                    <?php if ($paymentMethod === "cod"): ?>

                        <span class="payment-badge cod">
                            Cash on Delivery
                        </span>

                    <?php elseif ($paymentMethod === "online"): ?>

                        <span class="payment-badge online">
                            Online Payment
                        </span>

                    <?php else: ?>

                        <span class="payment-badge">
                            <?= htmlspecialchars(
                                $paymentMethodText
                            ); ?>
                        </span>

                    <?php endif; ?>

                </div>


                <div class="order-detail-row">

                    <span>
                        Payment Status
                    </span>


                    <strong
                        class="<?= $paymentStatus === 'paid'
                            ? 'payment-success'
                            : ''; ?>"
                    >
                        <?= htmlspecialchars($paymentStatusText); ?>
                    </strong>

                </div>

            </div>

        </div>

    </div>


    <!-- Ordered Products -->

    <div class="order-products-panel">

        <div class="order-panel-header">

            <div>

                <h3>
                    Ordered Products
                </h3>

                <p>
                    <?= count($orderItems); ?>
                    <?= count($orderItems) === 1
                        ? 'product'
                        : 'products'; ?>
                    in this order
                </p>

            </div>

        </div>


        <div class="order-products-wrapper">

            <table class="order-products-table">

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Total
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if (empty($orderItems)): ?>

                    <tr>

                        <td
                            colspan="4"
                            style="text-align:center;"
                        >
                            No products found in this order.
                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($orderItems as $item): ?>

                        <tr>

                            <td>

                                <div class="ordered-product">

                                    <div class="ordered-product-image">

                                        <?php if (!empty($item["image_path"])): ?>

                                            <img
                                                src="../../<?= htmlspecialchars(
                                                    $item["image_path"]
                                                ); ?>"
                                                alt="<?= htmlspecialchars(
                                                    $item["product_name"]
                                                ); ?>"
                                            >

                                        <?php else: ?>

                                            <div>
                                                No Image
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <div class="ordered-product-info">

                                        <strong>
                                            <?= htmlspecialchars(
                                                $item["product_name"]
                                            ); ?>
                                        </strong>

                                        <span>
                                            Product ID:
                                            #<?= (int) $item["product_id"]; ?>
                                        </span>

                                    </div>

                                </div>

                            </td>


                            <td>

                                ₹<?= number_format(
                                    (float) $item["price"],
                                    2
                                ); ?>

                            </td>


                            <td>

                                <?= (int) $item["quantity"]; ?>

                            </td>


                            <td class="ordered-product-total">

                                ₹<?= number_format(
                                    (float) $item["subtotal"],
                                    2
                                ); ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- Bottom Section -->

    <div class="order-bottom-grid">


        <!-- Order Status -->

        <div class="order-status-panel">

            <div class="order-panel-header">

                <div>

                    <h3>
                        Order Status
                    </h3>

                    <p>
                        Current order progress
                    </p>

                </div>

            </div>


            <div class="order-status-content">

                <div class="status-timeline">

                    <?php if ($isCancelled): ?>

                        <!-- Order Placed -->
                        <div class="status-step completed">
                            <div class="status-step-icon">✓</div>
                            <div>
                                <strong>Order Placed</strong>
                                <span>
                                    <?= htmlspecialchars($orderDate); ?>,
                                    <?= htmlspecialchars($orderTime); ?>
                                </span>
                            </div>
                        </div>

                        <div class="status-line completed-line"></div>

                        <!-- Cancelled -->
                        <div class="status-step cancelled-step">
                            <div class="status-step-icon cancelled-icon">✕</div>
                            <div>
                                <strong>Cancelled</strong>
                                <span>Order Cancelled</span>
                            </div>
                        </div>

                    <?php else: ?>

        <!-- Order Placed -->
        <div class="status-step completed">
            <div class="status-step-icon">
                ✓
            </div>
            <div>
                <strong>Order Placed</strong>
                <span>
                    <?= htmlspecialchars($orderDate); ?>,
                    <?= htmlspecialchars($orderTime); ?>
                </span>
            </div>
        </div>

        <div class="status-line <?= $currentRank >= 2 ? 'completed-line' : ''; ?>"></div>

        <!-- Confirmed -->
        <div class="status-step <?= $currentRank >= 2 ? 'completed' : ''; ?>">
            <div class="status-step-icon">
                <?= $currentRank >= 2 ? '✓' : '2'; ?>
            </div>

            <div>
                <strong>Confirmed</strong>
                <span>
                    <?= $currentRank >= 2 ? 'Completed' : 'Pending'; ?>
                </span>
            </div>
        </div>

        <div class="status-line <?= $currentRank >= 3 ? 'completed-line' : ''; ?>"></div>

        <!-- Shipped -->
        <div class="status-step <?= $currentRank >= 3 ? 'completed' : ''; ?>">
            <div class="status-step-icon">
                <?= $currentRank >= 3 ? '✓' : '3'; ?>
            </div>

            <div>
                <strong>Shipped</strong>
                <span>
                    <?= $currentRank >= 3 ? 'Completed' : 'Pending'; ?>
                </span>
            </div>
        </div>

        <div class="status-line <?= $currentRank >= 4 ? 'completed-line' : ''; ?>"></div>

        <!-- Delivered -->
        <div class="status-step <?= $currentRank >= 4 ? 'completed' : ''; ?>">
            <div class="status-step-icon">
                <?= $currentRank >= 4 ? '✓' : '4'; ?>
            </div>

            <div>
                <strong>Delivered</strong>
                <span>
                    <?= $currentRank >= 4 ? 'Completed' : 'Pending'; ?>
                </span>
            </div>
        </div>

    <?php endif; ?>

</div>

            </div>

        </div>


        <!-- Order Summary -->

        <div class="order-summary-panel">

            <div class="order-panel-header">

                <div>

                    <h3>
                        Order Summary
                    </h3>

                    <p>
                        Payment breakdown
                    </p>

                </div>

            </div>


            <div class="order-summary-content">


                <div class="summary-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>
                        ₹<?= number_format(
                            (float) $order["subtotal"],
                            2
                        ); ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        Delivery Charge
                    </span>

                    <strong>
                        ₹<?= number_format(
                            (float) $order["delivery_charge"],
                            2
                        ); ?>
                    </strong>

                </div>


                <div class="summary-row">

                    <span>
                        GST
                    </span>

                    <strong>
                        ₹<?= number_format(
                            (float) $order["gst_amount"],
                            2
                        ); ?>
                    </strong>

                </div>


                <div class="summary-divider"></div>


                <div class="summary-row summary-total">

                    <span>
                        Total Amount
                    </span>

                    <strong>
                        ₹<?= number_format(
                            (float) $order["total_amount"],
                            2
                        ); ?>
                    </strong>

                </div>

            </div>

        </div>

    </div>


    <!-- Admin Actions -->

    <div class="order-admin-actions">

        <div>

            <h3>
                Order Actions
            </h3>

            <p>
                Update the order status from the options below.
            </p>

        </div>


        <form
            method="POST"
            class="order-action-controls"
        >

            <select
                name="order_status"
                class="order-update-status"
                required
            >

                <option value="">
                    Update Status
                </option>

                <option
                    value="pending"
                    <?= $orderStatus === "pending"
                        ? "selected"
                        : ""; ?>
                >
                    Pending
                </option>

                <option
                    value="confirmed"
                    <?= $orderStatus === "confirmed"
                        ? "selected"
                        : ""; ?>
                >
                    Confirmed
                </option>

                <option
                    value="shipped"
                    <?= $orderStatus === "shipped"
                        ? "selected"
                        : ""; ?>
                >
                    Shipped
                </option>

                <option
                    value="delivered"
                    <?= $orderStatus === "delivered"
                        ? "selected"
                        : ""; ?>
                >
                    Delivered
                </option>

                <option
                    value="cancelled"
                    <?= $orderStatus === "cancelled"
                        ? "selected"
                        : ""; ?>
                >
                    Cancelled
                </option>

            </select>


            <button
                type="submit"
                class="update-order-status-btn"
            >
                Update Status
            </button>

        </form>

    </div>


</section>

</main>


<?php if (isset($_GET["updated"])): ?>

<script>

    Swal.fire({
        icon: "success",
        title: "Order Updated",
        text: "Order status has been updated successfully.",
        confirmButtonColor: "#0056F9"
    });

</script>

<?php endif; ?>


<?php if (isset($_GET["error"])): ?>

<script>

    Swal.fire({
        icon: "error",
        title: "Update Failed",
        text: "Unable to update the order status.",
        confirmButtonColor: "#0056F9"
    });

</script>

<?php endif; ?>


</body>

</html>