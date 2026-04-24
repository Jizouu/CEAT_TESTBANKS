<?php
require_once 'config.php';
requireRole('student');
$pageTitle = 'UPHSD Test Bank - Exam';
$userId = $_SESSION['user']['user_id'];
$classId = intval($_GET['class_id'] ?? 0);
$courseCode = $_GET['course_code'] ?? '';

// Handle exam submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam'])) {
    $classId = intval($_POST['class_id']);
    $courseCode = $_POST['course_code'];
    $answers = $_POST['answers'] ?? [];
    $attemptGroup = 'trial_' . time() . '_' . uniqid();
    $totalCorrect = 0;
    $totalQuestions = 0;

    foreach ($answers as $examId => $choice) {
        $examId = intval($examId);
        $choice = $conn->real_escape_string($choice);
        // Check if correct
        $q = $conn->query("SELECT anskey FROM exam WHERE exam_id = $examId")->fetch_assoc();
        $isCorrect = ($q && $q['anskey'] === $choice) ? 1 : 0;
        $totalCorrect += $isCorrect;
        $totalQuestions++;

        $stmt = $conn->prepare("INSERT INTO student_attempts (user_id, course_code, class_id, exam_id, selected_choice, is_correct, trial_number, attempt_group_id) VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("isiisis", $userId, $courseCode, $classId, $examId, $choice, $isCorrect, $attemptGroup);
        $stmt->execute();
        $stmt->close();
    }

    logActivity($conn, $userId, 'student', "Took Exam: $courseCode | Points: $totalCorrect / $totalQuestions", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
    setFlash('success', "Exam submitted! Score: $totalCorrect / $totalQuestions");
    header("Location: view_result.php?attempt_group=$attemptGroup");
    exit;
}

// Get class courses if no course selected
if ($classId && !$courseCode) {
    $classCourses = $conn->query("
        SELECT cc.*, co.course_code, co.course_name
        FROM class_courses cc
        JOIN courses co ON cc.course_id = co.id
        WHERE cc.class_id = $classId
        AND (cc.opening_date IS NULL OR cc.opening_date <= NOW() OR cc.opening_date = '0000-00-00 00:00:00')
    ");

    include 'includes/header.php';
    ?>
    <div class="main-content" style="max-width:800px;">
        <a href="dashboard.php" class="btn btn-ghost mb-6"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>
        <h2 style="margin-bottom:20px;">Select a Course Exam</h2>
        <div style="display:flex;flex-direction:column;gap:12px;">
        <?php while ($cc = $classCourses->fetch_assoc()): ?>
            <a href="exam.php?class_id=<?= $classId ?>&course_code=<?= urlencode($cc['course_code']) ?>" class="card" style="text-decoration:none;padding:20px 24px;">
                <div class="flex-between">
                    <div><h3 style="font-weight:600;"><?= htmlspecialchars($cc['course_name']) ?></h3><p class="text-muted" style="font-size:0.85rem;"><?= htmlspecialchars($cc['course_code']) ?></p></div>
                    <span class="btn btn-maroon btn-sm">Start Exam</span>
                </div>
            </a>
        <?php endwhile; ?>
        </div>
    </div></body></html>
    <?php exit;
}

// Load exam questions
if ($classId && $courseCode) {
    // Get limits from class_courses
    $ccData = $conn->query("
        SELECT cc.* FROM class_courses cc
        JOIN courses co ON cc.course_id = co.id
        WHERE cc.class_id = $classId AND co.course_code = '" . $conn->real_escape_string($courseCode) . "'
    ")->fetch_assoc();

    $easyLimit = intval($ccData['easy_limit'] ?? 3);
    $mediumLimit = intval($ccData['medium_limit'] ?? 3);
    $hardLimit = intval($ccData['hard_limit'] ?? 3);
    $subtopics = $ccData['selected_subtopic'] ?? '';

    // Build query for questions
    $escapedCode = $conn->real_escape_string($courseCode);
    $questions = [];

    // Fetch by difficulty
    foreach (['easy' => $easyLimit, 'medium' => $mediumLimit, 'hard' => $hardLimit] as $diff => $limit) {
        if ($limit > 0) {
            $q = $conn->query("SELECT * FROM exam WHERE course_code = '$escapedCode' AND LOWER(category) = '$diff' ORDER BY RAND() LIMIT $limit");
            while ($row = $q->fetch_assoc()) $questions[] = $row;
        }
    }
    shuffle($questions);

    logActivity($conn, $userId, 'student', "Student Started Exam: $courseCode | Questions: " . count($questions), $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
}

include 'includes/header.php';
?>
<div class="main-content exam-container">
    <a href="dashboard.php" class="btn btn-ghost mb-4"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back</a>

    <?php if (empty($questions)): ?>
        <div class="card"><div class="empty-state"><p>No questions available for this exam configuration.</p></div></div>
    <?php else: ?>
        <div class="exam-timer">
            <span><strong><?= htmlspecialchars($courseCode) ?></strong> — <?= count($questions) ?> question(s)</span>
            <span id="timer" style="font-weight:600;color:var(--maroon);"></span>
        </div>
        <form method="POST">
            <input type="hidden" name="submit_exam" value="1">
            <input type="hidden" name="class_id" value="<?= $classId ?>">
            <input type="hidden" name="course_code" value="<?= htmlspecialchars($courseCode) ?>">

            <?php foreach ($questions as $i => $q): ?>
            <div class="card question-card">
                <div class="card-header"><div class="question-number">Question <?= $i + 1 ?> of <?= count($questions) ?> · <span class="badge badge-<?= strtolower($q['category']) === 'easy' ? 'green' : (strtolower($q['category']) === 'medium' ? 'orange' : 'red') ?>"><?= htmlspecialchars($q['category']) ?></span></div></div>
                <div class="card-content">
                    <p class="question-text"><?= htmlspecialchars($q['questions']) ?></p>
                    <?php if ($q['subquestions']): ?><p style="font-size:0.9rem;color:var(--text-secondary);margin-bottom:12px;font-style:italic;"><?= htmlspecialchars($q['subquestions']) ?></p><?php endif; ?>
                    <?php if (!empty($q['question_img'])): ?>
                    <div style="margin: 16px 0; text-align: center; border: 1px solid var(--border-color); padding: 10px; border-radius: var(--border-radius-sm); background: var(--bg-primary);">
                        <img src="<?= htmlspecialchars($q['question_img']) ?>" alt="Question Image" style="max-width: 100%; height: auto; border-radius: 4px;">
                    </div>
                    <?php endif; ?>

                    <?php
                    $choices = [
                        'ch_1' => ['text' => $q['ch_1'], 'img' => $q['ch_1_img'] ?? ''],
                        'ch_2' => ['text' => $q['ch_2'], 'img' => $q['ch_2_img'] ?? ''],
                        'ch_3' => ['text' => $q['ch_3'], 'img' => $q['ch_3_img'] ?? ''],
                        'ch_4' => ['text' => $q['ch_4'], 'img' => $q['ch_4_img'] ?? '']
                    ];
                    
                    // Shuffle choices
                    $choiceKeys = array_keys($choices);
                    shuffle($choiceKeys);

                    foreach ($choiceKeys as $key):
                        $val = $choices[$key];
                        if (trim($val['text']) || trim($val['img'])):
                    ?>
                    <label class="option-label" style="display:flex; flex-direction:column; gap:8px; align-items:flex-start;">
                        <div style="display:flex; align-items:center;">
                            <input type="radio" name="answers[<?= $q['exam_id'] ?>]" value="<?= $key ?>">
                            <span><?= htmlspecialchars($val['text']) ?></span>
                        </div>
                        <?php if (!empty($val['img'])): ?>
                            <div style="margin-left: 28px;">
                                <img src="<?= htmlspecialchars($val['img']) ?>" alt="Choice Image" style="max-height: 150px; max-width: 100%; border-radius: 4px; display: block;">
                            </div>
                        <?php endif; ?>
                    </label>
                    <?php endif; endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-maroon btn-block btn-lg" onclick="return confirm('Submit your exam?')">Submit Exam</button>
        </form>
    <?php endif; ?>
</div>

<!-- Anti-Cheat Overlay -->
<div id="anti-cheat-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#ffffff; z-index:9999999; align-items:center; justify-content:center; flex-direction:column; text-align:center;">
    <h1 style="color:var(--maroon); font-weight:bold; font-size:3rem; border: 5px solid var(--maroon); padding: 40px; background: #ffebee; border-radius:12px;">SCREENSHOTS & TAB SWITCHING STRICTLY PROHIBITED</h1>
    <p style="margin-top:20px; font-size:1.2rem; color:var(--text-secondary);">Your actions are being logged and monitored.</p>
</div>

<script>
// Simple timer
let seconds = 0;
setInterval(() => {
    seconds++;
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    const timerDisplay = document.getElementById('timer');
    if (timerDisplay) timerDisplay.textContent = m + ':' + (s < 10 ? '0' : '') + s;
}, 1000);

// Anti-Cheat Variables
let switches = 0;
let lastSwitchTime = 0;
const MAX_SWITCHES = 3;
const userId = <?= json_encode($userId) ?>;
const courseCode = <?= json_encode($courseCode) ?>;
const form = document.querySelector('form');

function logCheatAttempt() {
    fetch('log_cheat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userId, course_code: courseCode })
    }).catch(e => console.error(e));
}

// 1. Monitor Tab Switching (Visibility Change)
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        const currentTime = new Date().getTime();
        switches++;
        
        // Log to backend if switched (debounce 2 seconds)
        if (currentTime - lastSwitchTime > 2000) {
            lastSwitchTime = currentTime;
            logCheatAttempt();
        }

        if (switches >= MAX_SWITCHES) {
            alert('Exam Terminated: Too many tab switches detected. Your exam will now auto-submit.');
            window.onbeforeunload = null; 
            if (form) form.submit();
        } else {
            alert(`WARNING: Tab switching is strictly prohibited. You have ${MAX_SWITCHES - switches} warnings left before your exam auto-submits.`);
        }
    }
});

// 2. Prevent right-click, copy, paste
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('copy', e => e.preventDefault());
document.addEventListener('paste', e => e.preventDefault());

// 3. Block Screenshots (Snipping Tool, PrintScreen)
const antiCheatOverlay = document.getElementById('anti-cheat-overlay');
let cheatLock = false;
let isWindowBlurred = false;

document.addEventListener('keydown', function(e) {
    if (e.metaKey || e.key === 'Meta' || e.key === 'OS' || e.key === 'PrintScreen') {
        antiCheatOverlay.style.display = 'flex';
    }
    
    // Windows Snipping Tool (Win + Shift + S)
    if (e.shiftKey && e.metaKey && e.key.toLowerCase() === 's') {
        e.preventDefault();
        cheatLock = true;
        antiCheatOverlay.style.display = 'flex';
        setTimeout(() => { 
            cheatLock = false; 
            if(!isWindowBlurred) antiCheatOverlay.style.display = 'none'; 
        }, 5000);
    }

    if (e.key === 'PrintScreen' || e.keyCode === 44) {
        e.preventDefault();
        cheatLock = true;
        antiCheatOverlay.style.display = 'flex';
        setTimeout(() => { 
            cheatLock = false; 
            if(!isWindowBlurred) antiCheatOverlay.style.display = 'none'; 
        }, 5000);
    }
});

document.addEventListener('keyup', function (e) {
    if (!cheatLock && !isWindowBlurred) {
        antiCheatOverlay.style.display = 'none';
    }
});

window.addEventListener('blur', function() {
    isWindowBlurred = true;
    antiCheatOverlay.style.display = 'flex';
});

window.addEventListener('focus', function() {
    isWindowBlurred = false;
    if(!cheatLock) {
        antiCheatOverlay.style.display = 'none';
    }
});

// Warn before leaving page
window.onbeforeunload = function() {
    return "Are you sure you want to leave? Your exam progress may be lost.";
};

if (form) {
    form.onsubmit = function() {
        window.onbeforeunload = null; 
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerText = "Submitting...";
        }
    };
}
</script>
</body></html>
