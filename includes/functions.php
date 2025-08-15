<?php

function logMessage($message, $logLevel = 'info') {
    $logFile = '../logs/system.log'; // Define the log file path
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$logLevel}] {$message}\n";

    // Ensure the logs directory exists
    if (!is_dir('../logs')) {
        mkdir('../logs', 0755, true); // Create directory if it doesn't exist
    }

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

?>