<?php
session_start();
require('db.php');

if (!isset($_SESSION['temp_user'])) {
    header("Location: registration.php");
    exit();
}

$error = "";
$success = "";

// Check if OTP was resent
if (isset($_SESSION['otp_resent']) && $_SESSION['otp_resent'] === true) {
    $success = "OTP has been resent to your email.";
    unset($_SESSION['otp_resent']); // Clear the session variable
} elseif (isset($_SESSION['otp_resend_error'])) {
    $error = $_SESSION['otp_resend_error'];
    unset($_SESSION['otp_resend_error']); // Clear the session variable
}

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
        } else {
            $error = "Incorrect OTP. Please try again.";
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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-image: url(bg.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;        
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 400px;
        }

        .form {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
        }

        .login-title {
            color: #ffffff;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .login-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            font-size: 16px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .login-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-input:focus {
            border-color: #a1c4fd;
            outline: none;
            background-color: rgba(255, 255, 255, 0.2);
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

        .error-message {
            color: #ff0000;
            background-color: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }

        .success-message {
            color: #50cf50;
            background-color: rgba(0, 255, 0, 0.1);
            border: 1px solid #00ff00;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }

        p {
            text-align: center;
            margin-top: 20px;
            color: #ffffff;
        }

        a {
            color: #a1c4fd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .form {
                padding: 30px;
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
                padding: 20px;
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
            <h1 class="login-title">Verify OTP</h1>
            <?php 
            if ($error) {
                echo "<div class='error-message'>$error</div>";
            }
            if ($success) {
                echo "<div class='success-message'>$success</div>";
            }
            ?>
            <input type="text" class="login-input" name="otp" placeholder="Enter 6-digit OTP" required />
            <input type="submit" name="submit" value="Verify" class="login-button">
            <p>Didn't receive OTP? <a href="resend_otp.php?email=<?php echo urlencode($_SESSION['temp_user']['email']); ?>">Resend OTP</a></p>
        </form>
    </div>
</body>
</html>