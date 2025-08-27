<?php
/* gutu-hospital/backend/config.php */

// --- Database Credentials ---
// These are the default credentials for a standard XAMPP installation.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'gutu_hospital');

// --- Attempt to connect to MySQL database ---
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if($link === false){
    // If the connection fails, stop the script and display a connection error.
    // In a real production environment, you would log this error instead of showing it to the user.
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>