<?php
include '../includes/session.php';
if (!isAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';
include '../includes/header.php'; // Include header for consistent UI
include '../includes/sidebar.php'; // Include sidebar for navigation

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    // No user ID provided, redirect back to manage users with an error
    header("Location: admin_panel.php?action=manage_users&error=No user selected for editing");
    exit;
}

// Fetch user data for the given ID
$stmt_select = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
$stmt_select->bind_param("i", $user_id);
$stmt_select->execute();
$result_select = $stmt_select->get_result();
$user_to_edit = $result_select->fetch_assoc();
$stmt_select->close();

if (!$user_to_edit) {
    // User not found, redirect with an error
    header("Location: admin_panel.php?action=manage_users&error=User not found");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_role = $_POST['role'];

    $stmt_update = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt_update->bind_param("si", $new_role, $user_id);

    if ($stmt_update->execute()) {
        header("Location: admin_panel.php?action=manage_users&success=User role updated successfully");
    } else {
        header("Location: admin_panel.php?action=manage_users&error=Error updating user role: " . $stmt_update->error);
    }

    $stmt_update->close();
    $conn->close();
    exit;
}
?>

<div class="content">
    <h1>Edit User Role</h1>
    <p>Edit the role for user: <?php echo htmlspecialchars($user_to_edit['username']); ?></p>
    <form action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>" method="POST">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['id']); ?>">
        <div class="form-group">
            <label for="role">New Role:</label>
            <select id="role" name="role">
                <option value="owner" <?php if ($user_to_edit['role'] === 'owner') echo 'selected'; ?>>Store Owner</option>
                <option value="admin" <?php if ($user_to_edit['role'] === 'admin') echo 'selected'; ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="button">Update Role</button>
        <p><a href="admin_panel.php?action=manage_users">Back to Manage Users</a></p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>