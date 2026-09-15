<?php
// =====================================================
// admin/logout.php — Admin Logout Handler
// Campus Event Registration System
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// If session cookie is used, clear it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page with logout confirmation flag
header('Location: login.php?logged_out=1');
exit;
?>
