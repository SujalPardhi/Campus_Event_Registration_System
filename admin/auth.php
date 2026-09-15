<?php
// =====================================================
// admin/auth.php — Admin Authentication Guard
// Campus Event Registration System
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is authenticated
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
