<?php
/**
 * Página de Logout - CorteFácil
 * Versão corrigida para produção - Proteção contra erro 500
 * Corrigido em 2025-08-21
 */

// Configurações de erro para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão de forma segura
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    // Incluir arquivos necessários de forma segura
    $auth_file = __DIR__ . '/includes/auth.php';
    $functions_file = __DIR__ . '/includes/functions.php';
    
    if (!file_exists($auth_file)) {
        throw new Exception('Arquivo de autenticação não encontrado');
    }
    if (!file_exists($functions_file)) {
        throw new Exception('Arquivo de funções não encontrado');
    }
    
    require_once $auth_file;
    require_once $functions_file;
    
    // Verificar se o usuário está logado com proteção
    if (function_exists('isLoggedIn') && isLoggedIn()) {
        try {
            if (function_exists('getLoggedUser')) {
                $usuario = getLoggedUser();
                
                // Log da atividade de logout com proteção
                if (function_exists('logAtividade') && is_array($usuario) && isset($usuario['nome'])) {
                    logAtividade("Usuário {$usuario['nome']} fez logout do sistema", 'INFO');
                }
            }
            
            // Fazer logout com proteção
            if (function_exists('logout')) {
                logout();
            }
            
            // Definir mensagem de sucesso com proteção
            if (function_exists('setFlashMessage')) {
                setFlashMessage('success', 'Logout realizado com sucesso!');
            }
        } catch (Exception $e) {
            error_log('Erro durante logout: ' . $e->getMessage());
            // Continuar com o logout mesmo com erro
        }
    }
    
} catch (Exception $e) {
    // Log do erro crítico
    error_log('Erro crítico na página de logout: ' . $e->getMessage());
    
    // Limpar sessão manualmente como fallback
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

// Redirecionar para a página de login sempre
header('Location: login.php');
exit;
?>