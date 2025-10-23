<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fm_radio');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', 'http://10.1.0.29');
define('ADMIN_URL', BASE_URL . '/admin');
define('ASSETS_URL', BASE_URL . '/assets');

session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>