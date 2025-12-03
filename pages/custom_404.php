<?php
session_start();
echo "<h1>404 Not Found</h1>";
echo "<p>Requested URL: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>";
echo "<p>Referrer: " . htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'None') . "</p>";
echo "<p>Server Name: " . htmlspecialchars($_SERVER['SERVER_NAME']) . "</p>";
echo "<p>Document Root: " . htmlspecialchars($_SERVER['DOCUMENT_ROOT']) . "</p>";
echo "<pre>Stack-like Trace:\n" . print_r(debug_backtrace(), true) . "</pre>"; // PHP backtrace if PHP handles it
?>