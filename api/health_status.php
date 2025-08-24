<?php
/**
 * API Endpoint para Verificação de Saúde do Sistema
 * Retorna status JSON da saúde do sistema
 */

// Configurações de erro para API
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Headers para API JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir health check
try {
    // Desabilitar redirecionamento automático
    define('HEALTH_CHECK_DISABLED', true);
    
    require_once __DIR__ . '/../includes/health_check.php';
    
    // Obter status do sistema
    $status = getSystemHealthStatus();
    
    // Adicionar informações extras
    $status['server_time'] = date('Y-m-d H:i:s');
    $status['php_version'] = PHP_VERSION;
    $status['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
    
    // Status HTTP baseado na saúde
    if ($status['healthy']) {
        http_response_code(200);
    } else {
        http_response_code(503); // Service Unavailable
    }
    
    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Erro crítico
    http_response_code(500);
    
    $error_response = [
        'healthy' => false,
        'message' => 'Erro interno do sistema',
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => 'Sistema indisponível'
    ];
    
    echo json_encode($error_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Log do erro
    error_log('Health status API error: ' . $e->getMessage());
}
?>