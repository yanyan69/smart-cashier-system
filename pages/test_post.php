<?php
session_start();
error_log("Test POST accessed");
error_log("$_POST contents from test_post.php: " . print_r($_POST, true));
?>