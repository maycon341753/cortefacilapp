<?php
require_once __DIR__ . '/includes/auth.php';

// Fazer logout
logout();

// Redirecionar para página inicial
header('Location: index.php');
exit;
?>