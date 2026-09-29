<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

require_once "../../includes/database.php";

$adminPage = 'users';
$adminBase = '../';


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");


/*
|--------------------------------------------------------------------------
| Validate Status Filter
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    "active",
    "inactive"
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


/* Total Customers */

$totalCustomers = 0;

$totalSql = "
    SELECT COUNT(*) AS total_customers
    FROM users
    WHERE role = 'customer'
";

$totalResult = mysqli_query($conn, $totalSql);

if ($totalResult) {
    $totalRow = mysqli_fetch_assoc($totalResult);

    $totalCustomers = (int) $totalRow["total_customers"];
}


/* Active Customers */

$activeCustomers = 0;

$activeSql = "
    SELECT COUNT(*) AS active_customers
    FROM users
    WHERE role = 'customer'
    AND status = 'active'
";

$activeResult = mysqli_query($conn, $activeSql);

if ($activeResult) {
    $activeRow = mysqli_fetch_assoc($activeResult);

    $activeCustomers = (int) $activeRow["active_customers"];
}


/* New Customers - Current Month */

$newCustomers = 0;

$newSql = "
    SELECT COUNT(*) AS new_customers
    FROM users
    WHERE role = 'customer'
    AND YEAR(created_at) = YEAR(CURDATE())
    AND MONTH(created_at) = MONTH(CURDATE())
";

$newResult = mysqli_query($conn, $newSql);

if ($newResult) {
    $newRow = mysqli_fetch_assoc($newResult);

    $newCustomers = (int) $newRow["new_customers"];
}


/* Customers With Orders */

$customersWithOrders = 0;

$ordersCustomerSql = "
    SELECT COUNT(DISTINCT u.user_id) AS customers_with_orders
    FROM users u
    INNER JOIN orders o
        ON u.user_id = o.user_id
    WHERE u.role = 'customer'
";

$ordersCustomerResult = mysqli_query(
    $conn,
    $ordersCustomerSql
);

if ($ordersCustomerResult) {
    $ordersCustomerRow = mysqli_fetch_assoc(
        $ordersCustomerResult
    );

    $customersWithOrders = (int)
        $ordersCustomerRow["customers_with_orders"];
}


/*
|--------------------------------------------------------------------------
| Count Filtered Customers
|--------------------------------------------------------------------------
*/

$countSql = "
    SELECT COUNT(*) AS total
    FROM users u
    WHERE u.role = 'customer'
";

$countTypes = "";
$countParams = [];


if ($search !== "") {

    $countSql .= "
        AND (
            u.full_name LIKE ?
            OR u.username LIKE ?
            OR u.email LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $countTypes .= "sss";

    $countParams[] = $searchValue;
    $countParams[] = $searchValue;
    $countParams[] = $searchValue;
}


if ($statusFilter !== "") {

    $countSql .= "
        AND u.status = ?
    ";

    $countTypes .= "s";

    $countParams[] = $statusFilter;
}


$countStmt = mysqli_prepare($conn, $countSql);

if (!$countStmt) {
    die("Customer count query preparation failed.");
}


if (!empty($countParams)) {

    mysqli_stmt_bind_param(
        $countStmt,
        $countTypes,
        ...$countParams
    );
}


mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);

$countRow = mysqli_fetch_assoc($countResult);

$totalFilteredCustomers = (int) $countRow["total"];

mysqli_stmt_close($countStmt);


/*
|--------------------------------------------------------------------------
| Pagination Calculation
|--------------------------------------------------------------------------
*/

$totalPages = max(
    1,
    (int) ceil(
        $totalFilteredCustomers / $recordsPerPage
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
| Fetch Customers
|--------------------------------------------------------------------------
*/

$usersSql = "
    SELECT
        u.user_id,
        u.full_name,
        u.username,
        u.email,
        u.status,
        u.profile_image,
        u.created_at,

        COUNT(
            DISTINCT o.order_id
        ) AS order_count,

        COALESCE(
            SUM(
                CASE
                    WHEN o.order_status != 'cancelled'
                    THEN o.total_amount
                    ELSE 0
                END
            ),
            0
        ) AS total_spent

    FROM users u

    LEFT JOIN orders o
        ON u.user_id = o.user_id

    WHERE u.role = 'customer'
";


$usersTypes = "";
$usersParams = [];


if ($search !== "") {

    $usersSql .= "
        AND (
            u.full_name LIKE ?
            OR u.username LIKE ?
            OR u.email LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $usersTypes .= "sss";

    $usersParams[] = $searchValue;
    $usersParams[] = $searchValue;
    $usersParams[] = $searchValue;
}


if ($statusFilter !== "") {

    $usersSql .= "
        AND u.status = ?
    ";

    $usersTypes .= "s";

    $usersParams[] = $statusFilter;
}


$usersSql .= "
    GROUP BY
        u.user_id,
        u.full_name,
        u.username,
        u.email,
        u.status,
        u.profile_image,
        u.created_at

    ORDER BY u.user_id DESC

    LIMIT ? OFFSET ?
";


$usersTypes .= "ii";

$usersParams[] = $recordsPerPage;
$usersParams[] = $offset;


$usersStmt = mysqli_prepare($conn, $usersSql);

if (!$usersStmt) {
    die("Customer query preparation failed.");
}


mysqli_stmt_bind_param(
    $usersStmt,
    $usersTypes,
    ...$usersParams
);


mysqli_stmt_execute($usersStmt);

$usersResult = mysqli_stmt_get_result($usersStmt);

$customers = [];


while ($customer = mysqli_fetch_assoc($usersResult)) {

    $customers[] = $customer;
}


mysqli_stmt_close($usersStmt);


/*
|--------------------------------------------------------------------------
| Pagination URL
|--------------------------------------------------------------------------
*/

function getUserPaginationUrl(
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

function getCustomerInitials(
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
| Format Joined Date
|--------------------------------------------------------------------------
*/

function formatJoinedDate(
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TechVolt Admin - Customers</title>

    <link rel="stylesheet" href="../includes/admin.css">

</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="users-page">


            <!-- Page Header -->

            <div class="users-page-header">

                <div>
                    <h2>Customers</h2>
                    <p>Manage registered TechVolt customers.</p>
                </div>

            </div>


            <!-- User Summary -->

            <div class="users-summary">

                <div class="user-summary-card">

                    <div class="user-summary-icon blue">
                        ♙
                    </div>

                    <div>
                        <span>Total Customers</span>
                        <strong><?= $totalCustomers; ?></strong>
                    </div>

                </div>


                <div class="user-summary-card">

                    <div class="user-summary-icon green">
                        ✓
                    </div>

                    <div>
                        <span>Active Customers</span>
                        <strong><?= $activeCustomers; ?></strong>
                    </div>

                </div>


                <div class="user-summary-card">

                    <div class="user-summary-icon orange">
                        +
                    </div>

                    <div>
                        <span>New Customers</span>
                        <strong><?= $newCustomers; ?></strong>
                    </div>

                </div>


                <div class="user-summary-card">

                    <div class="user-summary-icon purple">
                        ◫
                    </div>

                    <div>
                        <span>Customers With Orders</span>
                        <strong><?= $customersWithOrders; ?></strong>
                    </div>

                </div>

            </div>


            <!-- Customers Panel -->

            <div class="users-panel">

                <div class="users-panel-header">

                    <div>
                        <h3>Customer List</h3>
                        <p>View and manage registered customers.</p>
                    </div>

                </div>


                <!-- Toolbar -->

                <!-- Toolbar -->

                <form method="GET" class="users-toolbar">

                    <div class="user-search">

                        <span class="user-search-icon">
                            ⌕
                        </span>

                        <input
                            type="search"
                            name="search"
                            value="<?= htmlspecialchars($search); ?>"
                            placeholder="Search by name, username or email...">

                    </div>


                    <select
                        name="status"
                        class="user-status-filter">

                        <option value="">
                            All Customers
                        </option>

                        <option
                            value="active"
                            <?= $statusFilter === "active"
                                ? "selected"
                                : ""; ?>>
                            Active
                        </option>

                        <option
                            value="inactive"
                            <?= $statusFilter === "inactive"
                                ? "selected"
                                : ""; ?>>
                            Inactive
                        </option>

                    </select>


                    <button
                        type="submit"
                        class="filter-user-btn">
                        Filter
                    </button>


                    <a
                        href="manage-users.php"
                        class="refresh-users-btn">
                        ↻
                    </a>

                </form>


                <!-- Customers Table -->

                <div class="users-table-container">

                    <table class="users-table">

                        <thead>

                            <tr>

                                <th>Customer</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Orders</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (empty($customers)): ?>

                            <tr>

                                <td
                                    colspan="7"
                                    style="text-align: center;"
                                >
                                    No customers found.
                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($customers as $customer): ?>

                                <?php

                                $customerId =
                                    (int) $customer["user_id"];

                                $customerName =
                                    $customer["full_name"];

                                $initials =
                                    getCustomerInitials(
                                        $customerName
                                    );

                                $joinedDate =
                                    formatJoinedDate(
                                        $customer["created_at"]
                                    );

                                $customerStatus =
                                    strtolower(
                                        $customer["status"]
                                    );

                                $orderCount =
                                    (int) $customer["order_count"];

                                $totalSpent =
                                    (float) $customer["total_spent"];

                                ?>

                                <tr>

                                    <!-- Customer -->

                                    <td>

                                        <div class="user-info">

                                            <div class="user-avatar">

                                                <?php if (
                                                    !empty(
                                                        $customer["profile_image"]
                                                    )
                                                ): ?>

                                                    <img
                                                        src="../../<?= htmlspecialchars(
                                                            $customer["profile_image"]
                                                        ); ?>"
                                                        alt="<?= htmlspecialchars(
                                                            $customerName
                                                        ); ?>"
                                                    >

                                                <?php else: ?>

                                                    <?= htmlspecialchars(
                                                        $initials
                                                    ); ?>

                                                <?php endif; ?>

                                            </div>


                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $customerName
                                                    ); ?>
                                                </strong>

                                                <small>
                                                    Customer ID:
                                                    #CUS<?= str_pad(
                                                        $customerId,
                                                        4,
                                                        "0",
                                                        STR_PAD_LEFT
                                                    ); ?>
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Username -->

                                    <td>
                                        <?= htmlspecialchars(
                                            $customer["username"]
                                        ); ?>
                                    </td>


                                    <!-- Email -->

                                    <td>
                                        <?= htmlspecialchars(
                                            $customer["email"]
                                        ); ?>
                                    </td>


                                    <!-- Orders -->

                                    <td class="user-orders-count">
                                        <?= $orderCount; ?>
                                    </td>


                                    <!-- Joined -->

                                    <td>
                                        <?= htmlspecialchars(
                                            $joinedDate
                                        ); ?>
                                    </td>


                                    <!-- Status -->

                                    <td>

                                        <span
                                            class="user-status <?= htmlspecialchars(
                                                $customerStatus
                                            ); ?>"
                                        >

                                            <?= ucfirst(
                                                $customerStatus
                                            ); ?>

                                        </span>

                                    </td>


                                    <!-- Action -->

                                    <td>

                                        <button
                                            type="button"
                                            class="view-user-btn"
                                            onclick='openUserModal(<?= json_encode([
                                                "id" => "#CUS" . str_pad(
                                                    $customerId,
                                                    4,
                                                    "0",
                                                    STR_PAD_LEFT
                                                ),
                                                "name" => $customerName,
                                                "username" => $customer["username"],
                                                "email" => $customer["email"],
                                                "orders" => $orderCount,
                                                "joined" => $joinedDate,
                                                "status" => ucfirst(
                                                    $customerStatus
                                                ),
                                                "spent" => "₹" . number_format(
                                                    $totalSpent,
                                                    2
                                                ),
                                                "initials" => $initials,
                                                "profile_image" => $customer["profile_image"]
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'
                                        >

                                            View

                                        </button>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>


                <!-- Footer -->

            <div class="users-table-footer">

                <?php

                $showingFrom =
                    $totalFilteredCustomers > 0
                        ? $offset + 1
                        : 0;

                $showingTo =
                    min(
                        $offset + $recordsPerPage,
                        $totalFilteredCustomers
                    );

                ?>

                <span>

                    Showing

                    <strong>
                        <?= $showingFrom; ?>–<?= $showingTo; ?>
                    </strong>

                    of

                    <strong>
                        <?= $totalFilteredCustomers; ?>
                    </strong>

                    customers

                </span>


                <div class="users-pagination">

                    <!-- Previous -->

                    <a
                        href="<?= $currentPage > 1
                            ? getUserPaginationUrl(
                                $currentPage - 1,
                                $search,
                                $statusFilter
                            )
                            : '#'; ?>"
                        class="<?= $currentPage <= 1
                            ? 'disabled'
                            : ''; ?>"
                    >
                        ‹
                    </a>


                    <!-- Page Numbers -->

                    <?php for (
                        $page = 1;
                        $page <= $totalPages;
                        $page++
                    ): ?>

                        <a
                            href="<?= getUserPaginationUrl(
                                $page,
                                $search,
                                $statusFilter
                            ); ?>"
                            class="<?= $page === $currentPage
                                ? 'active-page'
                                : ''; ?>"
                        >
                            <?= $page; ?>
                        </a>

                    <?php endfor; ?>


                    <!-- Next -->

                    <a
                        href="<?= $currentPage < $totalPages
                            ? getUserPaginationUrl(
                                $currentPage + 1,
                                $search,
                                $statusFilter
                            )
                            : '#'; ?>"
                        class="<?= $currentPage >= $totalPages
                            ? 'disabled'
                            : ''; ?>"
                    >
                        ›
                    </a>

                </div>

            </div>

            </div>
        <!-- ================= CUSTOMER VIEW MODAL ================= -->

            <div
                class="user-modal-overlay"
                id="user-modal">

                <div class="user-modal">


                    <!-- ================= HEADER ================= -->

                    <div class="user-modal-header">

                        <div>

                            <h3>Customer Details</h3>

                            <p>
                                View registered customer information.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="user-modal-close"
                            onclick="closeUserModal()"
                        >
                            ×
                        </button>

                    </div>



                    <!-- ================= BODY ================= -->

                    <div class="user-modal-body">


                        <!-- CUSTOMER PROFILE -->

                        <div class="user-modal-profile">

                            <div
                                class="user-modal-avatar"
                                id="user-modal-avatar">
                                
                            </div>


                            <div class="user-modal-profile-info">

                                <h3 id="user-modal-name">
                                    
                                </h3>

                                <span id="user-modal-username">
                                        
                                </span>

                            </div>


                            <span
                                class="user-modal-status"
                                id="user-modal-status"
                            >
                                Active
                            </span>

                        </div>



                        <!-- ACCOUNT INFORMATION -->

                        <div class="user-modal-section">

                            <div class="user-modal-section-title">
                                Account Information
                            </div>


                            <div class="user-modal-info-grid">


                                <div class="user-modal-info-item">

                                    <span>Customer ID</span>

                                    <strong id="user-modal-id">
                                        
                                    </strong>

                                </div>


                                <div class="user-modal-info-item">

                                    <span>Joined</span>

                                    <strong id="user-modal-joined">
                                        
                                    </strong>

                                </div>


                                <div class="user-modal-info-item">

                                    <span>Total Orders</span>

                                    <strong id="user-modal-orders">
                                        0
                                    </strong>

                                </div>


                                <div class="user-modal-info-item">

                                    <span>Total Spent</span>

                                    <strong
                                        class="user-modal-spent"
                                        id="user-modal-spent"
                                    >
                                        ₹0.00
                                    </strong>

                                </div>


                            </div>

                        </div>



                        <!-- CONTACT INFORMATION -->

                        <div class="user-modal-section">

                            <div class="user-modal-section-title">
                                Contact Information
                            </div>


                            <div class="user-modal-contact-list">


                                <div class="user-modal-contact-item">

                                    <span class="user-contact-icon">
                                        @
                                    </span>

                                    <div>

                                        <span>Email</span>

                                        <strong id="user-modal-email">
                                            
                                        </strong>

                                    </div>

                                </div>


                            </div>

                        </div>


                    </div>



                    <!-- ================= FOOTER ================= -->

                    <div class="user-modal-footer">

                        <button
                            type="button"
                            class="user-modal-close-btn"
                            onclick="closeUserModal()"
                        >
                            Close
                        </button>

                    </div>


                </div>

            </div>
        </section>

    </main>
    <script src="../includes/admin.js"></script>
</body>

</html>