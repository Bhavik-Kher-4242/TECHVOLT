<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin-login.php");
    exit;
}

require_once "../includes/database.php";

$adminPage = "returns";
$adminBase = "./";

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

/*
|--------------------------------------------------------------------------
| Update Return Status
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $returnId = isset($_POST["return_id"])
        ? (int) $_POST["return_id"]
        : 0;

    $newStatus = trim(
        $_POST["return_status"] ?? ""
    );

    $allowedUpdateStatuses = [
        "approved",
        "rejected"
    ];

    if (
        $returnId <= 0 ||
        !in_array(
            $newStatus,
            $allowedUpdateStatuses,
            true
        )
    ) {

        $_SESSION["return_update_error"] =
            "Invalid return update request.";

        header("Location: manage-returns.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Only Pending Returns Can Be Approved / Rejected
    |--------------------------------------------------------------------------
    */

    $checkSql = "
        SELECT return_status
        FROM returns
        WHERE return_id = ?
        LIMIT 1
    ";

    $checkStmt = mysqli_prepare(
        $conn,
        $checkSql
    );

    if (!$checkStmt) {

        $_SESSION["return_update_error"] =
            "Unable to verify return status.";

        header("Location: manage-returns.php");
        exit;
    }

    mysqli_stmt_bind_param(
        $checkStmt,
        "i",
        $returnId
    );

    mysqli_stmt_execute(
        $checkStmt
    );

    $checkResult =
        mysqli_stmt_get_result(
            $checkStmt
        );

    $returnRow =
        mysqli_fetch_assoc(
            $checkResult
        );

    mysqli_stmt_close(
        $checkStmt
    );


    if (!$returnRow) {

        $_SESSION["return_update_error"] =
            "Return request not found.";

        header("Location: manage-returns.php");
        exit;
    }


    if ($returnRow["return_status"] !== "requested") {
        $_SESSION["return_update_error"] =
        "Only requested returns can be updated.";

        header("Location: manage-returns.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    $updateSql = "
        UPDATE returns
        SET
            return_status = ?,
            updated_at = NOW()
        WHERE
            return_id = ?
            AND return_status = 'requested'
    ";

    $updateStmt = mysqli_prepare(
        $conn,
        $updateSql
    );

    if (!$updateStmt) {

        $_SESSION["return_update_error"] =
            "Unable to update return status.";

        header("Location: manage-returns.php");
        exit;
    }

    mysqli_stmt_bind_param(
        $updateStmt,
        "si",
        $newStatus,
        $returnId
    );

    mysqli_stmt_execute(
        $updateStmt
    );


    if (mysqli_stmt_affected_rows($updateStmt) > 0) {

        $_SESSION["return_update_success"] =
            $newStatus === "approved"
                ? "Return approved successfully."
                : "Return rejected successfully.";

    } else {

        $_SESSION["return_update_error"] =
            "Return status was not updated.";
    }


    mysqli_stmt_close(
        $updateStmt
    );


    header("Location: manage-returns.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Validate Return Status
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    "requested",
    "approved",
    "rejected",
    "completed"
];

if (
    $statusFilter !== "" &&
    !in_array($statusFilter, $allowedStatuses, true)
) {
    $statusFilter = "";
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$recordsPerPage = 5;

$currentPage = isset($_GET["page"])
    ? (int) $_GET["page"]
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}

/*
|--------------------------------------------------------------------------
| Summary Statistics
|--------------------------------------------------------------------------
*/

/* Total Returns */

$totalReturns = 0;

$totalSql = "
    SELECT COUNT(*) AS total_returns
    FROM returns
";

$totalResult = mysqli_query($conn, $totalSql);

if ($totalResult) {
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalReturns = (int) $totalRow["total_returns"];
}


/* Pending Returns */

$pendingReturns = 0;

$pendingSql = "
    SELECT COUNT(*) AS pending_returns
    FROM returns
    WHERE return_status = 'requested'
";

$pendingResult = mysqli_query($conn, $pendingSql);

if ($pendingResult) {
    $pendingRow = mysqli_fetch_assoc($pendingResult);
    $pendingReturns = (int) $pendingRow["pending_returns"];
}


/* Approved Returns */

$approvedReturns = 0;

$approvedSql = "
    SELECT COUNT(*) AS approved_returns
    FROM returns
    WHERE return_status = 'approved'
";

$approvedResult = mysqli_query($conn, $approvedSql);

if ($approvedResult) {
    $approvedRow = mysqli_fetch_assoc($approvedResult);
    $approvedReturns = (int) $approvedRow["approved_returns"];
}


/* Rejected Returns */

$rejectedReturns = 0;

$rejectedSql = "
    SELECT COUNT(*) AS rejected_returns
    FROM returns
    WHERE return_status = 'rejected'
";

$rejectedResult = mysqli_query($conn, $rejectedSql);

if ($rejectedResult) {
    $rejectedRow = mysqli_fetch_assoc($rejectedResult);
    $rejectedReturns = (int) $rejectedRow["rejected_returns"];
}


/*
|--------------------------------------------------------------------------
| Count Filtered Returns
|--------------------------------------------------------------------------
*/

$countSql = "
    SELECT COUNT(*) AS total
    FROM returns r

    INNER JOIN users u
        ON r.user_id = u.user_id

    INNER JOIN order_items oi
        ON r.order_item_id = oi.order_item_id

    WHERE 1 = 1
";

$countTypes = "";
$countParams = [];


/* Search */

if ($search !== "") {

    $countSql .= "
        AND (
            CAST(oi.order_id AS CHAR) LIKE ?
            OR u.full_name LIKE ?
            OR u.email LIKE ?
            OR oi.product_name LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $countTypes .= "ssss";

    $countParams[] = $searchValue;
    $countParams[] = $searchValue;
    $countParams[] = $searchValue;
    $countParams[] = $searchValue;
}


/* Status */

if ($statusFilter !== "") {

    $countSql .= "
        AND r.return_status = ?
    ";

    $countTypes .= "s";

    $countParams[] = $statusFilter;
}


$countStmt = mysqli_prepare(
    $conn,
    $countSql
);

if (!$countStmt) {
    die("Return count query preparation failed.");
}


if (!empty($countParams)) {

    mysqli_stmt_bind_param(
        $countStmt,
        $countTypes,
        ...$countParams
    );
}


mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result(
    $countStmt
);

$countRow = mysqli_fetch_assoc(
    $countResult
);

$totalFilteredReturns = (int) $countRow["total"];

mysqli_stmt_close($countStmt);


/*
|--------------------------------------------------------------------------
| Pagination Calculation
|--------------------------------------------------------------------------
*/

$totalPages = max(
    1,
    (int) ceil(
        $totalFilteredReturns / $recordsPerPage
    )
);

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = (
    $currentPage - 1
) * $recordsPerPage;


/*
|--------------------------------------------------------------------------
| Fetch Returns
|--------------------------------------------------------------------------
*/

$returnsSql = "
    SELECT

        r.return_id,
        r.order_item_id,
        r.user_id,
        r.reason,
        r.description,
        r.return_status,
        r.created_at,
        r.updated_at,

        u.full_name,
        u.email,
        u.profile_image,

        oi.order_id,
        oi.product_id,
        oi.product_name,
        oi.price,
        oi.quantity,
        oi.subtotal

    FROM returns r

    INNER JOIN users u
        ON r.user_id = u.user_id

    INNER JOIN order_items oi
        ON r.order_item_id = oi.order_item_id

    WHERE 1 = 1
";

$returnsTypes = "";
$returnsParams = [];


/* Search */

if ($search !== "") {

    $returnsSql .= "
        AND (
            CAST(oi.order_id AS CHAR) LIKE ?
            OR u.full_name LIKE ?
            OR u.email LIKE ?
            OR oi.product_name LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $returnsTypes .= "ssss";

    $returnsParams[] = $searchValue;
    $returnsParams[] = $searchValue;
    $returnsParams[] = $searchValue;
    $returnsParams[] = $searchValue;
}


/* Status */

if ($statusFilter !== "") {

    $returnsSql .= "
        AND r.return_status = ?
    ";

    $returnsTypes .= "s";

    $returnsParams[] = $statusFilter;
}


/* Order + Pagination */

$returnsSql .= "
    ORDER BY r.return_id DESC
    LIMIT ? OFFSET ?
";

$returnsTypes .= "ii";

$returnsParams[] = $recordsPerPage;
$returnsParams[] = $offset;


/*
|--------------------------------------------------------------------------
| Prepare Returns Query
|--------------------------------------------------------------------------
*/

$returnsStmt = mysqli_prepare(
    $conn,
    $returnsSql
);

if (!$returnsStmt) {
    die("Return query preparation failed.");
}


mysqli_stmt_bind_param(
    $returnsStmt,
    $returnsTypes,
    ...$returnsParams
);


mysqli_stmt_execute(
    $returnsStmt
);

$returnsResult = mysqli_stmt_get_result(
    $returnsStmt
);

$returns = [];


while (
    $return = mysqli_fetch_assoc(
        $returnsResult
    )
) {
    $returns[] = $return;
}


mysqli_stmt_close(
    $returnsStmt
);


/*
|--------------------------------------------------------------------------
| Pagination URL
|--------------------------------------------------------------------------
*/

function getReturnPaginationUrl(
    int $page,
    string $search,
    string $status
): string {

    $params = [];

    if ($search !== "") {
        $params["search"] = $search;
    }

    if ($status !== "") {
        $params["status"] = $status;
    }

    $params["page"] = $page;

    return "?" . http_build_query($params);
}


/*
|--------------------------------------------------------------------------
| Customer Initials
|--------------------------------------------------------------------------
*/

function getReturnCustomerInitials(
    string $name
): string {

    $nameParts = explode(
        " ",
        trim($name)
    );

    $initials = "";

    foreach ($nameParts as $part) {

        if ($part === "") {
            continue;
        }

        $initials .= strtoupper(
            substr($part, 0, 1)
        );

        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials;
}


/*
|--------------------------------------------------------------------------
| Format Return Date
|--------------------------------------------------------------------------
*/

function formatReturnDate(
    string $date
): string {

    return date(
        "d M Y",
        strtotime($date)
    );
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>TechVolt Admin - Returns</title>

    <link
        rel="stylesheet"
        href="includes/admin.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <?php include 'includes/header.php'; ?>


    <main class="admin-main">

        <!-- ================= PAGE HEADER ================= -->

        <section class="returns-page">

            <div class="returns-page-header">

                <div>

                    <h2>Returns</h2>

                    <p>
                        Manage customer return requests and their status.
                    </p>

                </div>

            </div>


            <!-- ================= SUMMARY ================= -->

            <div class="returns-summary">

                <div class="return-summary-card">

                    <div class="return-summary-icon">
                        ↩
                    </div>

                    <div>

                        <small>Total Returns</small>

                        <strong><?= $totalReturns; ?></strong>

                    </div>

                </div>


                <div class="return-summary-card">

                    <div class="return-summary-icon pending-icon">
                        ⏳
                    </div>

                    <div>

                        <small>Pending</small>

                        <strong><?= $pendingReturns; ?></strong>

                    </div>

                </div>


                <div class="return-summary-card">

                    <div class="return-summary-icon approved-icon">
                        ✓
                    </div>

                    <div>

                        <small>Approved</small>

                        <strong><?= $approvedReturns; ?></strong>

                    </div>

                </div>


                <div class="return-summary-card">

                    <div class="return-summary-icon rejected-icon">
                        ✕
                    </div>

                    <div>

                        <small>Rejected</small>

                        <strong><?= $rejectedReturns; ?></strong>

                    </div>

                </div>

            </div>


            <!-- ================= RETURNS PANEL ================= -->

            <div class="returns-panel">

                <div class="returns-panel-header">

                    <div>

                        <h2>Return Requests</h2>

                        <p>
                            Review customer return requests.
                        </p>

                    </div>

                </div>


                <!-- ================= TOOLBAR ================= -->

                <form method="get" class="returns-toolbar">

                    <div class="return-search">

                        <input
                            type="text"
                            name="search"
                            value="<?= htmlspecialchars($search); ?>"
                            placeholder="Search by order ID, customer or product...">

                    </div>


                    <select name="status">

                        <option value="">
                            All Status
                        </option>

                        <option
                            value="requested"
                            <?= $statusFilter === "requested" ? "selected" : ""; ?>>
                            Requested
                        </option>

                        <option
                            value="approved"
                            <?= $statusFilter === "approved" ? "selected" : ""; ?>>
                            Approved
                        </option>

                        <option
                            value="rejected"
                            <?= $statusFilter === "rejected" ? "selected" : ""; ?>>
                            Rejected
                        </option>

                        <option
                            value="completed"
                            <?= $statusFilter === "completed" ? "selected" : ""; ?>>
                            Completed
                        </option>

                    </select>


                    <button class="filter-return-btn" type="submit">
                        Filter
                    </button>

                </form>


                <!-- ================= TABLE ================= -->

                <div class="returns-table-container">

                    <table class="returns-table">

                        <thead>

                            <tr>

                                <th>Return ID</th>

                                <th>Order ID</th>

                                <th>Customer</th>

                                <th>Product</th>

                                <th>Reason</th>

                                <th>Request Date</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (empty($returns)): ?>

                                <tr>
                                    <td colspan="8" class="no-data">
                                        No returns found.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($returns as $return): ?>

                                    <?php
                                    $returnId = (int) $return["return_id"];
                                    $orderId = (int) $return["order_id"];

                                    $customerName = $return["full_name"];
                                    $customerEmail = $return["email"];

                                    $productName = $return["product_name"];

                                    $reason = $return["reason"];
                                    $returnStatus = strtolower(
                                        $return["return_status"]
                                    );

                                    $requestDate = formatReturnDate(
                                        $return["created_at"]
                                    );

                                    $initials = getReturnCustomerInitials(
                                        $customerName
                                    );

                                    /*
            |--------------------------------------------------------------------------
            | Order Display ID
            |--------------------------------------------------------------------------
            */

                                    $displayOrderId =
                                        "#TV" . str_pad(
                                            $orderId,
                                            6,
                                            "0",
                                            STR_PAD_LEFT
                                        );

                                    /*
            |--------------------------------------------------------------------------
            | Return Display ID
            |--------------------------------------------------------------------------
            */

                                    $displayReturnId =
                                        "#RET" . str_pad(
                                            $returnId,
                                            3,
                                            "0",
                                            STR_PAD_LEFT
                                        );
                                    ?>

                                    <tr>

                                        <!-- Return ID -->
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($displayReturnId); ?>
                                            </strong>
                                        </td>


                                        <!-- Order ID -->
                                        <td>
                                            <?= htmlspecialchars($displayOrderId); ?>
                                        </td>


                                        <!-- Customer -->
                                        <td>

                                            <div class="customer-cell">

                                                <div class="customer-avatar">

                                                    <?php if (!empty($return["profile_image"])): ?>

                                                        <img
                                                            src="../<?= htmlspecialchars(
                                                                        $return["profile_image"]
                                                                    ); ?>"
                                                            alt="<?= htmlspecialchars(
                                                                        $customerName
                                                                    ); ?>">

                                                    <?php else: ?>

                                                        <?= htmlspecialchars($initials); ?>

                                                    <?php endif; ?>

                                                </div>


                                                <div class="customer-info">

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


                                        <!-- Product -->
                                        <td>
                                            <?= htmlspecialchars($productName); ?>
                                        </td>


                                        <!-- Reason -->
                                        <td>
                                            <?= htmlspecialchars($reason); ?>
                                        </td>


                                        <!-- Request Date -->
                                        <td>
                                            <?= htmlspecialchars($requestDate); ?>
                                        </td>


                                        <!-- Status -->
                                        <td>

                                            <span
                                                class="return-status <?= htmlspecialchars(
                                                                            $returnStatus
                                                                        ); ?>">
                                                <?= htmlspecialchars(
                                                    ucfirst($returnStatus)
                                                ); ?>
                                            </span>

                                        </td>


                                        <!-- Action -->
                                        <td>
                                            <button
                                                type="button"
                                                class="view-return-btn"
                                                onclick="openReturnModal(
                        <?= htmlspecialchars(
                                        json_encode([
                                            "id" => $displayReturnId,
                                            "return_id" => $returnId,
                                            "order_id" => $displayOrderId,

                                            "customer_name" => $customerName,
                                            "customer_email" => $customerEmail,
                                            "profile_image" => $return["profile_image"],
                                            "initials" => $initials,

                                            "product" => $productName,
                                            "quantity" => (int) $return["quantity"],
                                            "price" => (float) $return["price"],

                                            "reason" => $reason,
                                            "description" => $return["description"],

                                            "return_date" => $requestDate,
                                            "status" => $returnStatus
                                        ]),
                                        JSON_HEX_TAG |
                                            JSON_HEX_APOS |
                                            JSON_HEX_QUOT |
                                            JSON_HEX_AMP
                                    ); ?>
                    )">
                                                View
                                            </button>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- ================= PAGINATION ================= -->

                <div class="returns-pagination">

                    <!-- Previous -->
                    <?php if ($currentPage > 1): ?>

                        <a
                            href="<?= getReturnPaginationUrl(
                                        $currentPage - 1,
                                        $search,
                                        $statusFilter
                                    ); ?>"
                            class="page-btn">
                            Previous
                        </a>

                    <?php else: ?>

                        <a
                            href="#"
                            class="page-btn disabled"
                            aria-disabled="true">
                            Previous
                        </a>

                    <?php endif; ?>


                    <!-- Page Numbers -->
                    <?php for (
                        $page = 1;
                        $page <= $totalPages;
                        $page++
                    ): ?>

                        <a
                            href="<?= getReturnPaginationUrl(
                                        $page,
                                        $search,
                                        $statusFilter
                                    ); ?>"
                            class="page-btn <?= $page === $currentPage
                                                ? 'active'
                                                : ''; ?>">
                            <?= $page; ?>
                        </a>

                    <?php endfor; ?>


                    <!-- Next -->
                    <?php if ($currentPage < $totalPages): ?>

                        <a
                            href="<?= getReturnPaginationUrl(
                                        $currentPage + 1,
                                        $search,
                                        $statusFilter
                                    ); ?>"
                            class="page-btn">
                            Next
                        </a>

                    <?php else: ?>

                        <a
                            href="#"
                            class="page-btn disabled"
                            aria-disabled="true">
                            Next
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </section>
        <div
    class="return-modal-overlay"
    id="return-modal"
>
    <div class="return-modal">

        <!-- Modal Header -->
        <div class="return-modal-header">

            <div>
                <h3>
                    Return Details
                </h3>

                <p>
                    View complete return request information.
                </p>
            </div>

            <button
                type="button"
                class="return-modal-close"
                onclick="closeReturnModal()"
            >
                ×
            </button>

        </div>


        <!-- Modal Body -->
        <div class="return-modal-body">


            <!-- ================= RETURN INFORMATION ================= -->

            <div class="return-modal-section">

                <h4 class="return-modal-section-title">
                    Return Information
                </h4>


                <div class="return-modal-info-grid">

                    <div class="return-modal-info-item">

                        <span>
                            Return ID
                        </span>

                        <strong id="return-modal-id">
                            --
                        </strong>

                    </div>


                    <div class="return-modal-info-item">

                        <span>
                            Return Date
                        </span>

                        <strong id="return-modal-date">
                            --
                        </strong>

                    </div>


                    <div class="return-modal-info-item">

                        <span>
                            Order ID
                        </span>

                        <strong id="return-modal-order">
                            --
                        </strong>

                    </div>


                    <div class="return-modal-info-item">

                        <span>
                            Status
                        </span>

                        <strong
                            id="return-modal-status"
                            class="return-status"
                        >
                            --
                        </strong>

                    </div>

                </div>

            </div>


            <!-- ================= CUSTOMER INFORMATION ================= -->

            <div class="return-modal-section">

                <h4 class="return-modal-section-title">
                    Customer Information
                </h4>


                <div class="return-customer">

                    <div
                        class="return-customer-avatar"
                        id="return-modal-avatar"
                    >
                        --
                    </div>


                    <div class="return-customer-info">

                        <strong id="return-modal-customer">
                            --
                        </strong>

                        <span id="return-modal-email">
                            --
                        </span>

                    </div>

                </div>

            </div>


            <!-- ================= PRODUCT INFORMATION ================= -->

            <div class="return-modal-section">

                <h4 class="return-modal-section-title">
                    Product Information
                </h4>


                <div class="return-product-box">

                    <div class="return-product-info">

                        <strong id="return-modal-product">
                            --
                        </strong>


                        <span>
                            Quantity:

                            <strong id="return-modal-quantity">
                                --
                            </strong>
                        </span>

                    </div>


                    <strong
                        class="return-product-price"
                        id="return-modal-price"
                    >
                        ₹0
                    </strong>

                </div>

            </div>


            <!-- ================= RETURN REQUEST ================= -->

            <div class="return-modal-section">

                <h4 class="return-modal-section-title">
                    Return Request
                </h4>


                <div class="return-reason-box">

                    <div class="return-reason-label">
                        Reason
                    </div>


                    <strong id="return-modal-reason">
                        --
                    </strong>


                    <p id="return-modal-description">
                        --
                    </p>

                </div>

            </div>

        </div>


        <!-- ================= MODAL FOOTER ================= -->

        <div class="return-modal-footer">

            <button
                type="button"
                class="return-modal-cancel"
                onclick="closeReturnModal()"
            >
                Close
            </button>


            <div class="return-modal-actions">

                <button
                    type="button"
                    class="return-reject-btn"
                    id="return-modal-reject"
                >
                    Reject Return
                </button>


                <button
                    type="button"
                    class="return-approve-btn"
                    id="return-modal-approve"
                >
                    Approve Return
                </button>

            </div>

        </div>

    </div>
</div>
    </main>
    <!-- Return Details Modal -->


    <script src="includes/admin.js"></script>
<?php if (isset($_SESSION["return_update_success"])): ?>

    <script>
        Swal.fire({
            icon: "success",
            title: "Success",
            text: <?= json_encode(
                $_SESSION["return_update_success"]
            ); ?>,
            confirmButtonText: "OK"
        });
    </script>

    <?php unset(
        $_SESSION["return_update_success"]
    ); ?>

<?php endif; ?>


<?php if (isset($_SESSION["return_update_error"])): ?>

    <script>
        Swal.fire({
            icon: "error",
            title: "Update Failed",
            text: <?= json_encode(
                $_SESSION["return_update_error"]
            ); ?>,
            confirmButtonText: "OK"
        });
    </script>

    <?php unset(
        $_SESSION["return_update_error"]
    ); ?>

<?php endif; ?>
</body>

</html>