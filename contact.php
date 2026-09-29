<?php
    session_start();
    require_once "includes/database.php";
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $subject = trim($_POST["subject"]);
        $message = trim($_POST["message"]);
        $user_id = $_SESSION["user_id"] ?? NULL;
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $alert = "
                Swal.fire({
                    title: 'Incomplete Form',
                    text: 'Please fill all fields.',
                    icon: 'warning',
                    theme: 'dark',
                    confirmButtonColor: '#0042FE'
                });
            ";
        }

        elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $alert = "
                Swal.fire({
                    title: 'Invalid Email!',
                    text: 'Please enter a valid email address.',
                    icon: 'warning',
                    theme: 'dark',
                    confirmButtonColor: '#0042FE'
                });
            ";
        }
        else{
            $sql = "INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param( $stmt, "issss", $user_id, $name, $email, $subject, $message );
        if (mysqli_stmt_execute($stmt)) {
            $alert = "
                Swal.fire({
                    title: 'Message Sent!',
                    text: 'Your message has been sent successfully.',
                    icon: 'success',
                    theme: 'dark',
                    confirmButtonColor: '#0042FE'
                });
            ";
        }
        else {
            $alert = "
                Swal.fire({
                    title: 'Error',
                    text: 'Unable to send your message.',
                    icon: 'error',
                    theme: 'dark',
                    confirmButtonColor: '#0042FE'
                });
            ";
        }

        }
        

    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
	<link rel="stylesheet" href="assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include "includes/header.php";?>
    <main class="contact-us-page">
        <section class="contact-header-section">
            <h2>CONTACT TECHVOLT</h2>
            <h4>We're here to help.</h4>
            <p>Have a question? Send us a message and we'll get back to you as soon as possible.</p>
        </section>
        <section class="form-section">
            <h3>SEND US A MESSAGE</h3>
            <form action="" method="post" class="contact-form">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email">

                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject">

                <label for="message">Message:</label>
                <textarea
                    id="message"
                    class="contact-textarea-message"
                    name="message"
                    rows="6"
                    placeholder="Write your message here..."
                ></textarea>

                <button type="submit" class="contact-submit-btn">
                    Submit
                </button>
            </form>
        </section>
        <section class="faq-section">
            <h2>FREQUENTLY ASKED QUESTIONS</h2>
            <p>Have questions about TechVolt, products, orders, or shopping? Find helpful answers in our FAQ guide.</p>
            <button><a href="documents/TechVolt_FAQ.pdf" target="_blank">View FAQ Guide &#10132;</a></button>
        </section>
    </main>
    <?php if (!empty($alert)): ?>

        <script>
            window.addEventListener('DOMContentLoaded', function() {
                <?php echo $alert; ?>
            });
        </script>

    <?php endif; ?>
    <?php include "includes/footer.php" ?>
    <script src="assets/Js/main.js"></script>
</body>
</html>