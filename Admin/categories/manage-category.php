<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

$adminPage = 'categories';
$adminBase = '../';

require_once "../../includes/database.php";

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");


/*
|--------------------------------------------------------------------------
| Fetch Categories
|--------------------------------------------------------------------------
|
| Product count is calculated from products table.
| LEFT JOIN is used so even a category with 0 products is displayed.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        c.category_id,
        c.category_name,
        c.slug,
        c.description,
        c.status,
        c.created_at,
        COUNT(p.product_id) AS product_count
    FROM categories c
    LEFT JOIN products p
        ON c.category_id = p.category_id
";

if ($search !== "") {

    $sql .= "
        WHERE
            c.category_name LIKE ?
            OR c.description LIKE ?
            OR c.slug LIKE ?
    ";
}

$sql .= "
    GROUP BY
        c.category_id,
        c.category_name,
        c.slug,
        c.description,
        c.status,
        c.created_at
    ORDER BY c.category_id ASC
";


/*
|--------------------------------------------------------------------------
| Prepare Query
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Category query failed: " . mysqli_error($conn));
}


/*
|--------------------------------------------------------------------------
| Bind Search
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $searchTerm = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );
}


/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TechVolt Admin - Categories</title>

    <link rel="stylesheet" href="../includes/admin.css">

</head>


<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="category-management">


            <!-- =========================
                 PAGE HEADER
            ========================== -->

            <div class="category-page-header">

                <div>

                    <h2>
                        Categories
                    </h2>

                    <p>
                        Manage product categories and their products
                    </p>

                </div>


                <a
                    href="add-category.php"
                    class="add-category-btn"
                >

                    <span>+</span>

                    Add Category

                </a>

            </div>


            <!-- =========================
                 SEARCH TOOLBAR
            ========================== -->

            <div class="category-toolbar">

                <form
                    method="GET"
                    class="category-search"
                >

                    <span class="category-search-icon">
                        ⌕
                    </span>

                    <input
                        type="search"
                        id="category-search"
                        name="search"
                        placeholder="Search categories..."
                        value="<?= htmlspecialchars($search); ?>"
                    >

                </form>


                <button
                    type="button"
                    class="category-refresh-btn"
                    onclick="window.location.href='manage-category.php'"
                >

                    ↻ Refresh

                </button>

            </div>


            <!-- =========================
                 CATEGORY GRID
            ========================== -->

            <div class="category-grid">


                <?php if (mysqli_num_rows($result) > 0): ?>


                    <?php while ($category = mysqli_fetch_assoc($result)): ?>


                        <div class="category-card">


                            <!-- CATEGORY TOP -->

                            <div class="category-card-top">

                                <div class="category-icon">
                                    ⚙
                                </div>


                                <span
                                    class="category-status
                                    <?= strtolower($category["status"]) === "active"
                                        ? "active"
                                        : "inactive"; ?>"
                                >

                                    <?= htmlspecialchars($category["status"]); ?>

                                </span>

                            </div>


                            <!-- CATEGORY CONTENT -->

                            <div class="category-card-content">

                                <span class="category-id">

                                    CATEGORY #<?= str_pad(
                                        $category["category_id"],
                                        2,
                                        "0",
                                        STR_PAD_LEFT
                                    ); ?>

                                </span>


                                <h3>

                                    <?= htmlspecialchars(
                                        $category["category_name"]
                                    ); ?>

                                </h3>


                                <p>

                                    <?= htmlspecialchars(
                                        $category["description"] ?? "No description available."
                                    ); ?>

                                </p>

                            </div>


                            <!-- CATEGORY BOTTOM -->

                            <div class="category-card-bottom">


                                <div class="category-product-count">

                                    <strong>

                                        <?= (int) $category["product_count"]; ?>

                                    </strong>

                                    <span>

                                        Products

                                    </span>

                                </div>


                                <div class="category-actions">


                                    <a
                                        href="edit-category.php?id=<?= (int) $category["category_id"]; ?>"
                                        class="category-edit-btn"
                                    >

                                        Edit

                                    </a>


                                    <a
                                        
                                        class="category-delete-btn"
                                        href="delete-category.php?id=<?= (int) $category["category_id"]; ?>">

                                        Delete

                                    </a>


                                </div>

                            </div>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="category-empty">

                        <h3>
                            No Categories Found
                        </h3>

                        <p>
                            Try another search or add a new category.
                        </p>

                    </div>


                <?php endif; ?>


            </div>


        </section>

    </main>


    <?php

    mysqli_stmt_close($stmt);

    ?>

</body>

</html>