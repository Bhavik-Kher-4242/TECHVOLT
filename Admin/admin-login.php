<?php

    session_start();
    require_once "../includes/database.php";
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
        }
        else{
            $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'admin' AND status = 'active'";
            $stmt = mysqli_prepare($conn, $sql);
            if(!$stmt){
                die("Prepare failed: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "ss", $login, $login);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $admin = mysqli_fetch_assoc($result);
                if (password_verify($password, $admin["password"])) {
                    $alert = "
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful.',
                        text: 'You have logged in to your TechVolt account successfully.',
                        timer: 2000,
                        showConfirmButton: false,
                        theme: 'dark'
                    }).then(() => {
                        window.location.href = 'dashboard.php';
                    });
                    ";

                    session_regenerate_id(true);

                    $_SESSION["admin_id"] = $admin["user_id"];
                }
                else{
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
            }
            else {
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
    <link rel="stylesheet" href="../assets/css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="form-container">
        <h2>Admin Portal</h2>
        <form action="admin-login.php" method="post">
            <div class="input-box">

                <input id="username" name="login" type="text" placeholder="">
                <label for="username">Username</label>
            </div>
            <div class="input-box">
                <input id="password" name="password" type="password" placeholder="">
                <label for="password">Password</label>
            </div>
            
            <button type="submit" class="submit-btn">Login</button>
        </form>
    </div>
    <section>
        <img src="..\assets\images\logo 3.png" alt="Logo">
        <h3>Powering Your Store Behind the Scenes.</h3>
        <p>Manage your products, inventory, orders, and customers with the TechVolt Administration Portal.</p>
        <h4>Secure Admin Access</h4>
    </section>
    <h4 class="down-title">TechVolt Administration System</h4>
    <?php if (!empty($alert)) : ?>

        <script>
            window.addEventListener('DOMContentLoaded', function(){
                <?php echo $alert; ?>
            })
        </script>
        
    <?php endif;  ?>
</body>

</html>