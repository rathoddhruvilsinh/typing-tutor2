<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

require_once 'db.php';

if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (username, email, password, user_role) VALUES (?, ?, ?, 'user')";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        $newUserId = mysqli_insert_id($con);
        echo json_encode(['success' => true, 'id' => $newUserId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add user']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6">Add User</h1>
            <?php if (isset($success)): ?>
                <p class="text-green-500 mb-4"><?php echo $success; ?></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="text-red-500 mb-4"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="username" class="block mb-2">Username</label>
                    <input type="text" id="username" name="username" required class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="email" class="block mb-2">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="password" class="block mb-2">Password</label>
                    <input type="password" id="password" name="password" required class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-6">
                    <label for="role" class="block mb-2">Role</label>
                    <select id="role" name="role" required class="w-full px-3 py-2 border rounded">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add User</button>
            </form>
            <a href="dashboard.php" class="block text-center mt-4 text-blue-500 hover:underline">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>