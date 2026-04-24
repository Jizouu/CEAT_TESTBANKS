<?php
// Faculty Dashboard - included from dashboard.php
$facultyId = $_SESSION['user']['user_id'];

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_class') {
        $className = trim($_POST['class_name'] ?? '');
        $classCode = generateClassCode();
        if ($className) {
            $stmt = $conn->prepare("INSERT INTO classes (class_name, class_code, faculty_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $className, $classCode, $facultyId);
            $stmt->execute();
            $stmt->close();
            logActivity($conn, $facultyId, 'faculty', "Created Class: $className ($classCode)", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
            setFlash('success', "Class '$className' created! Code: $classCode");
        }
        header("Location: dashboard.php"); exit;
    }
    if ($_POST['action'] === 'delete_class') {
        $classId = intval($_POST['class_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ? AND faculty_id = ?");
        $stmt->bind_param("ii", $classId, $facultyId);
        $stmt->execute(); $stmt->close();
        setFlash('success', 'Class deleted.');
        header("Location: dashboard.php"); exit;
    }
    if ($_POST['action'] === 'archive_class') {
        $classId = intval($_POST['class_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE classes SET status = 'archived' WHERE class_id = ? AND faculty_id = ?");
        $stmt->bind_param("ii", $classId, $facultyId);
        $stmt->execute(); $stmt->close();
        logActivity($conn, $facultyId, 'faculty', "Archived Class", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
        setFlash('success', 'Class archived.');
        header("Location: dashboard.php"); exit;
    }
    if ($_POST['action'] === 'pull_course') {
        $classId = intval($_POST['class_id'] ?? 0);
        $courseId = intval($_POST['course_id'] ?? 0);
        $check = $conn->prepare("SELECT class_course_id FROM class_courses WHERE class_id = ? AND course_id = ?");
        $check->bind_param("ii", $classId, $courseId);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO class_courses (class_id, course_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $classId, $courseId);
            $stmt->execute(); $stmt->close();
            $cRes = $conn->query("SELECT course_code FROM courses WHERE id = $courseId");
            $cCode = $cRes->fetch_assoc()['course_code'] ?? '';
            logActivity($conn, $facultyId, 'faculty', "Pulled Course: $cCode", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
            setFlash('success', 'Course added to class.');
        } else {
            setFlash('error', 'Course already in this class.');
        }
        $check->close();
        header("Location: dashboard.php?tab=courses"); exit;
    }
}

// Fetch classes
$classes = $conn->query("
    SELECT c.*, COUNT(ce.enrollment_id) as student_count
    FROM classes c LEFT JOIN class_enrollments ce ON c.class_id = ce.class_id
    WHERE c.faculty_id = $facultyId AND c.status = 'active'
    GROUP BY c.class_id ORDER BY c.created_at DESC
");

$allCourses = $conn->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code");

$classCourses = $conn->query("
    SELECT cc.*, co.course_code, co.course_name, cl.class_name
    FROM class_courses cc JOIN courses co ON cc.course_id = co.id
    JOIN classes cl ON cc.class_id = cl.class_id
    WHERE cl.faculty_id = $facultyId AND cl.status = 'active'
    ORDER BY cl.class_name, co.course_code
");

$activeTab = $_GET['tab'] ?? 'classes';
?>
<div class="main-content">
    <div class="grid-4 mb-8">
        <a href="view_students.php" class="btn btn-maroon btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            View Students
        </a>
        <a href="view_student_results.php" class="btn btn-orange btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Student Results
        </a>
        <a href="archived_classes.php" class="btn btn-maroon btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/></svg>
            Archived Classes
        </a>
        <a href="faculty_analytics.php" class="btn btn-orange btn-nav">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Analytics
        </a>
    </div>

    <div class="tabs-nav">
        <a href="?tab=classes" class="tab-btn <?= $activeTab === 'classes' ? 'active' : '' ?>">Classes</a>
        <a href="?tab=courses" class="tab-btn <?= $activeTab === 'courses' ? 'active' : '' ?>">Courses</a>
    </div>

    <?php if ($activeTab === 'classes'): ?>
    <div class="flex-between mb-4">
        <h2 style="font-size:1.15rem;font-weight:600;">My Classes</h2>
        <button onclick="document.getElementById('createClassModal').classList.add('active')" class="btn btn-orange">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create Class
        </button>
    </div>
    <div class="grid-3">
        <?php if ($classes->num_rows > 0): while ($cls = $classes->fetch_assoc()): ?>
        <div class="card class-card">
            <div class="card-header">
                <h3 class="card-title"><?= htmlspecialchars($cls['class_name']) ?></h3>
                <p class="card-description">Code: <?= htmlspecialchars($cls['class_code']) ?> · <?= $cls['student_count'] ?> student(s)</p>
            </div>
            <div class="card-content">
                <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:12px;">Created: <?= date('M j, Y', strtotime($cls['created_at'])) ?></p>
                
                <a href="manage_class.php?id=<?= $cls['class_id'] ?>" class="btn btn-maroon btn-block mb-3" style="text-align:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:text-bottom;margin-right:4px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Manage Class Exams
                </a>

                <div class="card-actions">
                    <a href="view_students.php?class_id=<?= $cls['class_id'] ?>" class="btn btn-outline-dark btn-sm" style="flex:1;">Roster</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Archive?')"><input type="hidden" name="action" value="archive_class"><input type="hidden" name="class_id" value="<?= $cls['class_id'] ?>"><button class="btn btn-icon btn-outline-dark" title="Archive"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/></svg></button></form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete permanently?')"><input type="hidden" name="action" value="delete_class"><input type="hidden" name="class_id" value="<?= $cls['class_id'] ?>"><button class="btn btn-icon btn-danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button></form>
                </div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <div class="card" style="grid-column:1/-1;"><div class="empty-state"><p>No active classes.</p></div></div>
        <?php endif; ?>
    </div>

    <?php elseif ($activeTab === 'courses'): ?>
    <div class="flex-between mb-4">
        <h2 style="font-size:1.15rem;font-weight:600;">Class Courses Overview</h2>
    </div>
    <div class="card"><div class="card-content"><div class="table-wrapper">
        <table>
            <thead><tr><th>Class</th><th>Course Code</th><th>Course Name</th></tr></thead>
            <tbody>
                <?php if ($classCourses->num_rows > 0): while ($cc = $classCourses->fetch_assoc()): ?>
                <tr><td><?= htmlspecialchars($cc['class_name']) ?></td><td><span class="badge badge-maroon"><?= htmlspecialchars($cc['course_code']) ?></span></td><td><?= htmlspecialchars($cc['course_name']) ?></td></tr>
                <?php endwhile; else: ?>
                <tr><td colspan="3" class="text-center text-muted">No courses assigned to any class yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div></div></div>
    <?php endif; ?>
</div>

<!-- Create Class Modal -->
<div class="modal-overlay" id="createClassModal"><div class="modal">
    <div class="modal-header"><h3>Create New Class</h3><p>Add a new class for students</p></div>
    <form method="POST"><input type="hidden" name="action" value="create_class">
        <div class="modal-body"><div class="form-group"><label>Class Name</label><input type="text" name="class_name" class="form-control" placeholder="e.g., BSIT 1-2" required></div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-dark" onclick="document.getElementById('createClassModal').classList.remove('active')">Cancel</button><button type="submit" class="btn btn-maroon">Create</button></div>
    </form>
</div></div>

<script>document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});</script>
