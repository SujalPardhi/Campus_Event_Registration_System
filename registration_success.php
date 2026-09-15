<?php
// =====================================================
// registration_success.php — Confirmation Page
// Campus Event Registration System
// =====================================================
require_once 'db.php';

// ── Validate registration ID from URL ─────────────
$reg_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($reg_id <= 0) {
    header('Location: index.php');
    exit;
}

// ── Prepared SELECT with JOIN to get full details ──
$reg = null;
$stmt = mysqli_prepare($conn,
    "SELECT r.id, r.participant_name, r.email, r.roll_no, r.department, r.year,
            r.registered_at,
            e.event_name, e.event_date, e.event_time, e.venue, e.organizer
     FROM registrations r
     JOIN events e ON r.event_id = e.id
     WHERE r.id = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $reg_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $reg = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

// If not found redirect
if (!$reg) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful | Campus Events</title>
    <meta name="description" content="Your event registration was successful. View your confirmation details.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        @keyframes popIn {
            0%   { transform: scale(.6); opacity:0; }
            70%  { transform: scale(1.05); }
            100% { transform: scale(1); opacity:1; }
        }
        .success-icon-ring { animation: popIn .5s ease forwards; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-custom navbar-expand-lg" id="mainNav">
    <div class="container">
        <a class="navbar-brand-text" href="index.php">
            <i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span>
        </a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-pencil-square me-1"></i>Register</a></li>
                <li class="nav-item"><a class="nav-link" href="registrations.php"><i class="bi bi-list-check me-1"></i>Registrations</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link btn-nav-cta" href="admin/login.php"><i class="bi bi-shield-lock me-1"></i>Admin Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- SUCCESS CONTENT -->
<div class="container py-5">
    <div class="success-card">

        <!-- Header -->
        <div class="success-header">
            <div class="success-icon-ring">
                <i class="bi bi-check-lg" style="color:#fff;"></i>
            </div>
            <h1>Registration Successful!</h1>
            <p>You have been successfully registered for the event. Keep this confirmation for your records.</p>
        </div>

        <!-- Body: Registration Details -->
        <div class="success-body">

            <!-- Registration ID highlight -->
            <div style="background:var(--primary-light);border:1.5px dashed #93c5fd;border-radius:var(--radius-sm);padding:16px 18px;text-align:center;margin-bottom:24px;">
                <div style="font-size:.75rem;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">
                    Registration ID
                </div>
                <div class="reg-id-large">#<?= htmlspecialchars($reg['id']) ?></div>
            </div>

            <!-- Details -->
            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-person"></i></div>
                <div>
                    <div class="detail-label">Participant Name</div>
                    <div class="detail-value"><?= htmlspecialchars($reg['participant_name']) ?></div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-envelope"></i></div>
                <div>
                    <div class="detail-label">Email Address</div>
                    <div class="detail-value"><?= htmlspecialchars($reg['email']) ?></div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-hash"></i></div>
                <div>
                    <div class="detail-label">Roll Number</div>
                    <div class="detail-value"><?= htmlspecialchars($reg['roll_no']) ?></div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="detail-label">Department</div>
                    <div class="detail-value"><?= htmlspecialchars($reg['department']) ?></div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-mortarboard"></i></div>
                <div>
                    <div class="detail-label">Year of Study</div>
                    <div class="detail-value"><?= htmlspecialchars($reg['year']) ?></div>
                </div>
            </div>

            <div class="detail-row" style="background:var(--primary-light);margin:0 -4px;padding:14px 4px;border-radius:var(--radius-sm);">
                <div class="detail-icon" style="background:#dbeafe;"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="detail-label">Event Name</div>
                    <div class="detail-value" style="color:var(--primary);font-weight:700;">
                        <?= htmlspecialchars($reg['event_name']) ?>
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="detail-label">Event Date &amp; Time</div>
                    <div class="detail-value">
                        <?= date('D, d M Y', strtotime($reg['event_date'])) ?>
                        &nbsp;at&nbsp;
                        <?= date('h:i A', strtotime($reg['event_time'])) ?>
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-icon"><i class="bi bi-geo-alt"></i></div>
                <div>
                    <div class="detail-label">Venue</div>
                    <div class="detail-value"><?= htmlspecialchars($reg['venue']) ?></div>
                </div>
            </div>

            <div class="detail-row" style="border-bottom:none;">
                <div class="detail-icon"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="detail-label">Registered On</div>
                    <div class="detail-value">
                        <?= date('D, d M Y \a\t h:i A', strtotime($reg['registered_at'])) ?>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:24px;display:flex;gap:10px;flex-wrap:wrap;">
                <a href="events.php" class="btn-outline-custom">
                    <i class="bi bi-calendar-event"></i> Back to Events
                </a>
                <a href="registrations.php" class="btn-primary-custom">
                    <i class="bi bi-list-check"></i> View Registrations
                </a>
                <a href="register.php" class="btn-accent-custom">
                    <i class="bi bi-plus-circle"></i> Register Another
                </a>
            </div>

        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer-custom">
    <div class="container">
        <div class="row gy-4 mb-3">
            <div class="col-lg-5">
                <div class="footer-brand"><i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span></div>
                <p class="footer-desc">A digital platform for discovering and registering for campus events.</p>
            </div>
            <div class="col-lg-3 col-6">
                <div class="footer-heading">Navigate</div>
                <a href="index.php" class="footer-link">Home</a>
                <a href="events.php" class="footer-link">Events</a>
                <a href="register.php" class="footer-link">Register</a>
                <a href="registrations.php" class="footer-link">Registrations</a>
            </div>
            <div class="col-lg-4 col-6">
                <div class="footer-heading">Administration</div>
                <a href="admin/login.php" class="footer-link"><i class="bi bi-shield-lock me-1"></i>Admin Login</a>
                <a href="admin/dashboard.php" class="footer-link"><i class="bi bi-speedometer2 me-1"></i>Admin Dashboard</a>
            </div>
        </div>
        <hr class="footer-divider" style="margin-top:0;">
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Campus Event Registration System &mdash; DBMS Mini Project
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 30));
</script>
</body>
</html>
