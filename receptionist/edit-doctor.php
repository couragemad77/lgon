<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// Check if an ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<h1>Error</h1><p>No doctor ID provided. Please go back to the Manage Doctors page.</p>";
    require_once '../templates/dashboard_footer.php';
    exit;
}

$doctor_user_id = $_GET['id'];

// Fetch the doctor's combined data from 'users' and 'doctors' tables
$sql = "SELECT u.fullName, u.email, d.specialization, d.departments, d.contactNumber, d.roomNumber 
        FROM users u 
        JOIN doctors d ON u.id = d.user_id 
        WHERE u.id = ? AND u.role = 'doctor'";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $doctor_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doctor = mysqli_fetch_assoc($result);

if (!$doctor) {
    echo "<h1>Error</h1><p>No doctor found with that ID.</p>";
    require_once '../templates/dashboard_footer.php';
    exit;
}

// Fetch all department names for the dropdown
$departments_result = mysqli_query($link, "SELECT name FROM departments ORDER BY name ASC");
$all_departments = mysqli_fetch_all($departments_result, MYSQLI_ASSOC);
?>

<h1>Edit Doctor Details</h1>
<p>You are editing the profile for <strong>Dr. <?php echo htmlspecialchars($doctor['fullName']); ?></strong>.</p>

<div class="card form-container">
    <form action="../backend/doctor_handler.php" method="POST">
        <input type="hidden" name="action" value="update_doctor">
        <input type="hidden" name="user_id" value="<?php echo $doctor_user_id; ?>">

        <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" name="fullName" id="fullName" value="<?php echo htmlspecialchars($doctor['fullName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address (Cannot be changed)</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="specialization">Specialization</label>
            <input type="text" name="specialization" id="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
        </div>
        <div class="form-group">
            <label for="departments">Department</label>
            <select name="department" id="departments" required>
                <?php foreach ($all_departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept['name']); ?>" <?php echo ($dept['name'] == $doctor['departments']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="roomNumber">Room Number</label>
            <input type="text" name="roomNumber" id="roomNumber" value="<?php echo htmlspecialchars($doctor['roomNumber']); ?>" required>
        </div>
        <div class="form-group">
            <label for="contactNumber">Contact Number</label>
            <input type="tel" name="contactNumber" id="contactNumber" value="<?php echo htmlspecialchars($doctor['contactNumber']); ?>" required>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="manage-doctors.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../templates/dashboard_footer.php'; ?>
