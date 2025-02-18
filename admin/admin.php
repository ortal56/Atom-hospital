<?php
$db = new SQLite3('../database/users.db');

// Create users table if not exists
createUsersTable($db);

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            addUser($db, $_POST);
            break;
        case 'delete':
            deleteUser($db, $_POST['id']);
            break;
        case 'update':
            updateUser($db, $_POST);
            break;
        case 'search':
            searchUsers($db, $_POST);
            break;
        default:
            echo "Invalid action.";
    }
}

// Fetch all users including passwords
$users = $db->query("SELECT id, name, password, status, role FROM users");

function createUsersTable($db) {
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        password TEXT NOT NULL,
        status TEXT NOT NULL,
        role TEXT NOT NULL
    )";
    $db->exec($query);
}

function addUser($db, $data) {
    $name = $data['name'];
    $password = $data['password'];
    $status = $data['status'];
    $role = $data['role'];

    $stmt = $db->prepare("INSERT INTO users (name, password, status, role) VALUES (:name, :password, :status, :role)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo "User added successfully!";
    } else {
        echo "Error adding user.";
    }
}

function deleteUser($db, $id) {
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo "User deleted successfully!";
    } else {
        echo "Error deleting user.";
    }
}

function updateUser($db, $data) {
    $id = $data['id'];
    $name = $data['name'];
    $status = $data['status'];
    $role = $data['role'];

    $stmt = $db->prepare("UPDATE users SET name = :name, status = :status, role = :role WHERE id = :id");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo "User updated successfully!";
    } else {
        echo "Error updating user.";
    }
}

function searchUsers($db, $data) {
    $name = $data['name'] ?? '';
    $status = $data['status'] ?? '';
    $role = $data['role'] ?? '';

    $query = "SELECT * FROM users WHERE 1=1";
    if (!empty($name)) {
        $query .= " AND name LIKE :name";
    }
    if (!empty($status)) {
        $query .= " AND status = :status";
    }
    if (!empty($role)) {
        $query .= " AND role = :role";
    }

    $stmt = $db->prepare($query);
    if (!empty($name)) {
        $stmt->bindValue(':name', '%' . $name . '%', SQLITE3_TEXT);
    }
    if (!empty($status)) {
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    }
    if (!empty($role)) {
        $stmt->bindValue(':role', $role, SQLITE3_TEXT);
    }
    $result = $stmt->execute();

    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[] = $row;
    }

    echo json_encode($users);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Page</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="../logo/image.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
        <ul class="navbar">
            <li><a href="#" onclick="location.replace('../index.html'); return false;">Home</a></li>
            <li><a href="admin.php">Manage Users</a></li>
            <li><a href="#addUserForm">Add User</a></li>
            <li><a href="#searchForm">Search Form</a></li>
        </ul>
        <img src="../logo/image.png" alt="" onclick="location.replace('../index.html'); return false;">
    </div>

    <h1>Admin Panel</h1>
    <form id="addUserForm">
        <input type="text" id="name" placeholder="Name" required>
        <input type="password" id="password" placeholder="Password" required>
        <select id="status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
        <select id="role">
            <option value="Doctor">Doctor</option>
            <option value="Doctor assist">Doctor assist</option>
            <option value="Nurse">Nurse</option>
            <option value="receptionist">receptionist</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit">Add User</button>
    </form>

    <form id="searchForm">
        <input type="text" id="searchName" placeholder="Search by Name">
        <select id="searchStatus">
            <option value="">All Statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
        <select id="searchRole">
            <option value="">All Roles</option>
            <option value="Doctor">Doctor</option>
            <option value="Doctor assist">Doctor assist</option>
            <option value="Nurse">Nurse</option>
            <option value="receptionist">receptionist</option>
            <option value="Admin">Admin</option>
            
        </select>
        <button type="submit">Search</button>
    </form>

    <h3>Users List</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Password</th>
            <th>Status</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php while ($user = $users->fetchArray(SQLITE3_ASSOC)) : ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['name'] ?></td>
            <td>
                <button onclick="this.nextElementSibling.innerText = `<?= $user['password'] ?>`; this.style.display = 'none';">Show Password</button>
                <span></span>
            </td>
            <td><?= $user['status'] ?></td>
            <td><?= $user['role'] ?></td>
            <td>
                <button onclick="editUser(<?= $user['id'] ?>, '<?= $user['name'] ?>', '<?= $user['status'] ?>', '<?= $user['role'] ?>')">Edit</button>
                <button onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <script>
        $("#addUserForm").submit(function(event) {
            event.preventDefault();
            $.post("admin.php", {
                action: "add",
                name: $("#name").val(),
                password: $("#password").val(),
                status: $("#status").val(),
                role: $("#role").val()
            }, function(response) {
                location.reload();
            });
        });

        $("#searchForm").submit(function(event) {
            event.preventDefault();
            $.post("admin.php", {
                action: "search",
                name: $("#searchName").val(),
                status: $("#searchStatus").val(),
                role: $("#searchRole").val()
            }, function(response) {
                const users = JSON.parse(response);
                updateTable(users);
            });
        });
        
        function deleteUser(id) {
            if (confirm("Are you sure you want to delete this user?")) {
                $.post("admin.php", { action: "delete", id: id }, function(response) {
                    location.reload();
                });
            }
        }
        document.createElement("p").innerText
        function editUser(id, name, status, role) {
            const newName = prompt("Enter new name:", name);
            const newStatus = prompt("Enter new status:", status);
            const newRole = prompt("Enter new role:", role);

            if (newName && newStatus && newRole) {
                $.post("admin.php", {
                    action: "update",
                    id: id,
                    name: newName,
                    status: newStatus,
                    role: newRole
                }, function(response) {
                    alert("Details updated successfully!");
                    location.reload();
                });
            }
        }

        function updateTable(users) {
            const tableBody = $("table tbody");
            tableBody.empty();
            tableBody.append(
                `
                <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Password</th>
            <th>Status</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
                `
            )
            users.forEach(user => {
                const row = `<tr>
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td>${user.password}</td>
                    <td>${user.status}</td>
                    <td>${user.role}</td>
                    <td>
                        <button onclick="editUser(${user.id}, '${user.name}', '${user.status}', '${user.role}')">Edit</button>
                        <button onclick="deleteUser(${user.id})">Delete</button>
                    </td>
                </tr>`;
                tableBody.append(row);
            });
        }

        document.querySelector("a[href='#searchForm'").addEventListener("click", function() {
            document.getElementById("searchForm").style.display = "block";
            document.getElementById("addUserForm").style.display = "none";
        });
        document.querySelector("a[href='#addUserForm'").addEventListener("click", function() {
            document.getElementById("addUserForm").style.display = "block";
            document.getElementById("searchForm").style.display = "none";
        });
    </script>
</body>
</html>
