<?php
require_once 'config.php';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    logActivity($conn, $user['user_id'], $user['role'], 'Logout', $user['first_name'], $user['last_name']);
}

session_destroy();
header("Location: index.php");
exit;
?>
