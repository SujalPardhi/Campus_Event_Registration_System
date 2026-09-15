<?php
// =====================================================
// admin/dashboard.php — Administrator Dashboard
// Campus Event Registration System
// =====================================================
require_once 'auth.php';
require_once '../db.php';

// Flash messages
$msg  = '';
$type = '';
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $msg  = 'Event deleted successfully.';
    $type = 'success';
} elseif (isset($_GET['added']) && $_GET['added'] == '1') {
    $msg  = 'New event created successfully.';
    $type = 'success';
} elseif (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $msg  = 'Event details updated successfully.';
    $type = 'success';
} elseif (isset($_GET['error']) && $_GET['error'] === 'has_registrations') {
    $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
    $msg  = 'Cannot delete this event: ' . ($count > 0 ? $count : 'several') . ' student registration(s) exist for it. Student records are protected.';
    $type = 'danger';
}

// ── Metrics ──────────────────────────────────────────
// Total Events
$total_events = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM events");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $total_events = (int) $row['cnt'];
}

// Total Registrations
$total_regs = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM registrations");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $total_regs = (int) $row['cnt'];
}

// Upcoming Events
$upcoming_events = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM events WHERE status = 'upcoming' AND event_date >= CURDATE()");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $upcoming_events = (int) $row['cnt'];
}

// ── Fetch All Events with Registration Counts ────────
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .admin-nav {
            background: #0f172a;
            border-bottom: 3px solid var(--accent);
            padding: 12px 0;
        }
        .admin-stat-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform var(--transition), box-shadow var(--transition);
        }
        .admin-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }
        .stat-icon.blue   { background: #eff6ff; color: #2563eb; }
        .stat-icon.green  { background: #ecfdf5; color: #059669; }
        .stat-icon.amber  { background: #fffbeb; color: #d97706; }
        .stat-num {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary);
            line-height: 1.1;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .action-toolbar {
            background: #ffffff;
            border-radius: 14px;
            padding: 16px 20px;
            border: 1px solid var(--border);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<!-- ADMIN NAVBAR -->
<nav class="admin-nav">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <a class="navbar-brand-text text-white d-flex align-items-center" href="dashboard.php">
                <i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);font-size:1.4rem;"></i>
                <span style="font-weight:700;">Campus<span style="color:var(--accent);">Events</span></span>
                <span class="badge bg-secondary ms-2" style="font-size:0.75rem;letter-spacing:0.5px;">ADMIN PANEL</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small d-none d-md-inline">
                    <i class="bi bi-person-check-fill me-1 text-success"></i>Logged in as <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong>
                </span>
                <a href="../index.php" class="btn btn-sm btn-outline-light" target="_blank" title="View Public Website">
                    <i class="bi bi-box-arrow-up-right me-1"></i>View Site
                </a>
                <a href="logout.php" class="btn btn-sm btn-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4" style="max-width:1300px;">

    <!-- Flash message -->
    <?php if ($msg) : ?>
    <div class="alert-custom <?= $type === 'success' ? 'alert-success-custom' : 'alert-danger-custom' ?> mb-4">
        <i class="bi <?= $type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-octagon-fill' ?>"></i>
        <div><?= htmlspecialchars($msg) ?></div>
    </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1" style="color:var(--secondary);font-weight:700;">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Admin Dashboard
            </h1>
            <p class="text-muted mb-0">Overview of campus events, registrations, and administrative controls</p>
        </div>
        <div class="d-flex gap-2">
            <a href="add_event.php" class="btn-accent-custom" style="font-size:.9rem;padding:10px 20px;">
                <i class="bi bi-plus-circle-fill me-1"></i> Add Event
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div>
                    <div class="stat-label">Total Events</div>
                    <div class="stat-num"><?= $total_events ?></div>
                    <div class="small text-muted mt-1">Listed in database</div>
                </div>
                <div class="stat-icon blue">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div>
                    <div class="stat-label">Total Registrations</div>
                    <div class="stat-num"><?= $total_regs ?></div>
                    <div class="small text-muted mt-1">Student submissions</div>
                </div>
                <div class="stat-icon green">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card">
                <div>
                    <div class="stat-label">Upcoming Events</div>
                    <div class="stat-num"><?= $upcoming_events ?></div>
                    <div class="small text-muted mt-1">Scheduled ahead</div>
                </div>
                <div class="stat-icon amber">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Toolbar -->
    <div class="action-toolbar">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold text-secondary"><i class="bi bi-sliders me-1"></i>Quick Actions:</span>
            <a href="add_event.php" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Event
            </a>
            <a href="#manageEvents" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-table me-1"></i>Manage Events
            </a>
            <a href="../registrations.php" class="btn btn-sm btn-outline-info" target="_blank">
                <i class="bi bi-card-checklist me-1"></i>View Registrations
            </a>
        </div>
        <div>
            <a href="logout.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>

    <!-- Event Management Section -->
    <div id="manageEvents" class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
            <div>
                <h2 class="h5 mb-0 fw-bold" style="color:var(--secondary);">
                    <i class="bi bi-calendar-check text-primary me-2"></i>Manage Events
                </h2>
                <span class="text-muted small">Edit existing events or remove empty ones</span>
            </div>
            <a href="add_event.php" class="btn btn-sm btn-primary-custom">
                <i class="bi bi-plus-circle me-1"></i> Add Event
            </a>
        </div>

        <div class="card-body p-0">
            <?php if (empty($events)) : ?>
            <div class="empty-state py-5 text-center">
                <i class="bi bi-calendar-x" style="font-size:3rem;color:var(--text-light);"></i>
                <h3 class="h5 mt-3">No Events Yet</h3>
                <p class="text-muted">No events currently exist in the database.</p>
                <a href="add_event.php" class="btn-primary-custom mt-2">
                    <i class="bi bi-plus-circle"></i> Add First Event
                </a>
            </div>
            <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-custom">
                    <thead>
                        <tr>
                            <th style="width:60px;">#ID</th>
                            <th>Event Name</th>
                            <th>Date &amp; Time</th>
                            <th>Venue</th>
                            <th>Organiser</th>
                            <th>Registrations</th>
                            <th>Status</th>
                            <th style="width:170px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $ev) :
                            $reg_count = (int) $ev['reg_count'];
                            $max_part  = (int) $ev['max_participants'];
                            $pct       = ($max_part > 0) ? min(100, round(($reg_count / $max_part) * 100)) : 0;
                            $has_regs  = ($reg_count > 0);
                        ?>
                        <tr>
                            <td><span class="text-muted fw-semibold">#<?= (int)$ev['id'] ?></span></td>
                            <td>
                                <div class="fw-bold" style="color:var(--secondary);"><?= htmlspecialchars($ev['event_name']) ?></div>
                                <div class="text-muted small text-truncate" style="max-width:260px;"><?= htmlspecialchars(substr($ev['description'], 0, 80)) ?>...</div>
                            </td>
                            <td>
                                <div><i class="bi bi-calendar3 me-1 text-primary"></i><?= date('M d, Y', strtotime($ev['event_date'])) ?></div>
                                <div class="text-muted small"><i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($ev['event_time'])) ?></div>
                            </td>
                            <td>
                                <span class="small"><i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($ev['venue']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($ev['organizer']) ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold <?= $pct >= 90 ? 'text-danger' : 'text-dark' ?>">
                                    <?= $reg_count ?> / <?= $max_part ?>
                                </div>
                                <div class="progress mt-1" style="height: 5px; width: 90px;">
                                    <div class="progress-bar <?= $pct >= 90 ? 'bg-danger' : ($pct >= 70 ? 'bg-warning' : 'bg-success') ?>"
                                         role="progressbar"
                                         style="width: <?= $pct ?>%"
                                         aria-valuenow="<?= $pct ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-status badge-status-<?= htmlspecialchars($ev['status']) ?>">
                                    <?= htmlspecialchars(ucfirst($ev['status'])) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="edit_event.php?id=<?= (int)$ev['id'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit Event">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>

                                    <form method="POST"
                                          action="delete_event.php"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="<?= $has_regs ? 'Note: Has registrations' : 'Delete Event' ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer-bottom py-3 bg-white border-top text-center text-muted small">
    Campus Event Registration System &mdash; Administrator Control Panel
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
