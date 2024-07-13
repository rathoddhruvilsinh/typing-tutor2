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
            $expires = date('Y-m-d H:i:s', strtotime('+100 years'));
            
            $stmt = $con->prepare("INSERT INTO password_reset_tokens (email, token, expires) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expires);
            $stmt->execute();
            
            $reset_link = "http://localhost/login2/reset_password.php?email=" . urlencode($email) . "&token=" . $token;
            $message = "Click the following link to reset your password: " . $reset_link;
            
            if (send_email($email, "Password Reset", $message)) {
                $success_message = "A password reset link has been sent to your email.";
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
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form class="form" method="post">
        <h1 class="login-title">Forgot Password</h1>
        <?php 
        if ($error_message) echo "<p class='error-message'>$error_message</p>";
        if ($success_message) echo "<p class='success-message'>$success_message</p>";
        ?>
        <input type="email" class="login-input" name="email" placeholder="Email Address" required autofocus>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="submit" value="Send Reset Link" name="submit" class="login-button">
        <p class="link"><a href="login.php">Back to Login</a></p>
    </form>
</body>
</html>