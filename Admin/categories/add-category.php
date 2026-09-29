<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

$adminPage = 'categories';
$adminBase = '../';

require_once "../../includes/database.php";


// ============================================================
// FORM VALUES
// ============================================================

$category_name = "";
$category_description = "";
$category_status = "active";


// ============================================================
// FORM SUBMISSION
// ============================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category_name = trim($_POST["category_name"] ?? "");
    $category_description = trim($_POST["category_description"] ?? "");
    $category_status = $_POST["category_status"] ?? "active";


    // ========================================================
    // BASIC VALIDATION
    // ========================================================

    if ($category_name === "") {

        $error_message = "Category name is required.";

    } elseif (
        $category_status !== "active" &&
        $category_status !== "inactive"
    ) {

        $error_message = "Invalid category status.";

    } else {

        // ====================================================
        // GENERATE SLUG
        // ====================================================

        $slug = strtolower($category_name);

        // Replace & with "and"
        $slug = str_replace("&", "and", $slug);

        // Replace anything except letters and numbers with -
        $slug = preg_replace("/[^a-z0-9]+/", "-", $slug);

        // Remove - from beginning and end
        $slug = trim($slug, "-");


        // ====================================================
        // CHECK SLUG
        // ====================================================

        if ($slug === "") {

            $error_message = "Invalid category name.";

        } else {

            // ================================================
            // CHECK DUPLICATE CATEGORY
            // ================================================

            $checkSql = "
                SELECT category_id
                FROM categories
                WHERE category_name = ?
                    OR slug = ?
                LIMIT 1
            ";

            $checkStmt = mysqli_prepare($conn, $checkSql);

            if (!$checkStmt) {

                $error_message =
                    "Category check failed: " . mysqli_error($conn);

            } else {

                mysqli_stmt_bind_param(
                    $checkStmt,
                    "ss",
                    $category_name,
                    $slug
                );

                mysqli_stmt_execute($checkStmt);

                $checkResult = mysqli_stmt_get_result($checkStmt);

                $categoryExists = mysqli_num_rows($checkResult) > 0;

                mysqli_stmt_close($checkStmt);


                if ($categoryExists) {

                    $error_message =
                        "A category with this name already exists.";

                } else {

                    // ============================================
                    // INSERT CATEGORY
                    // ============================================

                    $insertSql = "
                        INSERT INTO categories
                        (
                            category_name,
                            slug,
                            description,
                            status
                        )
                        VALUES (?, ?, ?, ?)
                    ";

                    $insertStmt = mysqli_prepare(
                        $conn,
                        $insertSql
                    );


                    if (!$insertStmt) {

                        $error_message =
                            "Category insert failed: "
                            . mysqli_error($conn);

                    } else {

                        mysqli_stmt_bind_param(
                            $insertStmt,
                            "ssss",
                            $category_name,
                            $slug,
                            $category_description,
                            $category_status
                        );


                        if (mysqli_stmt_execute($insertStmt)) {

                            mysqli_stmt_close($insertStmt);

                            header(
                                "Location: add-category.php?success=1"
                            );

                            exit;

                        } else {

                            $error_message =
                                "Category could not be added: "
                                . mysqli_stmt_error($insertStmt);

                            mysqli_stmt_close($insertStmt);
                        }
                    }
                }
            }
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

    <title>TechVolt Admin - Add Category</title>

    <link
        rel="stylesheet"
        href="../includes/admin.css"
    >

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>

    <?php include '../includes/sidebar.php'; ?>

    <?php include '../includes/header.php'; ?>


    <main class="admin-main">

        <section class="add-category-page">


            <!-- ================= PAGE HEADER ================= -->

            <div class="add-category-page-header">

                <div>

                    <h2>
                        Add Category
                    </h2>

                    <p>
                        Create a new product category for your TechVolt store.
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
                method="POST"
            >


                <!-- ================= BASIC INFORMATION ================= -->

                <div class="category-form-panel">

                    <div class="category-form-panel-header">

                        <h3>
                            Category Information
                        </h3>

                        <p>
                            Enter the basic details of the new category.
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
                                placeholder="Enter category name"
                                value="<?= htmlspecialchars($category_name); ?>"
                                required
                            >

                        </div>


                        <!-- CATEGORY SLUG -->

                        <div class="form-group full-width">

                            <label for="category-slug">

                                Category Slug

                            </label>


                            <input
                                type="text"
                                id="category-slug"
                                name="category_slug_preview"
                                placeholder="Slug will be generated automatically"
                                value=""
                                readonly
                            >


                            <small>
                                Slug is generated automatically from the category name.
                            </small>

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
                            ><?= htmlspecialchars($category_description); ?></textarea>

                        </div>


                    </div>

                </div>


                <!-- ================= CATEGORY STATUS ================= -->

                <div class="category-form-panel">

                    <div class="category-form-panel-header">

                        <h3>
                            Category Status
                        </h3>

                        <p>
                            Choose whether this category should be active.
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
                                    <?= $category_status === "active" ? "checked" : ""; ?>
                                >

                                <span class="status-radio"></span>


                                <span class="status-option-content">

                                    <strong>
                                        Active
                                    </strong>

                                    <small>
                                        Category will be visible in the store.
                                    </small>

                                </span>

                            </label>


                            <!-- INACTIVE -->

                            <label class="category-status-option">

                                <input
                                    type="radio"
                                    name="category_status"
                                    value="inactive"
                                    <?= $category_status === "inactive" ? "checked" : ""; ?>
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

                        Save Category

                    </button>


                </div>


            </form>

        </section>

    </main>


    <!-- ================= SLUG GENERATOR ================= -->

    <script>

        const categoryNameInput =
            document.getElementById("category-name");

        const categorySlugInput =
            document.getElementById("category-slug");


        function generateSlug(value) {

            return value
                .toLowerCase()
                .replace(/&/g, "and")
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/^-+|-+$/g, "");

        }


        categoryNameInput.addEventListener(
            "input",
            function () {

                categorySlugInput.value =
                    generateSlug(this.value);

            }
        );

    </script>


    <!-- ================= SWEETALERT ================= -->

    <?php if (isset($_GET["success"]) && $_GET["success"] === "1"): ?>

        <script>

            Swal.fire({

                icon: "success",

                title: "Category Added!",

                text: "The category has been added successfully.",

                background: "#111827",

                color: "#ffffff",

                confirmButtonText: "OK"

            }).then(() => {

                window.location.href =
                    "manage-category.php";

            });

        </script>

    <?php endif; ?>


    <?php if (isset($error_message)): ?>

        <script>

            Swal.fire({

                icon: "error",

                title: "Unable to Add Category",

                text: <?= json_encode($error_message); ?>,

                background: "#111827",

                color: "#ffffff",

                confirmButtonText: "OK"

            });

        </script>

    <?php endif; ?>


</body>

</html>