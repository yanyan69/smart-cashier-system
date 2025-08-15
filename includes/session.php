<?php
session_start();

// Check if the user is NOT logged in and tries to access a protected page
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    // header("Location: ../index.php"); // TEMPORARILY COMMENT OUT THIS LINE
    // exit; // TEMPORARILY COMMENT OUT THIS LINE
}

// Functions to check user roles
function isStoreOwner() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'owner');
}

function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Function to check if the current user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Example of how to restrict access to certain pages based on role
// Place this at the top of your protected pages:
// if (!isAdmin()) {
//     header("Location: ../unauthorized.php"); // Create an unauthorized access page
//     exit;
// }
?>