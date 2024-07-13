<?php
require('db.php');
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';
$success_message = '';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];
    
    $stmt = $con->prepare("SELECT * FROM password_reset_tokens WHERE email = ? AND token = ? AND expires > NOW()");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $_SESSION['reset_email'] = $email;
    } else {
        $error_message = "Invalid or expired reset link.";
    }
}

if (isset($_POST['password']) && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password === $confirm_password) {
        $email = $_SESSION['reset_email'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $con->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        if ($stmt->execute()) {
            $success_message = "Password updated successfully. You can now login with your new password.";
            
            $stmt = $con->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            unset($_SESSION['reset_email']);
        } else {
            $error_message = "Failed to update password. Please try again.";
            error_log("Failed to update password for $email");
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
        <?php if (isset($_SESSION['reset_email']) && !$error_message && !$success_message): ?>
            <input type="password" class="login-input" name="password" placeholder="New Password" required>
            <input type="password" class="login-input" name="confirm_password" placeholder="Confirm New Password" required>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="submit" value="Reset Password" name="submit" class="login-button">
        <?php endif; ?>
        <p class="link"><a href="login.php">Back to Login</a></p>
    </form>
</body>
</html>