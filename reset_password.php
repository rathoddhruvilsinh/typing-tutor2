<?php
require('db.php');
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';
$success_message = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
    $token = $_GET['token'];
    
    error_log("Debug: Email = $email, Token = $token");

    // Check database connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // First, check if the token exists for this email
    $stmt = $con->prepare("SELECT * FROM password_reset_tokens WHERE email = ? AND token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // Token exists, now check if it's expired
        $row = $result->fetch_assoc();
        $expires = strtotime($row['expires']);
        $now = time();
        
        if ($expires > $now) {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;
            
            // Extend token expiration time
            $new_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $con->prepare("UPDATE password_reset_tokens SET expires = ? WHERE email = ? AND token = ?");
            $stmt->bind_param("sss", $new_expiry, $email, $token);
            if (!$stmt->execute()) {
                error_log("Failed to update token expiration: " . $stmt->error);
            }
        } else {
            $error_message = "Reset link has expired. Please request a new one.";
            error_log("Expired token used for email: $email");
        }
    } else {
        $error_message = "Invalid reset link. Please check your email or request a new link.";
        error_log("Invalid token used for email: $email");
    }
} else {
    if (!isset($_GET['email']) || !isset($_GET['token'])) {
        $error_message = "Missing email or token in the reset link.";
        error_log("Missing email or token in reset password request");
    }
}

if (isset($_POST['submit']) && isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password === $confirm_password) {
        if (isset($_SESSION['reset_email']) && isset($_SESSION['reset_token'])) {
            $email = $_SESSION['reset_email'];
            $token = $_SESSION['reset_token'];
            
            // Check if token is still valid
            $stmt = $con->prepare("SELECT * FROM password_reset_tokens WHERE email = ? AND token = ? AND expires > NOW()");
            $stmt->bind_param("ss", $email, $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);
                if ($stmt->execute()) {
                    $success_message = "Password updated successfully. You can now login with your new password.";
                    
                    $stmt = $con->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_token']);
                } else {
                    $error_message = "Failed to update password. Please try again.";
                    error_log("Failed to update password for $email: " . $stmt->error);
                }
            } else {
                $error_message = "Reset link has expired. Please request a new reset link.";
                error_log("Expired token used when resetting password for email: $email");
            }
        } else {
            $error_message = "Reset email not found in session. Please try the reset link again.";
            error_log("Reset email not found in session when attempting to reset password");
        }
    } else {
        $error_message = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <form class="form" method="post">
        <h1 class="login-title">Reset Password</h1>
        <?php 
        if ($error_message) echo "<p class='error-message'>$error_message</p>";
        if ($success_message) echo "<p class='success-message'>$success_message</p>";
        ?>
        <?php if (isset($_SESSION['reset_email']) && isset($_SESSION['reset_token']) && !$error_message && !$success_message): ?>
            <input type="password" class="login-input" name="password" placeholder="New Password" required>
            <input type="password" class="login-input" name="confirm_password" placeholder="Confirm New Password" required>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="submit" value="Reset Password" name="submit" class="login-button">
        <?php endif; ?>
        <p class="link"><a href="login.php">Back to Login</a></p>
    </form>
</body>
</html>