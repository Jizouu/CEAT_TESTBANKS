<?php
require_once 'config.php';
requireRole('faculty');
$pageTitle = 'UPHSD Test Bank - Student Results';
$facultyId = $_SESSION['user']['user_id'];

include 'includes/header.php';

$classId = intval($_GET['class_id'] ?? 0);
$courseCode = $_GET['course_code'] ?? '';

// Faculty's classes
$classes = $conn->query("SELECT class_id, class_name FROM classes WHERE faculty_id = $facultyId AND status = 'active'");

// Build results
$results = null;
if ($classId) {
    $escapedCode = $conn->real_escape_string($courseCode);
    $where = "sa.class_id = $classId";
    if ($courseCode) $where .= " AND sa.course_code = '$escapedCode'";

    $results = $conn->query("
        SELECT u.user_id, u.first_name, u.last_name, sa.course_code, sa.attempt_group_id,
            sa.date_taken, COUNT(*) as total_q, SUM(sa.is_correct) as correct,
            (SELECT switches FROM cheat_logs cl WHERE cl.user_id = u.user_id AND cl.course_code = sa.course_code LIMIT 1) as cheat_count
        FROM student_attempts sa
        JOIN users_new u ON sa.user_id = u.user_id
        WHERE $where
        GROUP BY sa.attempt_group_id
        ORDER BY sa.date_taken DESC
    ");
}
?>
<div class="main-content">
    <div class="flex-between mb-4">
        <a href="dashboard.php" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back</a>
        <div class="flex gap-2">
            <select class="form-control" style="width:auto;" onchange="if(this.value) window.location='?class_id='+this.value">
                <option value="">Select class</option>
                <?php while ($c = $classes->fetch_assoc()): ?><option value="<?= $c['class_id'] ?>" <?= $c['class_id']==$classId?'selected':'' ?>><?= htmlspecialchars($c['class_name']) ?></option><?php endwhile; ?>
            </select>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Student Exam Results</h2></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr><th>Student</th><th>Course</th><th>Score</th><th>%</th><th>Cheat Warnings</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if ($results && $results->num_rows > 0): ?>
                        <?php while ($r = $results->fetch_assoc()):
                            $pct = $r['total_q'] > 0 ? round(($r['correct'] / $r['total_q']) * 100) : 0;
                            $cheatCount = intval($r['cheat_count'] ?? 0);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td><span class="badge badge-maroon"><?= htmlspecialchars($r['course_code']) ?></span></td>
                            <td><?= $r['correct'] ?> / <?= $r['total_q'] ?></td>
                            <td><span class="badge badge-<?= $pct >= 50 ? 'green' : 'red' ?>"><?= $pct ?>%</span></td>
                            <td>
                                <?php if ($cheatCount > 0): ?>
                                    <span class="badge badge-red" style="font-weight:bold;"><?= $cheatCount ?> warnings</span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.8rem;">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= date('M j, Y g:i A', strtotime($r['date_taken'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No results found. Select a class above.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
</body></html>
