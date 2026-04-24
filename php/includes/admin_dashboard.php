<?php
// Admin Dashboard - included from dashboard.php

// Fetch stats
$totalUsers = $conn->query("SELECT COUNT(*) as cnt FROM users_new")->fetch_assoc()['cnt'];
$totalLogins = $conn->query("SELECT COUNT(*) as cnt FROM user_logs WHERE action LIKE '%Logged into%'")->fetch_assoc()['cnt'];
$totalActions = $conn->query("SELECT COUNT(*) as cnt FROM user_logs")->fetch_assoc()['cnt'];

// Fetch recent logs
$logs = $conn->query("SELECT * FROM user_logs ORDER BY timestamp DESC LIMIT 50");
?>

<div class="main-content">
    <!-- Navigation Buttons -->
    <div class="grid-4 mb-8">
        <a href="manage_programs.php" class="btn btn-maroon btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1 4 3 6 3s6-2 6-3v-5"/></svg>
            Manage Programs
        </a>
        <a href="manage_courses.php" class="btn btn-orange btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Manage Courses
        </a>
        <a href="manage_users.php" class="btn btn-maroon btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Manage Users
        </a>
        <a href="dashboard.php" class="btn btn-outline-dark btn-nav" style="border:1px solid var(--border-color);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            View Logs
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid-3 mb-8">
        <div class="card card-stat">
            <div class="card-header card-header-row">
                <h3 class="card-title" style="font-size:0.85rem;">Total Users</h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--maroon)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div class="card-content"><div class="stat-value"><?= $totalUsers ?></div><p class="stat-label">Active users in system</p></div>
        </div>
        <div class="card card-stat">
            <div class="card-header card-header-row">
                <h3 class="card-title" style="font-size:0.85rem;">Total Logins</h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div class="card-content"><div class="stat-value"><?= $totalLogins ?></div><p class="stat-label">Login attempts</p></div>
        </div>
        <div class="card card-stat">
            <div class="card-header card-header-row">
                <h3 class="card-title" style="font-size:0.85rem;">Total Activities</h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--maroon)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="card-content"><div class="stat-value"><?= $totalActions ?></div><p class="stat-label">All logged actions</p></div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Activity Logs</h3>
            <p class="card-description">Monitor all user activities in real-time</p>
        </div>
        <div class="card-content">
            <div class="table-wrapper">
            <table>
                <thead><tr><th>Timestamp</th><th>User</th><th>Role</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if ($logs->num_rows > 0): ?>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted"><?= date('M j, Y g:i A', strtotime($log['timestamp'])) ?></td>
                            <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                            <td><span class="badge badge-<?= $log['user_type'] === 'admin' ? 'purple' : ($log['user_type'] === 'faculty' ? 'blue' : ($log['user_type'] === 'superadmin' ? 'orange' : 'green')) ?>"><?= htmlspecialchars($log['user_type']) ?></span></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">No activity logs yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
