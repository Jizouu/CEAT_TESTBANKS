<?php
// SuperAdmin Dashboard - included from dashboard.php
$filter = $_GET['filter'] ?? 'all';

// Handle clear logs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_logs') {
    $conn->query("TRUNCATE TABLE user_logs");
    setFlash('success', 'All activity logs cleared.');
    header("Location: dashboard.php");
    exit;
}

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as cnt FROM users_new")->fetch_assoc()['cnt'];
$totalLogins = $conn->query("SELECT COUNT(*) as cnt FROM user_logs WHERE action LIKE '%Logged into%'")->fetch_assoc()['cnt'];
$totalActions = $conn->query("SELECT COUNT(*) as cnt FROM user_logs")->fetch_assoc()['cnt'];

// Filtered logs
$logQuery = "SELECT * FROM user_logs";
if ($filter === 'login') $logQuery .= " WHERE action LIKE '%Logged into%'";
elseif ($filter === 'logout') $logQuery .= " WHERE action LIKE '%Logout%'";
$logQuery .= " ORDER BY timestamp DESC LIMIT 100";
$logs = $conn->query($logQuery);

// Analytics
$loginCount = $conn->query("SELECT COUNT(*) as cnt FROM user_logs WHERE action LIKE '%Logged into%'")->fetch_assoc()['cnt'];
$logoutCount = $conn->query("SELECT COUNT(*) as cnt FROM user_logs WHERE action LIKE '%Logout%'")->fetch_assoc()['cnt'];
$otherCount = $totalActions - $loginCount - $logoutCount;

// Most active users
$activeUsers = $conn->query("
    SELECT first_name, last_name, user_type, COUNT(*) as cnt
    FROM user_logs
    GROUP BY first_name, last_name, user_type
    ORDER BY cnt DESC LIMIT 5
");

$activeTab = $_GET['tab'] ?? 'logs';
?>

<div class="main-content">
    <!-- Navigation Buttons -->
    <div class="grid-3 mb-8">
        <a href="system_settings.php" class="btn btn-maroon btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            System Settings
        </a>
        <a href="database_editor.php" class="btn btn-orange btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            Database Editor
        </a>
        <a href="dashboard.php" class="btn btn-outline-dark btn-nav" style="border:1px solid var(--border-color);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            View Analytics
        </a>
    </div>

    <!-- Stats -->
    <div class="grid-4 mb-8">
        <div class="card card-stat card-border-maroon"><div class="card-header card-header-row"><h3 class="card-title" style="font-size:0.85rem;">Total Users</h3></div><div class="card-content"><div class="stat-value"><?= $totalUsers ?></div><p class="stat-label">Active users</p></div></div>
        <div class="card card-stat card-border-orange"><div class="card-header card-header-row"><h3 class="card-title" style="font-size:0.85rem;">Total Logins</h3></div><div class="card-content"><div class="stat-value"><?= $totalLogins ?></div><p class="stat-label">Login attempts</p></div></div>
        <div class="card card-stat card-border-maroon"><div class="card-header card-header-row"><h3 class="card-title" style="font-size:0.85rem;">Total Activities</h3></div><div class="card-content"><div class="stat-value"><?= $totalActions ?></div><p class="stat-label">All actions</p></div></div>
        <div class="card card-stat card-border-orange"><div class="card-header card-header-row"><h3 class="card-title" style="font-size:0.85rem;">Recent</h3></div><div class="card-content"><div class="stat-value"><?= min(10, $totalActions) ?></div><p class="stat-label">Last 10 actions</p></div></div>
    </div>

    <!-- Tabs -->
    <div class="tabs-nav">
        <a href="?tab=logs" class="tab-btn <?= $activeTab === 'logs' ? 'active' : '' ?>">Activity Logs</a>
        <a href="?tab=analytics" class="tab-btn <?= $activeTab === 'analytics' ? 'active' : '' ?>">Analytics</a>
    </div>

    <?php if ($activeTab === 'logs'): ?>
    <div class="card">
        <div class="card-header">
            <div class="flex-between">
                <div><h3 class="card-title">User Activity Logs</h3><p class="card-description">Monitor all user activities</p></div>
                <div class="flex gap-2">
                    <select class="form-control" style="width:auto;" onchange="window.location='?tab=logs&filter='+this.value">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Actions</option>
                        <option value="login" <?= $filter === 'login' ? 'selected' : '' ?>>Logins Only</option>
                        <option value="logout" <?= $filter === 'logout' ? 'selected' : '' ?>>Logouts Only</option>
                    </select>
                    <form method="POST" onsubmit="return confirm('Clear all logs?')">
                        <input type="hidden" name="action" value="clear_logs">
                        <button type="submit" class="btn btn-danger btn-sm">Clear Logs</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>Timestamp</th><th>User</th><th>Role</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?= date('M j, Y g:i A', strtotime($log['timestamp'])) ?></td>
                        <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                        <td><span class="badge badge-<?= $log['user_type'] === 'admin' ? 'purple' : ($log['user_type'] === 'faculty' ? 'blue' : ($log['user_type'] === 'superadmin' ? 'orange' : 'green')) ?>"><?= $log['user_type'] ?></span></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    </div>

    <?php else: ?>
    <!-- Analytics -->
    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Activity Breakdown</h3></div>
            <div class="card-content">
                <?php
                $items = [['Login', $loginCount, 'maroon'], ['Logout', $logoutCount, 'maroon'], ['Other', $otherCount, 'orange']];
                foreach ($items as $item):
                    $pct = $totalActions > 0 ? ($item[1] / $totalActions) * 100 : 0;
                ?>
                <div class="progress-bar-container">
                    <div class="progress-label"><span><?= $item[0] ?></span><span><?= $item[1] ?></span></div>
                    <div class="progress-bar"><div class="progress-fill progress-fill-<?= $item[2] ?>" style="width:<?= $pct ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Most Active Users</h3></div>
            <div class="card-content">
                <?php while ($au = $activeUsers->fetch_assoc()): ?>
                <div class="user-item">
                    <div class="user-item-info"><p><?= htmlspecialchars($au['first_name'] . ' ' . $au['last_name']) ?></p><p><?= $au['user_type'] ?></p></div>
                    <span class="user-count-badge"><?= $au['cnt'] ?></span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
