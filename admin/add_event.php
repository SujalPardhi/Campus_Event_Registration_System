<?php
// =====================================================
// admin/add_event.php — Admin: Add New Event
// Campus Event Registration System
// =====================================================
require_once 'auth.php';
require_once '../db.php';

$errors  = [];
$form    = [
    'event_name'       => '',
    'description'      => '',
    'event_date'       => '',
    'event_time'       => '',
    'venue'            => '',
    'organizer'        => '',
    'max_participants' => '100',
    'status'           => 'upcoming',
];

$allowed_statuses = ['upcoming', 'ongoing', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['event_name']       = trim($_POST['event_name']       ?? '');
    $form['description']      = trim($_POST['description']      ?? '');
    $form['event_date']       = trim($_POST['event_date']       ?? '');
    $form['event_time']       = trim($_POST['event_time']       ?? '');
    $form['venue']            = trim($_POST['venue']            ?? '');
    $form['organizer']        = trim($_POST['organizer']        ?? '');
    $form['max_participants'] = (int) ($_POST['max_participants'] ?? 0);
    $form['status']           = trim($_POST['status']           ?? '');

    // Validation
    if ($form['event_name'] === '' || strlen($form['event_name']) > 150) {
        $errors['event_name'] = 'Event name is required (max 150 characters).';
    }
    if ($form['description'] === '') {
        $errors['description'] = 'Description is required.';
    }
    if ($form['event_date'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['event_date'])) {
        $errors['event_date'] = 'Please select a valid event date.';
    }
    if ($form['event_time'] === '') {
        $errors['event_time'] = 'Event time is required.';
    }
    if ($form['venue'] === '' || strlen($form['venue']) > 200) {
        $errors['venue'] = 'Venue is required (max 200 characters).';
    }
    if ($form['organizer'] === '' || strlen($form['organizer']) > 150) {
        $errors['organizer'] = 'Organiser is required (max 150 characters).';
    }
    if ($form['max_participants'] <= 0 || $form['max_participants'] > 10000) {
        $errors['max_participants'] = 'Max participants must be between 1 and 10000.';
    }
    if (!in_array($form['status'], $allowed_statuses, true)) {
        $errors['status'] = 'Please select a valid status.';
    }

    if (empty($errors)) {
        // Prepared INSERT
        $stmt = mysqli_prepare($conn,
            "INSERT INTO events
                 (event_name, description, event_date, event_time, venue, organizer, max_participants, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'ssssssis',
            $form['event_name'],
            $form['description'],
            $form['event_date'],
            $form['event_time'],
            $form['venue'],
            $form['organizer'],
            $form['max_participants'],
            $form['status']
        );
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: dashboard.php?added=1');
            exit;
        } else {
            $errors['general'] = 'Could not add event. Please try again.';
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
    <title>Add Event | Campus Events Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .admin-nav {
            background: #0f172a;
            border-bottom: 3px solid var(--accent);
            padding: 12px 0;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="admin-nav">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between">
            <a class="navbar-brand-text text-white d-flex align-items-center" href="dashboard.php">
                <i class="bi bi-mortarboard-fill me-2" style="color:var(--accent);"></i>Campus<span>Events</span>
                <span class="badge bg-secondary ms-2" style="font-size:0.75rem;">ADMIN</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="dashboard.php" class="text-white-50 text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width:820px;">
    <div class="admin-header-bar mb-4">
        <h1><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Event</h1>
    </div>

    <?php if (isset($errors['general'])) : ?>
    <div class="alert-custom alert-danger-custom mb-4">
        <i class="bi bi-x-octagon"></i>
        <div><?= htmlspecialchars($errors['general']) ?></div>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-body">
            <form method="POST" action="add_event.php">

                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <label class="form-label-custom">Event Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="event_name" class="form-control-custom"
                               placeholder="e.g. Tech Fest 2026"
                               value="<?= htmlspecialchars($form['event_name']) ?>"
                               maxlength="150" required>
                        <?php if (isset($errors['event_name'])) : ?>
                        <div class="field-error"><?= htmlspecialchars($errors['event_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Status <span style="color:var(--danger);">*</span></label>
                        <select name="status" class="form-control-custom" required>
                            <?php foreach ($allowed_statuses as $st) : ?>
                            <option value="<?= $st ?>" <?= $form['status'] === $st ? 'selected' : '' ?>>
                                <?= ucfirst($st) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['status'])) : ?>
                        <div class="field-error"><?= htmlspecialchars($errors['status']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Description <span style="color:var(--danger);">*</span></label>
                    <textarea name="description" class="form-control-custom" rows="4"
                              placeholder="Brief description of the event..."
                              style="resize:vertical;" required><?= htmlspecialchars($form['description']) ?></textarea>
                    <?php if (isset($errors['description'])) : ?>
                    <div class="field-error"><?= htmlspecialchars($errors['description']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Event Date <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="event_date" class="form-control-custom"
                               value="<?= htmlspecialchars($form['event_date']) ?>" required>
                        <?php if (isset($errors['event_date'])) : ?>
                        <div class="field-error"><?= htmlspecialchars($errors['event_date']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Event Time <span style="color:var(--danger);">*</span></label>
                        <input type="time" name="event_time" class="form-control-custom"
                               value="<?= htmlspecialchars($form['event_time']) ?>" required>
                        <?php if (isset($errors['event_time'])) : ?>
                        <div class="field-error"><?= htmlspecialchars($errors['event_time']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Venue <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="venue" class="form-control-custom"
                               placeholder="e.g. Main Auditorium"
                               value="<?= htmlspecialchars($form['venue']) ?>"
                               maxlength="200" required>
                        <?php if (isset($errors['venue'])) : ?>
                        <div class="field-error"><?= htmlspecialchars($errors['venue']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Organiser / Department <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="organizer" class="form-control-custom"
                               placeholder="e.g. CSE Department"
                               value="<?= htmlspecialchars($form['organizer']) ?>"
                               maxlength="150" required>
                        <?php if (isset($errors['organizer'])) : ?>
                        <div class="field-error"><?= htmlspecialchars($errors['organizer']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Max Participants <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="max_participants" class="form-control-custom"
                           placeholder="100"
                           value="<?= htmlspecialchars($form['max_participants']) ?>"
                           min="1" max="10000" required>
                    <?php if (isset($errors['max_participants'])) : ?>
                    <div class="field-error"><?= htmlspecialchars($errors['max_participants']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Add Event
                    </button>
                    <a href="dashboard.php" class="btn-outline-custom">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
