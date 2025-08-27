<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// Ensure the user is a doctor
if ($_SESSION["role"] != 'doctor') {
    // Redirect them to their own dashboard if they somehow get here
    header("location: /gutu-hospital/" . $_SESSION["role"] . "/index.php");
    exit;
}

// Get the doctor's primary key from the 'doctors' table, which is needed for queries.
// We get this by using the logged-in user's ID from the session.
$doctor_user_id = $_SESSION['id'];
$doc_query = mysqli_query($link, "SELECT id FROM doctors WHERE user_id = {$doctor_user_id}");
$doctor_data = mysqli_fetch_assoc($doc_query);
$doctor_table_id = $doctor_data['id'];

// Fetch pending appointment requests for THIS doctor
$pending_sql = "SELECT a.id, u.fullName AS patientName, a.department, a.appointmentDateTime 
                FROM appointments a 
                JOIN users u ON a.patientId = u.id 
                WHERE a.doctorId = ? AND a.status = 'pending'
                ORDER BY a.appointmentDateTime ASC";
$stmt_pending = mysqli_prepare($link, $pending_sql);
mysqli_stmt_bind_param($stmt_pending, "i", $doctor_table_id);
mysqli_stmt_execute($stmt_pending);
$pending_result = mysqli_stmt_get_result($stmt_pending);
$pending_appointments = mysqli_fetch_all($pending_result, MYSQLI_ASSOC);

// Fetch checked-in patients for THIS doctor (Live Queue)
$queue_sql = "SELECT u.fullName AS patientName 
              FROM appointments a 
              JOIN users u ON a.patientId = u.id 
              WHERE a.doctorId = ? AND a.status = 'checked-in'
              ORDER BY a.appointmentDateTime ASC"; // The earliest checked-in is first
$stmt_queue = mysqli_prepare($link, $queue_sql);
mysqli_stmt_bind_param($stmt_queue, "i", $doctor_table_id);
mysqli_stmt_execute($stmt_queue);
$queue_result = mysqli_stmt_get_result($stmt_queue);
$live_queue = mysqli_fetch_all($queue_result, MYSQLI_ASSOC);
?>

<h1>Doctor Dashboard</h1>
<p>Welcome, Dr. <strong><?php echo htmlspecialchars($_SESSION["fullName"]); ?>!</strong></p>

<div class="dashboard-grid">
    <!-- Appointment Requests Section -->
    <div class="card">
        <h3>Pending Appointment Requests</h3>
        <table class="data-table" id="pending-requests-table">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Date & Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_appointments)): ?>
                    <tr>
                        <td colspan="3">No pending requests.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pending_appointments as $appt): ?>
                        <tr id="appointment-row-<?php echo $appt['id']; ?>">
                            <td><?php echo htmlspecialchars($appt['patientName']); ?></td>
                            <td><?php echo date("D, M j, Y, g:i A", strtotime($appt['appointmentDateTime'])); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm approve-btn" data-appid="<?php echo $appt['id']; ?>">Approve</button>
                                <button class="btn btn-danger btn-sm decline-btn" data-appid="<?php echo $appt['id']; ?>">Decline</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Live Patient Queue Section -->
    <div class="card">
        <h3>Live Patient Queue</h3>
        <?php if (empty($live_queue)): ?>
            <p>No patients are currently checked in.</p>
        <?php else: ?>
            <ol class="live-queue-list">
                <?php foreach ($live_queue as $patient): ?>
                    <li><?php echo htmlspecialchars($patient['patientName']); ?></li>
                <?php endforeach; ?>
            </ol>
            <button id="call-next-btn" class="btn btn-primary" style="width:100%; margin-top: 1rem;">Call Next Patient</button>
        <?php endif; ?>
    </div>
</div>

<style>
    .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
    .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
    .btn-danger { background-color: var(--error-color); color: white; }
    .live-queue-list { padding-left: 20px; }
    .live-queue-list li { font-size: 1.1rem; margin-bottom: 0.5rem; }
    @media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }
</style>

<!-- Link to the doctor's specific JS file -->
 
<script src="/gutu-hospital/assets/js/libraries/jquery.min.js"></script>
<script src="/gutu-hospital/assets/js/doctor_dashboard.js"></script>

<?php require_once '../templates/dashboard_footer.php'; ?>