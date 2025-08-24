<?php
/**
 * Endpoint para regenerar token CSRF via AJAX
 * Usado para manter a sessão ativa sem recarregar a página
 */

// Configurar cabeçalhos para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Configurar sessão
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de sessão seguras para produção
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 7200); // 2 horas
    
    // Configurar cookie seguro apenas se HTTPS estiver disponível
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para proteção adicional
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    session_start();
}

try {
    // Incluir arquivos necessários
    require_once '../includes/auth.php';
    
    // Verificar se é parceiro autenticado
    if (!isLoggedIn() || !isParceiro()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Não autorizado'
        ]);
        exit;
    }
    
    // Gerar novo token
    $newToken = generateCsrfToken();
    
    if ($newToken) {
        echo json_encode([
            'success' => true,
            'token' => $newToken,
            'timestamp' => time()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao gerar token'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro ao regenerar token CSRF: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
}
?>