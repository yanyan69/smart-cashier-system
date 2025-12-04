<?php
// pages/admin_panel.php

require_once '../includes/session.php'; // Ensure user is admin - this should handle session check without duplicate start
require_once '../config/db.php'; // Database connection
require_once '../includes/functions.php'; // For logging, etc.

if (!isAdmin()) {
    header("Location: ../pages/unauthorized.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'overview';

// Handle form submissions (e.g., add user, backup)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    include 'process_admin_action.php'; // Correct path assuming it's in the same 'pages' directory
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Admin Panel - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Admin Panel</h1>
            </header>
            <section>
                <?php
                switch ($action) {
                    case 'overview':
                        // Overview with table for stats
                        echo '<h2>Admin Overview</h2>';
                        echo '<div class="table-wrapper">';
                        echo '<table class="table">';
                        echo '    <thead>';
                        echo '        <tr>';
                        echo '            <th>Statistic</th>';
                        echo '            <th>Value</th>';
                        echo '        </tr>';
                        echo '    </thead>';
                        echo '    <tbody>';
                        $userCount = $conn->query("SELECT COUNT(*) as count FROM `user`")->fetch_assoc()['count'];
                        echo '        <tr>';
                        echo '            <td>Total Users</td>';
                        echo "            <td>$userCount</td>";
                        echo '        </tr>';
                        echo '        <tr>';
                        echo '            <td>System Status</td>';
                        echo '            <td>All systems operational.</td>';
                        echo '        </tr>';
                        // Add more stats if needed (e.g., total backups)
                        $backupCount = count(glob('../backups/*.sql'));
                        echo '        <tr>';
                        echo '            <td>Total Backups</td>';
                        echo "            <td>$backupCount</td>";
                        echo '        </tr>';
                        echo '    </tbody>';
                        echo '</table>';
                        echo '</div>';

                        // Recent Activity table
                        echo '<h3>Recent Activity</h3>';
                        echo '<div class="table-wrapper">';
                        echo '<table class="table">';
                        echo '    <thead>';
                        echo '        <tr>';
                        echo '            <th>Timestamp</th>';
                        echo '            <th>Event</th>';
                        echo '        </tr>';
                        echo '    </thead>';
                        echo '    <tbody>';
                        $logFile = '../logs/system.log';
                        $logs = file_exists($logFile) ? array_slice(array_reverse(file($logFile)), 0, 10) : []; // Last 10 newest
                        if (!empty($logs)) {
                            foreach ($logs as $log) {
                                $log = trim($log);
                                if ($log) {
                                    // Try new format first: YYYY-MM-DD HH:MM:SS[role] event
                                    if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\[(.*?)\] (.*)/', $log, $matches)) {
                                        $timestamp = $matches[1];
                                        $role = $matches[2];
                                        $event = $matches[3];
                                    // Try old format: [YYYY-MM-DD HH:MM:SS] [role] event
                                    } elseif (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \[(.*?)\] (.*)/', $log, $matches)) {
                                        $timestamp = $matches[1];
                                        $role = $matches[2];
                                        $event = $matches[3];
                                    } else {
                                        $timestamp = 'N/A';
                                        $event = $log;
                                        $role = '';
                                    }
                                    echo '        <tr>';
                                    echo "            <td>$timestamp</td>";
                                    echo "            <td>[$role] $event</td>";
                                    echo '        </tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="2">No recent activity logged yet.</td></tr>';
                        }
                        echo '    </tbody>';
                        echo '</table>';
                        echo '</div>';
                        break;

                    case 'manage_users':
                        echo '<h2>Manage Users</h2>';
                        // Display users in a table first
                        echo '<h3>Existing Users</h3>';
                        echo '<div class="table-wrapper">';
                        echo '<table class="table">';
                        echo '    <thead>';
                        echo '        <tr>';
                        echo '            <th>ID</th>';
                        echo '            <th>Username</th>';
                        echo '            <th>Role</th>';
                        echo '            <th>Created At</th>';
                        echo '            <th>Actions</th>';
                        echo '        </tr>';
                        echo '    </thead>';
                        echo '    <tbody>';
                        $result = $conn->query("SELECT id, username, role, created_at FROM `user` ORDER BY created_at DESC");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '        <tr>';
                                echo "            <td>{$row['id']}</td>";
                                echo "            <td>{$row['username']}</td>";
                                echo "            <td>{$row['role']}</td>";
                                echo "            <td>{$row['created_at']}</td>";
                                echo '            <td>';
                                // Edit button
                                echo "                <button class='button small' onclick=\"editUser({$row['id']}, '{$row['username']}', '{$row['role']}')\">Edit</button>";
                                // Delete form
                                echo "                <form action='pages/admin_panel.php?action=manage_users' method='POST' style='display:inline;'>";
                                echo "                    <input type='hidden' name='action' value='delete_user'>";
                                echo "                    <input type='hidden' name='user_id' value='{$row['id']}'>";
                                echo "                    <button type='submit' class='button small danger' onclick=\"return confirm('Are you sure?');\">Delete</button>";
                                echo '                </form>';
                                echo '            </td>';
                                echo '        </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5">No users found.</td></tr>';
                        }
                        echo '    </tbody>';
                        echo '</table>';
                        echo '</div>';

                        // Add User button below the table
                        echo '<button id="add-user-btn" class="button" style="margin-top: 20px;">Add New User</button>';

                        // Form for add/edit user (hidden initially)
                        echo '<form id="user-form" action="pages/admin_panel.php?action=manage_users" method="POST" class="form-group" style="display: none; margin-top: 20px;">';
                        echo '    <input type="hidden" id="user-action" name="action" value="add_user">';
                        echo '    <input type="hidden" id="user-id" name="user_id" value="">';
                        echo '    <label for="username">Username:</label>';
                        echo '    <input type="text" id="username" name="username" required>';
                        echo '    <label for="password">Password (leave blank to keep current):</label>';
                        echo '    <input type="password" id="password" name="password">';
                        echo '    <label for="role">Role:</label>';
                        echo '    <select id="role" name="role">';
                        echo '        <option value="owner">Owner</option>';
                        echo '        <option value="admin">Admin</option>';
                        echo '    </select>';
                        echo '    <button type="submit" class="button">Save User</button>';
                        echo '</form>';
                        break;

                    case 'database_backups':
                        echo '<h2>Database Backups</h2>';
                        echo '<form action="pages/admin_panel.php?action=database_backups" method="POST">';
                        echo '    <input type="hidden" name="action" value="backup_database">';
                        echo '    <button type="submit" class="button">Backup Database Now</button>';
                        echo '</form>';

                        // Display backups in a table (last 10 newest, non-empty)
                        $backupDir = '../backups/';
                        $backups = array_unique(glob($backupDir . '*.sql'));
                        usort($backups, function($a, $b) { return filemtime($b) - filemtime($a); }); // Newest first
                        $backups = array_slice($backups, 0, 10); // Limit to last 10
                        echo '<div class="table-wrapper">';
                        echo '<table class="table">';
                        echo '    <thead>';
                        echo '        <tr>';
                        echo '            <th>Backup File</th>';
                        echo '            <th>Size</th>';
                        echo '            <th>Created</th>';
                        echo '            <th>Actions</th>';
                        echo '        </tr>';
                        echo '    </thead>';
                        echo '    <tbody>';
                        $hasBackups = false;
                        foreach ($backups as $file) {
                            if (filesize($file) > 0) { // Skip empty
                                $hasBackups = true;
                                $filename = basename($file);
                                $size = round(filesize($file) / 1024, 2) . ' KB';
                                $created = date('Y-m-d H:i:s', filemtime($file));
                                echo '        <tr>';
                                echo "            <td>$filename</td>";
                                echo "            <td>$size</td>";
                                echo "            <td>$created</td>";
                                echo '            <td>';
                                echo "                <a href='backups/$filename' download class='button small'>Download</a>";
                                echo '            </td>';
                                echo '        </tr>';
                            }
                        }
                        if (!$hasBackups) {
                            echo '<tr><td colspan="4">No valid backups found.</td></tr>';
                        }
                        echo '    </tbody>';
                        echo '</table>';
                        echo '</div>';
                        break;

                    case 'system_logs':
                        echo '<h2>System Logs</h2>';
                        // Display logs in a table
                        $logFile = '../logs/system.log';
                        $logs = file_exists($logFile) ? array_reverse(file($logFile)) : []; // Newest first
                        echo '<div class="table-wrapper">';
                        echo '<table class="table">';
                        echo '    <thead>';
                        echo '        <tr>';
                        echo '            <th>Timestamp</th>';
                        echo '            <th>Event</th>';
                        echo '        </tr>';
                        echo '    </thead>';
                        echo '    <tbody>';
                        if (!empty($logs)) {
                            foreach ($logs as $log) {
                                $log = trim($log);
                                if ($log) {
                                    // Try new format first: YYYY-MM-DD HH:MM:SS[role] event
                                    if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\[(.*?)\] (.*)/', $log, $matches)) {
                                        $timestamp = $matches[1];
                                        $role = $matches[2];
                                        $event = $matches[3];
                                    // Try old format: [YYYY-MM-DD HH:MM:SS] [role] event
                                    } elseif (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \[(.*?)\] (.*)/', $log, $matches)) {
                                        $timestamp = $matches[1];
                                        $role = $matches[2];
                                        $event = $matches[3];
                                    } else {
                                        $timestamp = 'N/A';
                                        $event = $log;
                                        $role = '';
                                    }
                                    echo '        <tr>';
                                    echo "            <td>$timestamp</td>";
                                    echo "            <td>[$role] $event</td>";
                                    echo '        </tr>';
                                }
                            }
                        } else {
                            echo '<tr><td colspan="2">No logs available.</td></tr>';
                        }
                        echo '    </tbody>';
                        echo '</table>';
                        echo '</div>';
                        break;

                    default:
                        echo '<h2>Invalid Action</h2>';
                        echo '<p>Please select a valid option from the menu.</p>';
                        break;
                }
                ?>
            </section>
            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
    <script>
        // JS to toggle add user form and handle edit
        document.addEventListener('DOMContentLoaded', () => {
            const addUserBtn = document.getElementById('add-user-btn');
            const userForm = document.getElementById('user-form');
            const userAction = document.getElementById('user-action');
            const userId = document.getElementById('user-id');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const roleSelect = document.getElementById('role');

            if (addUserBtn && userForm) {
                addUserBtn.addEventListener('click', () => {
                    // Reset for add
                    userAction.value = 'add_user';
                    userId.value = '';
                    usernameInput.value = '';
                    passwordInput.value = '';
                    roleSelect.value = 'owner';
                    passwordInput.required = true;
                    userForm.style.display = 'block';
                });
            }

            window.editUser = function(id, username, role) {
                userAction.value = 'edit_user';
                userId.value = id;
                usernameInput.value = username;
                roleSelect.value = role;
                passwordInput.value = ''; // Leave blank to keep current
                passwordInput.required = false;
                userForm.style.display = 'block';
            };
        });
    </script>
</body>
</html>