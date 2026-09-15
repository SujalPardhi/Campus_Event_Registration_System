<?php
// =====================================================
// registrations.php — Registration List + Search/Filter
// Campus Event Registration System
// =====================================================
require_once 'db.php';

// ── Fetch events for the filter dropdown ──────────
$events_dropdown = [];
$ev_res = mysqli_query($conn, "SELECT id, event_name FROM events ORDER BY event_date ASC");
if ($ev_res) {
    while ($row = mysqli_fetch_assoc($ev_res)) {
        $events_dropdown[] = $row;
    }
}

// ── Get filter values from GET (search form) ──────
$filter_event = isset($_GET['filter_event']) ? (int) $_GET['filter_event'] : 0;
$search_name  = isset($_GET['search_name'])  ? trim($_GET['search_name']) : '';

// ── Build query dynamically using prepared statements ─
//    Demonstrate: SELECT, JOIN, WHERE, LIKE, ORDER BY
// ─────────────────────────────────────────────────

$conditions = [];
$types      = '';
$params     = [];

if ($filter_event > 0) {
    $conditions[] = 'r.event_id = ?';
    $types       .= 'i';
    $params[]     = $filter_event;
}

if ($search_name !== '') {
    $conditions[] = 'r.participant_name LIKE ?';
    $types       .= 's';
    $like_val     = '%' . $search_name . '%';
    $params[]     = $like_val;
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql = "SELECT r.id, r.participant_name, r.email, r.roll_no, r.department, r.year,
               r.registered_at,
               e.event_name, e.event_date
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        {$where_clause}
        ORDER BY r.registered_at DESC";

$registrations = [];
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $registrations[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// ── Total count for display ───────────────────────
$total_count = count($registrations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations | Campus Event Registration System</title>
    <meta name="description" content="View all event registrations. Search and filter by event or participant name.">
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
                <li class="nav-item"><a class="nav-link" href="events.php"><i class="bi bi-calendar-event me-1"></i>Events</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-pencil-square me-1"></i>Register</a></li>
                <li class="nav-item"><a class="nav-link active" href="registrations.php"><i class="bi bi-list-check me-1"></i>Registrations</a></li>
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
                <li class="breadcrumb-item active">Registrations</li>
            </ol>
        </nav>
        <h1><i class="bi bi-list-check me-2"></i>Registration Records</h1>
        <p>All participant registrations stored in the database. Use filters to narrow results.</p>
    </div>
</div>

<div class="container pb-5">

    <!-- ── SEARCH / FILTER CARD ── -->
    <div class="filter-card">
        <form id="filterForm" method="GET" action="registrations.php">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="filter_event" class="form-label-custom">
                        <i class="bi bi-funnel me-1"></i>Filter by Event
                    </label>
                    <select id="filter_event" name="filter_event" class="form-select-custom">
                        <option value="">-- All Events --</option>
                        <?php foreach ($events_dropdown as $ev) : ?>
                        <option value="<?= (int)$ev['id'] ?>"
                            <?= ($filter_event === (int)$ev['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ev['event_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search_name" class="form-label-custom">
                        <i class="bi bi-search me-1"></i>Search by Participant Name
                    </label>
                    <input
                        type="text"
                        id="search_name"
                        name="search_name"
                        class="form-control-custom"
                        placeholder="e.g. Arjun"
                        value="<?= htmlspecialchars($search_name) ?>"
                        maxlength="100"
                    >
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2 pt-1">
                        <button type="submit" class="btn-primary-custom" id="filterBtn">
                            <i class="bi bi-search"></i> Apply Filter
                        </button>
                        <a href="registrations.php" class="btn-outline-custom">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ── Active filter indicators ── -->
    <?php if ($filter_event > 0 || $search_name !== '') : ?>
    <div class="alert-custom alert-info-custom mb-4">
        <i class="bi bi-funnel-fill"></i>
        <div>
            <strong>Active filters:</strong>
            <?php if ($filter_event > 0) : ?>
                Event ID = <?= $filter_event ?>
            <?php endif; ?>
            <?php if ($search_name !== '') : ?>
                <?= ($filter_event > 0) ? ' &amp; ' : '' ?>
                Name contains "<em><?= htmlspecialchars($search_name) ?></em>"
            <?php endif; ?>
            &mdash; Found <strong><?= $total_count ?></strong> record<?= $total_count !== 1 ? 's' : '' ?>.
        </div>
    </div>
    <?php endif; ?>

    <!-- ── RESULTS TABLE ── -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div style="font-size:.9rem;color:var(--text-muted);">
            Showing <strong><?= $total_count ?></strong> registration<?= $total_count !== 1 ? 's' : '' ?>
        </div>
        <a href="register.php" class="btn-accent-custom" style="font-size:.85rem;padding:7px 16px;">
            <i class="bi bi-plus-circle"></i> New Registration
        </a>
    </div>

    <?php if (empty($registrations)) : ?>
    <div class="empty-state">
        <i class="bi bi-person-x"></i>
        <h3>No Registrations Found</h3>
        <p>
            <?php if ($filter_event > 0 || $search_name !== '') : ?>
                No records match your filter criteria. Try resetting the filters.
            <?php else : ?>
                No one has registered yet. Be the first!
            <?php endif; ?>
        </p>
        <a href="register.php" class="btn-primary-custom mt-2">
            <i class="bi bi-pencil-square"></i> Register Now
        </a>
    </div>
    <?php else : ?>
    <div class="table-wrapper">
        <div style="overflow-x:auto;">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Participant Name</th>
                    <th>Roll No.</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Event</th>
                    <th>Event Date</th>
                    <th>Registered On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $reg) : ?>
                <tr>
                    <td><span class="reg-id-badge">#<?= htmlspecialchars($reg['id']) ?></span></td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($reg['participant_name']) ?></div>
                        <div style="font-size:.77rem;color:var(--text-light);"><?= htmlspecialchars($reg['email']) ?></div>
                    </td>
                    <td style="font-family:monospace;font-size:.87rem;"><?= htmlspecialchars($reg['roll_no']) ?></td>
                    <td><span class="badge-dept"><?= htmlspecialchars($reg['department']) ?></span></td>
                    <td><span class="badge-year"><?= htmlspecialchars($reg['year']) ?></span></td>
                    <td style="font-weight:500;"><?= htmlspecialchars($reg['event_name']) ?></td>
                    <td style="white-space:nowrap;font-size:.85rem;">
                        <?= date('d M Y', strtotime($reg['event_date'])) ?>
                    </td>
                    <td style="white-space:nowrap;font-size:.82rem;color:var(--text-muted);">
                        <?= date('d M Y, h:i A', strtotime($reg['registered_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
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
