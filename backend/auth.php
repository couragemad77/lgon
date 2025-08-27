<?php
/* gutu-hospital/backend/auth.php */

// Start session to manage user login state
session_start();

// Include our database connection file
require_once "config.php";

// Check if the request is a POST request (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST['action'] ?? '';

    // --- SIGNUP LOGIC ---
    if ($action == 'signup') {
        // ... (The signup code you already have remains unchanged)
        $fullName = trim($_POST['fullName']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirmPassword']);
        $dateOfBirth = trim($_POST['dateOfBirth']);
        $phoneNumber = trim($_POST['phoneNumber']);
        $address = trim($_POST['address']);
        $gender = trim($_POST['gender']);
        $emergencyContactName = trim($_POST['emergencyContactName']);
        $emergencyContactPhone = trim($_POST['emergencyContactPhone']);
        
        if ($password !== $confirmPassword) {
            die("Error: Passwords do not match.");
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'patient';

        $sql = "INSERT INTO users (fullName, email, password, role, dateOfBirth, phoneNumber, address, gender, emergencyContactName, emergencyContactPhone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssss", $fullName, $email, $hashed_password, $role, $dateOfBirth, $phoneNumber, $address, $gender, $emergencyContactName, $emergencyContactPhone);
            if (mysqli_stmt_execute($stmt)) {
                header("location: /gutu-hospital/index.php?signup=success");
                exit();
            } else {
                echo "Error: Something went wrong or the email is already registered.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // --- LOGIN LOGIC (NEW) ---
    if ($action == 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);

        // Prepare a select statement to include the new flag
        $sql = "SELECT id, fullName, email, password, role, must_change_password FROM users WHERE email = ? AND role = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                // Check if user exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $fullName, $db_email, $hashed_password, $db_role, $must_change_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["fullName"] = $fullName;
                            $_SESSION["role"] = $db_role;                            
                            
                            // NEW: Check if doctor must change password
                            if ($db_role == 'doctor' && $must_change_password) {
                                header("location: /gutu-hospital/doctor/force_password_change.php");
                                exit();
                            }

                            // Redirect user to their respective dashboard
                            header("location: /gutu-hospital/" . $db_role . "/index.php");
                            exit();
                        } else {
                            // Password is not valid
                            header("location: /gutu-hospital/index.php?error=invalidpassword");
                        }
                    }
                } else {
                    // Username doesn't exist
                    header("location: /gutu-hospital/index.php?error=nouser");
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// --- LOGOUT LOGIC (NEW) ---
// Check if the 'action' parameter is 'logout' in the URL
if(isset($_GET['action']) && $_GET['action'] == 'logout'){
    // Unset all of the session variables
    $_SESSION = array();
 
    // Destroy the session.
    session_destroy();
 
    // Redirect to login page
    header("location: /gutu-hospital/index.php");
    exit;
}

mysqli_close($link);
?>