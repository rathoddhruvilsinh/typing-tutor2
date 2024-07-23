<?php
$host = "localhost";
$username = "root";
$password = ""; // This is typically empty for XAMPP's default setup
$database = "user_auth"; // Make sure this database exists in your local MySQL server

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    error_log("Failed to connect to MySQL: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
}
?>