<?php
require_once '../templates/dashboard_header.php';
require_once '../backend/config.php';

// --- DATA FETCHING FOR DASHBOARD ---

// 1. KPI: Total Appointments for Today
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');
$kpi_total_sql = "SELECT COUNT(id) AS total FROM appointments WHERE appointmentDateTime BETWEEN ? AND ?";
$stmt_total = mysqli_prepare($link, $kpi_total_sql);
mysqli_stmt_bind_param($stmt_total, "ss", $today_start, $today_end);
mysqli_stmt_execute($stmt_total);
$kpi_total_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total));
$total_appointments_today = $kpi_total_result['total'];

// 2. KPI: Patients Checked-In Today
$kpi_checkedin_sql = "SELECT COUNT(id) AS total FROM appointments WHERE status = 'checked-in' AND appointmentDateTime BETWEEN ? AND ?";
$stmt_checkedin = mysqli_prepare($link, $kpi_checkedin_sql);
mysqli_stmt_bind_param($stmt_checkedin, "ss", $today_start, $today_end);
mysqli_stmt_execute($stmt_checkedin);
$kpi_checkedin_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_checkedin));
$patients_checked_in_today = $kpi_checkedin_result['total'];

// 3. KPI: Pending Approvals (All-time)
$kpi_pending_sql = "SELECT COUNT(id) AS total FROM appointments WHERE status = 'pending'";
$kpi_pending_result = mysqli_fetch_assoc(mysqli_query($link, $kpi_pending_sql));
$pending_approvals = $kpi_pending_result['total'];

// 4. Live Appointments Table: Fetch all of today's appointments
$live_appts_sql = "SELECT 
                        a.id, 
                        a.appointmentDateTime, 
                        a.status, 
                        p.fullName AS patientName, 
                        d_user.fullName AS doctorName
                   FROM appointments a
                   JOIN users p ON a.patientId = p.id
                   JOIN doctors doc ON a.doctorId = doc.id
                   JOIN users d_user ON doc.user_id = d_user.id
                   WHERE a.appointmentDateTime BETWEEN ? AND ?
                   ORDER BY a.appointmentDateTime ASC";
$stmt_live = mysqli_prepare($link, $live_appts_sql);
mysqli_stmt_bind_param($stmt_live, "ss", $today_start, $today_end);
mysqli_stmt_execute($stmt_live);
$live_appointments = mysqli_fetch_all(mysqli_stmt_get_result($stmt_live), MYSQLI_ASSOC);
?>

<style>
    .kpi-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .kpi-cards .card .value { font-size: 2.5rem; font-weight: bold; color: var(--accent-color); margin-top: 0.5rem; }
    #qr-reader { width: 100%; border: 2px dashed var(--accent-color); border-radius: 8px; }
    #qr-reader-results { margin-top: 1rem; }
    .status-badge { padding: 0.3rem 0.7rem; border-radius: 15px; font-size: 0.8rem; color: white; }
    .status-approved { background-color: #28a745; }
    .status-pending { background-color: #ffc107; color: #333; }
    .status-checked-in { background-color: var(--accent-color); }
    .status-completed { background-color: #6c757d; }
    .status-declined { background-color: #dc3545; }
</style>

<h1>Receptionist Dashboard</h1>
<p>Welcome, <strong><?php echo htmlspecialchars($_SESSION["fullName"]); ?>!</strong> Get a real-time overview of the hospital's activity below.</p>

<!-- KPI Cards Section -->
<div class="kpi-cards">
    <div class="card">
        <h3>Total Appointments Today</h3>
        <p class="value"><?php echo $total_appointments_today; ?></p>
    </div>
    <div class="card">
        <h3>Patients Checked-In</h3>
        <p class="value"><?php echo $patients_checked_in_today; ?></p>
    </div>
    <div class="card">
        <h3>Pending Approvals</h3>
        <p class="value"><?php echo $pending_approvals; ?></p>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
    
    <!-- Live Appointments Table -->
    <div class="card">
        <h3>Today's Live Appointments</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($live_appointments)): ?>
                    <tr><td colspan="4">No appointments scheduled for today.</td></tr>
                <?php else: ?>
                    <?php foreach ($live_appointments as $appt): ?>
                        <tr>
                            <td><?php echo date("g:i A", strtotime($appt['appointmentDateTime'])); ?></td>
                            <td><?php echo htmlspecialchars($appt['patientName']); ?></td>
                            <td>Dr. <?php echo htmlspecialchars($appt['doctorName']); ?></td>
                            <td><span class="status-badge status-<?php echo strtolower(htmlspecialchars($appt['status'])); ?>"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- QR Scanner Section -->
    <div class="card">
        <h3><i class="fas fa-id-card"></i> Patient Check-in</h3>
        <p>Scan the patient's QR code to check them into the queue.</p>
        <button id="scan-qr-btn" class="btn btn-primary" style="width: 100%;"><i class="fas fa-qrcode"></i> Scan Patient QR Code</button>

        <hr style="margin: 1.5rem 0;">

        <p>Alternatively, enter the 6-digit code provided to the patient.</p>
        <form id="code-checkin-form">
            <div class="form-group">
                <input type="text" id="checkin-code-input" class="form-control" placeholder="Enter 6-Digit Code" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-secondary" style="width: 100%;"><i class="fas fa-keyboard"></i> Check-in with Code</button>
        </form>
        <div id="qr-reader-results" class="alert" style="display:none; margin-top: 1rem;"></div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal-overlay" id="qr-scanner-modal">
    <div class="modal-container" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Scan QR Code</h2>
        </div>
        <div id="qr-reader"></div>
    </div>
</div>
<script src="/gutu-hospital/assets/js/libraries/jquery.min.js"></script>
<!-- QR Scanner Library -->
<script src="/gutu-hospital/assets/js/html5-qrcode.min.js"></script>

<!-- Custom JS for this page -->
<script src="/gutu-hospital/assets/js/receptionist_dashboard.js"></script>

<?php require_once '../templates/dashboard_footer.php'; ?>
