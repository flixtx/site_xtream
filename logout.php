<?php
session_start();

// Limpar cookie de "Manter conectado"
setcookie('iptv_remember', '', time() - 3600, '/'); // Expira o cookie

session_destroy();
header('Location: login.php');
exit;
?>