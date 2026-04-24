<?php
require_once 'config.php';
requireRole(['admin', 'superadmin']);
$pageTitle = 'UPHSD Test Bank - Manage Programs';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user = $_SESSION['user'];

    if ($action === 'add') {
        $code = trim($_POST['program_code'] ?? '');
        $name = trim($_POST['program_name'] ?? '');
        if ($code && $name) {
            $stmt = $conn->prepare("INSERT INTO programs (program_code, program_name) VALUES (?, ?)");
            $stmt->bind_param("ss", $code, $name);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $user['user_id'], $user['role'], "Added program: $name", $user['first_name'], $user['last_name']);
            setFlash('success', "Program '$name' added!");
        }
    }

    if ($action === 'delete') {
        $progId = intval($_POST['prog_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM programs WHERE prog_id = ?");
        $stmt->bind_param("i", $progId);
        $stmt->execute();
        $stmt->close();
        logActivity($conn, $user['user_id'], $user['role'], "Deleted program #$progId", $user['first_name'], $user['last_name']);
        setFlash('success', 'Program deleted.');
    }

    header("Location: manage_programs.php");
    exit;
}

$programs = $conn->query("SELECT * FROM programs ORDER BY program_code");

include 'includes/header.php';
?>
<div class="main-content">
    <div class="flex-between mb-4">
        <a href="dashboard.php" class="btn btn-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Dashboard
        </a>
        <button onclick="document.getElementById('addModal').classList.add('active')" class="btn btn-orange">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Program
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Academic Programs</h2>
            <p class="card-description"><?= $programs->num_rows ?> program(s) configured</p>
        </div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>Code</th><th>Program Name</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <?php if ($programs->num_rows > 0): ?>
                        <?php while ($p = $programs->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge badge-maroon"><?= htmlspecialchars($p['program_code']) ?></span></td>
                            <td><?= htmlspecialchars($p['program_name']) ?></td>
                            <td class="text-right">
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this program?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="prog_id" value="<?= $p['prog_id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">No programs yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header"><h3>Add New Program</h3><p>Create a new academic program</p></div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group"><label>Program Code</label><input type="text" name="program_code" class="form-control" placeholder="CS" required></div>
                <div class="form-group"><label>Program Name</label><input type="text" name="program_name" class="form-control" placeholder="College of Computer Studies" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-maroon">Add Program</button>
            </div>
        </form>
    </div>
</div>
<script>document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});</script>
</body></html>
