<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

/* ----------  stats  ---------- */
$db              = Database::getInstance()->getConnection();
$settings        = getAllSettings();
$totalPrograms   = (int) $db->query('SELECT COUNT(*) FROM programs')->fetchColumn();
$activePrograms  = (int) $db->query('SELECT COUNT(*) FROM programs WHERE is_active = 1')->fetchColumn();
$recentPrograms  = $db->query(
    'SELECT * FROM programs WHERE is_active = 1 ORDER BY id DESC LIMIT 8'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FM Radio Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL) ?>/assets/css/admin.css">
</head>
<body>
<!-- ----------  top nav  ---------- -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fas fa-radio"></i> FM Radio Admin</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#topNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>" target="_blank"><i class="fas fa-home"></i> View Site</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- ----------  sidebar  ---------- -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar pt-3">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="programs.php"><i class="fas fa-calendar"></i> Programs</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- ----------  main content  ---------- -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="programs.php?action=add" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Program</a>
                </div>
            </div>

            <!-- ----------  alert for low-config  ---------- -->
            <?php if (empty($settings['live_stream_url'])): ?>
                <div class="alert alert-warning">Live-stream URL not configured. <a href="settings.php" class="alert-link">Go to Settings</a></div>
            <?php endif; ?>

            <!-- ----------  stat cards  ---------- -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Programs</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPrograms ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Programs</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $activePrograms ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-play fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Station Name</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars($settings['station_name']) ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-radio fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Stream Host</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><small><?= parse_url($settings['live_stream_url'], PHP_URL_HOST) ?: 'Not set' ?></small></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-stream fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facebook Live Feed (staff only) -->
<div class="card shadow mt-4">
    <div class="card-header fw-bold"><i class="fab fa-facebook"></i> Facebook Live Feed</div>
    <div class="card-body p-0">
        <div class="ratio ratio-16x9">
            <?php if (!empty($settings['facebook_embed_url'])): ?>
                <iframe src="<?= htmlspecialchars($settings['facebook_embed_url']) ?>"
                        width="100%" height="315" style="border:none;overflow:hidden" scrolling="no"
                        frameborder="0" allowfullscreen="true"
                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                </iframe>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-muted p-4">
                    No Facebook embed URL set – <a href="settings.php" class="alert-link">add one in Settings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

            <!-- ----------  recent programs  ---------- -->
            <div class="card shadow mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="fas fa-calendar"></i> Recent Programs</span>
                    <a href="programs.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Host</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPrograms as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['title']) ?></td>
                                    <td><?= htmlspecialchars($p['host']) ?></td>
                                    <td><?= htmlspecialchars($p['day_of_week']) ?></td>
                                    <td><?= date('g:i A', strtotime($p['start_time'])) ?></td>
                                    <td>
                                        <a href="programs.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>