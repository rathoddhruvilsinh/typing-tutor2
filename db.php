<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Local database settings
$host = "localhost";
$username = "root";
$password = ""; // This is typically empty for XAMPP's default setup
$database = "user_auth"; // Make sure this database exists in your local MySQL server

$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
?>