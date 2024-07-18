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
    <title>Forgot Password - Typing Tutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --background-color: #ecf0f1;
            --text-color: #2c3e50;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
        }

        .container {
            background: linear-gradient(135deg, #ffffff, #f5f5f5);
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 24px;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 600;
        }

        input[type="email"] {
            width: 94%;
            padding: 10px;
            border: 2px solid var(--accent-color);
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="email"]:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
        }

        .btn {
            background-color: var(--accent-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-color);
        }

        .login-link a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--primary-color);
        }

        .error-message,
        .success-message {
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .error-message {
            color: var(--error-color);
            background-color: rgba(231, 76, 60, 0.1);
        }

        .success-message {
            color: var(--success-color);
            background-color: rgba(46, 204, 113, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        <form class="form" method="post">
            <?php 
            if ($error_message) echo "<p class='error-message'>$error_message</p>";
            if ($success_message) echo "<p class='success-message'>$success_message</p>";
            ?>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="btn" name="submit">Send Reset Link</button>
        </form>
        <div class="login-link">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>