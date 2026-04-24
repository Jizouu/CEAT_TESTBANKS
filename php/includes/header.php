<?php
// Shared header component - include after config.php
$pageTitle = $pageTitle ?? 'UPHSD Test Bank';
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UPHSD Online Assessment System - Test Bank Application">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <div class="header-brand">
            <div class="header-icon">
                <svg viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <div class="header-title"><?= htmlspecialchars($pageTitle) ?></div>
                <?php if ($user): ?>
                <div class="header-subtitle">Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-actions">
            <?php if ($user): ?>
            <a href="change_password.php" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Change Password
            </a>
            <a href="logout.php" class="btn btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php
$flash = getFlash();
if ($flash):
?>
<div class="main-content" style="padding-bottom:0;">
    <div class="flash flash-<?= $flash['type'] ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
</div>
<?php endif; ?>
