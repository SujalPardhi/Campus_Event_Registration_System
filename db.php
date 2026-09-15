<?php
// =====================================================
// db.php — Database Connection
// Campus Event Registration System
//
// Configured for local XAMPP environment.
// For InfinityFree, update host, username, password & dbname.
// =====================================================

$host     = 'sql211.infinityfree.com';          // Localhost for XAMPP
$username = 'if0_42712093';               // Default XAMPP username
$password = 'Sujal360';                   // Default XAMPP password (empty)
$database = 'if0_42712093_campus_event';    // Database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection — show generic message, never expose raw credentials
if (!$conn) {
    die(
        '<div style="font-family:sans-serif;text-align:center;padding:60px;color:#c0392b;">'
        . '<h2>⚠ Service Temporarily Unavailable</h2>'
        . '<p>We are unable to connect to the database. Please make sure MySQL is running in XAMPP.</p>'
        . '</div>'
    );
}

// Set charset to utf8mb4 for full Unicode support
mysqli_set_charset($conn, 'utf8mb4');
?>
