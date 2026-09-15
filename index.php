<?php
// =====================================================
// index.php — Home Page
// Campus Event Registration System
// =====================================================
require_once 'db.php';

// ── Statistics: COUNT queries ──────────────────────
$total_events = 0;
$total_regs   = 0;
$upcoming     = 0;

$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM events");
if ($res) { $total_events = mysqli_fetch_assoc($res)['cnt']; }

$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM registrations");
if ($res) { $total_regs = mysqli_fetch_assoc($res)['cnt']; }

$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM events WHERE status = 'upcoming' AND event_date >= CURDATE()");
if ($res) { $upcoming = mysqli_fetch_assoc($res)['cnt']; }

// ── Featured Events: 3 upcoming ───────────────────
$featured_events = [];
$sql = "SELECT e.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
        FROM events e
        WHERE e.status = 'upcoming' AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC
        LIMIT 3";
$res = mysqli_query($conn, $sql);
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $featured_events[] = $row;
    }
}

// Card colour palette cycling
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
    <title>Campus Event Registration System</title>
    <meta name="description" content="Discover, register and participate in exciting campus events. The official Campus Event Registration System.">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<!-- ═══════════════════════ NAVBAR ═══════════════════════ -->
<nav class="navbar navbar-custom navbar-expand-lg" id="mainNav">
    <div class="container">
        <a class="navbar-brand-text" href="index.php">
            <i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span>
        </a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-house me-1"></i>Home</a></li>
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

<!-- ═══════════════════════ HERO ═══════════════════════ -->
<section class="hero-section" id="hero">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i> Academic Year 2026–27
                </div>
                <h1 class="hero-title">
                    Campus Events,<br>
                    <span class="highlight">One Place.</span>
                </h1>
                <p class="hero-subtitle">
                    Discover, register and participate in exciting events happening across the campus.
                    From technical fests to cultural celebrations — it all starts here.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="events.php" class="btn-hero-primary">
                        <i class="bi bi-calendar-event"></i> Explore Events
                    </a>
                    <a href="register.php" class="btn-hero-outline">
                        <i class="bi bi-pencil-square"></i> Register Now
                    </a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-visual-card">
                    <p class="mb-3" style="color:rgba(255,255,255,.5);font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;">
                        Live System Statistics
                    </p>
                    <div class="row g-3 text-center">
                        <div class="col-4 hero-stat-item">
                            <div class="hero-stat-number"><?= $total_events ?></div>
                            <div class="hero-stat-label">Events</div>
                        </div>
                        <div class="col-4 hero-stat-item">
                            <div class="hero-stat-number"><?= $total_regs ?></div>
                            <div class="hero-stat-label">Registered</div>
                        </div>
                        <div class="col-4 hero-stat-item">
                            <div class="hero-stat-number"><?= $upcoming ?></div>
                            <div class="hero-stat-label">Upcoming</div>
                        </div>
                    </div>
                    <hr class="hero-divider my-3">
                    <div class="d-flex align-items-center gap-2" style="color:rgba(255,255,255,.6);font-size:.82rem;">
                        <span style="width:8px;height:8px;background:#4ade80;border-radius:50%;display:inline-block;"></span>
                        System Online — Registrations Open
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════ STATISTICS ═══════════════════════ -->
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-calendar-event-fill"></i></div>
                    <div class="stat-number"><?= $total_events ?></div>
                    <div class="stat-label">Total Events Hosted</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="bi bi-person-check-fill"></i></div>
                    <div class="stat-number"><?= $total_regs ?></div>
                    <div class="stat-label">Participants Registered</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-clock-history"></i></div>
                    <div class="stat-number"><?= $upcoming ?></div>
                    <div class="stat-label">Upcoming Events</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════ ABOUT ═══════════════════════ -->
<section class="about-section py-5" id="about">
    <div class="container">
        <div class="row gy-5 align-items-center">
            <div class="col-lg-5">
                <div class="section-label">About the System</div>
                <h2 class="section-title">A Smarter Way to Manage Campus Events</h2>
                <p class="section-subtitle mb-4">
                    The Campus Event Registration System is a centralised platform that makes it easy for
                    students to discover and register for college events, while helping departments track
                    participation effortlessly.
                </p>
                <a href="events.php" class="btn-primary-custom">
                    <i class="bi bi-arrow-right-circle"></i> Browse All Events
                </a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-search"></i></div>
                            <div>
                                <div class="feature-title">Discover Events</div>
                                <div class="feature-text">Browse all upcoming events with full details in one place.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <div class="feature-title">Easy Registration</div>
                                <div class="feature-text">Fill a simple form and get instant registration confirmation.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-shield-lock"></i></div>
                            <div>
                                <div class="feature-title">Secure & Reliable</div>
                                <div class="feature-text">All data is stored securely using prepared SQL statements.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="bi bi-table"></i></div>
                            <div>
                                <div class="feature-title">Track Registrations</div>
                                <div class="feature-text">View all registrations and filter by event or participant name.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════ FEATURED EVENTS ═══════════════════════ -->
<?php if (!empty($featured_events)) : ?>
<section class="py-5" style="background:var(--surface-alt);" id="featured">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Don't Miss Out</div>
            <h2 class="section-title">Featured Upcoming Events</h2>
            <p class="section-subtitle mx-auto">
                These events are coming up soon — register before seats fill up!
            </p>
        </div>
        <div class="row g-4">
            <?php foreach ($featured_events as $i => $ev) :
                $color   = $card_colors[$i % count($card_colors)];
                $icon    = $card_icons[$i % count($card_icons)];
                $seats_left = $ev['max_participants'] - $ev['reg_count'];
                $pct     = ($ev['max_participants'] > 0)
                           ? min(100, round(($ev['reg_count'] / $ev['max_participants']) * 100))
                           : 0;
                $fill_cls = ($pct >= 90) ? 'full' : (($pct >= 70) ? 'warn' : '');
            ?>
            <div class="col-md-4">
                <div class="event-card">
                    <div class="event-card-header <?= htmlspecialchars($color) ?>">
                        <div class="event-status-badge">
                            <?= htmlspecialchars(ucfirst($ev['status'])) ?>
                        </div>
                        <div class="event-card-icon"><i class="bi <?= htmlspecialchars($icon) ?>"></i></div>
                        <div class="event-card-title"><?= htmlspecialchars($ev['event_name']) ?></div>
                        <div class="event-card-organizer"><i class="bi bi-building me-1"></i><?= htmlspecialchars($ev['organizer']) ?></div>
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
                        <div class="event-seats-bar">
                            <div class="seats-label">
                                <span>Seats Filled</span>
                                <span><?= $ev['reg_count'] ?> / <?= $ev['max_participants'] ?></span>
                            </div>
                            <div class="seats-progress">
                                <div class="seats-fill <?= $fill_cls ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="event-card-footer">
                        <a href="event_details.php?id=<?= (int)$ev['id'] ?>" class="btn-outline-custom flex-fill justify-content-center">
                            <i class="bi bi-info-circle"></i> Details
                        </a>
                        <?php if ($seats_left > 0) : ?>
                        <a href="register.php?event_id=<?= (int)$ev['id'] ?>" class="btn-primary-custom flex-fill justify-content-center">
                            <i class="bi bi-pencil-square"></i> Register
                        </a>
                        <?php else : ?>
                        <span class="btn-primary-custom flex-fill justify-content-center" style="background:#94a3b8;cursor:not-allowed;">
                            <i class="bi bi-x-circle"></i> Full
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="events.php" class="btn-accent-custom">
                <i class="bi bi-grid"></i> View All Events
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════ FOOTER ═══════════════════════ -->
<footer class="footer-custom">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4">
                <div class="footer-brand"><i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span></div>
                <p class="footer-desc">
                    A digital platform for discovering and registering for campus events.
                    Built as a DBMS mini-project using PHP &amp; MySQL.
                </p>
            </div>
            <div class="col-lg-2 col-6">
                <div class="footer-heading">Navigate</div>
                <a href="index.php"      class="footer-link">Home</a>
                <a href="events.php"     class="footer-link">Events</a>
                <a href="register.php"   class="footer-link">Register</a>
                <a href="registrations.php" class="footer-link">Registrations</a>
            </div>
            <div class="col-lg-3 col-6">
                <div class="footer-heading">Administration</div>
                <a href="admin/login.php"     class="footer-link"><i class="bi bi-shield-lock me-1"></i>Admin Login</a>
                <a href="admin/dashboard.php" class="footer-link"><i class="bi bi-speedometer2 me-1"></i>Admin Dashboard</a>
            </div>
            <div class="col-lg-3">
                <div class="footer-heading">Tech Stack</div>
                <p style="font-size:.85rem;line-height:1.8;">
                    <i class="bi bi-filetype-php text-primary-custom me-1"></i>Core PHP &amp; MySQLi<br>
                    <i class="bi bi-bootstrap text-primary-custom me-1"></i>Bootstrap 5<br>
                    <i class="bi bi-database text-primary-custom me-1"></i>MySQL Database<br>
                    <i class="bi bi-cloud text-primary-custom me-1"></i>InfinityFree Hosting
                </p>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Campus Event Registration System &mdash; DBMS Mini Project &nbsp;|&nbsp;
            Made with <span>♥</span> using PHP &amp; MySQL
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sticky navbar shadow on scroll
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 30);
});
</script>
</body>
</html>
