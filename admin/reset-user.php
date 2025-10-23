<?php
/* ----------  bootstrap  ---------- */
require_once __DIR__ . '/../includes/functions.php';

/* ----------  create / overwrite admin user  ---------- */
$username = 'admin';
$password = 'pass1234';          // plain text
$hash     = password_hash($password, PASSWORD_DEFAULT);

$db = Database::getInstance()->getConnection();

// delete old row (if any) and insert new one
$db->prepare('DELETE FROM admins WHERE username = ?')->execute([$username]);

$stmt = $db->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
$stmt->execute([$username, $hash]);

/* ----------  auto-log-in and redirect  ---------- */
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id']        = $db->lastInsertId();
$_SESSION['admin_username']  = $username;

header('Location: dashboard.php');
exit();