<?php
// Logout (FR-03): hapus sesi lalu kembali ke halaman login
require __DIR__ . '/config.php';

$_SESSION = [];
session_destroy();
header('Location: ' . url('login.php'));
exit;
