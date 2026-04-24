<?php
require_once 'config.php';
requireRole(['faculty', 'admin']);

$courseCode = $_GET['id'] ?? '';
$classId = intval($_GET['class_id'] ?? 0);

if (!$courseCode || !$classId) {
    die("Invalid request.");
}

$escapedCode = $conn->real_escape_string($courseCode);

// Get class and course details
$stmt = $conn->prepare("
    SELECT c.course_name, cl.class_name, cl.class_code, cc.easy_limit, cc.medium_limit, cc.hard_limit, cc.selected_subtopic
    FROM courses c 
    JOIN class_courses cc ON c.id = cc.course_id 
    JOIN classes cl ON cc.class_id = cl.class_id
    WHERE c.course_code = ? AND cc.class_id = ?
");
$stmt->bind_param("si", $courseCode, $classId);
$stmt->execute();
$classData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$classData) {
    die("Course or Class not found.");
}

$easyLimit = intval($classData['easy_limit'] ?? 0);
$mediumLimit = intval($classData['medium_limit'] ?? 0);
$hardLimit = intval($classData['hard_limit'] ?? 0);
$subtopics = $classData['selected_subtopic'] ?? '';

// Build subtopic filter if applicable
$subtopicFilter = "";
if (!empty($subtopics) && $subtopics !== 'All') {
    $subsArray = explode('|', $subtopics);
    $inClause = implode(',', array_map(function($s) use ($conn) { return "'" . $conn->real_escape_string(trim($s)) . "'"; }, $subsArray));
    $subtopicFilter = " AND subtopic IN ($inClause)";
}

// Fetch questions based on difficulty limits
$questions = [];
foreach (['easy' => $easyLimit, 'medium' => $mediumLimit, 'hard' => $hardLimit] as $diff => $limit) {
    if ($limit > 0) {
        $q = $conn->query("SELECT * FROM exam WHERE course_code = '$escapedCode' $subtopicFilter AND LOWER(category) = '$diff' ORDER BY RAND() LIMIT $limit");
        while ($row = $q->fetch_assoc()) $questions[] = $row;
    }
}
shuffle($questions);

// Log the printing action
logActivity($conn, $_SESSION['user']['user_id'], $_SESSION['user']['role'], "Generated Physical Exam for $courseCode (Class ID: $classId)", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Exam - <?= htmlspecialchars($courseCode) ?></title>
    <style>
        :root {
            --maroon: #800000;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            line-height: 1.5;
            background: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .exam-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--maroon);
            padding-bottom: 20px;
        }
        .exam-header h1 {
            margin: 0 0 10px 0;
            color: var(--maroon);
            font-size: 24px;
            text-transform: uppercase;
        }
        .exam-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: normal;
        }
        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-field {
            display: flex;
            align-items: flex-end;
            margin-bottom: 15px;
        }
        .info-field span {
            font-weight: bold;
            margin-right: 10px;
        }
        .info-field .line {
            flex-grow: 1;
            border-bottom: 1px solid #000;
        }
        .question-block {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .q-text {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subq-text {
            font-style: italic;
            margin-bottom: 10px;
        }
        .q-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 10px 0;
            max-height: 250px;
        }
        .choices-list {
            list-style-type: upper-alpha;
            padding-left: 20px;
        }
        .choices-list li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        .choice-img {
            max-height: 100px;
            margin-left: 10px;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .no-print button {
            background: var(--maroon);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .print-container { box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Print Exam</button>
</div>

<div class="print-container">
    <div class="exam-header">
        <h1><?= htmlspecialchars($classData['course_name']) ?> (<?= htmlspecialchars($courseCode) ?>)</h1>
        <h2><?= htmlspecialchars($classData['class_name']) ?></h2>
    </div>

    <div class="student-info">
        <div style="width: 60%;">
            <div class="info-field"><span>Name:</span><div class="line"></div></div>
            <div class="info-field"><span>Section:</span><div class="line"></div></div>
        </div>
        <div style="width: 35%;">
            <div class="info-field"><span>Date:</span><div class="line"></div></div>
            <div class="info-field"><span>Score:</span><div class="line"></div></div>
        </div>
    </div>

    <div class="instructions" style="margin-bottom: 30px;">
        <strong>Instructions:</strong> Read each question carefully. Choose the best answer from the given options.
    </div>

    <div class="questions-list">
        <?php if (empty($questions)): ?>
            <p style="text-align:center; color:red;">No questions found based on the current exam rules. Please check the databank or update the limits.</p>
        <?php else: ?>
            <?php foreach ($questions as $index => $q): ?>
                <div class="question-block">
                    <div class="q-text"><?= ($index + 1) ?>. <?= htmlspecialchars($q['questions']) ?></div>
                    <?php if (!empty($q['subquestions'])): ?>
                        <div class="subq-text"><?= htmlspecialchars($q['subquestions']) ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($q['question_img'])): ?>
                        <img src="<?= htmlspecialchars($q['question_img']) ?>" class="q-image" alt="Question Image">
                    <?php endif; ?>

                    <?php
                    $choices = [
                        ['text' => $q['ch_1'], 'img' => $q['ch_1_img'] ?? ''],
                        ['text' => $q['ch_2'], 'img' => $q['ch_2_img'] ?? ''],
                        ['text' => $q['ch_3'], 'img' => $q['ch_3_img'] ?? ''],
                        ['text' => $q['ch_4'], 'img' => $q['ch_4_img'] ?? '']
                    ];
                    // Option to shuffle choices if needed, but for print usually kept A B C D as entered
                    ?>
                    <ol class="choices-list">
                        <?php foreach ($choices as $val): ?>
                            <?php if (trim($val['text']) || trim($val['img'])): ?>
                                <li>
                                    <span><?= htmlspecialchars($val['text']) ?></span>
                                    <?php if (!empty($val['img'])): ?>
                                        <img src="<?= htmlspecialchars($val['img']) ?>" class="choice-img" alt="Choice Image">
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
