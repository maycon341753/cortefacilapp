<?php
/**
 * Redirecionamento para Dashboard do Parceiro
 * Resolve erro 403 Forbidden ao acessar /parceiro/
 */

// Redirecionar para o dashboard do parceiro
header('Location: dashboard.php');
exit;
?>