<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = ""; // This is typically empty for XAMPP's default setup
$database = "user_auth"; // Make sure this database exists in your local MySQL server

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
    $query = "SELECT COUNT(*) as user_count FROM users WHERE user_role != 'admin'";
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
    return "N/A";
}

function getUsers() {
    global $con;
    if (!$con) {
        error_log("Database connection is not available in getUsers");
        return [];
    }
    
    $query = "SELECT id, username, email FROM users";
    $result = mysqli_query($con, $query);
    if (!$result) {
        error_log("MySQL Error in getUsers: " . mysqli_error($con));
        return [];
    }
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    error_log("Users: " . print_r($users, true));
    return $users;
}

$users = getUsers();
error_log("Users: " . print_r($users, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Typing Tutor</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --background-color: #ecf0f1;
            --text-color: #2c3e50;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            color: var(--text-color);
        }

        .header {
            background-color: var(--primary-color);
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header h1 {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
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

        .menu li:last-child {
            margin-right: 0;
        }

        .menu a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .menu a:hover {
            color: var(--accent-color);
        }

        .menu a.active {
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 5px;
        }

        .menu i {
            margin-right: 5px;
        }

        .logout-btn {
            background-color: var(--accent-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .logout-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 90%;
            margin: 40px auto;
        }

        h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 24px;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        .dashboard-column {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        .dashboard-item {
            flex: 1;
            min-width: 200px;
            margin: 10px;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 8px;
            text-align: center;
        }
        .dashboard-item h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .dashboard-item .value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .dashboard-item .description {
            font-size: 14px;
            color: #666;
        }

        #userTable, #dashboardUserTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #userTable th, #userTable td,
        #dashboardUserTable th, #dashboardUserTable td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        #userTable th, #dashboardUserTable th {
            background-color: var(--primary-color);
            color: #ffffff;
        }

        #userTable tr:nth-child(even),
        #dashboardUserTable tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        #userTable tr:hover,
        #dashboardUserTable tr:hover {
            background-color: #f0f0f0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 6px 12px;
            font-size: 14px;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .remove-btn {
            background-color: #EB5B00;
        }

        .remove-btn:hover {
            background-color: #C40C0C;
        }

        #addUserForm, #addAdminForm {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .input-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        button[type="submit"] {
            grid-column: 1 / -1;
            justify-self: start;
            background-color: var(--accent-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .menu {
                margin-top: 10px;
                margin-bottom: 10px;
            }

            .logout-btn {
                align-self: flex-end;
            }

            .input-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Typing Tutor Admin Dashboard</h1>
        <nav>
            <ul class="menu">
                <li><a href="#" data-section="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
            <h2>Dashboard Overview</h2>
            <div class="dashboard-column">
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
            <center>
                <p>Welcome to the Typing Tutor Admin Dashboard. Here you can manage users, view statistics, and perform administrative tasks.</p>
            </center>
            <div id="dashboardUserListSection">
                <h2>User List</h2>
                <table id="dashboardUserTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>USERNAME</th>
                            <th>EMAIL</th>
                            <th>JOINED DATE</th>
                            <th>PAYMENT STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="dashboardUserList"></tbody>
                </table>
            </div>
        </div>

        <div id="userSection" style="display: none;">
            <div id="userListSection">
                <h2>User List</h2>
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>USERNAME</th>
                            <th>EMAIL</th>
                            <th>JOINED DATE</th>
                            <th>PAYMENT STATUS</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="userList"></tbody>
                </table>
            </div>
            
            <div id="userDetailsSection" style="display: none;">
                <!-- User details will be dynamically inserted here -->
            </div>
            
            <div id="addUserSection">
                <h2>Add User</h2>
                <form id="addUserForm">
                    <div class="input-group">
                        <input type="text" id="newUsername" placeholder="Enter username" required>
                        <input type="email" id="newEmail" placeholder="Enter email" required>
                        <input type="password" id="newPassword" placeholder="Enter password" required>
                        <input type="tel" id="newPhone" placeholder="Enter phone number" required>
                        <button type="submit"><i class="fas fa-plus"></i> Add User</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="addAdminSection" style="display: none;">
            <h2>Add Admin User</h2>
            <form id="addAdminForm">
                <div class="input-group">
                    <input type="text" id="newAdminUsername" placeholder="Enter admin username" required>
                    <input type="password" id="newAdminPassword" placeholder="Enter admin password" required>
                    <button type="submit"><i class="fas fa-plus"></i> Add Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let users = [];
    try {
        const usersJSON = '<?php echo json_encode($users); ?>';
        console.log("Raw JSON:", usersJSON);
        users = JSON.parse(usersJSON);
        console.log("Parsed users:", users);
        if (!Array.isArray(users)) {
            throw new Error('Invalid user data');
        }
    } catch (error) {
        console.error('Error parsing user data:', error);
        console.error('Raw JSON:', '<?php echo json_encode($users); ?>');
        alert('There was an error loading user data. Please check the console for more information.');
    }

    function renderUserList() {
        const userList = document.getElementById('userList');
        userList.innerHTML = '';
        if (users.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="6" style="text-align: center; font-style: italic; color: #777;">There are no users right now</td>`;
            userList.appendChild(tr);
        } else {
            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${new Date().toLocaleDateString()}</td>
                    <td>Paid</td>
                    <td class="action-buttons">
                        <button class="remove-btn" onclick="deleteUser(${user.id})"><i class="fas fa-trash"></i> REMOVE</button>
                    </td>
                `;
                userList.appendChild(tr);
            });
        }
    }

    function renderDashboardUserList() {
        const dashboardUserList = document.getElementById('dashboardUserList');
        dashboardUserList.innerHTML = '';
        
        if (users.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="5" style="text-align: center; font-style: italic; color: #777;">There are no users right now</td>`;
            dashboardUserList.appendChild(tr);
        } else {
            users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${new Date().toLocaleDateString()}</td>
                    <td>Paid</td>
                `;
                dashboardUserList.appendChild(tr);
            });
        }
    }

    function deleteUser(userId) {
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
                renderDashboardUserList();
            } else {
                alert('Failed to delete user');
            }
        });
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
                users.push({id: data.id, username: username, email: email, phone: phone});
                renderUserList();
                renderDashboardUserList();
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
            addUser(newUsername, newEmail, newPassword, newPhone);
            document.getElementById('newUsername').value = '';
            document.getElementById('newEmail').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('newPhone').value = '';
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
            document.getElementById('newAdminUsername').value = '';
            document.getElementById('newAdminPassword').value = '';
        }
    });

    document.querySelectorAll('.menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            
            // Remove 'active' class from all links
            document.querySelectorAll('.menu a').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add 'active' class to the clicked link
            this.classList.add('active');
            
            document.getElementById('dashboardSection').style.display = 'none';
            document.getElementById('userSection').style.display = 'none';
            document.getElementById('addAdminSection').style.display = 'none';
            
            if (section === 'dashboard') {
                document.getElementById('dashboardSection').style.display = 'block';
                renderDashboardUserList();
            } else if (section === 'users') {
                document.getElementById('userSection').style.display = 'block';
                renderUserList();
            } else if (section === 'admin') {
                document.getElementById('addAdminSection').style.display = 'block';
            }
        });
    });

    // Initialize the dashboard view
    renderDashboardUserList();
    document.querySelector('.menu a[data-section="dashboard"]').classList.add('active');
    </script>
</body>
</html>