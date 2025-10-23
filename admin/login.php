<?php
// ----------  bootstrap  ----------
require_once __DIR__ . '/../includes/functions.php';

/* ---- Redirect if already logged-in ---- */
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['username'], $_POST['password'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT id, password FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        $_SESSION['admin_username']  = $username;
        header('Location: dashboard.php');
        exit();
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freedom FM Admin</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL) ?>/assets/css/admin.css">

    <style>
        html,body{height:100%}
        body{display:flex;align-items:center;background:#f2f4f7}
        .login-card{width:100%;max-width:420px;padding:2rem;margin:auto;border:0;border-radius:1rem;box-shadow:0 .5rem 1rem rgba(0,0,0,.15)}
        .login-header{text-align:center;margin-bottom:1.5rem}
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-radio fa-3x text-primary mb-2"></i>
            <h4 class="fw-bold">Freedom FM CMS</h4>
            <small class="text-muted">Sign in to manage your station</small>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" id="username" class="form-control" placeholder="admin" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePw">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">Smile <strong>Wifi</strong> / <strong>2025</strong></small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ---- Toggle password visibility ---- */
        document.getElementById('togglePw').addEventListener('click', function () {
            const pw  = document.getElementById('password');
            const ic  = this.querySelector('i');
            const show = pw.type === 'password';
            pw.type  = show ? 'text' : 'password';
            ic.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });

        /* ---- Simple loading state ---- */
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in…';
            btn.disabled = true;
        });
    </script>
</body>
</html>