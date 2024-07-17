<?php
require('db.php');
session_start();

$registered_username = '';
if (isset($_SESSION['registered_username'])) {
    $registered_username = $_SESSION['registered_username'];
    unset($_SESSION['registered_username']);
}

$error_message = '';

if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($con, $username);
    $password = stripslashes($_REQUEST['password']);
    
    $query = "SELECT * FROM `users` WHERE username='$username'";
    $result = mysqli_query($con, $query) or die(mysqli_error($con));
    $rows = mysqli_num_rows($result);
    if ($rows == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            echo "<script>window.onload = function() { if(typeof fetchHistory === 'function') { fetchHistory(); } }</script>";
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
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #BBE9FF;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.462);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo img {
            width: 120px;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 24px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
        }
        .forgot-password a {
            color: #1877f2;
            font-size: 14px;
            text-decoration: none;
        }
        .btn {
            background-color: #1877f2;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .divider span {
            padding: 0 10px;
            color: #666;
            font-size: 14px;
        }
        .social-login {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            margin: 0 5px;
        }
        .social-btn img {
            width: 20px;
            margin-right: 10px;
        }
        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #000000;
        }
        .signup-link a {
            color: #1877f2;
            text-decoration: none;
            font-weight: 500;
        }
        .error-message {
            color: #d93025;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome To Typing Tutor</h1>
        <form method="post" name="login">
            <?php if ($error_message): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Email or username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($registered_username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="forgot-password">
                <a href="forgot_password.php">Forgot password?</a>
            </div>
            <button type="submit" class="btn">Sign in</button>
        </form>
        <div class="divider">
            <span>Or</span>
        </div>
        <div class="social-login">
        </div>
        <div class="signup-link">
            Don't have an account? <a href="registration.php">Create an account</a>
        </div>
    </div>
</body>
</html>