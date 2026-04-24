<?php
// Student Dashboard - included from dashboard.php
$userId = $_SESSION['user']['user_id'];

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'enroll') {
        $classCode = trim($_POST['class_code'] ?? '');
        if ($classCode) {
            $stmt = $conn->prepare("SELECT class_id, class_name FROM classes WHERE class_code = ? AND status = 'active'");
            $stmt->bind_param("s", $classCode);
            $stmt->execute();
            $classResult = $stmt->get_result();

            if ($classResult->num_rows === 1) {
                $classData = $classResult->fetch_assoc();
                // Check if already enrolled
                $checkStmt = $conn->prepare("SELECT enrollment_id FROM class_enrollments WHERE class_id = ? AND user_id = ?");
                $checkStmt->bind_param("ii", $classData['class_id'], $userId);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows === 0) {
                    $insertStmt = $conn->prepare("INSERT INTO class_enrollments (class_id, user_id) VALUES (?, ?)");
                    $insertStmt->bind_param("ii", $classData['class_id'], $userId);
                    $insertStmt->execute();
                    $insertStmt->close();
                    logActivity($conn, $userId, 'student', "Enrolled in Class Code: $classCode", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
                    setFlash('success', "Successfully enrolled in {$classData['class_name']}!");
                } else {
                    setFlash('error', 'You are already enrolled in this class.');
                }
                $checkStmt->close();
            } else {
                setFlash('error', 'Invalid class code or class is not active.');
            }
            $stmt->close();
        }
        header("Location: dashboard.php");
        exit;
    }

    if ($_POST['action'] === 'unenroll') {
        $enrollmentId = intval($_POST['enrollment_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM class_enrollments WHERE enrollment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $enrollmentId, $userId);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Successfully unenrolled from class.');
        header("Location: dashboard.php");
        exit;
    }
}

// Fetch enrolled classes with course deadlines
$enrollments = $conn->query("
    SELECT ce.enrollment_id, ce.class_id, ce.enrollment_date, ce.is_approved,
           c.class_name, c.class_code, c.status,
           CONCAT(u.first_name, ' ', u.last_name) as instructor
    FROM class_enrollments ce
    JOIN classes c ON ce.class_id = c.class_id
    LEFT JOIN users_new u ON c.faculty_id = u.user_id
    WHERE ce.user_id = $userId AND c.status = 'active'
    ORDER BY ce.enrollment_date DESC
");

// Fetch upcoming deadlines
$deadlines = $conn->query("
    SELECT cc.class_course_id, cc.opening_date, cc.deadline,
           co.course_code, co.course_name,
           cl.class_name, cl.class_id
    FROM class_courses cc
    JOIN courses co ON cc.course_id = co.id
    JOIN classes cl ON cc.class_id = cl.class_id
    JOIN class_enrollments ce ON ce.class_id = cl.class_id AND ce.user_id = $userId
    WHERE cc.deadline >= NOW() AND cl.status = 'active'
    ORDER BY cc.deadline ASC
");
?>

<div class="main-content">
    <!-- Upcoming Deadlines -->
    <div class="mb-8">
        <h2 style="font-size:1.15rem;font-weight:600;margin-bottom:16px;">Test Deadlines & Timeline</h2>
        <?php if ($deadlines->num_rows > 0): ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php while ($dl = $deadlines->fetch_assoc()): ?>
            <div class="card deadline-card">
                <div class="deadline-info">
                    <div class="deadline-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <div class="deadline-title"><?= htmlspecialchars($dl['course_name']) ?></div>
                        <div class="deadline-course"><?= htmlspecialchars($dl['course_code']) ?> — <?= htmlspecialchars($dl['class_name']) ?></div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:16px;">
                    <div class="deadline-due">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Due: <?= date('M j, Y g:i A', strtotime($dl['deadline'])) ?>
                    </div>
                    <a href="exam.php?class_id=<?= $dl['class_id'] ?>&course_code=<?= urlencode($dl['course_code']) ?>" class="btn btn-maroon btn-sm">Take Exam</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="card"><div class="empty-state"><p>No upcoming deadlines</p></div></div>
        <?php endif; ?>
    </div>

    <!-- My Classes -->
    <div class="flex-between mb-4">
        <h2 style="font-size:1.15rem;font-weight:600;">My Classes</h2>
        <button onclick="document.getElementById('enrollModal').classList.add('active')" class="btn btn-orange">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Enroll in Class
        </button>
    </div>

    <div class="grid-3">
        <?php if ($enrollments->num_rows > 0): ?>
            <?php while ($enr = $enrollments->fetch_assoc()): ?>
            <div class="card class-card">
                <div class="card-header">
                    <h3 class="card-title"><?= htmlspecialchars($enr['class_name']) ?></h3>
                    <p class="card-description">Code: <?= htmlspecialchars($enr['class_code']) ?></p>
                </div>
                <div class="card-content">
                    <p class="instructor">Instructor: <?= htmlspecialchars($enr['instructor'] ?? 'N/A') ?></p>
                    <p class="enrolled-date">Enrolled: <?= date('M j, Y', strtotime($enr['enrollment_date'])) ?></p>
                    <div class="card-actions">
                        <a href="exam.php?class_id=<?= $enr['class_id'] ?>" class="btn btn-maroon btn-sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Take Exam
                        </a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Unenroll from this class?')">
                            <input type="hidden" name="action" value="unenroll">
                            <input type="hidden" name="enrollment_id" value="<?= $enr['enrollment_id'] ?>">
                            <button type="submit" class="btn btn-icon btn-danger" title="Unenroll">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card" style="grid-column:1/-1;">
                <div class="empty-state"><p>No enrolled classes. Click "Enroll in Class" to get started.</p></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enroll Modal -->
<div class="modal-overlay" id="enrollModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Enroll in a Class</h3>
            <p>Enter the class code provided by your instructor</p>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="enroll">
            <div class="modal-body">
                <div class="form-group">
                    <label for="class_code">Class Code</label>
                    <input type="text" id="class_code" name="class_code" class="form-control" placeholder="e.g., 950ED8" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" onclick="document.getElementById('enrollModal').classList.remove('active')">Cancel</button>
                <button type="submit" class="btn btn-maroon">Enroll</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('active'); });
});
</script>
