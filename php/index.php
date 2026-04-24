<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, password, role FROM users_new WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'user_id' => $user['user_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                // Log activity
                logActivity($conn, $user['user_id'], $user['role'], 'Logged into the system', $user['first_name'], $user['last_name']);

                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter both email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UPHSD Online Assessment System - Login">
    <title>UPHSD Test Bank - Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <svg viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h1 class="login-title">UPHSD Test Bank</h1>
            <p class="login-subtitle">Sign in to your account</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="user@uphsd.edu.ph" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-maroon btn-block" style="margin-top:8px;">Sign In</button>
            </form>

            <div class="demo-box">
                <p>Test Accounts:</p>
                <ul>
                    <li>Admin: ria.augusto_admin@uphsd.edu.ph</li>
                    <li>Faculty: via.dulos_faculty@uphsd.edu.ph</li>
                    <li>Student: irish.avellana_student@uphsd.edu.ph</li>
                    <li>SuperAdmin: johnpaul.orayle_superadmin@uphsd.edu.ph</li>
                </ul>
                <p style="margin-top:8px;font-weight:400;font-style:italic;">Default password for all: 123456</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
