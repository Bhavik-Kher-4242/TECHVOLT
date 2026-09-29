<?php

session_start();

require_once "../../includes/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

$adminPage = 'categories';
$adminBase = '../';

$category_id = (int) ($_GET["id"] ?? 0);

if ($category_id <= 0) {
    die("Invalid category ID.");
}

$success_message = "";
$error_message = "";


/* =========================================================
   FETCH CATEGORY
========================================================= */

$categorySql = "
    SELECT
        category_id,
        category_name,
        description,
        status,
        created_at
    FROM categories
    WHERE category_id = ?
";

$categoryStmt = mysqli_prepare(
    $conn,
    $categorySql
);

if (!$categoryStmt) {
    die(
        "Category query preparation failed: "
        . mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $categoryStmt,
    "i",
    $category_id
);

mysqli_stmt_execute(
    $categoryStmt
);

$categoryResult =
    mysqli_stmt_get_result(
        $categoryStmt
    );

$category =
    mysqli_fetch_assoc(
        $categoryResult
    );

mysqli_stmt_close(
    $categoryStmt
);

if (!$category) {
    die("Category not found.");
}


/* =========================================================
   PRODUCT COUNT
========================================================= */

$productCountSql = "
    SELECT COUNT(*) AS product_count
    FROM products
    WHERE category_id = ?
";

$productCountStmt = mysqli_prepare(
    $conn,
    $productCountSql
);

$productCount = 0;

if ($productCountStmt) {

    mysqli_stmt_bind_param(
        $productCountStmt,
        "i",
        $category_id
    );

    mysqli_stmt_execute(
        $productCountStmt
    );

    $productCountResult =
        mysqli_stmt_get_result(
            $productCountStmt
        );

    $productCountRow =
        mysqli_fetch_assoc(
            $productCountResult
        );

    $productCount =
        (int) (
            $productCountRow["product_count"]
            ?? 0
        );

    mysqli_stmt_close(
        $productCountStmt
    );
}


/* =========================================================
   DELETE CATEGORY
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_category"])
) {

    $postedCategoryId =
        (int) ($_POST["category_id"] ?? 0);

    if ($postedCategoryId !== $category_id) {

        $error_message =
            "Invalid category ID.";

    } elseif ($productCount > 0) {

        $error_message =
            "This category cannot be deleted because it contains "
            . $productCount
            . " "
            . ($productCount === 1 ? "product." : "products.");

    } else {

        $deleteSql = "
            DELETE FROM categories
            WHERE category_id = ?
        ";

        $deleteStmt = mysqli_prepare(
            $conn,
            $deleteSql
        );

        if (!$deleteStmt) {

            $error_message =
                "Category deletion preparation failed.";

        } else {

            mysqli_stmt_bind_param(
                $deleteStmt,
                "i",
                $category_id
            );

            if (
                mysqli_stmt_execute(
                    $deleteStmt
                )
            ) {

                $success_message =
                    "Category deleted successfully.";

            } else {

                $error_message =
                    "Failed to delete category: "
                    . mysqli_stmt_error($deleteStmt);
            }

            mysqli_stmt_close(
                $deleteStmt
            );
        }
    }
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

    <title>TechVolt Admin - Delete Category</title>

    <link
        rel="stylesheet"
        href="../includes/admin.css"
    >
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="delete-category-page">


            <!-- ================= PAGE HEADER ================= -->

            <div class="delete-category-page-header">

                <div>

                    <h2>Delete Category</h2>

                    <p>
                        Review the category before permanently deleting it.
                    </p>

                </div>


                <a
                    href="manage-category.php"
                    class="back-category-btn"
                >
                    ← Back to Categories
                </a>

            </div>



            <!-- ================= DELETE PANEL ================= -->

            <div class="delete-category-panel">


                <!-- ================= WARNING ================= -->

                <div class="delete-category-warning">

                    <div class="delete-category-warning-icon">
                        !
                    </div>

                    <div>

                        <h3>
                            Are you sure you want to delete this category?
                        </h3>

                        <p>
                            This action will permanently remove the category
                            from your TechVolt store.
                        </p>

                    </div>

                </div>



                <!-- ================= CATEGORY INFORMATION ================= -->

                <div class="delete-category-content">


                    <!-- CATEGORY ICON -->

                    <div class="delete-category-icon">
                        ▦
                    </div>



                    <!-- CATEGORY DETAILS -->

                    <div class="delete-category-details">

                        <span class="delete-category-label">
                            Category
                        </span>

                        <h3>
                            <?= htmlspecialchars($category["category_name"]); ?>
                        </h3>


                        <p class="delete-category-description">
                            <?= htmlspecialchars($category["description"] ?? ""); ?>
                        </p>


                        <div class="delete-category-info-grid">


                            <div>

                                <span>
                                    Category ID
                                </span>

                                <strong>
                                    <?= "#" . str_pad( $category["category_id"], 3, "0", STR_PAD_LEFT ); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Products
                                </span>

                                <strong>
                                    <?= $productCount; ?>
                                    <?= $productCount === 1 ? "Product" : "Products"; ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Status
                                </span>

                                <strong class="category-active-status">
                                    <?= ucfirst($category["status"]); ?>
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Created
                                </span>

                                <strong>
                                    <?= date( "d M Y", strtotime($category["created_at"]) ); ?>
                                </strong>

                            </div>


                        </div>

                    </div>

                </div>



                <!-- ================= PRODUCT WARNING ================= -->

                <div class="delete-category-product-warning">

                    <strong>
                        Important:
                    </strong>

                    <span>
                        <?php if ($productCount > 0): ?>

                            This category currently contains
                            <?= $productCount; ?>
                            <?= $productCount === 1 ? "product" : "products"; ?>.
                            Make sure the products are reassigned to another
                            category before deleting it.

                        <?php else: ?>

                            This category currently contains no products
                            and can be safely deleted.

                        <?php endif; ?>
                    </span>

                </div>



                <!-- ================= FINAL NOTE ================= -->

                <div class="delete-category-note">

                    <strong>
                        This action cannot be undone.
                    </strong>

                    <span>
                        Please verify that you have selected the correct
                        category before continuing.
                    </span>

                </div>



                <!-- ================= ACTIONS ================= -->

                <div class="delete-category-actions">

                    <a
                        href="manage-category.php"
                        class="cancel-category-delete-btn"
                    >
                        Cancel
                    </a>


                    <form action="" method="POST" id="deleteCategoryForm">

    <input
        type="hidden"
        name="category_id"
        value="<?= (int) $category["category_id"]; ?>"
    >

    <input
        type="hidden"
        name="delete_category"
        value="1"
    >

    <button
        type="submit"
        class="confirm-category-delete-btn"
        <?= $productCount > 0 ? "disabled" : ""; ?>
    >
        Delete Category
    </button>

</form>

                </div>


            </div>

        </section>

    </main>

    <?php if ($error_message !== ""): ?>

        <script>
            Swal.fire({
                icon: "error",
                title: "Cannot Delete Category",
                text: <?= json_encode($error_message); ?>,
                confirmButtonText: "OK"
            });
        </script>

    <?php endif; ?>


    <?php if ($success_message !== ""): ?>

        <script>
            Swal.fire({
                icon: "success",
                title: "Category Deleted",
                text: <?= json_encode($success_message); ?>,
                confirmButtonText: "OK"
            }).then(function () {
                window.location.href = "manage-category.php";
            });
        </script>

    <?php endif; ?>
    <script>
        document.getElementById("deleteCategoryForm").addEventListener("submit", function(event) {

            event.preventDefault();

            Swal.fire({
                title: "Delete Category?",
                text: "This category will be permanently deleted.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Delete",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then(function(result) {

                if (result.isConfirmed) {
                    document.getElementById("deleteCategoryForm").submit();
                }

            });

        });
    </script>
</body>

</html>