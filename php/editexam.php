<?php
require_once 'config.php';
requireRole(['faculty', 'admin']);

$courseCode = $_GET['id'] ?? '';
$classId = intval($_GET['class_id'] ?? 0);

if (!$courseCode || !$classId) {
    header('Location: dashboard.php');
    exit;
}

$escapedCode = $conn->real_escape_string($courseCode);

// Fetch course and class details
$stmt = $conn->prepare("
    SELECT c.course_name, cc.opening_date, cc.deadline, cc.easy_limit, cc.medium_limit, cc.hard_limit, cc.selected_subtopic, cc.show_correct_after_three, cc.show_right_wrong_feedback 
    FROM courses c 
    JOIN class_courses cc ON c.id = cc.course_id 
    WHERE c.course_code = ? AND cc.class_id = ?
");
$stmt->bind_param("si", $courseCode, $classId);
$stmt->execute();
$courseData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$courseData) {
    header('Location: dashboard.php');
    exit;
}

// Ensure exam_uploads directory exists
$uploadDir = "exam_uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function uploadImage($fileKey, $uploadDir) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
        $fileName = time() . "_" . basename($_FILES[$fileKey]["name"]);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $targetPath)) {
            return $targetPath;
        }
    }
    return null;
}

// 1. Handle Exam Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $open = $_POST['opening_date'];
    $dead = $_POST['deadline'];
    $stmt = $conn->prepare("UPDATE class_courses cc JOIN courses c ON cc.course_id = c.id SET cc.opening_date = ?, cc.deadline = ? WHERE c.course_code = ? AND cc.class_id = ?");
    $stmt->bind_param("sssi", $open, $dead, $courseCode, $classId);
    if ($stmt->execute()) setFlash('success', 'Exam schedule saved.');
    $stmt->close();
    header("Location: editexam.php?id=$escapedCode&class_id=$classId");
    exit;
}

// 2. Handle Add Singular Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $qImg = uploadImage('q_img_file', $uploadDir) ?? "";
    $c1Img = uploadImage('ch1_img_file', $uploadDir) ?? "";
    $c2Img = uploadImage('ch2_img_file', $uploadDir) ?? "";
    $c3Img = uploadImage('ch3_img_file', $uploadDir) ?? "";
    $c4Img = uploadImage('ch4_img_file', $uploadDir) ?? "";

    $bTitle = $_POST['book_title'] ?? '';
    $vol = $_POST['volume'] ?? '';
    $auth = $_POST['author'] ?? '';
    $pDate = $_POST['pub_date'] ?? '';
    $subt = $_POST['subtopic'] ?? '';
    $quest = $_POST['question'] ?? '';
    $subq = $_POST['subquestion'] ?? '';
    $c1 = $_POST['ch1'] ?? '';
    $c2 = $_POST['ch2'] ?? '';
    $c3 = $_POST['ch3'] ?? '';
    $c4 = $_POST['ch4'] ?? '';
    $ans = $_POST['anskey'] ?? '';
    $cat = $_POST['category'] ?? 'Easy';
    $cName = $courseData['course_name'];

    $stmt = $conn->prepare("INSERT INTO exam (course_code, course_name, book_title, volume, author, published_date, subtopic, questions, subquestions, ch_1, ch_2, ch_3, ch_4, anskey, category, question_img, ch_1_img, ch_2_img, ch_3_img, ch_4_img) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssssssssssss", $courseCode, $cName, $bTitle, $vol, $auth, $pDate, $subt, $quest, $subq, $c1, $c2, $c3, $c4, $ans, $cat, $qImg, $c1Img, $c2Img, $c3Img, $c4Img);
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['user']['user_id'], $_SESSION['user']['role'], "Added Question to $courseCode: $quest", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
        setFlash('success', 'Question added successfully.');
    }
    $stmt->close();
    header("Location: editexam.php?id=$escapedCode&class_id=$classId");
    exit;
}

// 3. Handle CSV Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_csv'])) {
    if ($_FILES['csv_file']['size'] > 0) {
        $file = fopen($_FILES['csv_file']['tmp_name'], "r");
        fgetcsv($file); // Skip header

        $stmt = $conn->prepare("INSERT INTO exam (course_code, course_name, book_title, volume, author, published_date, subtopic, questions, subquestions, ch_1, ch_2, ch_3, ch_4, anskey, category, question_img, ch_1_img, ch_2_img, ch_3_img, ch_4_img) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $cName = $courseData['course_name'];
        $lastQ = '';
        
        while (($col = fgetcsv($file, 10000, ",")) !== FALSE) {
            $qText = !empty(trim($col[5] ?? '')) ? trim($col[5]) : $lastQ;
            $lastQ = $qText;

            $bTitle = $col[0] ?? ''; $vol = $col[1] ?? ''; $auth = $col[2] ?? ''; $pDate = $col[3] ?? '';
            $subt = $col[4] ?? ''; $subq = $col[6] ?? '';
            $c1 = $col[7] ?? ''; $c2 = $col[8] ?? ''; $c3 = $col[9] ?? ''; $c4 = $col[10] ?? '';
            $ans = $col[11] ?? ''; $cat = $col[12] ?? '';
            $qImg = $col[13] ?? ''; $c1Img = $col[14] ?? ''; $c2Img = $col[15] ?? ''; $c3Img = $col[16] ?? ''; $c4Img = $col[17] ?? '';

            $stmt->bind_param("ssssssssssssssssssss", $courseCode, $cName, $bTitle, $vol, $auth, $pDate, $subt, $qText, $subq, $c1, $c2, $c3, $c4, $ans, $cat, $qImg, $c1Img, $c2Img, $c3Img, $c4Img);
            $stmt->execute();
        }
        fclose($file);
        $stmt->close();
        setFlash('success', 'CSV questions imported successfully.');
    }
    header("Location: editexam.php?id=$escapedCode&class_id=$classId");
    exit;
}

// 4. Handle Exam Rules (Limits and Subtopics)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rules'])) {
    $eLimit = intval($_POST['easy_limit'] ?? 0);
    $mLimit = intval($_POST['medium_limit'] ?? 0);
    $hLimit = intval($_POST['hard_limit'] ?? 0);
    
    $targetSubs = $_POST['target_subtopic'] ?? [];
    if (!is_array($targetSubs)) $targetSubs = [$targetSubs];
    $targetSubs = array_filter($targetSubs);
    $subtopicStr = implode('|', $targetSubs);
    
    // Verify question counts
    $subtopicFilter = "";
    if (!empty($subtopicStr) && $subtopicStr !== 'All') {
        $inClause = implode(',', array_map(function($s) use ($conn) { return "'" . $conn->real_escape_string(trim($s)) . "'"; }, $targetSubs));
        $subtopicFilter = " AND subtopic IN ($inClause)";
    }
    
    $eCount = $conn->query("SELECT COUNT(*) as c FROM exam WHERE course_code = '$escapedCode' $subtopicFilter AND LOWER(category) = 'easy'")->fetch_assoc()['c'];
    $mCount = $conn->query("SELECT COUNT(*) as c FROM exam WHERE course_code = '$escapedCode' $subtopicFilter AND LOWER(category) = 'medium'")->fetch_assoc()['c'];
    $hCount = $conn->query("SELECT COUNT(*) as c FROM exam WHERE course_code = '$escapedCode' $subtopicFilter AND LOWER(category) = 'hard'")->fetch_assoc()['c'];
    
    if ($eCount < $eLimit || $mCount < $mLimit || $hCount < $hLimit) {
        setFlash('error', "Not enough questions. Available: Easy($eCount), Medium($mCount), Hard($hCount).");
    } else {
        $stmt = $conn->prepare("UPDATE class_courses SET easy_limit = ?, medium_limit = ?, hard_limit = ?, selected_subtopic = ? WHERE course_id = (SELECT id FROM courses WHERE course_code = ?) AND class_id = ?");
        $stmt->bind_param("iiissi", $eLimit, $mLimit, $hLimit, $subtopicStr, $courseCode, $classId);
        if ($stmt->execute()) setFlash('success', 'Exam rules updated successfully.');
        $stmt->close();
    }
    header("Location: editexam.php?id=$escapedCode&class_id=$classId");
    exit;
}

// 5. Handle Visibility Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_visibility'])) {
    $showCorrect = isset($_POST['show_correct']) ? 1 : 0;
    $showFeedback = isset($_POST['show_feedback']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE class_courses SET show_correct_after_three = ?, show_right_wrong_feedback = ? WHERE course_id = (SELECT id FROM courses WHERE course_code = ?) AND class_id = ?");
    $stmt->bind_param("iisi", $showCorrect, $showFeedback, $courseCode, $classId);
    if ($stmt->execute()) setFlash('success', 'Visibility settings saved.');
    $stmt->close();
    header("Location: editexam.php?id=$escapedCode&class_id=$classId");
    exit;
}

$pageTitle = 'Edit Exam: ' . htmlspecialchars($courseCode);
include 'includes/header.php';
?>
<div class="main-content">
    <div class="flex-between mb-6">
        <a href="manage_class.php?id=<?= $classId ?>" class="btn btn-ghost"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Class</a>
        <div style="text-align: right;">
            <h2 style="font-size: 1.5rem; font-weight: bold;">Manage Exam: <?= htmlspecialchars($courseCode) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($courseData['course_name']) ?></p>
        </div>
    </div>

    <div class="grid-2" style="gap: 24px; align-items: start;">
        
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Schedule Panel -->
            <div class="card" style="border-left: 5px solid var(--maroon);">
                <div class="card-header"><h3 class="card-title">Exam Schedule</h3></div>
                <div class="card-content">
                    <form method="POST">
                        <div class="form-group">
                            <label>Opening Date</label>
                            <input type="datetime-local" name="opening_date" class="form-control" value="<?= $courseData['opening_date'] ? date('Y-m-d\TH:i', strtotime($courseData['opening_date'])) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Closing Deadline</label>
                            <input type="datetime-local" name="deadline" class="form-control" value="<?= $courseData['deadline'] ? date('Y-m-d\TH:i', strtotime($courseData['deadline'])) : '' ?>" required>
                        </div>
                        <button type="submit" name="save_schedule" class="btn btn-maroon btn-block">Save Schedule</button>
                    </form>
                </div>
            </div>

            <!-- Import CSV Panel -->
            <div class="card" style="border-left: 5px solid var(--maroon);">
                <div class="card-header">
                    <h3 class="card-title">Import via CSV</h3>
                    <p class="card-description" style="font-size: 0.8rem;">Note: Upload images to <code>exam_uploads/</code> first, then put the file path in the CSV.</p>
                </div>
                <div class="card-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>CSV File</label>
                            <input type="file" name="csv_file" accept=".csv" class="form-control" required style="padding: 6px;">
                        </div>
                        <button type="submit" name="upload_csv" class="btn btn-outline-dark btn-block">Upload CSV</button>
                    </form>
                </div>
            </div>

            <!-- Visibility Settings -->
            <div class="card">
                <div class="card-header"><h3 class="card-title">Student Visibility Settings</h3></div>
                <div class="card-content">
                    <form method="POST">
                        <div class="form-group">
                            <label class="option-label" style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="show_correct" <?= $courseData['show_correct_after_three'] ? 'checked' : '' ?>>
                                Show Correct Answers (After 3rd Trial)
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="option-label" style="display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="show_feedback" <?= $courseData['show_right_wrong_feedback'] ? 'checked' : '' ?>>
                                Highlight Right/Wrong Answers
                            </label>
                        </div>
                        <button type="submit" name="update_visibility" class="btn btn-outline-dark btn-block mt-2">Save Visibility</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Exam Rules Panel -->
            <div class="card" style="border-top: 5px solid var(--orange);">
                <div class="card-header">
                    <h3 class="card-title">Active Rules for this Exam</h3>
                </div>
                <div class="card-content">
                    <div style="background: var(--bg-primary); padding: 16px; border-radius: var(--border-radius-md); margin-bottom: 20px; text-align: center;">
                        <p style="font-weight: 600; margin-bottom: 8px;">Question Quantity per Category:</p>
                        <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 16px;">
                            <span class="badge badge-green">Easy: <?= $courseData['easy_limit'] ?></span>
                            <span class="badge badge-orange">Medium: <?= $courseData['medium_limit'] ?></span>
                            <span class="badge badge-red">Hard: <?= $courseData['hard_limit'] ?></span>
                        </div>
                        <p style="font-weight: 600; margin-bottom: 4px;">Target Subtopic:</p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);"><?= $courseData['selected_subtopic'] ? str_replace('|', ', ', htmlspecialchars($courseData['selected_subtopic'])) : 'All Subtopics' ?></p>
                    </div>

                    <form method="POST">
                        <div class="grid-3" style="gap: 16px; margin-bottom: 20px;">
                            <div class="form-group"><label>Easy</label><input type="number" name="easy_limit" class="form-control" value="<?= $courseData['easy_limit'] ?>" min="0" required></div>
                            <div class="form-group"><label>Medium</label><input type="number" name="medium_limit" class="form-control" value="<?= $courseData['medium_limit'] ?>" min="0" required></div>
                            <div class="form-group"><label>Hard</label><input type="number" name="hard_limit" class="form-control" value="<?= $courseData['hard_limit'] ?>" min="0" required></div>
                        </div>

                        <div class="form-group">
                            <label>Select Subtopics (Leave unchecked for All)</label>
                            <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 12px; max-height: 150px; overflow-y: auto; background: var(--bg-primary);">
                                <?php 
                                $subRes = $conn->query("SELECT DISTINCT subtopic FROM exam WHERE course_code = '$escapedCode' AND TRIM(subtopic) != '' ORDER BY subtopic ASC");
                                $activeSubs = explode('|', $courseData['selected_subtopic'] ?? '');
                                while($s = $subRes->fetch_assoc()): 
                                    $safeSub = htmlspecialchars($s['subtopic']);
                                    $isChecked = in_array($s['subtopic'], $activeSubs) ? 'checked' : '';
                                ?>
                                <label style="display:block; margin-bottom: 6px; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" name="target_subtopic[]" value="<?= $safeSub ?>" <?= $isChecked ?> style="margin-right: 8px;">
                                    <?= $safeSub ?>
                                </label>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <button type="submit" name="update_rules" class="btn btn-orange btn-block">Set Rules for this Exam</button>
                    </form>
                </div>
            </div>

            <!-- Add Singular Question -->
            <div class="card" style="border-top: 5px solid var(--maroon);">
                <div class="card-header"><h3 class="card-title">Add Singular Question</h3></div>
                <div class="card-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="grid-2" style="gap: 12px; margin-bottom: 12px;">
                            <div class="form-group"><label>Book Title</label><input type="text" name="book_title" class="form-control" required></div>
                            <div class="form-group"><label>Author</label><input type="text" name="author" class="form-control"></div>
                        </div>
                        <div class="grid-3" style="gap: 12px; margin-bottom: 12px;">
                            <div class="form-group"><label>Volume</label><input type="text" name="volume" class="form-control"></div>
                            <div class="form-group"><label>Pub Date</label><input type="text" name="pub_date" class="form-control"></div>
                            <div class="form-group"><label>Subtopic</label><input type="text" name="subtopic" class="form-control" required></div>
                        </div>

                        <div class="form-group mb-4">
                            <label>Difficulty</label>
                            <div style="display:flex; gap:16px;">
                                <label><input type="radio" name="category" value="Easy" checked> Easy</label>
                                <label><input type="radio" name="category" value="Medium"> Medium</label>
                                <label><input type="radio" name="category" value="Hard"> Hard</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Main Question / Scenario</label>
                            <textarea name="question" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Question Image (Optional)</label>
                            <input type="file" name="q_img_file" accept="image/*" class="form-control" style="padding: 6px;">
                        </div>
                        <div class="form-group">
                            <label>Sub-Question (Optional)</label>
                            <textarea name="subquestion" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="grid-2" style="gap: 16px; margin-top: 20px;">
                            <?php for($i=1; $i<=4; $i++): ?>
                            <div style="border: 1px solid var(--border-color); padding: 12px; border-radius: var(--border-radius-sm);">
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom: 8px;">
                                    <input type="radio" name="anskey" value="ch_<?= $i ?>" required>
                                    <input type="text" name="ch<?= $i ?>" class="form-control" placeholder="Choice <?= $i ?> Text" required style="margin:0;">
                                </div>
                                <input type="file" name="ch<?= $i ?>_img_file" accept="image/*" class="form-control" style="padding: 4px; font-size: 0.8rem;">
                            </div>
                            <?php endfor; ?>
                        </div>

                        <button type="submit" name="add_question" class="btn btn-maroon btn-block mt-4">Add Question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body></html>
