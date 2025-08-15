<?php
require_once '../config/functions.php';

// Start the session (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted username
    $username = $_POST['username'];

    // Basic validation (you should add more robust validation)
    if (empty($username)) {
        $_SESSION['error'] = "Please enter your username.";
        header("Location: password_reset.php");
        exit();
    }

    // Database connection (assuming your connect_db() function is in functions.php)
    $conn = connect_db();

    // Sanitize input to prevent SQL injection (use prepared statements in a real application)
    $username = mysqli_real_escape_string($conn, $username);

    // Check if the user exists in the database
    $query = "SELECT user_id, user_email FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];
        $user_email = $user['user_email'];

        // --- START OF INSECURE TOKEN GENERATION - REPLACE THIS ---
        $reset_token = md5(uniqid(rand(), true)); // Very basic and not cryptographically secure
        $expiry_time = date("Y-m-d H:i:s", time() + 3600); // Token valid for 1 hour

        // Store the reset token in the database (you'll need 'reset_token' and 'reset_expiry' columns in your 'users' table)
        $update_query = "UPDATE users SET reset_token = '$reset_token', reset_expiry = '$expiry_time' WHERE user_id = $user_id";
        mysqli_query($conn, $update_query);
        // --- END OF INSECURE TOKEN GENERATION ---

        // --- START OF BASIC EMAIL SENDING - YOU'LL NEED TO CONFIGURE A MAIL SERVER ---
        $reset_link = "http://localhost/SMART-CASHIER-SYSTEM/auth/reset_password_form.php?token=$reset_token";
        $subject = "Password Reset Request";
        $message = "Dear user,\n\nYou have requested a password reset. Please click the following link to reset your password:\n\n" . $reset_link . "\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.\n\nSincerely,\nYour System Administrator";
        $headers = "From: webmaster@yourdomain.com\r\n"; // Replace with your actual email

        // In a real application, use a proper email library (like PHPMailer) for better reliability and security
        if (mail($user_email, $subject, $message, $headers)) {
            $_SESSION['success'] = "An email with instructions to reset your password has been sent to your registered email address.";
        } else {
            $_SESSION['error'] = "Failed to send password reset email. Please try again later.";
            // You might want to log this error for debugging
        }
        // --- END OF BASIC EMAIL SENDING ---

    } else {
        $_SESSION['error'] = "Username not found.";
    }

    mysqli_close($conn);
    header("Location: password_reset.php");
    exit();

} else {
    // If the page is accessed directly (not via POST), redirect to the password reset form
    header("Location: password_reset.php");
    exit();
}
?>