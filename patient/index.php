<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

$patientId = $_SESSION['id'];

// Query 1: Get all upcoming APPROVED appointments
$approved_sql = "SELECT a.id, a.appointmentDateTime, a.qrCodeData, a.checkin_code, u.fullName AS doctorName, doc.departments
                 FROM appointments a 
                 JOIN doctors doc ON a.doctorId = doc.id 
                 JOIN users u ON doc.user_id = u.id 
                 WHERE a.patientId = ? AND a.status = 'approved' AND a.appointmentDateTime >= NOW() 
                 ORDER BY a.appointmentDateTime ASC";
$stmt_approved = mysqli_prepare($link, $approved_sql);
mysqli_stmt_bind_param($stmt_approved, "i", $patientId);
mysqli_stmt_execute($stmt_approved);
$approved_appointments = mysqli_fetch_all(mysqli_stmt_get_result($stmt_approved), MYSQLI_ASSOC);

// Query 2: Get all PENDING appointments
$pending_sql = "SELECT a.id, a.appointmentDateTime, u.fullName AS doctorName 
                FROM appointments a 
                JOIN doctors doc ON a.doctorId = doc.id 
                JOIN users u ON doc.user_id = u.id 
                WHERE a.patientId = ? AND a.status = 'pending' 
                ORDER BY a.createdAt DESC";
$stmt_pending = mysqli_prepare($link, $pending_sql);
mysqli_stmt_bind_param($stmt_pending, "i", $patientId);
mysqli_stmt_execute($stmt_pending);
$pending_appointments = mysqli_fetch_all(mysqli_stmt_get_result($stmt_pending), MYSQLI_ASSOC);

// Query 3: Get the most recent DECLINED appointment for a status message
$declined_sql = "SELECT 1 FROM appointments WHERE patientId = ? AND status = 'declined' ORDER BY createdAt DESC LIMIT 1";
$stmt_declined = mysqli_prepare($link, $declined_sql);
mysqli_stmt_bind_param($stmt_declined, "i", $patientId);
mysqli_stmt_execute($stmt_declined);
$declined_appointment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_declined));
?>

<style>
    .qrcode-container { padding: 10px; background: white; width: 150px; height: 150px; margin: 0 auto; border-radius: 8px; }
    .qrcode-container canvas { width: 100% !important; height: 100% !important; }
    .qr-column { text-align: center; }
    .checkin-code-display { margin-top: 1rem; }
    .checkin-code-display span {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--primary-color);
        letter-spacing: 3px;
        background-color: #f0f4f8;
        padding: 5px 15px;
        border-radius: 5px;
        border: 1px dashed var(--accent-color);
    }
</style>

<h1>Patient Dashboard</h1>
<p>Welcome, <strong><?php echo htmlspecialchars($_SESSION["fullName"]); ?>!</strong> Here is a summary of your appointments.</p>

<!-- 1. Approved Appointments Section -->
<div class="card" style="margin-top: 2rem;">
    <h3>Upcoming Approved Appointments</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Department</th>
                <th>Date & Time</th>
                <th class="qr-column">QR Code</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($approved_appointments)): ?>
                <tr><td colspan="4">You have no upcoming approved appointments.</td></tr>
            <?php else: ?>
                <?php foreach ($approved_appointments as $appt): ?>
                    <tr>
                        <td>Dr. <?php echo htmlspecialchars($appt['doctorName']); ?></td>
                        <td><?php echo htmlspecialchars($appt['departments']); ?></td>
                        <td><?php echo date("l, M j, Y, g:i A", strtotime($appt['appointmentDateTime'])); ?></td>
                        <td class="qr-column">
                            <div class="qrcode-container" id="qrcode-<?php echo $appt['id']; ?>" data-qrdata="<?php echo htmlspecialchars($appt['qrCodeData']); ?>"></div>
                            <?php if (!empty($appt['checkin_code'])): ?>
                                <div class="checkin-code-display">
                                    <p style="margin-bottom: 5px;">Or use this code:</p>
                                    <span><?php echo htmlspecialchars($appt['checkin_code']); ?></span>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 2. Pending Appointments Section -->
<div class="card" style="margin-top: 2rem;">
    <h3>Pending Appointment Requests</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Requested Date & Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending_appointments)): ?>
                <tr><td colspan="3">You have no pending appointment requests.</td></tr>
            <?php else: ?>
                <?php foreach ($pending_appointments as $appt): ?>
                    <tr>
                        <td>Dr. <?php echo htmlspecialchars($appt['doctorName']); ?></td>
                        <td><?php echo date("l, M j, Y, g:i A", strtotime($appt['appointmentDateTime'])); ?></td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 3. Status Message for Declined Appointments -->
<?php if ($declined_appointment): ?>
<div class="card" style="margin-top: 2rem; border-left: 5px solid var(--error-color);">
    <p>Please note, one or more of your past appointment requests were declined. If you still need to see a doctor, please book a new appointment.</p>
    <a href="book-appointment.php" class="btn btn-primary">Book a New Appointment</a>
</div>
<?php endif; ?>

<!-- Include Libraries and the main patient JS file -->
<script src="/gutu-hospital/assets/js/libraries/jquery.min.js"></script>
<script src="/gutu-hospital/assets/js/libraries/qrcode.min.js"></script>
<script src="/gutu-hospital/assets/js/patient_dashboard.js"></script>

<?php require_once '../templates/dashboard_footer.php'; ?>