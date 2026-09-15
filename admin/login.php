<?php
// =====================================================
// admin/login.php — Admin Authentication
// Campus Event Registration System
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../db.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Prepared statement for admin lookup
        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && $admin = mysqli_fetch_assoc($result)) {
                // Verify password using password_verify()
                if (password_verify($password, $admin['password'])) {
                    // Prevent session fixation
                    session_regenerate_id(true);
                    $_SESSION['admin_id']       = (int) $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    mysqli_stmt_close($stmt);
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database query failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Campus Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .login-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-header {
            background: #0f172a;
            color: #ffffff;
            padding: 32px 30px 24px;
            text-align: center;
            border-bottom: 3px solid var(--accent);
        }
        .login-header .admin-badge-icon {
            width: 56px;
            height: 56px;
            background: rgba(245, 158, 11, 0.15);
            color: var(--accent);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 12px;
        }
        .login-body {
            padding: 32px 30px 30px;
        }
        .login-footer {
            background: #f8fafc;
            padding: 16px 30px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .hint-box {
            background: #f1f5f9;
            border-left: 3px solid var(--primary);
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 18px;
        }
    </style>
</head>
<body>

<!-- Minimal Top Bar -->
<nav class="navbar navbar-custom py-2">
    <div class="container">
        <a class="navbar-brand-text" href="../index.php">
            <i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span>
        </a>
        <a href="../index.php" class="text-white-50 small text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Back to Website
        </a>
    </div>
</nav>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="admin-badge-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h2 class="h4 text-white mb-1">Admin Portal</h2>
            <p class="text-white-50 small mb-0">Sign in to manage events & registrations</p>
        </div>

        <div class="login-body">
            <?php if (isset($_GET['logged_out']) && $_GET['logged_out'] == '1') : ?>
            <div class="alert-custom alert-success-custom mb-3 py-2 px-3 small">
                <i class="bi bi-check-circle me-1"></i> You have been safely logged out.
            </div>
            <?php endif; ?>

            <?php if (!empty($error)) : ?>
            <div class="alert-custom alert-danger-custom mb-3 py-2 px-3 small">
                <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label-custom fw-semibold" for="username">
                        <i class="bi bi-person me-1 text-primary"></i>Username
                    </label>
                    <input type="text"
                           id="username"
                           name="username"
                           class="form-control form-control-custom"
                           placeholder="e.g. admin"
                           value="<?= htmlspecialchars($username) ?>"
                           required
                           autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom fw-semibold" for="password">
                        <i class="bi bi-key me-1 text-primary"></i>Password
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control form-control-custom"
                           placeholder="Enter your password"
                           required>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary-custom py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                    </button>
                </div>
            </form>

            <div class="hint-box">
                <div class="fw-semibold text-dark mb-1"><i class="bi bi-info-circle me-1 text-primary"></i>Demo Credentials:</div>
                <div>Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code></div>
            </div>
        </div>

        <div class="login-footer">
            <span>DBMS Mini-Project &bull; Campus Event System</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
