<?php
// Database connection (assuming it's in db_connect.php)

// Log message function
function logMessage($message, $logLevel = 'info') {
    $logFile = '../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [$logLevel] $message\n", FILE_APPEND);
    error_log($message);  // Also log to PHP error log for easy checking
}

// Other functions (e.g., validate_input, etc.)
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Add calls like logMessage("User ID: $user_id, Role: $role"); in dashboard.php or other files as needed