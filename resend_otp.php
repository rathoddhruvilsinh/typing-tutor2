<?php
session_start();
require('db.php');
require('send_email.php');

if (!isset($_SESSION['temp_user'])) {
    header("Location: registration.php");
    exit();
}

$email = $_SESSION['temp_user']['email'];
$username = $_SESSION['temp_user']['username'];

// Generate new OTP
$new_otp = sprintf("%06d", mt_rand(1, 999999));

// Update OTP in the database
$update_otp_query = "UPDATE `otp_table` SET otp = '$new_otp', created_at = NOW() WHERE email = '$email'";
if (!mysqli_query($con, $update_otp_query)) {
    // If no existing record, insert a new one
    $insert_otp_query = "INSERT INTO `otp_table` (email, otp) VALUES ('$email', '$new_otp')";
    mysqli_query($con, $insert_otp_query);
}

// Send new OTP via email
$subject = "Your New OTP for Registration";
$message = "Hello " . $username . ",<br>Your new OTP is: " . $new_otp;

if (send_email($email, $subject, $message)) {
    $_SESSION['otp_resent'] = true;
    header("Location: verify_otp.php");
} else {
    $_SESSION['otp_resend_error'] = "Failed to send new OTP. Please try again.";
    header("Location: verify_otp.php");
}
exit();
?>