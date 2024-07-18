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
    <title>Register - Typing Tutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            width: 94%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 600;
        }

        label a {
            color: var(--accent-color);
        }

         .input-icon {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon i {
        position: absolute;
        left: 10px;
        color: var(--accent-color);
        z-index: 1;
    }

    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="password"] {
        width: 100%;
        padding: 10px 10px 10px 35px;
        border: 2px solid var(--accent-color);
        border-radius: 5px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="tel"]:focus,
    input[type="password"]:focus {
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

        .btn i {
            margin-right: 8px;
        }

        .error-message {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Create an account</h1>
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" id="username" required value="<?php echo isset($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="email" required value="<?php echo isset($_REQUEST['email']) ? htmlspecialchars($_REQUEST['email']) : ''; ?>">
                </div>
                <p class="error-message" id="email-error"><?php echo $email_error; ?></p>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="input-icon">
                    <i class="fas fa-phone"></i>
                    <input type="tel" name="phone" id="phone" required pattern="\d{10}" title="Please enter exactly 10 digits" value="<?php echo isset($_REQUEST['phone']) ? htmlspecialchars($_REQUEST['phone']) : ''; ?>">
                </div>
                <p class="error-message" id="phone-error"><?php echo $phone_error; ?></p>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" required>
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="agree" id="agree" value="yes" required> I agree with the
                    <a href="#" title="term of services">terms of service</a>
                </label>
            </div>
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Create account
            </button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Log in</a>
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