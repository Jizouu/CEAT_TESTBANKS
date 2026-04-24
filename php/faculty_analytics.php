<?php
require_once 'config.php';
requireRole('faculty');
$pageTitle = 'UPHSD Test Bank - Analytics';
$facultyId = $_SESSION['user']['user_id'];

include 'includes/header.php';

// Get faculty's active classes
$classes = $conn->query("SELECT class_id, class_name FROM classes WHERE faculty_id = $facultyId AND status = 'active'");
$classIds = [];
while ($c = $classes->fetch_assoc()) $classIds[] = $c['class_id'];
$classIdStr = implode(',', $classIds) ?: '0';

// Aggregate data
$totalStudents = $conn->query("SELECT COUNT(DISTINCT user_id) as cnt FROM class_enrollments WHERE class_id IN ($classIdStr)")->fetch_assoc()['cnt'] ?? 0;
$totalAttempts = $conn->query("SELECT COUNT(DISTINCT attempt_group_id) as cnt FROM student_attempts WHERE class_id IN ($classIdStr)")->fetch_assoc()['cnt'] ?? 0;
$avgScore = $conn->query("SELECT AVG(is_correct) * 100 as avg FROM student_attempts WHERE class_id IN ($classIdStr)")->fetch_assoc()['avg'] ?? 0;

// Per-course stats
$courseStats = $conn->query("
    SELECT course_code, COUNT(DISTINCT attempt_group_id) as attempts,
           AVG(is_correct) * 100 as avg_score,
           COUNT(DISTINCT user_id) as unique_students
    FROM student_attempts WHERE class_id IN ($classIdStr)
    GROUP BY course_code ORDER BY course_code
");
?>
<div class="main-content">
    <a href="dashboard.php" class="btn btn-ghost mb-4"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>

    <div class="grid-3 mb-8">
        <div class="card card-stat card-border-maroon"><div class="card-header"><h3 class="card-title" style="font-size:0.85rem;">Total Students</h3></div><div class="card-content"><div class="stat-value"><?= $totalStudents ?></div></div></div>
        <div class="card card-stat card-border-orange"><div class="card-header"><h3 class="card-title" style="font-size:0.85rem;">Total Attempts</h3></div><div class="card-content"><div class="stat-value"><?= $totalAttempts ?></div></div></div>
        <div class="card card-stat card-border-maroon"><div class="card-header"><h3 class="card-title" style="font-size:0.85rem;">Avg Score</h3></div><div class="card-content"><div class="stat-value"><?= round($avgScore) ?>%</div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Performance by Course</h2></div>
        <div class="card-content">
            <?php if ($courseStats->num_rows > 0): ?>
                <?php while ($cs = $courseStats->fetch_assoc()): ?>
                <div class="progress-bar-container">
                    <div class="progress-label">
                        <span><strong><?= htmlspecialchars($cs['course_code']) ?></strong> · <?= $cs['unique_students'] ?> student(s) · <?= $cs['attempts'] ?> attempt(s)</span>
                        <span><?= round($cs['avg_score']) ?>%</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill progress-fill-<?= $cs['avg_score'] >= 50 ? 'maroon' : 'orange' ?>" style="width:<?= round($cs['avg_score']) ?>%"></div></div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted text-center">No exam data available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body></html>
