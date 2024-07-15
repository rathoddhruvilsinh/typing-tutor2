<?php
require('db.php');
session_start();

$registered_username = '';
if (isset($_SESSION['registered_username'])) {
    $registered_username = $_SESSION['registered_username'];
    unset($_SESSION['registered_username']); // Clear the session variable after use
}

$error_message = '';

// When form submitted, check and create user session.
if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($con, $username);
    $password = stripslashes($_REQUEST['password']);
    
    // Check user is exist in the database
    $query = "SELECT * FROM `users` WHERE username='$username'";
    $result = mysqli_query($con, $query) or die(mysqli_error($con));
    $rows = mysqli_num_rows($result);
    if ($rows == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            // Fetch history immediately after login
            echo "<script>window.onload = function() { if(typeof fetchHistory === 'function') { fetchHistory(); } }</script>";
            // Redirect to user dashboard page
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Incorrect Username/password.";
        }
    } else {
        $error_message = "Incorrect Username/password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url(bg1.jpeg);
            background-size: 1366px 768px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form {
            background-color: transparent;
            backdrop-filter: blur(2px);
            padding: 40px;
            border-radius: 9px;
            box-shadow: 0 1px 5px rgb(255, 255, 255);
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            font-size: 24px;
            color: #ffffff;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            margin-left: -12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .login-button {
            width: 107%;
            padding: 12px;
            margin-left: -12px;
            background-color: #1876f2;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-button:hover {
            background-color: #166fe5;
        }

        .link {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #ffffff;
        }

        .link a {
            color: #1877f2;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #d93025;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }

        .password-container {
            position: relative;
        }
        .password-container .fa-eye, .password-container .fa-eye-slash {
            position: absolute;
            top: 38%;
            right: 7px;
            transform: translateY(-50%);
            cursor: pointer;
        }          
    </style>
</head>
<body>
<form class="form" method="post" name="login">
    <h1 class="login-title">Login</h1>
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <input type="text" class="login-input" name="username" placeholder="Username" autofocus="true" value="<?php echo htmlspecialchars($registered_username); ?>"/>
    <div class="password-container">
        <input type="password" class="login-input" name="password" placeholder="Password" id="password"/>
        <i class="fas fa-eye" id="togglePassword"></i>
    </div>
    
    <input type="submit" value="Login" name="submit" class="login-button"/>
    
    <p class="link"> <a href="forgot_password.php">Forgot your password?</a></p>
    <p class="link">Don't have an account? <a href="registration.php">Sign up Now</a></p>
</form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            togglePassword.addEventListener('click', function (e) {
                // toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                // toggle the eye / eye slash icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>