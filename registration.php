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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style12.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <title>Register</title>
    <style>
        body{
            background-image: url(bg.jpg);
            background-size: 1366px 768px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
        }

        .form {
            background-color: transparent;
            backdrop-filter: blur(2px);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgb(255, 255, 255);
        }

        h1 {
            text-align: center;
            color: #ffffff;
            margin-bottom: 20px;
        }

        label {
    display: block;
    margin-bottom: 5px;
    color: #ffffff;
}

footer {
    text-align: center;
    margin-top: 20px;
    color: #ffffff;
}

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .password-container {
            position: relative;
        }
        .password-container .fa-eye, .password-container .fa-eye-slash {
            position: absolute;
            top: 72%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }       
        /* Added CSS for phone input */
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type="tel"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>
</head>
<body>
<main>
<form class="form" action="" method="post">
        <h1>Sign Up</h1>
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required value="<?php echo isset($_REQUEST['username']) ? htmlspecialchars($_REQUEST['username']) : ''; ?>">
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required value="<?php echo isset($_REQUEST['email']) ? htmlspecialchars($_REQUEST['email']) : ''; ?>">
            <p class="error-message" id="email-error"><?php echo $email_error; ?></p>
        </div>
        <div>
            <label for="phone">Phone Number:</label>
            <input type="tel" name="phone" id="phone" required pattern="\d{10}" title="Please enter exactly 10 digits" value="<?php echo isset($_REQUEST['phone']) ? htmlspecialchars($_REQUEST['phone']) : ''; ?>">
            <p class="error-message" id="phone-error"><?php echo $phone_error; ?></p>
        </div>
        <div class="password-container">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <i class="fas fa-eye" id="togglePassword"></i>
        </div>
        <div>
            <label for="agree">
                <input type="checkbox" name="agree" id="agree" value="yes" required/> I agree
                with the
                <a href="#" title="term of services">term of services</a>
            </label>
        </div>
        <button type="submit">Register</button>
        <footer>Already a member? <a href="login.php">Login here</a></footer>
    </form>
</main>

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

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>