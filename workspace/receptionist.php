<?php
// Connect to SQLite database
$db = new SQLite3('../database/clinic.db');

// Create table if not exists
$db->exec("CREATE TABLE IF NOT EXISTS patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    age INTEGER,
    condition TEXT,
    phone_number TEXT,
    doctor_name TEXT,
    patient_gender TEXT,
    room TEXT,
    created_at TEXT
)");

// Handle form submission for adding a new patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['patient_name'];
    $age = $_POST['patient_age'];
    $condition = $_POST['condition'];
    $phone_number = $_POST['phone_number'];
    $doctor_name = $_POST['doctor_name'];
    $patient_gender = $_POST['patient_gender'];
    $room = $_POST['room'];

    $stmt = $db->prepare("INSERT INTO patients (name, age, condition, phone_number, doctor_name, patient_gender, room, created_at) VALUES (:name, :age, :condition, :phone_number, :doctor_name, :patient_gender, :room, :created_at)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':age', $age, SQLITE3_INTEGER);
    $stmt->bindValue(':condition', $condition, SQLITE3_TEXT);
    $stmt->bindValue(':phone_number', $phone_number, SQLITE3_TEXT);
    $stmt->bindValue(':doctor_name', $doctor_name, SQLITE3_TEXT);
    $stmt->bindValue(':patient_gender', $patient_gender, SQLITE3_TEXT);
    $stmt->bindValue(':created_at', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(':room', $room, SQLITE3_TEXT);
    $stmt->execute();
}

// Handle AJAX request for updating a patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['patient_id'];
    $name = $_POST['patient_name'];
    $age = $_POST['patient_age'];
    $condition = $_POST['condition'];
    $phone_number = $_POST['phone_number'];
    $doctor_name = $_POST['doctor_name'];
    $patient_gender = $_POST['patient_gender'];
    $room = $_POST['room'];

    $stmt = $db->prepare("UPDATE patients SET name=:name, age=:age, condition=:condition, phone_number=:phone_number, doctor_name=:doctor_name, patient_gender=:patient_gender, room=:room WHERE id=:id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':age', $age, SQLITE3_INTEGER);
    $stmt->bindValue(':condition', $condition, SQLITE3_TEXT);
    $stmt->bindValue(':phone_number', $phone_number, SQLITE3_TEXT);
    $stmt->bindValue(':doctor_name', $doctor_name, SQLITE3_TEXT);
    $stmt->bindValue(':patient_gender', $patient_gender, SQLITE3_TEXT);
    $stmt->bindValue(':room', $room, SQLITE3_TEXT);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

// Handle AJAX request for deleting a patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['patient_id'];
    $stmt = $db->prepare("DELETE FROM patients WHERE id=:id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

// Fetch patients
$patients = $db->query("SELECT * FROM patients ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATOM Receptionist</title>
    <link rel="stylesheet" href="../style/registration.css">
    <link rel="shortcut icon" href="../logo/image.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle update button click
            $('.update-btn').on('click', function(e) {
                e.preventDefault();
                const patientId = $(this).data('id');
                const patientName = prompt("Enter new name:", $(`#name-${patientId}`).text());
                const patientAge = prompt("Enter new age:", $(`#age-${patientId}`).text());
                const patientGender = prompt("Enter new gender:", $(`#gender-${patientId}`).text());
                const patientCondition = prompt("Enter new condition:", $(`#condition-${patientId}`).text());
                const patientPhone = prompt("Enter new phone number:", $(`#phone-${patientId}`).text());
                const doctorName = prompt("Enter new doctor name:", $(`#doctor-${patientId}`).text());
                const room = prompt("Enter new room:", $(`#room-${patientId}`).text());

                $.post('', {
                    action: 'update',
                    patient_id: patientId,
                    patient_name: patientName,
                    patient_age: patientAge,
                    patient_gender: patientGender,
                    condition: patientCondition,
                    phone_number: patientPhone,
                    doctor_name: doctorName,
                    room: room
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }, 'json');
            });

            // Handle delete button click
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                if (confirm("Are you sure you want to delete this patient?")) {
                    const patientId = $(this).data('id');
                    $.post('', { action: 'delete', patient_id: patientId }, function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }, 'json');
                }
            });
        });
    </script>
</head>
<body>
    <div class="navbar">
        <ul>
            <li><a href="#" onclick="location.replace('../index.html'); return false;">Home</a></li>
            <li><a href="patient.html">patient</a></li>
            <li><a href="archive.html" target="_parent">archive</a></li>
        </ul>
        <img src="../logo/image.png" alt=""onclick="location.replace('../index.html'); return false;" >
    </div>
    <div class="container">
        <div class="form">
            <h2>Patient Registration</h2>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <input type="text" name="patient_name" placeholder="Patient Name" required>
                <input type="number" name="patient_age" placeholder="Patient Age" required>
                <select name="patient_gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" name="phone_number" placeholder="Patient Phone Number" required>
                <input type="text" name="condition" placeholder="Condition/Disease">
                <input type="number" name="room" placeholder="Room">
                <input type="text" name="doctor_name" placeholder="Doctor Name">
                <button type="submit">Register</button>
            </form>
        </div>

        <div class="list">
            <h3>Registered Patients</h3>
            <table>
                <thead>
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
                </thead>
                <tbody>
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
                                <button class="update-btn" data-id="<?= $row['id'] ?>">Update</button>
                                <button class="delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
