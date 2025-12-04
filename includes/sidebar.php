<?php
// includes/sidebar.php
// This file generates the sidebar based on the user's login status and role.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="sidebar">
    <h2>Techlaro</h2>
    <ul class="nav-list">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Admin Sidebar Links -->
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=overview">Dashboard</a></li>
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=manage_users">Manage Users</a></li>
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=database_backups">Database Backups</a></li>
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=system_logs">System Logs</a></li>
                <li><a href="/smart-cashier-system/pages/about_us.php">About Us</a></li>
                <li><a href="/smart-cashier-system/pages/settings.php">Settings</a></li>
                <li><a href="/smart-cashier-system/auth/logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a></li>
            <?php else: ?>
                <!-- Store Owner Sidebar Links -->
                <li><a href="/smart-cashier-system/pages/dashboard.php">Dashboard</a></li>
                <li><a href="/smart-cashier-system/pages/products.php">Products</a></li>
                <li><a href="/smart-cashier-system/pages/sales.php">Sales</a></li>
                <li><a href="/smart-cashier-system/pages/credits.php">Credits</a></li>
                <li><a href="/smart-cashier-system/pages/customers.php">Customers</a></li>
                <li><a href="/smart-cashier-system/pages/reports.php">Reports</a></li>
                <li><a href="/smart-cashier-system/pages/about_us.php">About Us</a></li>
                <li><a href="/smart-cashier-system/pages/settings.php">Settings</a></li>
                <li><a href="/smart-cashier-system/auth/logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a></li>
            <?php endif; ?>
        <?php else: ?>
            <!-- Guest Sidebar Links -->
            <li><a href="/smart-cashier-system/index.php">Login</a></li>
            <li><a href="/smart-cashier-system/auth/register.php">Register</a></li>
            <li><a href="/smart-cashier-system/pages/settings.php">Settings</a></li>
            <li><a href="/smart-cashier-system/pages/about_us.php">About Us</a></li>
        <?php endif; ?>
    </ul>
</div>

<!-- Floating Ask AI Button -->
<button id="ask-ai" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;" class="button small">Ask our AI</button>

<!-- AI Modal (Floating Window) -->
<div id="ai-modal" class="modal" style="display: none;">
    <div class="modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 600px; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">  <!-- Added inline background-color for dark mode; change to #f8f9fa for light if needed -->
        <span id="close-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
        <h2>Ask AI</h2>
        <p>Choose a pre-defined question or type your own:</p>
        <ul style="list-style-type: none; padding: 0;">
            <?php if (!isset($_SESSION['user_id'])): ?> <!-- Guest -->
                <li><a href="javascript:void(0);" onclick="showTutorial('How to register?')">How to register?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('How to login?')">How to login?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('Need more help?')">Need more help?</a></li>
            <?php elseif ($_SESSION['role'] === 'admin'): ?> <!-- Admin -->
                <li><a href="javascript:void(0);" onclick="showTutorial('How to manage users?')">How to manage users?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('How to backup database?')">How to backup database?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('How to view logs?')">How to view logs?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('Need more help?')">Need more help?</a></li>
            <?php else: ?> <!-- User/Owner -->
                <li><a href="javascript:void(0);" onclick="showTutorial('How to add a new product?')">How to add a new product?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('How to make a sale?')">How to make a sale?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('How to manage customer credits?')">How to manage customer credits?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('How to generate reports?')">How to generate reports?</a></li>
                <li><a href="javascript:void(0);" onclick="showTutorial('Need more help?')">Need more help?</a></li>
            <?php endif; ?>
        </ul>
        <form id="ai-form" onsubmit="handleAiQuery(event)" style="margin-top: 20px;">
            <div class="form-group">
                <label for="ai-query">Your Question:</label>
                <textarea id="ai-query" name="query" rows="4" required placeholder="Type your question here..."></textarea>
            </div>
            <button type="submit" class="button">Ask</button>
        </form>
        <div id="ai-response" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; display: none; background-color: #2a2a2a;"></div>  <!-- Added inline background-color for response box -->
    </div>
</div>

<div id="tutorial-modal" class="modal" style="display: none;">
    <div class="modal-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80%; max-width: 800px; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">  <!-- Added inline bg for consistency -->
        <span id="close-tutorial-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
        <h2 id="tutorial-title">Tutorial</h2>
        <img id="tutorial-image" src="" alt="Tutorial Image" style="max-width: 100%; height: auto; margin: 10px 0;">
        <p id="tutorial-description">Description here.</p>
    </div>
</div>