<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
    $userId = $username !== 'Guest' ? getUserId($username) : null;
    $wpm = intval($_POST['wpm']);
    $cpm = intval($_POST['cpm']);
    $accuracy = floatval($_POST['accuracy']);
    $errors = intval($_POST['errors']);
    $time = floatval($_POST['time']);

    saveResult($userId, $wpm, $cpm, $accuracy, $errors, $time);
    echo "Result saved successfully";
} else {
    echo "Invalid request method";
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

function saveResult($userId, $wpm, $cpm, $accuracy, $errors, $time) {
    global $con;
    $stmt = $con->prepare("INSERT INTO typing_results (user_id, wpm, cpm, accuracy, errors, time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiidid", $userId, $wpm, $cpm, $accuracy, $errors, $time);
    $stmt->execute();
    $stmt->close();
}
?>