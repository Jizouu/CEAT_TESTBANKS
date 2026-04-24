<?php
require_once 'config.php';
requireRole('faculty');
$pageTitle = 'UPHSD Test Bank - Archived Classes';
$facultyId = $_SESSION['user']['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $classId = intval($_POST['class_id'] ?? 0);
    if ($action === 'restore') {
        $conn->query("UPDATE classes SET status = 'active' WHERE class_id = $classId AND faculty_id = $facultyId");
        logActivity($conn, $facultyId, 'faculty', "Restored archived class", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
        setFlash('success', 'Class restored.');
    }
    if ($action === 'delete_permanent') {
        $conn->query("DELETE FROM classes WHERE class_id = $classId AND faculty_id = $facultyId AND status = 'archived'");
        logActivity($conn, $facultyId, 'faculty', "Permanently Deleted Class", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
        setFlash('success', 'Class permanently deleted.');
    }
    header("Location: archived_classes.php");
    exit;
}

$archived = $conn->query("
    SELECT c.*, COUNT(ce.enrollment_id) as student_count
    FROM classes c LEFT JOIN class_enrollments ce ON c.class_id = ce.class_id
    WHERE c.faculty_id = $facultyId AND c.status = 'archived'
    GROUP BY c.class_id ORDER BY c.created_at DESC
");

include 'includes/header.php';
?>
<div class="main-content">
    <div class="flex-between mb-4">
        <a href="dashboard.php" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Archived Classes</h2><p class="card-description"><?= $archived->num_rows ?> archived class(es)</p></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>Class Name</th><th>Code</th><th>Students</th><th>Created</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    <?php if ($archived->num_rows > 0): ?>
                        <?php while ($a = $archived->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['class_name']) ?></td>
                            <td><span class="badge badge-maroon"><?= htmlspecialchars($a['class_code']) ?></span></td>
                            <td><?= $a['student_count'] ?></td>
                            <td class="text-muted"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
                            <td class="text-right flex gap-2" style="justify-content:flex-end;">
                                <form method="POST" style="display:inline;"><input type="hidden" name="action" value="restore"><input type="hidden" name="class_id" value="<?= $a['class_id'] ?>"><button type="submit" class="btn btn-maroon btn-sm">Restore</button></form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete?')"><input type="hidden" name="action" value="delete_permanent"><input type="hidden" name="class_id" value="<?= $a['class_id'] ?>"><button type="submit" class="btn btn-danger btn-sm">Delete</button></form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No archived classes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
</body></html>
