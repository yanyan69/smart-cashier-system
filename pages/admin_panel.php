<?php 
session_start();
include '../includes/session.php'; ?>
<?php
session_start();
// Check if the user is an admin
if (!isAdmin()) {
    header("Location: ../unauthorized.php"); // You'll need to create this page
    exit;
}

include '../config/db.php';
include '../includes/functions.php'; // Include the functions file

$action = $_GET['action'] ?? 'overview';

// Fetch users for manage_users action
if ($action === 'manage_users') {
    $sql_users = "SELECT id, username, role, created_at FROM users ORDER BY created_at DESC";
    $result_users = $conn->query($sql_users);
    $users = [];
    if ($result_users->num_rows > 0) {
        while ($row = $result_users->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

// Close database connection (we'll reopen as needed in included files)
$conn->close();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Admin Panel</h1>
    <?php
    echo "<h2>" . ucfirst(str_replace('_', ' ', $action)) . "</h2>";

    switch ($action) {
        case 'manage_users':
            echo "<p>Here you can manage store owner and administrator accounts (create, edit roles, delete).</p>";

            if (!empty($users)): ?>
                <h3>Current Users</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="button small">Edit Role</a>
                                    <?php if ($_SESSION['user_id'] !== $user['id']): // Prevent deleting own account ?>
                                        <a href="delete_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="button small alert">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>

            <h3>Add New User</h3>
            <form action="add_user.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="owner">Store Owner</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="button">Add User</button>
                </div>
            </form>
            <?php
            break;
        case 'database_backups':
            echo "<p>Use this section to perform database backups.</p>";
            echo "<p><a href='backup_db.php' class='button'>Create Database Backup</a></p>";
            if (isset($_GET['error'])) {
                echo "<div class='alert-danger'>" . htmlspecialchars($_GET['error']) . "</div>";
            }
            break;
        case 'system_logs':
            echo "<p>This page displays system maintenance logs.</p>";
            $logFile = '../logs/system.log';
            if (file_exists($logFile)) {
                $logs = file_get_contents($logFile);
                $logLines = explode("\n", trim($logs));
                if (!empty($logLines)):
                    echo "<pre>";
                    foreach ($logLines as $line) {
                        echo htmlspecialchars($line) . "\n";
                    }
                    echo "</pre>";
                else:
                    echo "<p>No logs recorded yet.</p>";
                endif;
            } else {
                echo "<p>Log file not found.</p>";
            }
            break;
        default:
            echo "<p>Welcome to the Admin Panel. Please select an action from the sidebar.</p>";
            break;
    }
    ?>
</div>

<?php include '../includes/footer.php'; ?>