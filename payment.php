<?php
session_start();
require('db.php');

if (!isset($_SESSION['temp_user'])) {
    header("Location: registration.php");
    exit();
}

$key_id = 'rzp_test_sC9wQzWpja3MGt';
$key_secret = 'h6DAMnUJo2QrP9Xl4lmzBDIG';
$amount = 100000; // Amount in paise (e.g., 100 paise = 1 INR)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Typing Tutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
            text-align: center;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 24px;
            font-family: 'Montserrat', sans-serif;
        }

        #pay-button {
            background-color: var(--accent-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        #pay-button:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Complete Payment</h1>
        <button id="pay-button">Pay Now</button>
    </div>

    <script>
        var options = {
            "key": "<?php echo $key_id; ?>",
            "amount": "<?php echo $amount; ?>",
            "currency": "INR",
            "name": "Typing Tutor",
            "description": "Registration Fee",
            "handler": function (response){
                // Send the payment ID to the server for verification
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "verify_payment.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var result = JSON.parse(xhr.responseText);
                                if (result.success) {
                                    window.location.href = "index.php";
                                } else {
                                    alert("Payment failed: " + (result.error || "Unknown error"));
                                }
                            } catch (e) {
                                alert("An error occurred while processing the payment response.");
                            }
                        } else {
                            alert("Server error: " + xhr.status);
                        }
                    }
                };
                xhr.send("payment_id=" + response.razorpay_payment_id);
            },
            "prefill": {
                "name": "<?php echo $_SESSION['temp_user']['username']; ?>",
                "email": "<?php echo $_SESSION['temp_user']['email']; ?>"
            },
            "theme": {
                "color": "#3498db"
            }
        };
        var rzp = new Razorpay(options);
        document.getElementById('pay-button').onclick = function(e){
            rzp.open();
            e.preventDefault();
        }
    </script>
</body>
</html>