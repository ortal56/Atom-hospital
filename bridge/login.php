<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    try {
        // Connect to SQLite database
        $db = new SQLite3('../database/users.db');

        // Check if user exists
        $stmt = $db->prepare('SELECT password, role FROM users WHERE name = :name');
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);


        function get_role($user){
            if ($user['role'] == 'Admin') {
                return "admin/admin.php";
            } 
            elseif($user['role'] == 'Doctor'){
                return "workspace/patient.html";
            }
            elseif($user['role'] == 'Doctor assist'){
                return "workspace/patient.html";
            }
            elseif($user['role'] == 'Nurse'){
                return "workspace/patient.html";
            }
            elseif($user['role'] == 'receptionist'){
                return "workspace/receptionist.php";
            }else{
                return "index.html";    
            }
        }

        if ($user && $password == $user['password']) {
            echo json_encode([
                "password" => $password,
                "dir" => get_role($user)
            ]);
        } else {
            echo "Invalid username or password.";
        }

        // Close the statement and database connection
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
}
