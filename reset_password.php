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
    
    error_log("Debug: Request received. Email: " . ($email ?? 'not set') . ", Token: " . ($token ?? 'not set'));

    // Check database connection
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    // Check if the token exists for this email
    $stmt = $con->prepare("SELECT * FROM password_reset_tokens WHERE email = ? AND token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_token'] = $token;
        
        // Update the expiration time
        $new_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $stmt->close(); // Close the previous statement
        $stmt = $con->prepare("UPDATE password_reset_tokens SET expires = ? WHERE email = ? AND token = ?");
        $stmt->bind_param("sss", $new_expiry, $email, $token);
        if (!$stmt->execute()) {
            error_log("Failed to update token expiration: " . $stmt->error);
        }
        $stmt->close();
    } else {
        $error_message = "Invalid reset link. Please check your email or request a new link.";
        error_log("Invalid token used for email: $email");
    }
} else {
    $error_message = "Missing email or token in the reset link.";
    error_log("Missing email or token in reset password request");
}

if (isset($_POST['submit']) && isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
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
                
                $stmt->close(); // Close the previous statement
                
                $stmt = $con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);
                if ($stmt->execute()) {
                    $success_message = "Password updated successfully. You can now login with your new password.";
                    
                    $stmt->close(); // Close the previous statement
                    
                    $stmt = $con->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->close(); // Close this statement as well
                    
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
            // Removed the redundant $stmt->close() call here
        } else {
            $error_message = "Reset email not found in session. Please try the reset link again.";
            error_log("Reset email not found in session when attempting to reset password");
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Typing Tutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style1.css">
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        <form class="form" method="post">
            <?php 
            if ($error_message) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> $error_message</p>";
            if ($success_message) echo "<p class='success-message'><i class='fas fa-check-circle'></i> $success_message</p>";
            ?>
            <?php if (isset($_SESSION['reset_email']) && isset($_SESSION['reset_token']) && !$success_message): ?>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" class="btn" name="submit"><i class="fas fa-key"></i> Reset Password</button>
            <?php endif; ?>
        </form>
        <div class="login-link">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Back to Login</a>
        </div>
    </div>
</body>
</html>