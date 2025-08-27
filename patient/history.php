<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// Get the logged-in patient's ID
$patientId = $_SESSION['id'];

// Fetch all historical appointments (not pending or approved)
$sql = "SELECT 
            a.appointmentDateTime, 
            a.status, 
            u.fullName AS doctorName, 
            a.department
        FROM appointments a
        JOIN doctors doc ON a.doctorId = doc.id
        JOIN users u ON doc.user_id = u.id
        WHERE a.patientId = ? AND a.status IN ('completed', 'declined', 'cancelled')
        ORDER BY a.appointmentDateTime DESC";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $patientId);
mysqli_stmt_execute($stmt);
$history = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>

<style>
    /* Re-using status badge styles from receptionist dashboard for consistency */
    .status-badge { padding: 0.3rem 0.7rem; border-radius: 15px; font-size: 0.8rem; color: white; }
    .status-completed { background-color: #6c757d; }
    .status-declined { background-color: #dc3545; }
    .status-cancelled { background-color: #ffc107; color: #333; }
</style>

<h1>Appointment History</h1>
<p>Here is a list of all your past and cancelled appointments.</p>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Doctor</th>
                <th>Department</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($history)): ?>
                <tr><td colspan="4">You have no past appointments.</td></tr>
            <?php else: ?>
                <?php foreach ($history as $appt): ?>
                    <tr>
                        <td><?php echo date("l, M j, Y, g:i A", strtotime($appt['appointmentDateTime'])); ?></td>
                        <td>Dr. <?php echo htmlspecialchars($appt['doctorName']); ?></td>
                        <td><?php echo htmlspecialchars($appt['department']); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower(htmlspecialchars($appt['status'])); ?>"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../templates/dashboard_footer.php'; ?>
