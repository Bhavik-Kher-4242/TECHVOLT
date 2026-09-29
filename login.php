<?php
session_start();
require_once "includes/database.php";
$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]);
    $password = $_POST["password"];
    if (
        empty($login) ||
        empty($password)
    ) {
        $alert = "
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields.',
            timer: 2000,
            theme: 'dark'
        });
        ";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "ss", $login, $login);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user["password"])) {
                if ($user["status"] == "active") {

                    session_regenerate_id(true);

                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["username"] = $user["username"];
                    $alert = "
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful.',
                            text: 'You have logged in to your TechVolt account successfully.',
                            timer: 2000,
                            showConfirmButton: false,
                            theme: 'dark'
                        }).then(() => {
                            window.location.href = 'index.php';
                        });
                        ";
                } else {
                    $alert = "
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Unsuccessful.',
                            text: 'Your account is Suspended.',
                            timer: 2000,
                            showConfirmButton: false,
                            theme: 'dark'
                        }).then(() => {
                            window.location.href = 'index.php';
                        });
                        ";
                }
            } else {
                $alert = "
                    Swal.fire({
                        icon: 'error',
                        title: 'Incorrect Password',
                        text: 'Please make sure your password is correct.',
                        timer: 2000,
                        theme: 'dark'
                    });
                    ";
            }
        } else {
            $alert = "
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'We could not find an account with that username or email.',
                    theme: 'dark'
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
    <title>Login to TechVolt</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="form-container">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <div class="input-box">

                <input id="username" name="login" type="text" placeholder="">
                <label for="username" class="username-or-password">Username OR Email</label>
            </div>
            <div class="input-box">
                <input id="password" name="password" type="password" placeholder="">
                <label for="password">Password</label>
            </div>
            <button type="submit" class="submit-btn">Login</button>
            <div class="extra-link">
                <p>Don't have an account?</p>
                <a class="register" href="register.php">Create Account &#10132;</a>
                <br>
                <br>
                <a class="admin" href="Admin/admin-login.php">Admin Login &#10132;</a>
            </div>
    </div>
    </form>
    <section>
        <img src="assets/images/logo 3.png" alt="Logo">
        <h3>Power Your Ideas. Build Your Future.</h3>
        <p>Your one-stop destination for electronic components, development boards, sensors, modules, and more.</p>
    </section>
    <!-- PHP Alert -->
    <?php if (!empty($alert)): ?>

        <script>
            window.addEventListener('DOMContentLoaded', function() {
                <?php echo $alert; ?>
            });
        </script>

    <?php endif; ?>
</body>

</html>