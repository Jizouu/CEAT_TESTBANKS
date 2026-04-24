<?php
require_once 'config.php';
requireRole(['admin', 'superadmin']);
$pageTitle = 'UPHSD Test Bank - Manage Users';
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $fn = trim($_POST['first_name'] ?? '');
        $ln = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'student';
        $pass = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
        if ($fn && $ln && $email) {
            $stmt = $conn->prepare("INSERT INTO users_new (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fn, $ln, $email, $pass, $role);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $user['user_id'], $user['role'], "Created User: $fn $ln ($role)", $user['first_name'], $user['last_name']);
            setFlash('success', "User '$fn $ln' added!");
        }
    }
    if ($action === 'delete') {
        $uid = intval($_POST['user_id'] ?? 0);
        $conn->query("DELETE FROM users_new WHERE user_id = $uid");
        logActivity($conn, $user['user_id'], $user['role'], "Deleted user #$uid", $user['first_name'], $user['last_name']);
        setFlash('success', 'User deleted.');
    }
    header("Location: manage_users.php");
    exit;
}

$users = $conn->query("SELECT * FROM users_new ORDER BY role, first_name");
include 'includes/header.php';
?>
<div class="main-content">
    <div class="flex-between mb-4">
        <a href="dashboard.php" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>
        <button onclick="document.getElementById('addModal').classList.add('active')" class="btn btn-orange"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg> Add User</button>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="card-title">User Management</h2><p class="card-description"><?= $users->num_rows ?> user(s) in system</p></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?= $u['user_id'] ?></td>
                        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'purple' : ($u['role'] === 'faculty' ? 'blue' : ($u['role'] === 'superadmin' ? 'orange' : 'green')) ?>"><?= $u['role'] ?></span></td>
                        <td class="text-muted"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td class="text-right">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete user?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--red);"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<div class="modal-overlay" id="addModal"><div class="modal">
    <div class="modal-header"><h3>Add New User</h3><p>Create a new user account</p></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="modal-body">
            <div class="form-group"><label>First Name</label><input type="text" name="first_name" class="form-control" required></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last_name" class="form-control" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="form-group"><label>Role</label>
                <select name="role" class="form-control">
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" placeholder="Min 6 characters" required minlength="6"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-dark" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
            <button type="submit" class="btn btn-maroon">Add User</button>
        </div>
    </form>
</div></div>
<script>document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});</script>
</body></html>
