<?php
require('db.php');
require('send_email.php');
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';
$success_message = '';

if (isset($_POST['email']) && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        $stmt = $con->prepare("SELECT * FROM `users` WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Changed to 1 hour expiration
            
            $stmt = $con->prepare("INSERT INTO password_reset_tokens (email, token, expires) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expires);
            $stmt->execute();
            
            $reset_link = "http://localhost/login2/reset_password.php?email=" . urlencode($email) . "&token=" . $token;
            $message = "Click the following link to reset your password: " . $reset_link . "\n\nThis link will expire in 1 hour.";
            
            if (send_email($email, "Password Reset", $message)) {
                $success_message = "A password reset link has been sent to your email. It will expire in 1 hour.";
            } else {
                $error_message = "Failed to send reset email. Please try again.";
                error_log("Failed to send email to $email");
            }
        } else {
            $error_message = "No account found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    :root {
        --primary-color: #3498db;
        --secondary-color: #2980b9;
        --background-color: #ecf0f1;
        --text-color: #34495e;
        --error-color: #e74c3c;
        --success-color: #2ecc71;
    }

    body {
        font-family: 'Roboto', sans-serif;
        background-color: #BBE9FF;
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        color: var(--text-color);
        padding: 20px;
    }

    .container {
        width: 100%;
        max-width: 400px;
    }

    .form {
        background-color: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(5px);
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.582);
        width: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-title {
        font-size: 24px;
        color: #333;
        text-align: center;
        margin-bottom: 24px;
    }

    .input-group {
        margin-bottom: 20px;
    }

    .login-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .login-input:focus {
        outline: none;
        border-color: #1877f2;
    }

    .login-button {
        width: 100%;
        padding: 12px;
        background-color: #1877f2;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .login-button:hover {
        background-color: #166fe5;
    }

    .link {
        text-align: center;
        margin-top: 20px;
    }

    .link a {
        color: #1877f2;
        text-decoration: none;
        transition: color 0.3s;
    }

    .link a:hover {
        color: #166fe5;
    }

    .error-message,
    .success-message {
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
    }

    .error-message {
        background-color: #ffebee;
        color: #c62828;
    }

    .success-message {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    @media (max-width: 480px) {
        .form {
            padding: 30px 20px;
        }

        .login-title {
            font-size: 22px;
        }

        .login-input,
        .login-button {
            font-size: 14px;
        }
    }

    @media (max-width: 320px) {
        .form {
            padding: 20px 15px;
        }

        .login-title {
            font-size: 20px;
        }

        .login-input,
        .login-button {
            font-size: 13px;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <form class="form" method="post">
            <h1 class="login-title">Forgot Password</h1>
            <?php 
            if ($error_message) echo "<p class='error-message'>$error_message</p>";
            if ($success_message) echo "<p class='success-message'>$success_message</p>";
            ?>
            <div class="input-group">
                <input type="email" class="login-input" name="email" placeholder="Email Address" required autofocus>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="input-group">
                <input type="submit" value="Send Reset Link" name="submit" class="login-button">
            </div>
            <p class="link"><a href="login.php">Back to Login</a></p>
        </form>
    </div>
</body>
</html>