<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = ""; 
$database = "user_auth";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    error_log("Failed to connect to MySQL: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
}

// Test database connection
$test_query = "SELECT 1";
$test_result = mysqli_query($con, $test_query);
if (!$test_result) {
    error_log("Database connection test failed: " . mysqli_error($con));
} else {
    error_log("Database connection test successful");
}

function getUserCount() {
    global $con;
    $query = "SELECT COUNT(*) as user_count FROM users";
    $result = mysqli_query($con, $query);
    if (!$result) {
        error_log("MySQL Error in getUserCount: " . mysqli_error($con));
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return $row['user_count'];
}

function getAdminCount() {
    global $con;
    $query = "SELECT COUNT(*) as admin_count FROM users WHERE user_role = 'admin'";
    $result = mysqli_query($con, $query);
    if (!$result) {
        error_log("MySQL Error in getAdminCount: " . mysqli_error($con));
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return $row['admin_count'];
}

function getNewUserCount() {
    global $con;
    $query = "SELECT COUNT(*) as new_user_count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    $result = mysqli_query($con, $query);
    if (!$result) {
        error_log("MySQL Error in getNewUserCount: " . mysqli_error($con));
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    return $row['new_user_count'];
}

function getUsers() {
    global $con;
    if (!$con) {
        error_log("Database connection is not available in getUsers");
        return [];
    }
    
    $query = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 10";
    $result = mysqli_query($con, $query);
    if (!$result) {
        error_log("MySQL Error in getUsers: " . mysqli_error($con));
        return [];
    }
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    return $users;
}

$users = getUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Typing Tutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --background-color: #f4f7f9;
            --card-background: #ffffff;
            --text-color: #333333;
            --text-light: #666666;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #BBE9FF;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            line-height: 1.6;
        }

        .header {
            background: var(--secondary-color);
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .menu {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        .menu li {
            margin-right: 20px;
        }

        .menu a {
    position: relative;
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    display: flex;
    align-items: center;
    transition: var(--transition);
    padding: 8px 12px;
}

.menu a::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    bottom: 0;
    left: 0;
    background-color: #2980b9;
    transform: scaleX(0);
    transform-origin: bottom right;
    transition: transform 0.3s ease-out;
}

.menu a:hover::after,
.menu a.active::after {
    transform: scaleX(1);
    transform-origin: bottom left;
}

        .menu i {
            margin-right: 8px;
        }

        .logout-btn {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: var(--border-radius);
            padding: 10px 16px;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background-color: #2980b9;
        }

        .container {
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .dashboard-item {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .dashboard-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .dashboard-item h2 {
            margin: 0 0 16px;
            font-size: 18px;
            color: var(--text-light);
        }

        .dashboard-item .value {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .dashboard-item .description {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 16px;
            text-align: left;
        }

        th {
            background-color: var(--secondary-color);
            color: #ffffff;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .action-buttons button {
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .remove-btn {
            background-color: var(--error-color);
        }

        .remove-btn:hover {
            background-color: #c0392b;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--secondary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 8px;
            display: inline-block;
        }

        .form-container {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        .form-container h2 {
            margin-top: 0;
            margin-bottom: 24px;
            font-size: 20px;
            color: var(--secondary-color);
        }

        .input-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: var(--border-radius);
            padding: 12px 24px;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
        }

        button[type="submit"]:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .menu {
                margin-top: 20px;
                flex-wrap: wrap;
            }

            .menu li {
                margin-bottom: 10px;
            }

            .container {
                padding: 20px;
            }
        }

        .add-user-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 15px;
            padding: 30px;
            width: 95%;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus {
            border-color: #2196f3;
            outline: none;
        }

        .add-user-btn {
            background-color: #2196f3;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .add-user-btn:hover {
            background-color: #1e88e5;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Typing Tutor Admin Dashboard</h1>
        <nav>
            <ul class="menu">
                <li><a href="#" data-section="dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" data-section="users"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="#" data-section="admin"><i class="fas fa-user-shield"></i> Admin User</a></li>
            </ul>
        </nav>
        <form action="logout.php" method="post">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </header>

    <div class="container">
        <div id="dashboardSection">
            <h2 class="section-title">Dashboard Overview</h2>
            <div class="dashboard-grid">
                <div class="dashboard-item">
                    <h2><i class="fas fa-users"></i> Total Users</h2>
                    <div class="value"><?php echo getUserCount(); ?></div>
                    <div class="description">All registered users</div>
                </div>
                <div class="dashboard-item">
                    <h2><i class="fas fa-user-shield"></i> Admin Users</h2>
                    <div class="value"><?php echo getAdminCount(); ?></div>
                    <div class="description">Total admin accounts</div>
                </div>
                <div class="dashboard-item">
                    <h2><i class="fas fa-user-plus"></i> New Users</h2>
                    <div class="value"><?php echo getNewUserCount(); ?></div>
                    <div class="description">Joined last month</div>
                </div>
            </div>

            <h2 class="section-title">Recent Users</h2>
            <table id="dashboardUserTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>USERNAME</th>
                        <th>EMAIL</th>
                        <th>JOINED DATE</th>
                    </tr>
                </thead>
                <tbody id="dashboardUserList">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="userSection" style="display: none;">
            <h2 class="section-title">User Management</h2>
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>USERNAME</th>
                        <th>EMAIL</th>
                        <th>JOINED DATE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody id="userList">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td class="action-buttons">
                            <button class="remove-btn" onclick="deleteUser(<?php echo $user['id']; ?>)"><i class="fas fa-trash"></i> REMOVE</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            
            <h2 class="section-title">Add User</h2>
            <div class="add-user-container">
        <form id="addUserForm">
            <div class="input-group">
                <input type="text" id="newUsername" placeholder="Enter username" required>
            </div>
            <div class="input-group">
                <input type="email" id="newEmail" placeholder="Enter email" required>
            </div>
            <div class="input-group">
                <input type="password" id="newPassword" placeholder="Enter password" required>
            </div>
            <div class="input-group">
                <input type="tel" id="newPhone" placeholder="Enter phone number" required>
            </div>
            <button type="submit" class="add-user-btn">Add User</button>
        </form>
    </div>
        </div>

        <div id="addAdminSection" style="display: none;">
            <h2 class="section-title">Add Admin User</h2>
            <div class="form-container">
            <form id="addAdminForm">
                <div class="input-group">
                    <input type="text" id="newAdminUsername" placeholder="Enter admin username" required>
                    <input type="password" id="newAdminPassword" placeholder="Enter admin password" required>
                </div>
                <button type="submit"><i class="fas fa-plus"></i> Add Admin</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let users = <?php echo json_encode($users); ?>;

    function renderUserList() {
        const userList = document.getElementById('userList');
        userList.innerHTML = '';
        if (users.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="5" style="text-align: center; font-style: italic; color: #777;">There are no users right now</td>`;
            userList.appendChild(tr);
        } else {
            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                    <td class="action-buttons">
                        <button class="remove-btn" onclick="deleteUser(${user.id})"><i class="fas fa-trash"></i> REMOVE</button>
                    </td>
                `;
                userList.appendChild(tr);
            });
        }
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    users = users.filter(user => user.id !== userId);
                    renderUserList();
                    alert('User deleted successfully');
                } else {
                    alert('Failed to delete user');
                }
            });
        }
    }

    function addUser(username, email, password, phone) {
        fetch('add_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'username=' + encodeURIComponent(username) +
                  '&email=' + encodeURIComponent(email) +
                  '&password=' + encodeURIComponent(password) +
                  '&phone=' + encodeURIComponent(phone)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                users.push({id: data.id, username: username, email: email, created_at: new Date().toISOString()});
                renderUserList();
                alert('User added successfully');
            } else {
                alert('Failed to add user');
            }
        });
    }

    document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const newUsername = document.getElementById('newUsername').value;
            const newEmail = document.getElementById('newEmail').value;
            const newPassword = document.getElementById('newPassword').value;
            const newPhone = document.getElementById('newPhone').value;
            if (newUsername && newEmail && newPassword && newPhone) {
                // Here you would typically call a function to add the user
                console.log('Adding user:', { newUsername, newEmail, newPassword, newPhone });
                this.reset();
            }
        });

    function addAdminUser(username, password) {
        fetch('add_admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Admin user added successfully');
            } else {
                alert('Failed to add admin user');
            }
        });
    }

    document.getElementById('addAdminForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const newAdminUsername = document.getElementById('newAdminUsername').value;
        const newAdminPassword = document.getElementById('newAdminPassword').value;
        if (newAdminUsername && newAdminPassword) {
            addAdminUser(newAdminUsername, newAdminPassword);
            this.reset();
        }
    });

    // Navigation functionality
    const menuLinks = document.querySelectorAll('.menu a');
    const sections = {
        dashboard: document.getElementById('dashboardSection'),
        users: document.getElementById('userSection'),
        admin: document.getElementById('addAdminSection')
    };

    function showSection(sectionId) {
        for (let key in sections) {
            sections[key].style.display = key === sectionId ? 'block' : 'none';
        }
    }

    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            
            menuLinks.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            
            showSection(sectionId);
            
            if (sectionId === 'users') {
                renderUserList();
            }
        });
    });

    // Initialize the dashboard view
    showSection('dashboard');
    menuLinks[0].classList.add('active');
});
</script>
</body>
</html>