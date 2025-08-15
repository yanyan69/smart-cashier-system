<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to the appropriate dashboard based on their role
    if ($_SESSION['role'] === 'admin') {
        header("Location: pages/admin_panel.php?action=overview");
    } else {
        header("Location: pages/dashboard.php");
    }
    exit;
} else {
    // User is not logged in, show the login form
    include 'assets/html/index.html';
}
?>