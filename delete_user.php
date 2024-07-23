<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false]);
    exit();
}

require_once 'db.php';

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $query = "DELETE FROM users WHERE id = '$id'";
    if (mysqli_query($con, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>