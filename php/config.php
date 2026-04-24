<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'online_assessment_system';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

/**
 * Check if user is logged in, redirect to login if not
 */
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit;
    }
}

/**
 * Check if user has the required role
 */
function requireRole($roles) {
    requireLogin();
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    if (!in_array($_SESSION['user']['role'], $roles)) {
        header("Location: dashboard.php");
        exit;
    }
}

/**
 * Sanitize input
 */
function sanitize($conn, $input) {
    return $conn->real_escape_string(htmlspecialchars(trim($input)));
}

/**
 * Log user activity
 */
function logActivity($conn, $userId, $userType, $action, $firstName, $lastName) {
    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, user_type, action, timestamp, first_name, last_name) VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param("issss", $userId, $userType, $action, $firstName, $lastName);
    $stmt->execute();
    $stmt->close();
}

/**
 * Display flash messages
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate random class code
 */
function generateClassCode($length = 6) {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
}
?>
