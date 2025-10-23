<?php
// Boot-strap the core files with absolute paths
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/* ---------- Public / Front-end helpers ---------- */
function getSetting(string $key): string
{
    $db  = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row['setting_value'] ?? '';
}

function getAllSettings(): array
{
    $db  = Database::getInstance()->getConnection();
    return $db->query('SELECT setting_key, setting_value FROM settings')
              ->fetchAll(PDO::FETCH_KEY_PAIR);
}

function getPrograms(): array
{
    $db  = Database::getInstance()->getConnection();
    return $db->query('SELECT * FROM programs WHERE is_active = 1 ORDER BY day_of_week, start_time')
              ->fetchAll();
}

function getCurrentProgram(): ?array
{
    $db    = Database::getInstance()->getConnection();
    $day   = date('l');
    $time  = date('H:i:s');

    $stmt = $db->prepare(
        'SELECT * FROM programs
         WHERE day_of_week = ?
           AND start_time <= ?
           AND end_time > ?
           AND is_active = 1
         ORDER BY start_time LIMIT 1'
    );
    $stmt->execute([$day, $time, $time]);
    return $stmt->fetch() ?: null;
}

/* ---------- Admin / Auth helpers ---------- */
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', rtrim(BASE_URL, '/') . '/admin');
}

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit();
    }
}