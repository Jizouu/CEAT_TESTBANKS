<?php
require_once 'config.php';
requireLogin();

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['user_id']) && isset($data['course_code'])) {
    // Basic validation to ensure the requesting user is the one being logged
    if ($_SESSION['user']['user_id'] != $data['user_id']) {
        exit('Unauthorized');
    }

    $uid = intval($data['user_id']);
    $ccode = $data['course_code'];
    
    // Check if a record already exists
    $stmt = $conn->prepare("SELECT switches FROM cheat_logs WHERE user_id = ? AND course_code = ?");
    $stmt->bind_param("is", $uid, $ccode);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE cheat_logs SET switches = switches + 1 WHERE user_id = ? AND course_code = ?");
        $stmt->bind_param("is", $uid, $ccode);
        $stmt->execute();
        $stmt->close();
    } else {
        $status = 'clear';
        $switches = 1;
        $stmt = $conn->prepare("INSERT INTO cheat_logs (user_id, course_code, switches, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $uid, $ccode, $switches, $status);
        $stmt->execute();
        $stmt->close();
    }

    // Also log this in user_logs for complete traceability
    logActivity($conn, $uid, $_SESSION['user']['role'], "Cheating Attempt Detected: Window Blur/Switch on course $ccode", $_SESSION['user']['first_name'], $_SESSION['user']['last_name']);
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}
?>
