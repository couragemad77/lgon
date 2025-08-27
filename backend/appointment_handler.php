<?php
/* gutu-hospital/backend/appointment_handler.php */

require_once "config.php";
session_start();

// General security check: User must be logged in to use this handler.
if (!isset($_SESSION["loggedin"])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $role = $_SESSION['role'];
    $user_id = $_SESSION['id'];

    // =================================================================
    // --- PATIENT-SPECIFIC ACTIONS ---
    // =================================================================
    if ($action == 'get_available_slots') {
        if ($role != 'patient') {
            http_response_code(403); exit;
        }
        // ... (logic is unchanged)
        $doctorId = $_POST['doctorId'];
        $date = $_POST['date'];
        $startTime = new DateTime('09:00');
        $endTime = new DateTime('17:00');
        $interval = new DateInterval('PT30M');
        $sql = "SELECT appointmentDateTime FROM appointments WHERE doctorId = ? AND DATE(appointmentDateTime) = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "is", $doctorId, $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $bookedSlots = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $bookedSlots[] = (new DateTime($row['appointmentDateTime']))->format('H:i');
        }
        mysqli_stmt_close($stmt);
        $allSlots = [];
        $period = new DatePeriod($startTime, $interval, $endTime);
        foreach ($period as $time) {
            $allSlots[] = $time->format('H:i');
        }
        $availableSlots = array_diff($allSlots, $bookedSlots);
        header('Content-Type: application/json');
        echo json_encode(array_values($availableSlots));
        exit;
    }

    if ($action == 'book_appointment') {
        if ($role != 'patient') {
            http_response_code(403); exit;
        }
        // ... (logic is unchanged)
        $patientId = $user_id;
        $doctorId = $_POST['doctorId'];
        $department = $_POST['department'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $appointmentDateTime = $date . ' ' . $time;
        $sql = "INSERT INTO appointments (patientId, doctorId, department, appointmentDateTime, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "iiss", $patientId, $doctorId, $department, $appointmentDateTime);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Appointment requested successfully!"]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error: Could not book appointment."]);
        }
        exit;
    }

    // =================================================================
    // --- DOCTOR-SPECIFIC ACTIONS ---
    // =================================================================
    if ($action == 'update_appointment_status') {
        if ($role != 'doctor') {
            http_response_code(403); exit;
        }
        $appointmentId = $_POST['appointment_id'];
        $newStatus = $_POST['status']; // 'approved' or 'declined'

        $qrCodeData = null;
        if ($newStatus == 'approved') {
            // Generate a unique string for the QR code
            $qrCodeData = uniqid('GUTU_APPT_', true);
            $sql = "UPDATE appointments SET status = ?, qrCodeData = ? WHERE id = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $newStatus, $qrCodeData, $appointmentId);
        } else {
            $sql = "UPDATE appointments SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "si", $newStatus, $appointmentId);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            // Create a notification for the patient
            $notif_sql = "SELECT patientId FROM appointments WHERE id = ?";
            $notif_stmt = mysqli_prepare($link, $notif_sql);
            mysqli_stmt_bind_param($notif_stmt, "i", $appointmentId);
            mysqli_stmt_execute($notif_stmt);
            $patient_result = mysqli_fetch_assoc(mysqli_stmt_get_result($notif_stmt));
            $patientId = $patient_result['patientId'];
            $message = "Your appointment request has been " . $newStatus . ".";
            
            $insert_notif_sql = "INSERT INTO notifications (userId, message) VALUES (?, ?)";
            $insert_stmt = mysqli_prepare($link, $insert_notif_sql);
            mysqli_stmt_bind_param($insert_stmt, "is", $patientId, $message);
            mysqli_stmt_execute($insert_stmt);

            echo json_encode(['success' => true, 'message' => 'Appointment ' . $newStatus]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
        }
        exit;
    }

    // =================================================================
    // --- RECEPTIONIST-SPECIFIC ACTIONS ---
    // =================================================================
    if ($action == 'check_in_patient') {
        header('Content-Type: application/json');
        if ($role != 'receptionist') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Error: Unauthorized action.']);
            exit;
        }
        
        $qrCodeData = $_POST['qrCodeData'] ?? '';
        if (empty($qrCodeData)) {
            echo json_encode(['success' => false, 'message' => 'Error: QR Code data is missing.']);
            exit;
        }

        $sql = "SELECT id, status, appointmentDateTime FROM appointments WHERE qrCodeData = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $qrCodeData);
        mysqli_stmt_execute($stmt);
        $appointment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Check-in Failed: Invalid QR Code.']);
            exit;
        }

        if ($appointment['status'] == 'checked-in') {
            echo json_encode(['success' => false, 'message' => 'Notice: Patient already checked in.']);
            exit;
        }
        if ($appointment['status'] != 'approved') {
            echo json_encode(['success' => false, 'message' => 'Check-in Failed: Appointment not approved.']);
            exit;
        }
        
        $today = date('Y-m-d');
        $appointmentDate = date('Y-m-d', strtotime($appointment['appointmentDateTime']));
        if ($appointmentDate != $today) {
            echo json_encode(['success' => false, 'message' => 'Check-in Failed: Appointment is for ' . $appointmentDate]);
            exit;
        }

        $update_sql = "UPDATE appointments SET status = 'checked-in' WHERE id = ?";
        $update_stmt = mysqli_prepare($link, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $appointment['id']);
        
        if (mysqli_stmt_execute($update_stmt)) {
            echo json_encode(['success' => true, 'message' => 'Check-in Successful! Patient added to queue.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: Database update failed.']);
        }
        exit;
    }

    // =================================================================
    // --- DOCTOR-SPECIFIC ACTIONS (CONTINUED) ---
    // =================================================================
    if ($action == 'call_next_patient') {
        header('Content-Type: application/json');
        if ($role != 'doctor') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Error: Unauthorized action.']);
            exit;
        }

        // First, get the doctor's ID from the 'doctors' table using their user_id
        $doc_query = mysqli_prepare($link, "SELECT id FROM doctors WHERE user_id = ?");
        mysqli_stmt_bind_param($doc_query, "i", $user_id);
        mysqli_stmt_execute($doc_query);
        $doc_result = mysqli_fetch_assoc(mysqli_stmt_get_result($doc_query));
        if (!$doc_result) {
            echo json_encode(['success' => false, 'message' => 'Error: Doctor profile not found.']);
            exit;
        }
        $doctor_table_id = $doc_result['id'];

        // Find the top patient in this doctor's queue (oldest checked-in appointment)
        $find_sql = "SELECT a.id, p.fullName 
                     FROM appointments a 
                     JOIN users p ON a.patientId = p.id
                     WHERE a.doctorId = ? AND a.status = 'checked-in'
                     ORDER BY a.appointmentDateTime ASC 
                     LIMIT 1";
        $find_stmt = mysqli_prepare($link, $find_sql);
        mysqli_stmt_bind_param($find_stmt, "i", $doctor_table_id);
        mysqli_stmt_execute($find_stmt);
        $next_patient = mysqli_fetch_assoc(mysqli_stmt_get_result($find_stmt));

        if (!$next_patient) {
            echo json_encode(['success' => false, 'message' => 'The queue is currently empty.']);
            exit;
        }

        $appointment_to_update_id = $next_patient['id'];
        $patientName = $next_patient['fullName'];

        // Update that appointment's status to 'completed'
        $update_sql = "UPDATE appointments SET status = 'completed' WHERE id = ?";
        $update_stmt = mysqli_prepare($link, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $appointment_to_update_id);

        if (mysqli_stmt_execute($update_stmt)) {
            echo json_encode(['success' => true, 'patientName' => $patientName]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: Could not update patient status.']);
        }
        exit;
    }
}

mysqli_close($link);
?>