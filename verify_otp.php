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
            // OTP is correct, proceed to payment
            // Delete the used OTP
            $delete_otp_query = "DELETE FROM `otp_table` WHERE email='$email'";
            mysqli_query($con, $delete_otp_query);
        
            $success = "OTP verified successfully! Redirecting to payment...";
            header("refresh:3;url=payment.php"); // Redirect to payment page after 3 seconds
        } else {
            $error = "Incorrect OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Verify OTP - Typing Tutor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto+Mono&family=Roboto:wght@400;700&display=swap');

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
            margin: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 400px;
            margin-top: 200px;
        }

        .form {
            background: linear-gradient(135deg, #ffffff, #f5f5f5);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .form-title {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .form-input {
            width: 92%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid var(--accent-color);
            border-radius: 5px;
            font-size: 16px;
            background-color: #ffffff;
            color: var(--text-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
        }

        .form-button {
            width: 100%;
            padding: 12px;
            background: rgb(147,191,255);
            background: linear-gradient(135deg, rgba(147,191,255,1) 28%, rgba(23,74,255,1) 66%);
            color: #ffffff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .error-message {
            color: var(--error-color);
            background-color: rgba(231, 76, 60, 0.1);
            border: 1px solid var(--error-color);
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }

        .success-message {
            color: var(--success-color);
            background-color: rgba(46, 204, 113, 0.1);
            border: 1px solid var(--success-color);
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }

        p {
            text-align: center;
            margin-top: 20px;
            color: var(--text-color);
        }

        a {
            color: var(--accent-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        @media screen and (max-width: 480px) {
            .container {
                padding: 0 20px;
            }

            .form {
                padding: 20px;
            }

            .form-title {
                font-size: 20px;
            }

            .form-input,
            .form-button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form class="form" method="post">
            <h1 class="form-title">Verify OTP</h1>
            <?php 
            if ($error) {
                echo "<div class='error-message'>$error</div>";
            }
            if ($success) {
                echo "<div class='success-message'>$success</div>";
            }
            ?>
            <input type="text" class="form-input" name="otp" placeholder="Enter 6-digit OTP" required />
            <button type="submit" name="submit" class="form-button">Verify</button>
            <p>Didn't receive OTP? <a href="resend_otp.php?email=<?php echo urlencode($_SESSION['temp_user']['email']); ?>">Resend OTP</a></p>
        </form>
    </div>
</body>
</html>