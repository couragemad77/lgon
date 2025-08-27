<?php
session_start();
// Redirect if user is not logged in, not a doctor, or doesn't need to change password
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] != 'doctor') {
    header("location: /gutu-hospital/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password - Gutu Rural Hospital</title>
    <!-- Link the main stylesheet for variables and basic styles -->
    <link rel="stylesheet" href="/gutu-hospital/assets/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: var(--primary-color);
        }
        .change-password-container {
            width: 100%;
            max-width: 450px;
            padding: 2rem 3rem;
            background-color: var(--secondary-color);
            border-radius: 12px;
            text-align: center;
        }
        .change-password-container h1 {
            color: var(--accent-color);
        }
    </style>
</head>
<body>

    <div class="change-password-container">
        <h1>Create a New Password</h1>
        <p>For security, you must create a new password before you can access your dashboard.</p>

        <!-- Display Error Messages -->
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger" style="margin-top: 1rem;">
                <?php 
                    if($_GET['error'] == 'password_mismatch') echo 'The new passwords did not match. Please try again.';
                    if($_GET['error'] == 'db_error') echo 'A database error occurred. Please try again.';
                ?>
            </div>
        <?php endif; ?>

        <form action="../backend/settings_handler.php" method="POST" style="margin-top: 1.5rem;">
            <!-- This action name needs to be different from the regular password change -->
            <input type="hidden" name="action" value="force_change_password">
            
            <div class="form-group">
                <label for="newPassword" style="text-align: left;">New Password</label>
                <input type="password" name="newPassword" id="newPassword" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="confirmNewPassword" style="text-align: left;">Confirm New Password</label>
                <input type="password" name="confirmNewPassword" id="confirmNewPassword" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Set New Password</button>
        </form>
    </div>

</body>
</html>
