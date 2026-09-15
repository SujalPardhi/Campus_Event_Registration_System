<?php
// =====================================================
// register.php — Registration Form + PHP Processing
// Campus Event Registration System
// =====================================================
require_once 'db.php';

// ── Allowed departments and years ─────────────────
$allowed_depts = ['CSE', 'CSE AIML', 'IT', 'Mechanical', 'Civil', 'Electrical', 'E&TC'];
$allowed_years = ['First Year', 'Second Year', 'Third Year', 'Final Year'];

// ── Fetch events for the dropdown ─────────────────
$events_list = [];
$ev_res = mysqli_query($conn,
    "SELECT id, event_name, event_date, status
     FROM events
     WHERE status = 'upcoming' AND event_date >= CURDATE()
     ORDER BY event_date ASC"
);
if ($ev_res) {
    while ($row = mysqli_fetch_assoc($ev_res)) {
        $events_list[] = $row;
    }
}

// ── Pre-select event from URL param (from event card) ─
$preselected_event = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

// ── Initialise variables ──────────────────────────
$errors      = [];
$success_msg = '';
$form        = [
    'name'   => '',
    'email'  => '',
    'roll'   => '',
    'dept'   => '',
    'year'   => '',
    'event'  => $preselected_event,
];

// ── Handle POST submission ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Collect and trim input ---
    $form['name']  = trim($_POST['participant_name'] ?? '');
    $form['email'] = trim($_POST['email']            ?? '');
    $form['roll']  = trim($_POST['roll_no']          ?? '');
    $form['dept']  = trim($_POST['department']       ?? '');
    $form['year']  = trim($_POST['year']             ?? '');
    $form['event'] = (int) ($_POST['event_id']       ?? 0);

    // --- Server-side Validation ---

    // Name
    if ($form['name'] === '') {
        $errors['name'] = 'Participant name is required.';
    } elseif (strlen($form['name']) > 100) {
        $errors['name'] = 'Name must not exceed 100 characters.';
    } elseif (!preg_match('/^[A-Za-z\s\.\-]+$/', $form['name'])) {
        $errors['name'] = 'Name must contain only letters, spaces, dots, or hyphens.';
    }

    // Email
    if ($form['email'] === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($form['email']) > 150) {
        $errors['email'] = 'Email must not exceed 150 characters.';
    }

    // Roll number (e.g., ABC2023001 — 3 letters, 4 digits year, 3 digits)
    if ($form['roll'] === '') {
        $errors['roll'] = 'Roll number is required.';
    } elseif (!preg_match('/^[A-Z0-9]{3,30}$/i', $form['roll'])) {
        $errors['roll'] = 'Roll number must be 3–30 alphanumeric characters (e.g., CSE2023001).';
    }

    // Department
    if (!in_array($form['dept'], $allowed_depts, true)) {
        $errors['dept'] = 'Please select a valid department.';
    }

    // Year
    if (!in_array($form['year'], $allowed_years, true)) {
        $errors['year'] = 'Please select a valid year.';
    }

    // Event
    if ($form['event'] <= 0) {
        $errors['event'] = 'Please select an event.';
    } else {
        // Verify event exists and is still open (prepared SELECT)
        $chk = mysqli_prepare($conn,
            "SELECT id, max_participants,
                    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = events.id) AS reg_count
             FROM events
             WHERE id = ? AND status = 'upcoming' AND event_date >= CURDATE()
             LIMIT 1"
        );
        mysqli_stmt_bind_param($chk, 'i', $form['event']);
        mysqli_stmt_execute($chk);
        $ev_res2 = mysqli_stmt_get_result($chk);
        $ev_row  = $ev_res2 ? mysqli_fetch_assoc($ev_res2) : null;
        mysqli_stmt_close($chk);

        if (!$ev_row) {
            $errors['event'] = 'The selected event is not available for registration.';
        } elseif ($ev_row['reg_count'] >= $ev_row['max_participants']) {
            $errors['event'] = 'Sorry, this event is already full. Please choose another event.';
        }
    }

    // ── If no errors → INSERT ─────────────────────
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO registrations
                 (participant_name, email, roll_no, department, year, event_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'sssssi',
            $form['name'],
            $form['email'],
            $form['roll'],
            $form['dept'],
            $form['year'],
            $form['event']
        );

        if (mysqli_stmt_execute($stmt)) {
            $new_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            // Redirect to success page
            header('Location: registration_success.php?id=' . $new_id);
            exit;
        } else {
            // Generic error — don't expose SQL message
            $errors['general'] = 'Registration could not be completed. Please try again later.';
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event | Campus Event Registration System</title>
    <meta name="description" content="Register for a campus event. Fill in your details and confirm your participation.">
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
                <li class="nav-item"><a class="nav-link active" href="register.php"><i class="bi bi-pencil-square me-1"></i>Register</a></li>
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
                <li class="breadcrumb-item"><a href="events.php">Events</a></li>
                <li class="breadcrumb-item active">Register</li>
            </ol>
        </nav>
        <h1><i class="bi bi-pencil-square me-2"></i>Event Registration</h1>
        <p>Fill in your details below to register for a campus event.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- General error -->
            <?php if (isset($errors['general'])) : ?>
            <div class="alert-custom alert-danger-custom">
                <i class="bi bi-x-octagon"></i>
                <div><?= htmlspecialchars($errors['general']) ?></div>
            </div>
            <?php endif; ?>

            <!-- No events available -->
            <?php if (empty($events_list) && $_SERVER['REQUEST_METHOD'] !== 'POST') : ?>
            <div class="alert-custom alert-warning-custom">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>No upcoming events available.</strong>
                    There are currently no events open for registration.
                    <a href="events.php" class="ms-1" style="color:inherit;text-decoration:underline;">Browse all events →</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:46px;height:46px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h2>Participant Details</h2>
                            <p style="color:rgba(255,255,255,.65);font-size:.85rem;margin:0;">
                                All fields marked with <span style="color:var(--accent);">*</span> are required.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="form-card-body">
                    <form id="registrationForm" method="POST" action="register.php" novalidate>

                        <!-- Row 1: Name + Email -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="participant_name" class="form-label-custom">
                                    Participant Name <span style="color:var(--danger);">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="participant_name"
                                    name="participant_name"
                                    class="form-control-custom"
                                    placeholder="e.g. Arjun Sharma"
                                    value="<?= htmlspecialchars($form['name']) ?>"
                                    maxlength="100"
                                    required
                                >
                                <?php if (isset($errors['name'])) : ?>
                                <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['name']) ?></div>
                                <?php else : ?>
                                <div class="form-text-hint">Full name (letters and spaces only)</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label-custom">
                                    Email Address <span style="color:var(--danger);">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control-custom"
                                    placeholder="e.g. arjun@college.edu"
                                    value="<?= htmlspecialchars($form['email']) ?>"
                                    maxlength="150"
                                    required
                                >
                                <?php if (isset($errors['email'])) : ?>
                                <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['email']) ?></div>
                                <?php else : ?>
                                <div class="form-text-hint">Use your college email address</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Row 2: Roll Number + Department -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="roll_no" class="form-label-custom">
                                    Roll Number <span style="color:var(--danger);">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="roll_no"
                                    name="roll_no"
                                    class="form-control-custom"
                                    placeholder="e.g. CSE2023001"
                                    value="<?= htmlspecialchars($form['roll']) ?>"
                                    maxlength="30"
                                    pattern="[A-Za-z0-9]{3,30}"
                                    required
                                >
                                <?php if (isset($errors['roll'])) : ?>
                                <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['roll']) ?></div>
                                <?php else : ?>
                                <div class="form-text-hint">College roll number (3–30 alphanumeric characters)</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label-custom">
                                    Department <span style="color:var(--danger);">*</span>
                                </label>
                                <select id="department" name="department" class="form-select-custom" required>
                                    <option value="">-- Select Department --</option>
                                    <?php foreach ($allowed_depts as $dept) : ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"
                                        <?= ($form['dept'] === $dept) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['dept'])) : ?>
                                <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['dept']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Row 3: Year + Event -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="year" class="form-label-custom">
                                    Year of Study <span style="color:var(--danger);">*</span>
                                </label>
                                <select id="year" name="year" class="form-select-custom" required>
                                    <option value="">-- Select Year --</option>
                                    <?php foreach ($allowed_years as $yr) : ?>
                                    <option value="<?= htmlspecialchars($yr) ?>"
                                        <?= ($form['year'] === $yr) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($yr) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['year'])) : ?>
                                <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['year']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="event_id" class="form-label-custom">
                                    Select Event <span style="color:var(--danger);">*</span>
                                </label>
                                <select id="event_id" name="event_id" class="form-select-custom" required>
                                    <option value="">-- Choose an Event --</option>
                                    <?php foreach ($events_list as $ev) : ?>
                                    <option value="<?= (int)$ev['id'] ?>"
                                        <?= ((int)$form['event'] === (int)$ev['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ev['event_name']) ?>
                                        (<?= date('d M', strtotime($ev['event_date'])) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['event'])) : ?>
                                <div class="field-error"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['event']) ?></div>
                                <?php else : ?>
                                <div class="form-text-hint">Only upcoming events with available seats are listed</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Privacy note -->
                        <div style="background:var(--surface-alt);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px 16px;font-size:.82rem;color:var(--text-muted);margin-bottom:28px;">
                            <i class="bi bi-shield-check text-primary-custom me-1"></i>
                            Your information is stored securely in our database and will only be used for event management purposes.
                            We do not collect sensitive personal information.
                        </div>

                        <!-- Submit -->
                        <div class="d-flex gap-3 flex-wrap">
                            <button type="submit" class="btn-hero-primary" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Submit Registration
                            </button>
                            <a href="events.php" class="btn-hero-outline" style="border-color:var(--border);color:var(--text-muted);">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

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

// Disable submit button after click to prevent double submission
document.getElementById('registrationForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Submitting…';
});
</script>
</body>
</html>
