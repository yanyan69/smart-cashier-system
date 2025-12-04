<?php
// pages/process_admin_action.php

require_once '../config/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add_user':
            // Check for existing username
            $username = $_POST['username'];
            $checkStmt = $conn->prepare("SELECT id FROM `user` WHERE username = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                header("Location: admin_panel.php?action=manage_users&error=Username already exists");
                exit();
            }

            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = $_POST['role'];
            $stmt = $conn->prepare("INSERT INTO `user` (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $role);
            if ($stmt->execute()) {
                $logMsg = date('Y-m-d H:i:s') . "[admin] Admin user '" . ($_SESSION['username'] ?? 'unknown') . "' added new user '$username' with role '$role'.";
                logMessage($logMsg);
                header("Location: admin_panel.php?action=manage_users&success=User added");
            } else {
                header("Location: admin_panel.php?action=manage_users&error=Error adding user");
            }
            break;

        case 'edit_user':
            $user_id = $_POST['user_id'];
            $username = $_POST['username'];
            $role = $_POST['role'];
            $passwordSql = '';
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $passwordSql = ", password = '$password'";
            }
            $stmt = $conn->prepare("UPDATE `user` SET username = ?, role = ? $passwordSql WHERE id = ?");
            $stmt->bind_param("ssi", $username, $role, $user_id);
            if ($stmt->execute()) {
                $logMsg = date('Y-m-d H:i:s') . "[admin] Admin user '" . ($_SESSION['username'] ?? 'unknown') . "' edited user ID $user_id (username: $username, role: $role).";
                logMessage($logMsg);
                header("Location: admin_panel.php?action=manage_users&success=User updated");
            } else {
                header("Location: admin_panel.php?action=manage_users&error=Error updating user");
            }
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'];
            $stmt = $conn->prepare("DELETE FROM `user` WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $logMsg = date('Y-m-d H:i:s') . "[admin] Admin user '" . ($_SESSION['username'] ?? 'unknown') . "' deleted user ID $user_id.";
                logMessage($logMsg);
                header("Location: admin_panel.php?action=manage_users&success=User deleted");
            } else {
                header("Location: admin_panel.php?action=manage_users&error=Error deleting user");
            }
            break;

        case 'backup_database':
            $backupDir = '../backups/';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            $backupFile = $backupDir . 'cashier_db_' . date('Y-m-d_H-i-s') . '.sql';
            // Corrected command: omit --password if empty
            $command = "mysqldump --host=127.0.0.1 --port=3307 -u root cashier_db > " . escapeshellarg($backupFile);
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);
            if ($return_var === 0 && filesize($backupFile) > 0) {
                $logMsg = date('Y-m-d H:i:s') . "[admin] Database backup created: $backupFile";
                logMessage($logMsg);
                header("Location: admin_panel.php?action=database_backups&success=Backup created");
            } else {
                // Delete empty file
                if (file_exists($backupFile)) unlink($backupFile);
                $logMsg = date('Y-m-d H:i:s') . "[admin] Database backup failed: " . implode("\n", $output);
                logMessage($logMsg);
                header("Location: admin_panel.php?action=database_backups&error=Backup failed");
            }
            break;
    }
}