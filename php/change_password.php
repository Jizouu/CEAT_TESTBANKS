<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'UPHSD Test Bank - Change Password';
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users_new WHERE user_id = ?");
    $stmt->bind_param("i", $user['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($current, $result['password'])) {
        setFlash('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 6) {
        setFlash('error', 'New password must be at least 6 characters.');
    } elseif ($new !== $confirm) {
        setFlash('error', 'New passwords do not match.');
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users_new SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed, $user['user_id']);
        $stmt->execute();
        $stmt->close();

        logActivity($conn, $user['user_id'], $user['role'], 'Changed password', $user['first_name'], $user['last_name']);
        setFlash('success', 'Password changed successfully!');
    }

    header("Location: change_password.php");
    exit;
}

include 'includes/header.php';
?>
<div class="main-content" style="max-width:640px;">
    <a href="dashboard.php" class="btn btn-ghost mb-4">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Dashboard
    </a>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Change Password</h2>
            <p class="card-description">Update your account security credentials</p>
        </div>
        <div class="card-content">
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-maroon btn-block">Change Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
