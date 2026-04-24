<?php
require_once 'config.php';
requireRole('student');
$pageTitle = 'UPHSD Test Bank - Exam Results';
$userId = $_SESSION['user']['user_id'];
$attemptGroup = $_GET['attempt_group'] ?? '';

include 'includes/header.php';

if ($attemptGroup) {
    $escapedGroup = $conn->real_escape_string($attemptGroup);
    $attempts = $conn->query("
        SELECT sa.*, e.questions, e.subquestions, e.ch_1, e.ch_2, e.ch_3, e.ch_4, e.anskey, e.category
        FROM student_attempts sa
        JOIN exam e ON sa.exam_id = e.exam_id
        WHERE sa.attempt_group_id = '$escapedGroup' AND sa.user_id = $userId
    ");

    $totalCorrect = 0;
    $totalQ = 0;
    $rows = [];
    while ($r = $attempts->fetch_assoc()) { $rows[] = $r; $totalCorrect += $r['is_correct']; $totalQ++; }
    $pct = $totalQ > 0 ? round(($totalCorrect / $totalQ) * 100) : 0;
?>
<div class="main-content" style="max-width:800px;">
    <a href="dashboard.php" class="btn btn-ghost mb-4"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>

    <div class="card mb-6">
        <div class="card-content" style="text-align:center;padding:32px;">
            <h2 style="font-size:1.5rem;margin-bottom:8px;">Exam Results</h2>
            <p class="text-muted mb-4"><?= htmlspecialchars($rows[0]['course_code'] ?? '') ?></p>
            <div style="font-size:3rem;font-weight:700;color:<?= $pct >= 50 ? 'var(--green)' : 'var(--red)' ?>;"><?= $totalCorrect ?> / <?= $totalQ ?></div>
            <p style="font-size:1.1rem;color:var(--text-secondary);margin-top:8px;"><?= $pct ?>% Score</p>
        </div>
    </div>

    <?php foreach ($rows as $i => $r):
        $choices = ['ch_1' => $r['ch_1'], 'ch_2' => $r['ch_2'], 'ch_3' => $r['ch_3'], 'ch_4' => $r['ch_4']];
    ?>
    <div class="card question-card">
        <div class="card-header"><div class="question-number">Question <?= $i + 1 ?> · <?= $r['is_correct'] ? '<span class="badge badge-green">Correct</span>' : '<span class="badge badge-red">Wrong</span>' ?></div></div>
        <div class="card-content">
            <p class="question-text"><?= htmlspecialchars($r['questions']) ?></p>
            <?php foreach ($choices as $key => $val): if (trim($val)): ?>
                <div class="option-label <?= $key === $r['anskey'] ? 'option-correct' : ($key === $r['selected_choice'] && !$r['is_correct'] ? 'option-wrong' : '') ?>" style="cursor:default;">
                    <?= htmlspecialchars($val) ?>
                    <?php if ($key === $r['anskey']): ?> <strong style="color:var(--green);">✓ Correct</strong><?php endif; ?>
                    <?php if ($key === $r['selected_choice'] && $key !== $r['anskey']): ?> <strong style="color:var(--red);">✗ Your answer</strong><?php endif; ?>
                </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php } else { ?>
<div class="main-content"><div class="card"><div class="empty-state"><p>No results to display.</p></div></div></div>
<?php } ?>
</body></html>
