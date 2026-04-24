<?php
require_once 'config.php';
requireRole('superadmin');
$pageTitle = 'UPHSD Test Bank - System Settings';
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_setting') {
        $key = trim($_POST['setting_key'] ?? '');
        $value = trim($_POST['setting_value'] ?? '');
        if ($key) {
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $user['user_id'], $user['role'], "Updated setting: $key", $user['first_name'], $user['last_name']);
            setFlash('success', 'Setting updated.');
        }
    }
    if ($action === 'add_setting') {
        $key = trim($_POST['new_key'] ?? '');
        $value = trim($_POST['new_value'] ?? '');
        if ($key) {
            $stmt = $conn->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
            $stmt->close();
            setFlash('success', "Setting '$key' added.");
        }
    }
    if ($action === 'delete_setting') {
        $key = $_POST['setting_key'] ?? '';
        $conn->query("DELETE FROM system_settings WHERE setting_key = '" . $conn->real_escape_string($key) . "'");
        setFlash('success', 'Setting deleted.');
    }
    header("Location: system_settings.php");
    exit;
}

$settings = $conn->query("SELECT * FROM system_settings ORDER BY setting_key");

// System info
$dbSize = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.tables WHERE table_schema = 'online_assessment_system'")->fetch_assoc()['size'] ?? '0';
$tableCount = $conn->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'online_assessment_system'")->fetch_assoc()['cnt'] ?? '0';

include 'includes/header.php';
?>
<div class="main-content">
    <a href="dashboard.php" class="btn btn-ghost mb-4"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>

    <div class="grid-2 mb-8">
        <div class="card card-stat card-border-maroon"><div class="card-header"><h3 class="card-title" style="font-size:0.85rem;">Database Size</h3></div><div class="card-content"><div class="stat-value"><?= $dbSize ?> MB</div></div></div>
        <div class="card card-stat card-border-orange"><div class="card-header"><h3 class="card-title" style="font-size:0.85rem;">Tables</h3></div><div class="card-content"><div class="stat-value"><?= $tableCount ?></div></div></div>
    </div>

    <div class="card mb-6">
        <div class="card-header"><div class="flex-between"><h2 class="card-title">System Settings</h2>
            <button onclick="document.getElementById('addSettingModal').classList.add('active')" class="btn btn-orange btn-sm"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Setting</button>
        </div></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>Key</th><th>Value</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <?php while ($s = $settings->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['setting_key']) ?></strong></td>
                        <td>
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="action" value="update_setting">
                                <input type="hidden" name="setting_key" value="<?= htmlspecialchars($s['setting_key']) ?>">
                                <input type="text" name="setting_value" value="<?= htmlspecialchars($s['setting_value']) ?>" class="form-control" style="max-width:300px;">
                                <button type="submit" class="btn btn-maroon btn-sm">Save</button>
                            </form>
                        </td>
                        <td class="text-right">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete_setting"><input type="hidden" name="setting_key" value="<?= htmlspecialchars($s['setting_key']) ?>"><button class="btn btn-ghost btn-sm" style="color:var(--red);"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<div class="modal-overlay" id="addSettingModal"><div class="modal">
    <div class="modal-header"><h3>Add Setting</h3></div>
    <form method="POST"><input type="hidden" name="action" value="add_setting">
        <div class="modal-body">
            <div class="form-group"><label>Key</label><input type="text" name="new_key" class="form-control" required></div>
            <div class="form-group"><label>Value</label><input type="text" name="new_value" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-dark" onclick="document.getElementById('addSettingModal').classList.remove('active')">Cancel</button><button type="submit" class="btn btn-maroon">Add</button></div>
    </form>
</div></div>
<script>document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});</script>
</body></html>
