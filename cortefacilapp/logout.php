<?php
/**
 * Script de Logout
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once 'includes/auth.php';

// Realizar logout
$auth->logout();

// Redirecionar para página inicial com mensagem
header('Location: index.php?logout=success');
exit;
?>