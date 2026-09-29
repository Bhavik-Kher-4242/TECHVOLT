<?php
require_once "includes/database.php";

$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullName = trim($_POST["fullName"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["ConfirmPassword"];

    // Empty fields
    if (
        empty($fullName) ||
        empty($username) ||
        empty($email) ||
        empty($password) ||
        empty($confirmPassword)
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
    }

    // Invalid email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $alert = "
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Email',
            text: 'Please enter a valid email address.',
            timer: 2000,
            theme: 'dark'
        });
        ";
    }

    // Password mismatch
    elseif ($password !== $confirmPassword) {

        $alert = "
        Swal.fire({
            icon: 'error',
            title: 'Passwords Do Not Match',
            text: 'Please make sure both passwords are the same.',
            timer: 2000,
            theme: 'dark'
        });
        ";
    }

    else {

        // Check email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $alert = "
            Swal.fire({
                icon: 'error',
                title: 'Email Already Registered',
                text: 'An account with this email already exists. Please use another email.',
                timer: 2000,
                theme: 'dark'
            });
            ";

        } else {

            // Hash password
            $password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users 
                    (full_name, username, email, password)
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $fullName,
                $username,
                $email,
                $password
            );

            if (mysqli_stmt_execute($stmt)) {

                $alert = "
                Swal.fire({
                    icon: 'success',
                    title: 'Account Created!',
                    text: 'Your TechVolt account has been created successfully.',
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
                    title: 'Registration Failed',
                    text: 'We could not create your account. Please try again.',
                    theme: 'dark'
                });
                ";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - TechVolt</title>

    <link rel="stylesheet" href="assets\css\login.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="form-container register-container">

        <h2>Create an Account</h2>

        <form action="register.php" method="post">

            <div class="input-box">
                <input id="fullName" name="fullName" type="text" placeholder="">
                <label for="fullName">Full Name</label>
            </div>

            <div class="input-box">
                <input id="username" name="username" type="text" placeholder="">
                <label for="username">Username</label>
            </div>

            <div class="input-box">
                <input id="email" name="email" type="email" placeholder="" >
                <label for="email" class="email">E-mail</label>
            </div>

            <div class="input-box">
                <input id="password" name="password" type="password" placeholder="">
                <label for="password">Password</label>
            </div>

            <div class="input-box">
                <input id="ConfirmPassword" name="ConfirmPassword" type="password" placeholder="" >
                <label for="ConfirmPassword" class="confirm-password">Confirm Password</label>
            </div>

            <button type="submit" class="submit-btn">
                Create Account
            </button>

            <div class="extra-link">
                <p>Already have an account?</p>
                <a class="register" href="login.php">
                    Login &#10132;
                </a>
            </div>

        </form>

    </div>

    <section>

        <img src="assets/images/logo 3.png" alt="Logo">

        <h3>Build Something Amazing. Start Your Journey.</h3>

        <p>
            Create your TechVolt account and explore the world of
            electronic components, development boards, sensors,
            modules, and more.
        </p>

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