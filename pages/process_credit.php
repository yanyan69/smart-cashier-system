<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $where = ($role === 'owner') ? "AND user_id = $user_id" : "";
    $stmt = $conn->prepare("DELETE FROM credit WHERE id = ? $where");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: credits.php?success=Credit deleted");
    } else {
        header("Location: credits.php?error=Failed to delete credit");
    }
}

$stmt->close();
$conn->close();
?>