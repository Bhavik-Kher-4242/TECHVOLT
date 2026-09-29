<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../admin-login.php");
    exit;
}

require_once "../../includes/database.php";

$adminPage = "contact";
$adminBase = "../";


/* =========================================================
   FETCH CONTACT MESSAGES
========================================================= */

$sql = " SELECT * FROM contact_messages ORDER BY created_at DESC ";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to fetch contact messages: " . mysqli_error($conn));
}

$messageCount = mysqli_num_rows($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Contact Messages - TechVolt Admin</title>
    <link rel="stylesheet" href="../includes/admin.css">

</head>


<body>


    <!-- =====================================================
         SIDEBAR
    ===================================================== -->

    <?php include "../includes/sidebar.php"; ?>
    <?php include "../includes/header.php"; ?>



    <!-- =====================================================
         MAIN ADMIN AREA
    ===================================================== -->

    <main class="admin-main">
        <!-- =================================================
             CONTACT PAGE
        ================================================= -->

        <section class="contact-page">


            <!-- PAGE HEADER -->

            <div class="contact-page-header">


                <div class="contact-page-title">

                    <h2>
                        Contact Messages
                    </h2>

                    <p>
                        View messages submitted by TechVolt customers.
                    </p>

                </div>


                <div class="contact-count">

                    <div class="contact-count-icon">
                        ✉
                    </div>


                    <div class="contact-count-info">

                        <span>
                            Total Messages
                        </span>

                        <strong>
                            <?= $messageCount; ?>
                        </strong>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 MESSAGE PANEL
            ================================================== -->

            <div class="contact-panel">


                <div class="contact-panel-header">

                    <div>

                        <h3>
                            Customer Messages
                        </h3>

                        <p>
                            Messages received through the Contact Us page.
                        </p>

                    </div>

                </div>


                <!-- TABLE -->

                <div class="contact-table-container">


                    <table class="contact-table">


                        <thead>

                            <tr>

                                <th>Customer ID</th>

                                <th>Name</th>

                                <th>Email</th>

                                <th>Subject</th>

                                <th>Message</th>

                                <th>Action</th>

                                <th>Date</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if ($messageCount > 0): ?>


                            <?php while (
                                $row =
                                mysqli_fetch_assoc($result)
                            ): ?>


                                <tr>


                                    <!-- ID -->

                                    <td>

                                        <span class="contact-id">

                                            #CUS<?= str_pad(
                                                        $row["user_id"],
                                                        4,
                                                        "0",
                                                        STR_PAD_LEFT
                                                    ); ?>

                                        </span>

                                    </td>


                                    <!-- NAME -->

                                    <td>

                                        <div class="contact-name">

                                            <?= htmlspecialchars(
                                                $row["name"]
                                            ); ?>

                                        </div>

                                    </td>


                                    <!-- EMAIL -->

                                    <td>

                                        <div class="contact-email">

                                            <?= htmlspecialchars(
                                                $row["email"]
                                            ); ?>

                                        </div>

                                    </td>


                                    <!-- SUBJECT -->

                                    <td>

                                        <div class="contact-subject">

                                            <?= htmlspecialchars(
                                                $row["subject"]
                                            ); ?>

                                        </div>

                                    </td>


                                    <!-- MESSAGE PREVIEW -->

                                    <td>

                                        <div class="contact-preview">

                                            <?= htmlspecialchars(
                                                $row["message"]
                                            ); ?>

                                        </div>

                                    </td>


                                    <!-- VIEW BUTTON -->

                                    <td>

                                        <button
                                            type="button"
                                            class="view-message-btn"
                                            onclick="openMessageModal(this)"
                                            data-name="<?= htmlspecialchars(
                                                $row["name"],
                                                ENT_QUOTES
                                            ); ?>"
                                            data-email="<?= htmlspecialchars(
                                                $row["email"],
                                                ENT_QUOTES
                                            ); ?>"
                                            data-subject="<?= htmlspecialchars(
                                                $row["subject"],
                                                ENT_QUOTES
                                            ); ?>"
                                            data-message="<?= htmlspecialchars(
                                                $row["message"],
                                                ENT_QUOTES
                                            ); ?>"
                                            data-date="<?= date(
                                                "d M Y, h:i A",
                                                strtotime(
                                                    $row["created_at"]
                                                )
                                            ); ?>"
                                        >

                                            View Message

                                        </button>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <div class="contact-date">

                                            <?= date(
                                                "d M Y, h:i A",
                                                strtotime(
                                                    $row["created_at"]
                                                )
                                            ); ?>

                                        </div>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="7"
                                    class="contact-empty"
                                >

                                    ✉

                                    <br>

                                    No contact messages found.

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </div>


        </section>


    </main>


    <!-- =====================================================
         MESSAGE MODAL
    ===================================================== -->

    <div
        class="message-modal"
        id="messageModal"
    >


        <div class="message-modal-box">


            <!-- MODAL HEADER -->

            <div class="message-modal-header">


                <div class="message-modal-title">

                    Contact Message

                </div>


                <button
                    type="button"
                    class="message-modal-close"
                    onclick="closeMessageModal()"
                >

                    ×

                </button>


            </div>


            <!-- MODAL BODY -->

            <div class="message-modal-body">


                <div class="message-info-grid">


                    <!-- NAME -->

                    <div class="message-info-item">

                        <span class="message-info-label">
                            Name
                        </span>

                        <span
                            class="message-info-value"
                            id="modalName"
                        ></span>

                    </div>


                    <!-- EMAIL -->

                    <div class="message-info-item">

                        <span class="message-info-label">
                            Email
                        </span>

                        <span
                            class="message-info-value"
                            id="modalEmail"
                        ></span>

                    </div>


                    <!-- SUBJECT -->

                    <div class="message-info-item">

                        <span class="message-info-label">
                            Subject
                        </span>

                        <span
                            class="message-info-value"
                            id="modalSubject"
                        ></span>

                    </div>


                    <!-- DATE -->

                    <div class="message-info-item">

                        <span class="message-info-label">
                            Date
                        </span>

                        <span
                            class="message-info-value"
                            id="modalDate"
                        ></span>

                    </div>


                </div>


                <!-- FULL MESSAGE -->

                <div class="full-message-title">

                    Message

                </div>


                <div
                    class="full-message-content"
                    id="modalMessage"
                ></div>


            </div>


            <!-- MODAL FOOTER -->

            <div class="message-modal-footer">


                <button
                    type="button"
                    class="modal-close-btn"
                    onclick="closeMessageModal()"
                >

                    Close

                </button>


            </div>


        </div>


    </div>


    <!-- =====================================================
         JAVASCRIPT
    ===================================================== -->

    <script>

        function openMessageModal(button) {

            const modal =
                document.getElementById("messageModal");


            const name =
                button.dataset.name;

            const email =
                button.dataset.email;

            const subject =
                button.dataset.subject;

            const message =
                button.dataset.message;

            const date =
                button.dataset.date;


            document.getElementById("modalName")
                .textContent = name;


            document.getElementById("modalEmail")
                .textContent = email;


            document.getElementById("modalSubject")
                .textContent = subject;


            document.getElementById("modalDate")
                .textContent = date;


            document.getElementById("modalMessage")
                .textContent = message;


            modal.classList.add("show");


            document.body.style.overflow = "hidden";

        }


        function closeMessageModal() {

            const modal =
                document.getElementById("messageModal");


            modal.classList.remove("show");


            document.body.style.overflow = "";

        }


        /* Close when clicking outside modal */

        document.getElementById("messageModal")
            .addEventListener(
                "click",
                function(event) {

                    if (event.target === this) {

                        closeMessageModal();

                    }

                }
            );


        /* Close with ESC key */

        document.addEventListener(
            "keydown",
            function(event) {

                if (event.key === "Escape") {

                    closeMessageModal();

                }

            }
        );

    </script>


</body>

</html>