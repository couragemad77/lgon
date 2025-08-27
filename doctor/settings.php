<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// Fetch the current doctor's data from both users and doctors tables
$user_id = $_SESSION['id'];
$sql = "SELECT u.fullName, u.email, d.contactNumber, d.roomNumber 
        FROM users u
        JOIN doctors d ON u.id = d.user_id
        WHERE u.id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doctor = mysqli_fetch_assoc($result);
?>

<h1>Settings</h1>
<p>Manage your personal information and password.</p>

<!-- Display Success/Error Messages -->
<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
            if($_GET['success'] == 'profile_updated') echo 'Your profile has been updated successfully!';
            if($_GET['success'] == 'password_changed') echo 'Your password has been changed successfully!';
        ?>
    </div>
<?php elseif(isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php 
            if($_GET['error'] == 'password_mismatch') echo 'The new passwords did not match.';
            if($_GET['error'] == 'current_password_incorrect') echo 'The current password you entered was incorrect.';
            if($_GET['error'] == 'db_error') echo 'A database error occurred. Please try again.';
        ?>
    </div>
<?php endif; ?>

<div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Profile Information Form -->
    <div class="card">
        <h3>Update Profile Information</h3>
        <form action="../backend/settings_handler.php" method="POST">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" name="fullName" id="fullName" value="<?php echo htmlspecialchars($doctor['fullName']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address (Cannot be changed)</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="contactNumber">Contact Number</label>
                <input type="tel" name="contactNumber" id="contactNumber" value="<?php echo htmlspecialchars($doctor['contactNumber']); ?>" required>
            </div>
            <div class="form-group">
                <label for="roomNumber">Room Number</label>
                <input type="text" name="roomNumber" id="roomNumber" value="<?php echo htmlspecialchars($doctor['roomNumber']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="card">
        <h3>Change Password</h3>
        <form action="../backend/settings_handler.php" method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label for="currentPassword">Current Password</label>
                <input type="password" name="currentPassword" id="currentPassword" required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <input type="password" name="newPassword" id="newPassword" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="confirmNewPassword">Confirm New Password</label>
                <input type="password" name="confirmNewPassword" id="confirmNewPassword" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

<?php require_once '../templates/dashboard_footer.php'; ?>
