<?php
require_once 'config.php';
requireLogin();

$role = $_SESSION['user']['role'];

switch ($role) {
    case 'student':
        $pageTitle = 'UPHSD Test Bank - Student';
        include 'includes/header.php';
        include 'includes/student_dashboard.php';
        break;
    case 'faculty':
        $pageTitle = 'UPHSD Test Bank - Faculty';
        include 'includes/header.php';
        include 'includes/faculty_dashboard.php';
        break;
    case 'admin':
        $pageTitle = 'UPHSD Test Bank - Admin';
        include 'includes/header.php';
        include 'includes/admin_dashboard.php';
        break;
    case 'superadmin':
        $pageTitle = 'UPHSD Test Bank - Super Admin';
        include 'includes/header.php';
        include 'includes/superadmin_dashboard.php';
        break;
    default:
        header("Location: logout.php");
        exit;
}
?>
</div>
</body>
</html>
