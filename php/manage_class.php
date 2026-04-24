<?php
require_once 'config.php';
requireRole('faculty');

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$classId = intval($_GET['id']);
$facultyId = $_SESSION['user']['user_id'];

// Check if faculty owns this class
$stmt = $conn->prepare("SELECT * FROM classes WHERE class_id = ? AND faculty_id = ?");
$stmt->bind_param("ii", $classId, $facultyId);
$stmt->execute();
$classData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$classData) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Manage Class: ' . htmlspecialchars($classData['class_code']);

// Handle Course Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course']) && isset($_POST['course_id'])) {
    $courseId = intval($_POST['course_id']);
    
    // Check if course exists
    $cRes = $conn->query("SELECT course_code FROM courses WHERE id = $courseId");
    $courseInfo = $cRes->fetch_assoc();
    
    if ($courseInfo) {
        $courseCodeVal = $courseInfo['course_code'];
        
        // Add to class_courses
        $stmt = $conn->prepare("INSERT IGNORE INTO class_courses (class_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $classId, $courseId);
        
        if ($stmt->execute()) {
            logActivity($conn, $facultyId, 'faculty', "Pulled Course: $courseCodeVal into Class: " . $classData['class_name'], $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
            setFlash('success', "Course $courseCodeVal successfully added to class.");
        }
        $stmt->close();
    }
    header("Location: manage_class.php?id=$classId");
    exit;
}

// Handle Course Removal
if (isset($_GET['remove_course_id'])) {
    $removeId = intval($_GET['remove_course_id']);
    $stmt = $conn->prepare("DELETE FROM class_courses WHERE class_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $classId, $removeId);
    if ($stmt->execute()) {
        setFlash('success', 'Course removed from class.');
    }
    $stmt->close();
    header("Location: manage_class.php?id=$classId");
    exit;
}

// Fetch Assigned Courses
$assignedCourses = [];
$resCourses = $conn->query("
    SELECT c.* FROM courses c 
    JOIN class_courses cc ON c.id = cc.course_id 
    WHERE cc.class_id = $classId
");
while ($row = $resCourses->fetch_assoc()) {
    $assignedCourses[] = $row;
}

// Fetch Available Courses
$allCourses = [];
$resAll = $conn->query("
    SELECT * FROM courses 
    WHERE id NOT IN (
        SELECT course_id FROM class_courses WHERE class_id = $classId
    ) 
    ORDER BY course_name ASC
");
while ($row = $resAll->fetch_assoc()) {
    $allCourses[] = $row;
}

include 'includes/header.php';
?>
<div class="main-content">
    <div class="flex-between mb-6">
        <a href="dashboard.php" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>
        <div style="text-align: right;">
            <h2 style="font-size: 1.5rem; font-weight: bold;"><?= htmlspecialchars($classData['class_name']) ?></h2>
            <p class="text-muted">Class Code: <span class="badge badge-maroon"><?= htmlspecialchars($classData['class_code']) ?></span></p>
        </div>
    </div>

    <div class="grid-2" style="gap: 24px; align-items: start;">
        <!-- Left Panel: Add Courses -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Course</h3>
                <p class="card-description">Pull a course from the databank into this class.</p>
            </div>
            <div class="card-content">
                <form method="POST">
                    <div class="form-group">
                        <input type="text" id="courseSearch" class="form-control" placeholder="Search course name or code..." onkeyup="filterCourseList()">
                    </div>
                    <div class="form-group">
                        <select name="course_id" id="courseSelect" class="form-control" size="10" required style="height: 250px; overflow-y: auto;">
                            <?php foreach ($allCourses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_course" class="btn btn-maroon btn-block mt-4">Add Selected Course</button>
                </form>
            </div>
        </div>

        <!-- Right Panel: Assigned Courses -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned Assessments</h3>
                <p class="card-description">Courses currently active in this class.</p>
            </div>
            <div class="card-content">
                <?php if (empty($assignedCourses)): ?>
                    <div class="empty-state">
                        <p>No courses assigned to this class yet.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($assignedCourses as $c): ?>
                            <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <h4 style="font-weight: 600; font-size: 1.1rem;"><span class="badge badge-maroon mr-2"><?= htmlspecialchars($c['course_code']) ?></span> <?= htmlspecialchars($c['course_name']) ?></h4>
                                    <a href="manage_class.php?id=<?= $classId ?>&remove_course_id=<?= $c['id'] ?>" class="btn btn-icon btn-danger" onclick="return confirm('Remove course from class?')" title="Remove Course">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </a>
                                </div>
                                <div class="grid-3" style="gap: 8px;">
                                    <a href="editexam.php?id=<?= urlencode($c['course_code']) ?>&class_id=<?= $classId ?>" class="btn btn-outline-dark" style="text-align:center; padding: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:block;margin:0 auto 4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit Exam
                                    </a>
                                    <a href="view_student_results.php?course_code=<?= urlencode($c['course_code']) ?>&class_id=<?= $classId ?>" class="btn btn-outline-dark" style="text-align:center; padding: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:block;margin:0 auto 4px;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                        Results
                                    </a>
                                    <a href="print_exam.php?id=<?= urlencode($c['course_code']) ?>&class_id=<?= $classId ?>" target="_blank" class="btn btn-outline-dark" style="text-align:center; padding: 8px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:block;margin:0 auto 4px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                        Print
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function filterCourseList() {
    let input = document.getElementById('courseSearch').value.toUpperCase();
    let select = document.getElementById('courseSelect');
    let options = select.options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === "") continue;
        let matches = options[i].text.toUpperCase().includes(input);
        options[i].style.display = matches ? "" : "none";
    }
}
</script>
</body></html>
