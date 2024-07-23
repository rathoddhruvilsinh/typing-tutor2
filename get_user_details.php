<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $response['message'] = 'Unauthorized access';
} else if (!isset($_GET['id'])) {
    $response['message'] = 'User ID not provided';
} else {
    try {
        require_once 'db.php';
        
        if (!$con) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }
        
        $userId = intval($_GET['id']);
        
        $query = "SELECT id, username, email, phone, created_at, user_role FROM users WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $userId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            $response = ['success' => true, 'user' => $user];
        } else {
            $response['message'] = 'User not found';
        }
        
        mysqli_close($con);
    } catch (Exception $e) {
        $response['message'] = 'Server error: ' . $e->getMessage();
    }
}

echo json_encode($response);