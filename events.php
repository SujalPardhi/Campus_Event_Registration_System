<?php
// =====================================================
// events.php — All Events Page
// Campus Event Registration System
// =====================================================
require_once 'db.php';

// ── Fetch all events with registration count ───────
$events = [];
$sql = "SELECT e.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
        FROM events e
        ORDER BY e.event_date ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
}

$card_colors = ['', 'green', 'amber', 'purple', 'red', 'teal'];
$card_icons  = [
    'bi-cpu',
    'bi-code-slash',
    'bi-trophy',
    'bi-easel2',
    'bi-camera-video',
    'bi-music-note-beamed',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | Campus Event Registration System</title>
    <meta name="description" content="Browse all campus events. View details and register online.">
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

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="container">
        <nav class="page-breadcrumb mb-2" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Events</li>
            </ol>
        </nav>
        <h1><i class="bi bi-calendar-event me-2"></i>Campus Events</h1>
        <p>Browse all upcoming and ongoing campus events. Register now to secure your seat.</p>
    </div>
</div>

<!-- EVENTS GRID -->
<div class="container pb-5">

    <?php if (empty($events)) : ?>
    <div class="empty-state">
        <i class="bi bi-calendar-x"></i>
        <h3>No Events Found</h3>
        <p>No campus events are currently listed. Please check back later!</p>
    </div>
    <?php else : ?>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="mb-0" style="color:var(--text-muted);font-size:.9rem;">
            Showing <strong><?= count($events) ?></strong> event<?= count($events) !== 1 ? 's' : '' ?>
        </p>
        <a href="register.php" class="btn-primary-custom" style="font-size:.82rem;padding:6px 14px;">
            <i class="bi bi-pencil-square"></i> Register for Event
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($events as $i => $ev) :
            $color      = $card_colors[$i % count($card_colors)];
            $icon       = $card_icons[$i % count($card_icons)];
            $seats_left = $ev['max_participants'] - $ev['reg_count'];
            $pct        = ($ev['max_participants'] > 0)
                          ? min(100, round(($ev['reg_count'] / $ev['max_participants']) * 100))
                          : 0;
            $fill_cls   = ($pct >= 90) ? 'full' : (($pct >= 70) ? 'warn' : '');
            $is_past    = (strtotime($ev['event_date']) < strtotime(date('Y-m-d')));
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="event-card">
                <div class="event-card-header <?= htmlspecialchars($color) ?>">
                    <div class="event-status-badge">
                        <?= $is_past ? 'Completed' : htmlspecialchars(ucfirst($ev['status'])) ?>
                    </div>
                    <div class="event-card-icon">
                        <i class="bi <?= htmlspecialchars($icon) ?>"></i>
                    </div>
                    <div class="event-card-title"><?= htmlspecialchars($ev['event_name']) ?></div>
                    <div class="event-card-organizer">
                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($ev['organizer']) ?>
                    </div>
                </div>
                <div class="event-card-body">
                    <p class="event-card-desc"><?= htmlspecialchars($ev['description']) ?></p>
                    <div class="event-meta-row">
                        <i class="bi bi-calendar3"></i>
                        <?= date('D, d M Y', strtotime($ev['event_date'])) ?>
                    </div>
                    <div class="event-meta-row">
                        <i class="bi bi-clock"></i>
                        <?= date('h:i A', strtotime($ev['event_time'])) ?>
                    </div>
                    <div class="event-meta-row">
                        <i class="bi bi-geo-alt"></i>
                        <?= htmlspecialchars($ev['venue']) ?>
                    </div>
                    <div class="event-meta-row">
                        <i class="bi bi-people"></i>
                        <?= htmlspecialchars($ev['organizer']) ?>
                    </div>
                    <div class="event-seats-bar">
                        <div class="seats-label">
                            <span>Registrations</span>
                            <span><?= $ev['reg_count'] ?> / <?= $ev['max_participants'] ?></span>
                        </div>
                        <div class="seats-progress">
                            <div class="seats-fill <?= $fill_cls ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <div style="font-size:.75rem;color:var(--text-muted);margin-top:4px;">
                            <?php if ($seats_left <= 0) : ?>
                                <span style="color:var(--danger);font-weight:600;"><i class="bi bi-x-circle"></i> Seats Full</span>
                            <?php elseif ($seats_left <= 10) : ?>
                                <span style="color:var(--accent-dark);font-weight:600;"><i class="bi bi-exclamation-triangle"></i> Only <?= $seats_left ?> seats left!</span>
                            <?php else : ?>
                                <span style="color:var(--success);font-weight:500;"><i class="bi bi-check-circle"></i> <?= $seats_left ?> seats available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="event-card-footer">
                    <a href="event_details.php?id=<?= (int)$ev['id'] ?>" class="btn-outline-custom flex-fill justify-content-center">
                        <i class="bi bi-info-circle"></i> Details
                    </a>
                    <?php if (!$is_past && $seats_left > 0) : ?>
                    <a href="register.php?event_id=<?= (int)$ev['id'] ?>" class="btn-primary-custom flex-fill justify-content-center">
                        <i class="bi bi-pencil-square"></i> Register
                    </a>
                    <?php else : ?>
                    <span class="btn-primary-custom flex-fill justify-content-center"
                          style="background:#94a3b8;cursor:not-allowed;pointer-events:none;">
                        <?= $is_past ? '<i class="bi bi-clock-history"></i> Ended' : '<i class="bi bi-x-circle"></i> Full' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
