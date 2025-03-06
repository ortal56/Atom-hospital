<?php

session_start();
$db = new SQLite3('../database/users.db');
$db2 = new SQLite3('../database/clinic.db');
$db3 = new SQLite3('../database/with.db');
$arc = new SQLite3("../database/archive.db");
// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Create users table if not exists
createUsersTable($db);
createWithTable($db3);
createarchiveTable($arc);
// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            addUser($db, $_POST);
            break;
        case 'delete':
            deleteUser($db, $_POST['id']);
            break;
        case "deletepation":
            deletePation($db2, $_POST['id']);
            break;
        case 'update':
            updateUser($db, $_POST);
            break;
        case 'search':
            searchUsers($db, $_POST);
            break;
        case 'chart':
            chart($db, $db2);
            break;
        case "getassist":
            getassistanddoctor($db);
            break;
        case "removeAssist":
            removeAssist($db3, $_POST['id']);
            break;
        case "addassist":
            addassist($db3, $_POST);
            break;
        default:
            echo "Invalid action.";
    }
}


// Fetch all users excluding passwords
$users = $db->query("SELECT id, name, status, role FROM users");
$patients = $db2->query("SELECT * FROM patients");
$doctorAssists = $db3->query("SELECT * FROM assist");
function createUsersTable($db) {
    $query = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        password TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        status TEXT NOT NULL,
        role TEXT NOT NULL
    )";
    $db->exec($query);
}

function createWithTable($db) {
    $query = "CREATE TABLE IF NOT EXISTS assist (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name_doctor TEXT NOT NULL,
        name_assist TEXT NOT NULL
    )";
    $db->exec($query);
}

function createarchiveTable($arc) {
    $arc->exec("CREATE TABLE IF NOT EXISTS archive (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        age INTEGER,
        condition TEXT,
        phone_number TEXT,
        doctor_name TEXT,
        patient_gender TEXT,
        room TEXT,
        created_at TEXT,
        code TEXT,
        end_at TEXT,
        statu TEXT
    )");
}

function getassistanddoctor($db) {
    $stmt = $db->prepare("SELECT name, role FROM users WHERE role = 'Doctor' OR role = 'Doctor_assist'");
    $result = $stmt->execute();
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = ['role' => $row['role'], 'name' => $row['name']];
    }
    echo json_encode($rows);
    exit();
}

function addassist($db, $data) {
    $doctor = $data['Doctor_name'];
    $assist = isset($data['assist_name']) ? $data['assist_name'] : '';
    $stmt = $db->prepare("INSERT INTO assist (name_doctor, name_assist) VALUES (:doctor, :assist)");
    $stmt->bindValue(':doctor', $doctor, SQLITE3_TEXT);
    $stmt->bindValue(':assist', $assist, SQLITE3_TEXT);
    $stmt->execute();
    $stmt->close();
    echo "Assistance added successfully!";
    exit();
}
function removeAssist($db, $data) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM assist WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    $stmt->close();
    echo "Assistance removed successfully!";
    exit();
}

function chart($db, $db2) {
    $roles = [];
    $result = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $patientCount = $db2->querySingle("SELECT COUNT(*) as count FROM patients");

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $roles[$row['role']] = $row['count'];
    }
    $roles['patients'] = $patientCount;

    header('Content-Type: application/json');
    echo json_encode($roles);
    exit;
}

function addUser($db, $data) {
    $name = $data['name'];
    $password = $data['password'];
    $password_hash = password_hash($data['password'], PASSWORD_BCRYPT); // Hash the password
    $status = $data['status'];
    $role = $data['role'];
    
    // Find the first available ID
    $result = $db->query("SELECT id FROM users ORDER BY id ASC");
    $availableId = 1;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['id'] != $availableId) {
            break;
        }
        $availableId++;
    }

    $stmt = $db->prepare("INSERT INTO users (id, name, password, password_hash, status, role) VALUES (:id, :name, :password, :password_hash, :status, :role)");
    $stmt->bindValue(':id', $availableId, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':password_hash', $password_hash, SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo "User added successfully with ID: $availableId";
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

function deletePation($db, $id) {
    $stmt = $db->prepare("DELETE FROM patients WHERE id = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo "Patient deleted successfully!";
    } else {
        echo "Error deleting patient.";
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

    $query = "SELECT id, name, status, role FROM users WHERE 1=1";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="../logo/image.ico" type="image/x-icon">
</head>
<body>
    <div class="body">
        <div class="container">
            <img src="../logo/image.png" alt="" onclick="location.replace('../index.html'); return false;">
            <ul class="navbar">
                <li><img src="../logo/photo-512.png" alt=""></li>
                <li><a href="#Home">Home</a></li>
                <li><a href="#Users_List">Manage Users</a></li>
                <li><a href="#addUserForm">Add User</a></li>
                <li><a href="#searchForm">Search Form</a></li>
                <li><a href="#doctorAssist">Doctor assist</a></li>
                <li><a href="#pation">Patients</a></li>
            </ul>
            <button onclick="logout()">Logout</button>
        </div>

        <div class="cont">

            <div class="home" id="home">
                <h1>Welcome to the Admin Panel</h1>
                <p>Here you can manage users, add new users, search for users, and manage patients.</p>

                <div class="chart">
                    <canvas id="employees" style="width:100%;max-width:500px"></canvas>
                    <canvas id="patients" style="width: 100%; max-width: 500px;position:absolute;right:0;"></canvas>
                    <canvas></canvas>
                </div>  
            </div>

            <div class="addUserForm">
                <h1>Admin Panel</h1>
                <form id="addUserForm" method="POST" action="admin.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="text" name="name" id="name" placeholder="Name" required>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <select name="status" id="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <select name="role" id="role">
                        <option value="Doctor">Doctor</option>
                        <option value="Doctor_assist">Doctor assist</option>
                        <option value="Nurse">Nurse</option>
                        <option value="laboratory">laboratory</option>
                        <option value="Receptionist">Receptionist</option>
                        <option value="Admin">Admin</option>
                    </select>
                    <button type="submit">Add User</button>
                </form>
            </div>

            <div class="searchForm">
                <h2>Search Panel</h2>
                <form id="searchForm" method="POST" action="admin.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="text" name="name" id="searchName" placeholder="Search by Name">
                    <select name="status" id="searchStatus">
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <select name="role" id="searchRole">
                        <option value="">All Roles</option>
                        <option value="Doctor">Doctor</option>
                        <option value="Doctor_assist">Doctor assist</option>
                        <option value="Nurse">Nurse</option>
                        <option value="laboratory">laboratory</option>
                        <option value="Receptionist">Receptionist</option>
                        <option value="Admin">Admin</option>
                    </select>
                    <button type="submit">Search</button>
                </form>

                <table class="uptodate">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </table>
            </div>

            <div class="Users_List" id="Users_List">
                <h3>Users List</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($user = $users->fetchArray(SQLITE3_ASSOC)) : ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['status'] ?></td>
                        <td><?= $user['role'] ?></td>
                        <td>
                            <button onclick="togglePassword(<?= $user['id'] ?>, this)">Show password</button>
                            <span id="password-<?= $user['id'] ?>" style="display: none;"><?php 
                                $password = $db->querySingle("SELECT password FROM users WHERE id = " . $user['id']);
                                echo htmlspecialchars($password);
                            ?></span>
                        </td>
                        <td>
                            <button onclick="editUser(<?= $user['id'] ?>, '<?= $user['name'] ?>', '<?= $user['status'] ?>', '<?= $user['role'] ?>')">Edit</button>
                            <button onclick="deleteUser(<?= $user['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="pation" id="pation">
                <h3>Patient List</h3>
                <table>
                    <tr>
                        <th>Patient Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Condition</th>
                        <th>Phone Number</th>
                        <th>Doctor Name</th>
                        <th>Created At</th>
                        <th>Room</th>
                        <th>Actions</th>
                    </tr>
                    <tbody id="pationTable">
                        <?php while ($row = $patients->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td id="name-<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></td>
                            <td id="age-<?= $row['id'] ?>"><?= $row['age'] ?></td>
                            <td id="gender-<?= $row['id'] ?>"><?= htmlspecialchars($row['patient_gender']) ?></td>
                            <td id="condition-<?= $row['id'] ?>"><?= htmlspecialchars($row['condition']) ?></td>
                            <td id="phone-<?= $row['id'] ?>"><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td id="doctor-<?= $row['id'] ?>"><?= htmlspecialchars($row['doctor_name']) ?></td>
                            <td><?= isset($row['created_at']) ? htmlspecialchars($row['created_at']) : 'N/A' ?></td>
                            <td id="room-<?= $row['id'] ?>"><?= isset($row['room']) ? htmlspecialchars($row['room']) : 'N/A' ?></td>
                            <td>
                            <button class="delete-btn" onclick="deletePation(<?= $row['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="doctorAssist" id="doctorAssist">
                <h2>Doctor Assist</h2>
                <form action="admin.php" method="post" id="addassist">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <select name="Doctor_name" id="Doctor_name">
                    <option value="" disabled selected>Select Doctor</option>
                    </select>
                    <select name="assist_name" id="assist_name">
                        <option value="" disabled selected>Select Assist name</option>
                    </select>
                    <button>Add the assist</button>
                </form>
                <h3>Doctor Assist List</h3>
                <table>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Assist Name</th>
                        <th>Actions</th>
                    </tr>
                    <tbody id="assistTable">
                        <?php while ($row = $doctorAssists->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td id="doctor-<?= $row['id']?>"><?= htmlspecialchars($row['name_doctor'])?></td>
                            <td id="assist-<?= $row['id']?>"><?= htmlspecialchars($row['name_assist'])?></td>
                            <td>
                            <button class="delete-btn" onclick="deleteAssist(<?= $row['id']?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile;?>
                    </tbody>
                </table>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="chart.js"></script>
    <script src="admin.js"></script>
    <script src="../js/logout.js"></script>
    <script src="../js/password.js"></script>
</body>
</html>