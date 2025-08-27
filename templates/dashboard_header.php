<!-- gutu-hospital/templates/dashboard_header.php -->
<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /gutu-hospital/index.php");
    exit;
}

$userRole = $_SESSION["role"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gutu Rural Hospital</title>
    <!-- We link both stylesheets. style.css for variables, dashboard.css for layout -->
    <link rel="stylesheet" href="/gutu-hospital/assets/css/style.css">
    <link rel="stylesheet" href="/gutu-hospital/assets/css/dashboard.css">
</head>
<body class="dashboard-body">
    <style>
        .header-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0.5rem 2rem;
            background-color: var(--secondary-color);
            border-bottom: 1px solid var(--primary-color);
        }
        .notifications-widget {
            position: relative;
        }
        .notification-bell {
            font-size: 1.5rem;
            color: var(--text-color);
            cursor: pointer;
        }
        .notification-count {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: var(--error-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            display: none; /* Hidden by default */
        }
        .notifications-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            background-color: var(--primary-color);
            border: 1px solid var(--accent-color);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: none;
            z-index: 1000;
        }
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid var(--secondary-color);
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item p {
            margin: 0;
            font-size: 0.9rem;
        }
        .notification-item .timestamp {
            font-size: 0.75rem;
            color: #aaa;
            margin-top: 5px;
        }
        .no-notifications {
            padding: 2rem;
            text-align: center;
            color: #aaa;
        }
    </style>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Gutu Hospital</h2>
            <p>Role: <?php echo ucfirst($userRole); ?></p>
        </div>
        <nav>
            <ul class="sidebar-nav">
                <?php if ($userRole == 'patient'): ?>
                    <li><a href="/gutu-hospital/patient/index.php" class="active">Dashboard</a></li>
                    <li><a href="/gutu-hospital/patient/book-appointment.php">Book Appointment</a></li>
                    <li><a href="/gutu-hospital/patient/history.php">Appointment History</a></li>
                    <li><a href="/gutu-hospital/patient/settings.php">Settings</a></li>
                <?php elseif ($userRole == 'doctor'): ?>
                    <li><a href="/gutu-hospital/doctor/index.php" class="active">Dashboard</a></li>
                    <li><a href="/gutu-hospital/doctor/schedule.php">My Schedule</a></li>
                    <li><a href="/gutu-hospital/doctor/settings.php">Settings</a></li>
                <?php elseif ($userRole == 'receptionist'): ?>
                    <li><a href="/gutu-hospital/receptionist/index.php" class="active">Dashboard</a></li>
                    <li><a href="/gutu-hospital/receptionist/manage-doctors.php">Manage Doctors</a></li>
                    <li><a href="/gutu-hospital/receptionist/analytics.php">Analytics</a></li>
                    <li><a href="/gutu-hospital/receptionist/settings.php">Settings</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <a href="/gutu-hospital/backend/auth.php?action=logout" class="btn logout-btn">Logout</a>
    </aside>

    <main class="main-content">
        <?php if ($userRole == 'patient'): ?>
        <div class="header-bar">
            <div class="notifications-widget">
                <span id="notification-bell" class="notification-bell">&#128276;</span> <!-- Bell character -->
                <span id="notification-count" class="notification-count">0</span>
                <div id="notifications-dropdown" class="notifications-dropdown">
                    <div class="no-notifications">No new notifications.</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($userRole == 'patient'): ?>
        <script src="/gutu-hospital/assets/js/notifications.js"></script>
        <?php endif; ?>