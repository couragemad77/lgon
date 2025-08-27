<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// Ensure the user is a doctor
if ($_SESSION["role"] != 'doctor') {
    header("location: /gutu-hospital/index.php");
    exit;
}

// Get the doctor's primary key from the 'doctors' table
$doctor_user_id = $_SESSION['id'];
$doc_query = mysqli_query($link, "SELECT id FROM doctors WHERE user_id = {$doctor_user_id}");
$doctor_data = mysqli_fetch_assoc($doc_query);
$doctor_table_id = $doctor_data['id'];

// Fetch all APPROVED appointments for this doctor, ordered by date
$sql = "SELECT u.fullName AS patientName, u.phoneNumber, a.appointmentDateTime
        FROM appointments a
        JOIN users u ON a.patientId = u.id
        WHERE a.doctorId = ? AND a.status = 'approved'
        ORDER BY a.appointmentDateTime ASC";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $doctor_table_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$approved_appointments = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<h1>My Schedule</h1>
<p>Showing all of your approved upcoming appointments.</p>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Patient Name</th>
                <th>Patient Contact</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($approved_appointments)): ?>
                <tr>
                    <td colspan="3">You have no approved appointments in your schedule.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($approved_appointments as $appt): ?>
                    <tr>
                        <td><?php echo date("D, M j, Y, g:i A", strtotime($appt['appointmentDateTime'])); ?></td>
                        <td><?php echo htmlspecialchars($appt['patientName']); ?></td>
                        <td><?php echo htmlspecialchars($appt['phoneNumber']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../templates/dashboard_footer.php'; ?>