<?php
ob_start();
session_start();
require('db.php');
require('send_email.php');

$email_error = $phone_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);

    if (!preg_match('/^\d{10}$/', $phone)) {
        $phone_error = "Phone number must be exactly 10 digits.";
    } else {
        $check_email_query = "SELECT 1 FROM `users` WHERE email='$email' LIMIT 1";
        $check_email_result = mysqli_query($con, $check_email_query);
        
        if (mysqli_num_rows($check_email_result) > 0) {
            $email_error = "This email already exists.";
        } else {
            $otp = sprintf("%06d", mt_rand(1, 999999));
            
            $store_otp_query = "INSERT INTO `otp_table` (email, otp) VALUES ('$email', '$otp')";
            if (mysqli_query($con, $store_otp_query)) {
                $subject = "Your OTP for Registration";
                $message = "Welcome, $username<br>Your OTP is: $otp";
                if (send_email($email, $subject, $message)) {
                    $_SESSION['temp_user'] = [
                        'username' => $username,
                        'email' => $email,
                        'password' => $hashed_password,
                        'phone' => $phone
                    ];
                    
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $email_error = "Failed to send OTP. Please try again.";
                }
            } else {
                $email_error = "Failed to generate OTP. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #BBE9FF;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.462);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo img {
            width: 120px;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 24px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }

        label a{
            color:  #1877f2;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            background-color: #1877f2;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .error-message {
            color: #d93025;
            font-size: 14px;
            margin-top: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-link a {
            color: #1877f2;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <h1>Create an account</h1>
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required value="<?php echo isset($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required value="<?php echo isset($_REQUEST['email']) ? htmlspecialchars($_REQUEST['email']) : ''; ?>">
                <p class="error-message" id="email-error"><?php echo $email_error; ?></p>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phone" required pattern="\d{10}" title="Please enter exactly 10 digits" value="<?php echo isset($_REQUEST['phone']) ? htmlspecialchars($_REQUEST['phone']) : ''; ?>">
                <p class="error-message" id="phone-error"><?php echo $phone_error; ?></p>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="agree" id="agree" value="yes" required> I agree with the
                    <a href="#" title="term of services">terms of service</a>
                </label>
            </div>
            <button type="submit" class="btn">Create account</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>

    <script>
        document.getElementById('email').addEventListener('input', function() {
            document.getElementById('email-error').textContent = '';
        });

        document.getElementById('phone').addEventListener('input', function() {
            var phoneNumber = this.value;
            var phoneError = document.getElementById('phone-error');
            if (phoneNumber.length !== 10 || !/^\d+$/.test(phoneNumber)) {
                phoneError.textContent = 'Phone number must be exactly 10 digits.';
            } else {
                phoneError.textContent = '';
            }
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>