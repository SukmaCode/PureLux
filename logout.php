<?php
require_once 'config/config.php';

// Hapus semua session
session_destroy();

// Redirect ke halaman login
header('Location: homepage2.html');
exit();
?>
