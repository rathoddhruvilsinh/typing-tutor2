<?php
session_start();
require('db.php');

if (!isset($_SESSION['temp_user'])) {
    header("Location: registration.php");
    exit();
}

$error = "";
$success = "";

if (isset($_POST['otp'])) {
    $entered_otp = mysqli_real_escape_string($con, $_POST['otp']);
    $email = $_SESSION['temp_user']['email'];
    
    if (strlen($entered_otp) != 6 || !ctype_digit($entered_otp)) {
        $error = "Invalid OTP format. Please enter a 6-digit number.";
    } else {
        $check_otp_query = "SELECT * FROM `otp_table` WHERE email='$email' AND otp='$entered_otp' AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) ORDER BY created_at DESC LIMIT 1";
        $check_otp_result = mysqli_query($con, $check_otp_query);
        
        if (mysqli_num_rows($check_otp_result) == 1) {
            // OTP is correct, register the user
            $username = $_SESSION['temp_user']['username'];
            $email = $_SESSION['temp_user']['email'];
            $password = $_SESSION['temp_user']['password'];
            $phone = $_SESSION['temp_user']['phone'];
        
            $query = "INSERT into `users` (username, email, phone, password) 
          VALUES ('$username', '$email', '$phone', '$password')";

            $result = mysqli_query($con, $query);
            if ($result) {
                // Registration successful
                $_SESSION['username'] = $username;

                // Delete the used OTP
                $delete_otp_query = "DELETE FROM `otp_table` WHERE email='$email'";
                mysqli_query($con, $delete_otp_query);
    
                $success = "Registration successful! Redirecting to homepage...";
                header("refresh:3;url=index.php"); // Redirect after 3 seconds
            } else {
                $error = "Error in registration: " . mysqli_error($con);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: rgb(255,0,151);
            background: linear-gradient(302deg, rgba(255,0,151,1) 0%, rgba(255,176,147,1) 50%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        .form {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .login-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .login-input:focus {
            border-color: #a1c4fd;
            outline: none;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background-color: #1876f2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-button:hover {
            background-color: #0056b3;
        }

        .error, .success {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        p {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <form class="form" method="post">
        <h1 class="login-title">Verify OTP</h1>
        <input type="text" class="login-input" name="otp" placeholder="Enter 6-digit OTP" required />
        <input type="submit" name="submit" value="Verify" class="login-button">
        <?php 
        if ($error) echo "<p class='error'>$error</p>";
        if ($success) echo "<p class='success'>$success</p>";
        ?>
        <p>Didn't receive OTP? <a href="resend_otp.php">Resend OTP</a></p>
    </form>
</body>
</html>