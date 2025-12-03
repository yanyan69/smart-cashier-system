<?php
// Start the session to access and destroy it
session_start();

// Destroy all session data
session_destroy();

// Redirect to the root index.php (which handles the login page)
header("Location: /smart-cashier-system/index.php");
exit();
?>