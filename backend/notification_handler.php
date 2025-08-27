<?php
/* gutu-hospital/backend/notification_handler.php */

require_once "config.php";
session_start();

header('Content-Type: application/json');

// User must be logged in to access notifications
if (!isset($_SESSION["loggedin"])) {
    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
    exit;
}

$user_id = $_SESSION['id'];
$action = $_GET['action'] ?? '';

// --- ACTION: Fetch unread notifications ---
if ($action == 'fetch_unread') {
    $sql = "SELECT id, message, createdAt FROM notifications WHERE userId = ? AND isRead = 0 ORDER BY createdAt DESC";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    exit;
}

// --- ACTION: Mark notifications as read ---
if ($action == 'mark_as_read') {
    // We expect a POST request with a list of notification IDs
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];

        if (!empty($ids) && is_array($ids)) {
            // Create placeholders for the IN clause
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));

            $sql = "UPDATE notifications SET isRead = 1 WHERE userId = ? AND id IN ($placeholders)";
            $stmt = mysqli_prepare($link, $sql);
            
            // Bind the user_id first, then the array of notification IDs
            $params = array_merge([$user_id], $ids);
            mysqli_stmt_bind_param($stmt, "i" . $types, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Notifications marked as read.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database update failed.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No notification IDs provided.']);
        }
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action specified.']);
?>
