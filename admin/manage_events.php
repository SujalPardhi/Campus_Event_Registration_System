<?php
// =====================================================
// admin/manage_events.php — Redirect to Dashboard
// Campus Event Registration System
// =====================================================
require_once 'auth.php';

// Redirect to dashboard where events are managed
$params = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '#manageEvents';
header('Location: dashboard.php' . $params);
exit;
?>
