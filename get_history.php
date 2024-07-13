<?php
session_start();
include 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$userId = $username !== 'Guest' ? getUserId($username) : null;

if ($userId === null) {
    echo json_encode(['error' => 'User not logged in or user ID not found']);
    exit;
}

try {
    $stmt = $con->prepare("SELECT wpm, cpm, accuracy, errors, time FROM typing_results WHERE user_id = ? ORDER BY id DESC LIMIT 10");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = array();
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    $stmt->close();

    echo json_encode($history);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function getUserId($username) {
    global $con;
    $stmt = $con->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user ? $user['id'] : null;
}
?>