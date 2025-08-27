<?php
/* gutu-hospital/backend/settings_handler.php */

require_once "config.php";
session_start();

// General security check: User must be logged in.
if (!isset($_SESSION["loggedin"])) {
    http_response_code(403);
    die("Authentication required.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $role = $_SESSION['role'];
    $user_id = $_SESSION['id'];
    $redirect_url = "/gutu-hospital/{$role}/settings.php";

    // --- ACTION: UPDATE USER PROFILE ---
    if ($action == 'update_profile') {
        $fullName = trim($_POST['fullName']);

        // Update the 'users' table (common for all roles)
        $sql_user = "UPDATE users SET fullName = ? WHERE id = ?";
        $stmt_user = mysqli_prepare($link, $sql_user);
        mysqli_stmt_bind_param($stmt_user, "si", $fullName, $user_id);
        mysqli_stmt_execute($stmt_user);
        $_SESSION['fullName'] = $fullName; // Update session variable

        // Role-specific updates
        if ($role == 'patient') {
            $phoneNumber = trim($_POST['phoneNumber']);
            $address = trim($_POST['address']);
            $emergencyContactName = trim($_POST['emergencyContactName']);
            $emergencyContactPhone = trim($_POST['emergencyContactPhone']);
            
            $sql_patient = "UPDATE users SET phoneNumber = ?, address = ?, emergencyContactName = ?, emergencyContactPhone = ? WHERE id = ?";
            $stmt_patient = mysqli_prepare($link, $sql_patient);
            mysqli_stmt_bind_param($stmt_patient, "ssssi", $phoneNumber, $address, $emergencyContactName, $emergencyContactPhone, $user_id);
            mysqli_stmt_execute($stmt_patient);

        } else if ($role == 'doctor') {
            $contactNumber = trim($_POST['contactNumber']);
            $roomNumber = trim($_POST['roomNumber']);

            $sql_doctor = "UPDATE doctors SET contactNumber = ?, roomNumber = ? WHERE user_id = ?";
            $stmt_doctor = mysqli_prepare($link, $sql_doctor);
            mysqli_stmt_bind_param($stmt_doctor, "ssi", $contactNumber, $roomNumber, $user_id);
            mysqli_stmt_execute($stmt_doctor);
        }
        
        header("location: {$redirect_url}?success=profile_updated");
        exit;
    }

    // --- ACTION: CHANGE USER PASSWORD ---
    if ($action == 'change_password') {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmNewPassword = $_POST['confirmNewPassword'];

        if ($newPassword !== $confirmNewPassword) {
            header("location: {$redirect_url}?error=password_mismatch");
            exit;
        }

        // Get the current hashed password from the DB
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $hashed_password = $result['password'];

        // Verify the current password
        if (password_verify($currentPassword, $hashed_password)) {
            // Hash the new password
            $new_hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($link, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "si", $new_hashed_password, $user_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                header("location: {$redirect_url}?success=password_changed");
            } else {
                header("location: {$redirect_url}?error=db_error");
            }
        } else {
            // Current password was incorrect
            header("location: {$redirect_url}?error=current_password_incorrect");
        }
        exit;
    }

    // --- ACTION: FORCE CHANGE DOCTOR PASSWORD ---
    if ($action == 'force_change_password') {
        if ($role != 'doctor') {
            http_response_code(403); die("Access Denied.");
        }

        $newPassword = $_POST['newPassword'];
        $confirmNewPassword = $_POST['confirmNewPassword'];
        $redirect_url = "/gutu-hospital/doctor/force_password_change.php";

        if ($newPassword !== $confirmNewPassword) {
            header("location: {$redirect_url}?error=password_mismatch");
            exit;
        }

        // Hash the new password
        $new_hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password and set must_change_password flag to 0
        $sql = "UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_hashed_password, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the actual dashboard upon success
            header("location: /gutu-hospital/doctor/index.php?success=password_set");
        } else {
            header("location: {$redirect_url}?error=db_error");
        }
        exit;
    }
}

mysqli_close($link);
?>
