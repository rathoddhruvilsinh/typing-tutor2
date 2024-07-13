<?php
require('db.php');

$query = "SELECT id, password FROM users";
$result = mysqli_query($con, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $old_password = $row['password'];
    $new_password = password_hash($old_password, PASSWORD_DEFAULT);
    
    $update_query = "UPDATE users SET password = '$new_password' WHERE id = $id";
    mysqli_query($con, $update_query);
}

echo "Passwords updated successfully.";
?>