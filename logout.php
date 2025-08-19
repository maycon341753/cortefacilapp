<?php
/**
 * Página de Logout
 * Encerra a sessão do usuário e redireciona para a página inicial
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Verificar se o usuário está logado
if (isLoggedIn()) {
    $usuario = getLoggedUser();
    
    // Log da atividade de logout
    logActivity($usuario['id'], 'logout', 'Usuário fez logout do sistema');
    
    // Fazer logout
    logout();
    
    // Definir mensagem de sucesso
    setFlashMessage('success', 'Logout realizado com sucesso!');
}

// Redirecionar para a página inicial
header('Location: index.php');
exit;
?>