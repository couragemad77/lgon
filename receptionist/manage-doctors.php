<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php'; // For database connection

// Fetch the list of departments for the dropdown
$departments_result = mysqli_query($link, "SELECT name FROM departments ORDER BY name ASC");
$departments = mysqli_fetch_all($departments_result, MYSQLI_ASSOC);

// Fetch the list of existing doctors to display
$doctors_sql = "SELECT u.id AS user_id, u.fullName, u.email, d.specialization, d.departments, d.roomNumber 
                FROM users u 
                JOIN doctors d ON u.id = d.user_id 
                WHERE u.role = 'doctor'";
$doctors_result = mysqli_query($link, $doctors_sql);
$doctors = mysqli_fetch_all($doctors_result, MYSQLI_ASSOC);
?>

<h1>Manage Doctors</h1>

<!-- Section to Add New Doctor -->
<div class="card form-container">
    <h3>Add New Doctor</h3>
    <p>The doctor will be created with a default password of <strong>doc@gutu</strong>, which they will be required to change on first login.</p>
    
    <!-- The 'enctype' is crucial for file uploads -->
    <form action="../backend/doctor_handler.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_doctor">

        <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" name="fullName" id="fullName" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div class="form-group">
            <label for="specialization">Specialization</label>
            <input type="text" name="specialization" id="specialization" placeholder="e.g., General Physician" required>
        </div>
        <div class="form-group">
            <label for="departments">Department</label>
            <select name="department" id="departments" required>
                <option value="">Select a Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept['name']); ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="roomNumber">Room Number</label>
            <input type="text" name="roomNumber" id="roomNumber" required>
        </div>
        <div class="form-group">
            <label for="contactNumber">Contact Number</label>
            <input type="tel" name="contactNumber" id="contactNumber" required>
        </div>
        <div class="form-group">
            <label for="profilePicture">Profile Picture</label>
            <input type="file" name="profilePicture" id="profilePicture" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-primary">Add Doctor</button>
    </form>
</div>

<!-- Section to View Existing Doctors -->
<div class="card" style="margin-top: 2rem;">
    <h3>Existing Doctors</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>Department</th>
                <th>Room No.</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($doctors)): ?>
                <tr>
                    <td colspan="6">No doctors found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($doctors as $doctor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doctor['fullName']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['departments']); ?></td>
                        <td><?php echo htmlspecialchars($doctor['roomNumber']); ?></td>
                        <td>
                            <a href="edit-doctor.php?id=<?php echo $doctor['user_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../templates/dashboard_footer.php'; ?>