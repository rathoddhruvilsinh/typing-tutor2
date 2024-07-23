<?php
session_start();

// Set static username and password
$static_username = "admin";
$static_password = "123";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $static_username && $password === $static_password) {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = 1; // You can set any arbitrary ID
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'admin';
        
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), 0, $params["path"], $params["domain"], true, true);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .container {
            display: flex;
            width: 800px;
            height: 500px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow:  2px 2px 4px rgba(117, 117, 117, 0.8), 
            -2px -2px 4px rgba(117, 117, 117, 0.8);
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            color: #fff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .left-panel h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .left-panel p {
            font-size: 16px;
            line-height: 1.5;
        }
        
        .right-panel {
            flex: 1;
            padding: 40px;
        }
        
        .right-panel h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            box-shadow: inset 0px 0px 5px rgba(6, 6, 6, 0.252);
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .sign-up {
            background-color: #007bff;
            color: #fff;
            margin-bottom: 10px;
        }
        
        .sign-up:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 100%;
                height: auto;
            }
            
            .left-panel, .right-panel {
                width: 100%;
            }
            
            .left-panel {
                padding: 20px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h1>Welcome to Admin Page</h1>
            <p>Please login to admin page for see admin dashboard</p>
        </div>
        <div class="right-panel">
            <h2>Admin login</h2>
            <form method="POST">
                <div class="input-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="sign-up">Login</button>
            </form>
        </div>
    </div>
</body>
</html>