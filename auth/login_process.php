<?php
session_start(); // Start the session at the very beginning

// Database credentials (replace with your actual values)
$host = "localhost";
$dbname = "cashier_db";
$username = "root";
$password = "admin";

// Establish database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Now, $conn should be available
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on user role
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../pages/admin_panel.php?action=overview"); // Redirect to admin panel
            } else {
                header("Location: ../pages/dashboard.php"); // Redirect to store owner dashboard
            }
            exit();
        } else {
            header("Location: ../index.php?error=Invalid password"); // Redirect back to login page with error
            exit();
        }
    } else {
        header("Location: ../index.php?error=Invalid username"); // Redirect back to login page with error
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php"); // If accessed directly, redirect to login page
    exit();
}
?>