<?php
require_once 'config.php';
requireRole('faculty');
$pageTitle = 'UPHSD Test Bank - View Students';
$facultyId = $_SESSION['user']['user_id'];
$classId = intval($_GET['class_id'] ?? 0);

include 'includes/header.php';

// Get faculty's classes
$classes = $conn->query("SELECT class_id, class_name, class_code FROM classes WHERE faculty_id = $facultyId AND status = 'active' ORDER BY class_name");

if ($classId) {
    $classInfo = $conn->query("SELECT * FROM classes WHERE class_id = $classId AND faculty_id = $facultyId")->fetch_assoc();
    $students = $conn->query("
        SELECT ce.*, u.user_id, u.first_name, u.last_name, u.email
        FROM class_enrollments ce
        JOIN users_new u ON ce.user_id = u.user_id
        WHERE ce.class_id = $classId
        ORDER BY u.last_name, u.first_name
    ");
}
?>
<div class="main-content">
    <div class="flex-between mb-4">
        <a href="dashboard.php" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>
        <select class="form-control" style="width:auto;" onchange="if(this.value) window.location='?class_id='+this.value">
            <option value="">Select a class</option>
            <?php while ($c = $classes->fetch_assoc()): ?>
            <option value="<?= $c['class_id'] ?>" <?= $c['class_id'] == $classId ? 'selected' : '' ?>><?= htmlspecialchars($c['class_name']) ?> (<?= $c['class_code'] ?>)</option>
            <?php endwhile; ?>
        </select>
    </div>

    <?php if ($classId && $classInfo): ?>
    <div class="card">
        <div class="card-header"><h2 class="card-title"><?= htmlspecialchars($classInfo['class_name']) ?> — Students</h2><p class="card-description"><?= $students->num_rows ?> enrolled student(s)</p></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Enrolled</th></tr></thead>
                <tbody>
                    <?php while ($s = $students->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted"><?= $s['user_id'] ?></td>
                        <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($s['email']) ?></td>
                        <td class="text-muted"><?= date('M j, Y', strtotime($s['enrollment_date'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    </div>
    <?php else: ?>
    <div class="card"><div class="empty-state"><p>Select a class to view its students.</p></div></div>
    <?php endif; ?>
</div>
</body></html>
