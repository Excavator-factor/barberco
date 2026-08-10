<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Hapus cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

session_unset();
session_destroy();

header("Location: login.php");
exit;