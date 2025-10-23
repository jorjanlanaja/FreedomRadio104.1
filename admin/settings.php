<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$message = $error = '';
$activeTab = $_POST['active_tab'] ?? 'general';

/* ----------  handle saves  ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance()->getConnection();

        /* ✅ UPSERT helper */
        $store = function (string $key, string $value) use ($db) {
            $stmt = $db->prepare('
                INSERT INTO settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    updated_at = CURRENT_TIMESTAMP
            ');
            $stmt->execute([$key, $value]);
        };

        if ($activeTab === 'general') {
            $keys = [
                'station_name', 'station_description', 'live_stream_url',
                'contact_email', 'contact_phone', 'location',
                'facebook_url', 'twitter_url', 'instagram_url',
                'facebook_embed_url', 'localserver_url'   // <-- NEW
            ];
            foreach ($keys as $k) $store($k, $_POST[$k] ?? '');
            $message = 'General settings saved.';
        } elseif ($activeTab === 'appearance') {
            $store('primary_color', $_POST['primary_color']);
            $store('secondary_color', $_POST['secondary_color']);

            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['logo'];
                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $dir = __DIR__ . '/../assets/images/';
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    $path = '/assets/images/logo_' . time() . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], __DIR__ . '/..' . $path);
                    $store('logo', $path);
                }
            }
            $message = 'Appearance saved.';
        } elseif ($activeTab === 'security') {
            $cur  = $_POST['current_password'];
            $new  = $_POST['new_password'];
            $conf = $_POST['confirm_password'];
            if ($new !== $conf) throw new RuntimeException('New passwords do not match.');
            $stmt = $db->prepare('SELECT password FROM admins WHERE id = ?');
            $stmt->execute([$_SESSION['admin_id']]);
            if (!password_verify($cur, $stmt->fetchColumn()))
                throw new RuntimeException('Current password is incorrect.');
            $db->prepare('UPDATE admins SET password = ? WHERE id = ?')
               ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            $message = 'Password changed successfully.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

/* ----------  read current values  ---------- */
$settings = getAllSettings();
$defaults = [
    'station_name' => 'FM Radio Station',
    'station_description' => 'Your favourite station',
    'live_stream_url' => 'http://10.1.0.29:8000/stream ',
    'primary_color' => '#ff6b35',
    'secondary_color' => '#004e89',
    'logo' => '',
    'contact_email' => '',
    'contact_phone' => '',
    'location' => '',
    'facebook_url' => '',
    'twitter_url' => '',
    'instagram_url' => '',
    'facebook_embed_url' => '',
    'localserver_url' => 'http://localhost:8000'   // <-- NEW
];
foreach ($defaults as $k => $v)
    if (!isset($settings[$k])) $settings[$k] = $v;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - FM Radio Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css " rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css ">
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
                <li class="nav-item"><a class="nav-link" href="programs.php"><i class="fas fa-calendar"></i> Programs</a></li>
                <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Settings</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item"><button class="nav-link <?= $activeTab === 'general' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button></li>
                <li class="nav-item"><button class="nav-link <?= $activeTab === 'appearance' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#appearance" type="button">Appearance</button></li>
                <li class="nav-item"><button class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#security" type="button">Security</button></li>
            </ul>

            <form method="post" enctype="multipart/form-data" class="tab-content pt-4" id="settingsTabContent">
                <!-- General -->
                <div class="tab-pane fade <?= $activeTab === 'general' ? 'show active' : '' ?>" id="general">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Station Name</label><input type="text" class="form-control" name="station_name" value="<?= htmlspecialchars($settings['station_name']) ?>" required></div>
                            <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="station_description" rows="3"><?= htmlspecialchars($settings['station_description']) ?></textarea></div>
                            <div class="mb-3"><label class="form-label">Live-Stream URL</label><input type="url" class="form-control" name="live_stream_url" value="<?= htmlspecialchars($settings['live_stream_url']) ?>" required></div>
                            <!-- ➜ NEW FIELD -->
                            <div class="mb-3">
                                <label class="form-label">Facebook Embed URL (live video)</label>
                                <input type="url" class="form-control" name="facebook_embed_url" value="<?= htmlspecialchars($settings['facebook_embed_url'] ?? '') ?>" placeholder="https://www.facebook.com/plugins/video.php?href= ...">
                                <div class="form-text">Paste the Facebook <b>Embed</b> URL you copy from the video → Embed → iFrame</div>
                            </div>
                            <!-- ➜ NEW LOCALSERVER FIELD -->
                            <div class="mb-3">
                                <label class="form-label">Local-Server Connectivity URL</label>
                                <input type="url" class="form-control" name="localserver_url" value="<?= htmlspecialchars($settings['localserver_url']) ?>" placeholder="http://localhost:8000">
                                <div class="form-text">Endpoint used for local server communication.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Contact Email</label><input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($settings['contact_email']) ?>"></div>
                            <div class="mb-3"><label class="form-label">Contact Phone</label><input type="tel" class="form-control" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone']) ?>"></div>
                            <div class="mb-3"><label class="form-label">Location</label><input type="text" class="form-control" name="location" value="<?= htmlspecialchars($settings['location']) ?>"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"><div class="mb-3"><label class="form-label"><i class="fab fa-facebook"></i> Facebook</label><input type="url" class="form-control" name="facebook_url" value="<?= htmlspecialchars($settings['facebook_url']) ?>"></div></div>
                        <div class="col-md-4"><div class="mb-3"><label class="form-label"><i class="fab fa-twitter"></i> Twitter</label><input type="url" class="form-control" name="twitter_url" value="<?= htmlspecialchars($settings['twitter_url']) ?>"></div></div>
                        <div class="col-md-4"><div class="mb-3"><label class="form-label"><i class="fab fa-instagram"></i> Instagram</label><input type="url" class="form-control" name="instagram_url" value="<?= htmlspecialchars($settings['instagram_url']) ?>"></div></div>
                    </div>
                    <!-- SAVE BUTTON INSIDE TAB -->
                    <div class="text-end mt-4">
                        <input type="hidden" name="active_tab" value="general">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save General</button>
                    </div>
                </div>

                <!-- Appearance -->
                <div class="tab-pane fade <?= $activeTab === 'appearance' ? 'show active' : '' ?>" id="appearance">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Primary Colour</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color']) ?>">
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($settings['primary_color']) ?>" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Secondary Colour</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color']) ?>">
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($settings['secondary_color']) ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Station Logo</label>
                                <?php if ($settings['logo']): ?>
                                    <div class="mb-2"><img src="<?= htmlspecialchars(BASE_URL . $settings['logo']) ?>" style="max-height:100px"></div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="logo" accept="image/*">
                                <div class="form-text">Leave empty to keep current logo</div>
                            </div>
                        </div>
                    </div>
                    <!-- SAVE BUTTON INSIDE TAB -->
                    <div class="text-end mt-4">
                        <input type="hidden" name="active_tab" value="appearance">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Appearance</button>
                    </div>
                </div>

                <!-- Security -->
                <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : '' ?>" id="security">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
                            <div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" required></div>
                            <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" name="confirm_password" required></div>
                        </div>
                    </div>
                    <!-- SAVE BUTTON INSIDE TAB -->
                    <div class="text-end mt-4">
                        <input type="hidden" name="active_tab" value="security">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Security</button>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js "></script>

<!-- keeps same tab open after save -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
  const hidden     = document.querySelector('input[name="active_tab"]');
  tabButtons.forEach(btn => {
    btn.addEventListener('shown.bs.tab', e => {
      if (hidden) hidden.value = e.target.dataset.bsTarget.substring(1);
    });
  });
});
</script>
</body>
</html>