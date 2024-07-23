<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Validate input
        if (empty($_POST['username']) || empty($_POST['password'])) {
            throw new Exception('Username and password are required');
        }
    
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
        // Add admin user to database
        $query = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param('ss', $username, $password);
    
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to insert admin user: ' . $stmt->error);
        }
    } catch (Exception $e) {
        error_log('Error adding admin user: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}