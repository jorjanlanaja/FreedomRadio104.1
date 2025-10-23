<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$db   = Database::getInstance()->getConnection();
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

/* ----------  CRUD HANDLERS  ---------- */
if (isset($_POST['save'])) {
    // Add or Update
    $id          = (int)($_POST['id'] ?? 0);
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $host        = trim($_POST['host']);
    $day         = $_POST['day_of_week'];
    $start       = $_POST['start_time'];
    $end         = $_POST['end_time'];
    $active      = isset($_POST['is_active']) ? 1 : 0;

    if ($id === 0) {
        // INSERT
        $stmt = $db->prepare('INSERT INTO programs 
                             (title,description,host,day_of_week,start_time,end_time,is_active)
                             VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$title,$description,$host,$day,$start,$end,$active]);
        $msg = 'Program added.';
    } else {
        // UPDATE
        $stmt = $db->prepare('UPDATE programs 
                             SET title=?, description=?, host=?, day_of_week=?, 
                                 start_time=?, end_time=?, is_active=? 
                             WHERE id=?');
        $stmt->execute([$title,$description,$host,$day,$start,$end,$active,$id]);
        $msg = 'Program updated.';
    }
    header('Location: programs.php?msg='.urlencode($msg));
    exit();
}

if (isset($_GET['del'])) {
    $stmt = $db->prepare('DELETE FROM programs WHERE id = ?');
    $stmt->execute([(int)$_GET['del']]);
    header('Location: programs.php?msg='.urlencode('Program deleted.'));
    exit();
}

/* ----------  DATA FOR LIST  ---------- */
$programs = $db->query('SELECT * FROM programs ORDER BY 
                        FIELD(day_of_week,"Monday","Tuesday","Wednesday",
                              "Thursday","Friday","Saturday","Sunday"), start_time')->fetchAll();

/* ----------  DATA FOR EDIT (if ?edit=id)  ---------- */
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM programs WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs - FM Radio Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL) ?>/assets/css/admin.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><i class="fas fa-radio"></i> FM Radio Admin</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>" target="_blank"><i class="fas fa-home"></i> View Site</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar pt-3">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="programs.php"><i class="fas fa-calendar"></i> Programs</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Programs</h1>
                <a href="?add=1" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Program</a>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <!-- LIST TABLE -->
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Host</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($programs as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['title']) ?></td>
                                    <td><?= htmlspecialchars($p['host']) ?></td>
                                    <td><?= htmlspecialchars($p['day_of_week']) ?></td>
                                    <td><?= date('g:i A', strtotime($p['start_time'])) . ' - ' . date('g:i A', strtotime($p['end_time'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $p['is_active'] ? 'success' : 'secondary' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="?del=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this program?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ADD / EDIT FORM -->
            <?php if (isset($_GET['add']) || $edit): ?>
            <div class="card shadow mt-4">
                <div class="card-header"><?= $edit ? 'Edit Program' : 'Add New Program' ?></div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3"><label class="form-label">Title *</label><input type="text" class="form-control" name="title" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required></div>
                                <div class="mb-3"><label class="form-label">Host *</label><input type="text" class="form-control" name="host" value="<?= htmlspecialchars($edit['host'] ?? '') ?>" required></div>
                                <div class="mb-3"><label class="form-label">Day *</label>
                                    <select class="form-select" name="day_of_week" required>
                                        <?php foreach ($days as $d): ?>
                                        <option value="<?= $d ?>" <?= ($edit['day_of_week'] ?? '') === $d ? 'selected' : '' ?>><?= $d ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3"><label class="form-label">Start Time *</label><input type="time" class="form-control" name="start_time" value="<?= $edit['start_time'] ?? '' ?>" required></div>
                                <div class="mb-3"><label class="form-label">End Time *</label><input type="time" class="form-control" name="end_time" value="<?= $edit['end_time'] ?? '' ?>" required></div>
                                <div class="mb-3 form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label">Active</label></div>
                            </div>
                        </div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea></div>
                        <div class="text-end">
                            <a href="programs.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>