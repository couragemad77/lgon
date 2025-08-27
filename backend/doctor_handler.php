<?php
/* gutu-hospital/backend/doctor_handler.php */

require_once "config.php";
session_start();

// Ensure a user is logged in to access this file
if (!isset($_SESSION["loggedin"])) {
    header("location: /gutu-hospital/index.php");
    exit;
}

// --- MAIN LOGIC ROUTER ---
// Check if the request is a POST request and an action is specified
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ROUTE 1: Actions for RECEPTIONISTS ---
    if ($action == 'add_doctor') {
        // Security Check: Only allow receptionists for this action
        if ($_SESSION["role"] != 'receptionist') {
            die("Access Denied.");
        }
        
        // --- Code to add a new doctor (your original code) ---
        $fullName = trim($_POST['fullName']);
        $email = trim($_POST['email']);
        $specialization = trim($_POST['specialization']);
        $department = trim($_POST['department']);
        $roomNumber = trim($_POST['roomNumber']);
        $contactNumber = trim($_POST['contactNumber']);
        
        $default_password = "doc@gutu";
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        $role = 'doctor';
        
        $profilePictureUrl = null;
        if (isset($_FILES["profilePicture"]) && $_FILES["profilePicture"]["error"] == 0) {
            $target_dir = "../assets/images/profile_pictures/";
            $file_extension = pathinfo($_FILES["profilePicture"]["name"], PATHINFO_EXTENSION);
            $target_file = $target_dir . uniqid('doc_', true) . '.' . $file_extension;
            if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $target_file)) {
                $profilePictureUrl = str_replace('../', '/', $target_file);
            }
        }

        $sql_user = "INSERT INTO users (fullName, email, password, role, must_change_password) VALUES (?, ?, ?, ?, 1)";
        if ($stmt_user = mysqli_prepare($link, $sql_user)) {
            mysqli_stmt_bind_param($stmt_user, "ssss", $fullName, $email, $hashed_password, $role);
            if (mysqli_stmt_execute($stmt_user)) {
                $user_id = mysqli_insert_id($link);
                $sql_doctor = "INSERT INTO doctors (user_id, profilePictureUrl, specialization, departments, contactNumber, roomNumber) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_doctor = mysqli_prepare($link, $sql_doctor)) {
                    mysqli_stmt_bind_param($stmt_doctor, "isssss", $user_id, $profilePictureUrl, $specialization, $department, $contactNumber, $roomNumber);
                    if (mysqli_stmt_execute($stmt_doctor)) {
                        header("location: /gutu-hospital/receptionist/manage-doctors.php?success=true");
                        exit();
                    }
                }
            }
        }
        // If we reach here, something went wrong
        echo "Error: An unexpected error occurred while adding the doctor.";
        exit();
    }

    if ($action == 'update_doctor') {
        // Security Check: Only allow receptionists for this action
        if ($_SESSION["role"] != 'receptionist') {
            die("Access Denied.");
        }

        // 1. Get all the data from the form submission
        $user_id = $_POST['user_id'];
        $fullName = trim($_POST['fullName']);
        $specialization = trim($_POST['specialization']);
        $department = trim($_POST['department']);
        $roomNumber = trim($_POST['roomNumber']);
        $contactNumber = trim($_POST['contactNumber']);

        // 2. Start a transaction
        mysqli_begin_transaction($link);

        try {
            // 3. Update the 'users' table
            $sql_user = "UPDATE users SET fullName = ? WHERE id = ?";
            $stmt_user = mysqli_prepare($link, $sql_user);
            mysqli_stmt_bind_param($stmt_user, "si", $fullName, $user_id);
            mysqli_stmt_execute($stmt_user);

            // 4. Update the 'doctors' table
            $sql_doctor = "UPDATE doctors SET specialization = ?, departments = ?, contactNumber = ?, roomNumber = ? WHERE user_id = ?";
            $stmt_doctor = mysqli_prepare($link, $sql_doctor);
            mysqli_stmt_bind_param($stmt_doctor, "ssssi", $specialization, $department, $contactNumber, $roomNumber, $user_id);
            mysqli_stmt_execute($stmt_doctor);
            
            // 5. If both queries were successful, commit the transaction
            mysqli_commit($link);
            
            // 6. Redirect back with a success message
            header("location: /gutu-hospital/receptionist/manage-doctors.php?success=updated");
            exit();

        } catch (mysqli_sql_exception $exception) {
            // 7. If any query fails, roll back the transaction
            mysqli_rollback($link);
            die("Error: Failed to update doctor details. " . $exception->getMessage());
        }
    }

    // --- ROUTE 2: Actions for DOCTORS ---
    if ($action == 'approve_appointment' || $action == 'decline_appointment') {
        // Security Check: Only allow doctors for these actions
        if ($_SESSION["role"] != 'doctor') {
            http_response_code(403);
            echo json_encode(["message" => "Access denied."]);
            exit;
        }

        $appointmentId = $_POST['appointment_id'];
        $sql = ""; // Initialize SQL variable

        if ($action == 'approve_appointment') {
            $qrCodeData = $appointmentId . '-' . bin2hex(random_bytes(10));
            $sql = "UPDATE appointments SET status = 'approved', qrCodeData = ? WHERE id = ?";
        } else { // 'decline_appointment'
            $sql = "UPDATE appointments SET status = 'declined' WHERE id = ?";
        }

        if ($stmt = mysqli_prepare($link, $sql)) {
            if ($action == 'approve_appointment') {
                mysqli_stmt_bind_param($stmt, "si", $qrCodeData, $appointmentId);
            } else {
                mysqli_stmt_bind_param($stmt, "i", $appointmentId);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                http_response_code(200);
                echo json_encode(["message" => "Action successful."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Database update failed."]);
            }
            mysqli_stmt_close($stmt);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Database prepare statement failed."]);
        }
        exit();
    }
}

mysqli_close($link);
?>