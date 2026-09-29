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
        slug,
        description,
        status,
        created_at
    FROM categories
    WHERE category_id = ?
";

$categoryStmt = mysqli_prepare($conn, $categorySql);

if (!$categoryStmt) {
    die("Category query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $categoryStmt,
    "i",
    $category_id
);

mysqli_stmt_execute($categoryStmt);

$categoryResult = mysqli_stmt_get_result($categoryStmt);

$category = mysqli_fetch_assoc($categoryResult);

mysqli_stmt_close($categoryStmt);

if (!$category) {
    die("Category not found.");
}


/* =========================================================
   UPDATE CATEGORY
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category_id = (int) ($_POST["category_id"] ?? 0);

    $category_name = trim(
        $_POST["category_name"] ?? ""
    );

    $category_description = trim(
        $_POST["category_description"] ?? ""
    );

    $category_status = $_POST["category_status"] ?? "";


    /* ================= VALIDATION ================= */

    if ($category_id <= 0) {

        $error_message = "Invalid category ID.";

    } elseif ($category_name === "") {

        $error_message = "Category name is required.";

    } elseif (
        $category_status !== "active" &&
        $category_status !== "inactive"
    ) {

        $error_message = "Invalid category status.";

    } else {


        /* =================================================
           CHECK DUPLICATE CATEGORY NAME
        ================================================= */

        $duplicateSql = "
            SELECT category_id
            FROM categories
            WHERE category_name = ?
            AND category_id != ?
        ";

        $duplicateStmt = mysqli_prepare(
            $conn,
            $duplicateSql
        );

        if (!$duplicateStmt) {

            $error_message =
                "Duplicate category check failed.";

        } else {

            mysqli_stmt_bind_param(
                $duplicateStmt,
                "si",
                $category_name,
                $category_id
            );

            mysqli_stmt_execute(
                $duplicateStmt
            );

            $duplicateResult =
                mysqli_stmt_get_result(
                    $duplicateStmt
                );

            if (
                mysqli_num_rows(
                    $duplicateResult
                ) > 0
            ) {

                $error_message =
                    "A category with this name already exists.";
            }

            mysqli_stmt_close(
                $duplicateStmt
            );
        }


        /* =================================================
           UPDATE IF NO ERROR
        ================================================= */

        if ($error_message === "") {


            /* ================= GENERATE SLUG ================= */

            $slug = strtolower($category_name);

            $slug = str_replace(
                "&",
                "and",
                $slug
            );

            $slug = preg_replace(
                "/[^a-z0-9]+/",
                "-",
                $slug
            );

            $slug = trim(
                $slug,
                "-"
            );


            /* ================= UPDATE ================= */

            $updateSql = "
                UPDATE categories
                SET
                    category_name = ?,
                    slug = ?,
                    description = ?,
                    status = ?
                WHERE category_id = ?
            ";

            $updateStmt = mysqli_prepare(
                $conn,
                $updateSql
            );

            if (!$updateStmt) {

                $error_message =
                    "Category update preparation failed.";

            } else {

                mysqli_stmt_bind_param(
                    $updateStmt,
                    "ssssi",
                    $category_name,
                    $slug,
                    $category_description,
                    $category_status,
                    $category_id
                );

                if (
                    mysqli_stmt_execute(
                        $updateStmt
                    )
                ) {

                    $success_message =
                        "Category updated successfully.";

                    /*
                     * Refresh displayed category data
                     */
                    $category["category_name"] =
                        $category_name;

                    $category["slug"] =
                        $slug;

                    $category["description"] =
                        $category_description;

                    $category["status"] =
                        $category_status;

                } else {

                    $error_message =
                        "Failed to update category: "
                        . mysqli_stmt_error($updateStmt);
                }

                mysqli_stmt_close(
                    $updateStmt
                );
            }
        }
    }
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
        (int) ($productCountRow["product_count"] ?? 0);

    mysqli_stmt_close(
        $productCountStmt
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

    <title>TechVolt Admin - Edit Category</title>

    <link
        rel="stylesheet"
        href="../includes/admin.css"
    >

</head>


<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="add-category-page">


            <!-- ================= PAGE HEADER ================= -->

            <div class="add-category-page-header">

                <div>

                    <h2>Edit Category</h2>

                    <p>
                        Update the details of an existing TechVolt category.
                    </p>

                </div>


                <a
                    href="manage-category.php"
                    class="back-category-btn"
                >
                    ← Back to Categories
                </a>

            </div>



            <!-- ================= CATEGORY FORM ================= -->

            <form
                class="category-form"
                action="#"
                method="POST"
            >

                <!-- Product/Category ID -->
                <input
                    type="hidden"
                    name="category_id"
                    value="<?= (int) $category["category_id"]; ?>"
                >


                <!-- ================= BASIC INFORMATION ================= -->

                <div class="category-form-panel">

                    <div class="category-form-panel-header">

                        <h3>Category Information</h3>

                        <p>
                            Update the basic details of this category.
                        </p>

                    </div>


                    <div class="category-form-body">


                        <!-- CATEGORY NAME -->

                        <div class="form-group full-width">

                            <label for="category-name">

                                Category Name

                                <span>*</span>

                            </label>

                            <input
                                type="text"
                                id="category-name"
                                name="category_name"
                                value="<?= htmlspecialchars($category["category_name"]); ?>"
                                placeholder="Enter category name"
                            >

                        </div>



                        <!-- CATEGORY DESCRIPTION -->

                        <div class="form-group full-width">

                            <label for="category-description">

                                Category Description

                            </label>

                            <textarea
                                id="category-description"
                                name="category_description"
                                rows="5"
                                placeholder="Enter category description"
                            ><?= htmlspecialchars($category["description"]); ?></textarea>

                        </div>


                    </div>

                </div>



                <!-- ================= CATEGORY STATUS ================= -->

                <div class="category-form-panel">

                    <div class="category-form-panel-header">

                        <h3>Category Status</h3>

                        <p>
                            Update whether this category is active.
                        </p>

                    </div>


                    <div class="category-form-body">


                        <div class="category-status-options">


                            <!-- ACTIVE -->

                            <label class="category-status-option active">

                                <input
                                    type="radio"
                                    name="category_status"
                                    value="active"
                                    <?= ($category["status"] == "active")? "checked" : "" ?>
                                >

                                <span class="status-radio"></span>

                                <span class="status-option-content">

                                    <strong>
                                        Active
                                    </strong>

                                    <small>
                                        Category is visible in the store.
                                    </small>

                                </span>

                            </label>



                            <!-- INACTIVE -->

                            <label class="category-status-option">

                                <input
                                    type="radio"
                                    name="category_status"
                                    value="inactive"
                                    <?= ($category["status"] == "inactive")? "checked" : "" ?>
                                >

                                <span class="status-radio"></span>

                                <span class="status-option-content">

                                    <strong>
                                        Inactive
                                    </strong>

                                    <small>
                                        Category will remain hidden from the store.
                                    </small>

                                </span>

                            </label>


                        </div>


                    </div>

                </div>



                <!-- ================= CATEGORY SUMMARY ================= -->

                <div class="category-form-panel">

                    <div class="category-form-panel-header">

                        <h3>Category Summary</h3>

                        <p>
                            Current information related to this category.
                        </p>

                    </div>


                    <div class="category-form-body">

                        <div class="category-summary-grid">


                            <div class="category-summary-item">

                                <span>
                                    Category ID
                                </span>

                                <strong>
                                    <?= "#" . str_pad($category["category_id"], 3, "0", STR_PAD_LEFT); ?>
                                </strong>

                            </div>


                            <div class="category-summary-item">

                                <span>
                                    Products
                                </span>

                                <strong>
                                    <?= $productCount; ?>
                                    <?= $productCount == 1 ? "Product" : "Products"; ?>
                                </strong>

                            </div>


                            <div class="category-summary-item">

                                <span>
                                    Status
                                </span>

                                <strong class="category-summary-active">
                                    <?= ucfirst($category["status"]); ?>
                                </strong>

                            </div>


                            <div class="category-summary-item">
                                <span>
                                    Created At
                                </span>

                                <strong>
                                    <?= date("d M Y", strtotime($category["created_at"])); ?>
                                </strong>
                            </div>


                        </div>

                    </div>

                </div>



                <!-- ================= FORM ACTIONS ================= -->

                <div class="category-form-actions">

                    <a
                        href="manage-category.php"
                        class="cancel-category-btn"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="save-category-btn"
                    >
                        Update Category
                    </button>

                </div>


            </form>

        </section>

    </main>


</body>

</html>