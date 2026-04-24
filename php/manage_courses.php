<?php
require_once 'config.php';
requireRole(['admin', 'superadmin']);
$pageTitle = 'UPHSD Test Bank - Manage Courses';
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $code = trim($_POST['course_code'] ?? '');
        $name = trim($_POST['course_name'] ?? '');
        $progId = intval($_POST['prog_id'] ?? 0);
        if ($code && $name && $progId) {
            $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, prog_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $code, $name, $progId);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $user['user_id'], $user['role'], "Added course: $code", $user['first_name'], $user['last_name']);
            setFlash('success', "Course '$code' added!");
        }
    }
    if ($action === 'delete') {
        $id = intval($_POST['course_id'] ?? 0);
        $conn->query("DELETE FROM courses WHERE id = $id");
        logActivity($conn, $user['user_id'], $user['role'], "Deleted course #$id", $user['first_name'], $user['last_name']);
        setFlash('success', 'Course deleted.');
    }
    header("Location: manage_courses.php");
    exit;
}

$courses = $conn->query("SELECT c.*, p.program_code, p.program_name FROM courses c LEFT JOIN programs p ON c.prog_id = p.prog_id ORDER BY c.course_code");
$programs = $conn->query("SELECT prog_id, program_code, program_name FROM programs ORDER BY program_code");

include 'includes/header.php';
?>
<div class="main-content">
    <div class="flex-between mb-4">
        <a href="dashboard.php" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>
        <button onclick="document.getElementById('addModal').classList.add('active')" class="btn btn-orange"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Course</button>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Course Directory</h2><p class="card-description"><?= $courses->num_rows ?> course(s) available</p></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>Code</th><th>Course Name</th><th>Program</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <?php while ($c = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><span class="badge badge-orange"><?= htmlspecialchars($c['course_code']) ?></span></td>
                        <td><?= htmlspecialchars($c['course_name']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($c['program_code'] ?? 'N/A') ?></td>
                        <td class="text-right">
                            <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
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
    <div class="modal-header"><h3>Add New Course</h3><p>Create a new course</p></div>
    <form method="POST"><input type="hidden" name="action" value="add">
        <div class="modal-body">
            <div class="form-group"><label>Course Code</label><input type="text" name="course_code" class="form-control" placeholder="CS101" required></div>
            <div class="form-group"><label>Course Name</label><input type="text" name="course_name" class="form-control" placeholder="Introduction to Programming" required></div>
            <div class="form-group"><label>Program</label>
                <select name="prog_id" class="form-control" required><option value="">Select Program</option>
                    <?php while ($p = $programs->fetch_assoc()): ?>
                    <option value="<?= $p['prog_id'] ?>"><?= htmlspecialchars($p['program_code'] . ' - ' . $p['program_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-dark" onclick="document.getElementById('addModal').classList.remove('active')">Cancel</button>
            <button type="submit" class="btn btn-maroon">Add Course</button>
        </div>
    </form>
</div></div>
<script>document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});</script>
</body></html>
