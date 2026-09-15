<?php
// =====================================================
// admin/delete_event.php — Admin: Delete Event Handler
// Campus Event Registration System
//
// Only authenticated admins can delete an event.
// Only accepts POST requests.
// Protects student registration records from accidental deletion.
// =====================================================
require_once 'auth.php';
require_once '../db.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;

if ($event_id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// ── Check if event has existing student registrations ──
$count = 0;
$check_stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM registrations WHERE event_id = ?");
if ($check_stmt) {
    mysqli_stmt_bind_param($check_stmt, 'i', $event_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_bind_result($check_stmt, $count);
    mysqli_stmt_fetch($check_stmt);
    mysqli_stmt_close($check_stmt);
}

// If registrations exist, refuse deletion to protect student registration records
if ($count > 0) {
    header('Location: dashboard.php?error=has_registrations&count=' . $count);
    exit;
}

// ── Prepared DELETE for event with no registrations ──
$stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $event_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: dashboard.php?deleted=1');
exit;
?>
