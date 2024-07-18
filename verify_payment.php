<?php
session_start();
require('db.php');

$key_id = 'rzp_test_sC9wQzWpja3MGt';
$key_secret = 'h6DAMnUJo2QrP9Xl4lmzBDIG';

if (isset($_POST['payment_id'])) {
    $payment_id = $_POST['payment_id'];
    
    // Verify the payment with Razorpay API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/payments/$payment_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_status === 200) {
        $payment_details = json_decode($result, true);
        
        if ($payment_details['status'] === 'authorized') {
            // Capture the authorized payment
            $capture_url = "https://api.razorpay.com/v1/payments/" . $payment_id . "/capture";
            $capture_data = http_build_query(array('amount' => $payment_details['amount']));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $capture_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $capture_data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
            $capture_result = curl_exec($ch);
            $capture_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($capture_http_status === 200) {
                $capture_details = json_decode($capture_result, true);
                if ($capture_details['status'] === 'captured') {
                    // Payment successfully captured, proceed with user registration
                    $username = $_SESSION['temp_user']['username'];
                    $email = $_SESSION['temp_user']['email'];
                    $password = $_SESSION['temp_user']['password'];
                    $phone = $_SESSION['temp_user']['phone'];
                
                    $query = "INSERT into `users` (username, email, phone, password) 
                              VALUES ('$username', '$email', '$phone', '$password')";

                    $result = mysqli_query($con, $query);
                    if ($result) {
                        $_SESSION['username'] = $username;
                        unset($_SESSION['temp_user']);
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Registration failed: ' . mysqli_error($con)]);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Payment capture failed. Status: ' . $capture_details['status']]);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to capture payment. HTTP Status: ' . $capture_http_status]);
            }
        } elseif ($payment_details['status'] === 'captured') {
            // Payment was already captured, proceed with user registration
            $username = $_SESSION['temp_user']['username'];
            $email = $_SESSION['temp_user']['email'];
            $password = $_SESSION['temp_user']['password'];
            $phone = $_SESSION['temp_user']['phone'];
        
            $query = "INSERT into `users` (username, email, phone, password) 
                      VALUES ('$username', '$email', '$phone', '$password')";

            $result = mysqli_query($con, $query);
            if ($result) {
                $_SESSION['username'] = $username;
                unset($_SESSION['temp_user']);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Registration failed: ' . mysqli_error($con)]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Payment not authorized. Status: ' . $payment_details['status']]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to verify payment. HTTP Status: ' . $http_status]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request: No payment ID provided']);
}