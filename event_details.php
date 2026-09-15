<?php
// =====================================================
// event_details.php — Single Event Detail View
// Campus Event Registration System
// =====================================================
require_once 'db.php';

// ── Validate and sanitise event ID from URL ────────
$event_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($event_id <= 0) {
    header('Location: events.php');
    exit;
}

// ── Prepared SELECT: fetch event + registration count ─
$event = null;
$stmt  = mysqli_prepare($conn,
    "SELECT e.*,
            (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
     FROM events e
     WHERE e.id = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result) {
    $event = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

// Redirect if event not found
if (!$event) {
    header('Location: events.php');
    exit;
}

$seats_left = $event['max_participants'] - $event['reg_count'];
$pct        = ($event['max_participants'] > 0)
              ? min(100, round(($event['reg_count'] / $event['max_participants']) * 100))
              : 0;
$fill_cls   = ($pct >= 90) ? 'full' : (($pct >= 70) ? 'warn' : '');
$is_past    = (strtotime($event['event_date']) < strtotime(date('Y-m-d')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['event_name']) ?> | Campus Events</title>
    <meta name="description" content="<?= htmlspecialchars(substr($event['description'], 0, 150)) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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
                <li class="nav-item"><a class="nav-link active" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-pencil-square me-1"></i>Register</a></li>
                <li class="nav-item"><a class="nav-link" href="registrations.php"><i class="bi bi-list-check me-1"></i>Registrations</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link btn-nav-cta" href="admin/login.php"><i class="bi bi-shield-lock me-1"></i>Admin Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO BANNER -->
<div class="event-detail-hero">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb" style="--bs-breadcrumb-divider-color:rgba(255,255,255,.3);">
                <li class="breadcrumb-item"><a href="index.php" style="color:rgba(255,255,255,.55);">Home</a></li>
                <li class="breadcrumb-item"><a href="events.php" style="color:rgba(255,255,255,.55);">Events</a></li>
                <li class="breadcrumb-item active" style="color:rgba(255,255,255,.85);"><?= htmlspecialchars($event['event_name']) ?></li>
            </ol>
        </nav>
        <div class="event-detail-badge">
            <i class="bi bi-circle-fill" style="font-size:.55rem;color:#4ade80;"></i>
            <?= $is_past ? 'Completed' : htmlspecialchars(ucfirst($event['status'])) ?>
        </div>
        <div class="event-detail-title"><?= htmlspecialchars($event['event_name']) ?></div>
        <div class="event-detail-org">
            <i class="bi bi-building me-1"></i> Organised by <?= htmlspecialchars($event['organizer']) ?>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="container py-5">
    <div class="row gy-4">

        <!-- LEFT: Info Grid + Description + Register -->
        <div class="col-lg-8">

            <div class="info-grid mb-4">
                <div class="info-item">
                    <div class="info-item-icon"><i class="bi bi-calendar3"></i></div>
                    <div>
                        <div class="info-item-label">Date</div>
                        <div class="info-item-value"><?= date('D, d M Y', strtotime($event['event_date'])) ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-icon"><i class="bi bi-clock"></i></div>
                    <div>
                        <div class="info-item-label">Time</div>
                        <div class="info-item-value"><?= date('h:i A', strtotime($event['event_time'])) ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-icon"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <div class="info-item-label">Venue</div>
                        <div class="info-item-value"><?= htmlspecialchars($event['venue']) ?></div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-icon"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="info-item-label">Organiser</div>
                        <div class="info-item-value"><?= htmlspecialchars($event['organizer']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;margin-bottom:24px;box-shadow:var(--shadow-sm);">
                <h2 style="font-size:1.15rem;margin-bottom:14px;color:var(--secondary);">
                    <i class="bi bi-file-text me-2" style="color:var(--primary);"></i>About This Event
                </h2>
                <p style="color:var(--text-muted);font-size:.95rem;line-height:1.8;margin:0;">
                    <?= nl2br(htmlspecialchars($event['description'])) ?>
                </p>
            </div>

            <!-- Register button -->
            <?php if (!$is_past && $seats_left > 0) : ?>
            <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1px solid #bfdbfe;border-radius:var(--radius);padding:24px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;color:var(--secondary);margin-bottom:4px;">Ready to participate?</div>
                    <div style="font-size:.88rem;color:var(--text-muted);">
                        <?= $seats_left ?> seat<?= $seats_left !== 1 ? 's' : '' ?> remaining — register before they fill up!
                    </div>
                </div>
                <a href="register.php?event_id=<?= (int)$event['id'] ?>" class="btn-hero-primary">
                    <i class="bi bi-pencil-square"></i> Register for this Event
                </a>
            </div>
            <?php elseif ($is_past) : ?>
            <div class="alert-custom alert-warning-custom">
                <i class="bi bi-clock-history"></i>
                <div>This event has already concluded. Check other upcoming events!</div>
            </div>
            <?php else : ?>
            <div class="alert-custom alert-danger-custom">
                <i class="bi bi-x-circle"></i>
                <div>Registrations are closed — all seats have been filled for this event.</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Stats sidebar -->
        <div class="col-lg-4">
            <div style="background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm);position:sticky;top:80px;">
                <div style="background:linear-gradient(135deg,#0f172a,#2563eb);padding:20px 22px;">
                    <div style="color:rgba(255,255,255,.65);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;">
                        Event Statistics
                    </div>
                    <div style="color:#fff;font-size:2rem;font-weight:800;"><?= $event['reg_count'] ?></div>
                    <div style="color:rgba(255,255,255,.65);font-size:.85rem;">people registered</div>
                </div>
                <div style="padding:20px 22px;">
                    <div class="event-seats-bar mb-3">
                        <div class="seats-label">
                            <span style="font-weight:600;">Capacity Used</span>
                            <span><?= $pct ?>%</span>
                        </div>
                        <div class="seats-progress" style="height:8px;">
                            <div class="seats-fill <?= $fill_cls ?>" style="width:<?= $pct ?>%;"></div>
                        </div>
                    </div>
                    <div class="detail-row" style="padding:10px 0;">
                        <div class="detail-icon"><i class="bi bi-person-lines-fill"></i></div>
                        <div>
                            <div class="detail-label">Max Participants</div>
                            <div class="detail-value"><?= htmlspecialchars($event['max_participants']) ?></div>
                        </div>
                    </div>
                    <div class="detail-row" style="padding:10px 0;">
                        <div class="detail-icon"><i class="bi bi-person-check"></i></div>
                        <div>
                            <div class="detail-label">Registered</div>
                            <div class="detail-value"><?= $event['reg_count'] ?></div>
                        </div>
                    </div>
                    <div class="detail-row" style="padding:10px 0;border-bottom:none;">
                        <div class="detail-icon" style="background:#ecfdf5;color:#059669;">
                            <i class="bi bi-person-plus"></i>
                        </div>
                        <div>
                            <div class="detail-label">Seats Available</div>
                            <div class="detail-value" style="color:<?= $seats_left <= 0 ? 'var(--danger)' : 'var(--success)' ?>;font-weight:700;">
                                <?= max(0, $seats_left) ?>
                            </div>
                        </div>
                    </div>
                    <hr style="border-color:var(--border);margin:12px 0;">
                    <a href="events.php" class="btn-outline-custom w-100 justify-content-center mb-2">
                        <i class="bi bi-arrow-left"></i> Back to Events
                    </a>
                    <?php if (!$is_past && $seats_left > 0) : ?>
                    <a href="register.php?event_id=<?= (int)$event['id'] ?>" class="btn-primary-custom w-100 justify-content-center">
                        <i class="bi bi-pencil-square"></i> Register Now
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer-custom">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <div class="footer-brand"><i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span></div>
                <p class="footer-desc">A digital platform for discovering and registering for campus events.</p>
            </div>
            <div class="col-lg-2 col-6">
                <div class="footer-heading">Navigate</div>
                <a href="index.php" class="footer-link">Home</a>
                <a href="events.php" class="footer-link">Events</a>
                <a href="register.php" class="footer-link">Register</a>
                <a href="registrations.php" class="footer-link">Registrations</a>
            </div>
            <div class="col-lg-3 col-6">
                <div class="footer-heading">Administration</div>
                <a href="admin/login.php" class="footer-link"><i class="bi bi-shield-lock me-1"></i>Admin Login</a>
                <a href="admin/dashboard.php" class="footer-link"><i class="bi bi-speedometer2 me-1"></i>Admin Dashboard</a>
            </div>
        </div>
        <hr class="footer-divider">
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
