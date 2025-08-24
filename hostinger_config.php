<?php
/**
 * Configurações específicas para Hostinger - CorteFácil
 * Este arquivo contém configurações otimizadas para o ambiente Hostinger
 * 
 * @version 1.0
 * @date 2024
 */

// Configurações de timezone para o Brasil
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro para produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

// Configurações de sessão seguras para CSRF
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 7200); // 2 horas
ini_set('session.cookie_lifetime', 0);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Configurações de cookie seguro (descomente para HTTPS)
// ini_set('session.cookie_secure', 1);
// ini_set('session.cookie_samesite', 'Lax');

// Configurações de performance
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('max_input_vars', 3000);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
ini_set('max_file_uploads', 20);

// Configurações de buffer de saída
ini_set('output_buffering', 'On');
ini_set('output_buffer_size', 4096);

// Configurações de compressão (se suportado)
if (extension_loaded('zlib') && !ob_get_level()) {
    ob_start('ob_gzhandler');
}

// Cabeçalhos de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
    $csp .= "img-src 'self' data: https:; ";
    $csp .= "font-src 'self' https://cdnjs.cloudflare.com; ";
    $csp .= "connect-src 'self';";
    header('Content-Security-Policy: ' . $csp);
}

// Função para detectar ambiente Hostinger
function isHostingerEnvironment() {
    return (
        isset($_SERVER['HTTP_HOST']) && 
        (strpos($_SERVER['HTTP_HOST'], '.hostinger') !== false ||
         strpos($_SERVER['HTTP_HOST'], '.hostingerapp') !== false ||
         isset($_SERVER['HOSTINGER_WP_CONFIG_PATH'])
        )
    );
}

// Configurações específicas do Hostinger
if (isHostingerEnvironment()) {
    // Configurações adicionais para Hostinger
    ini_set('user_agent', 'CorteFacil/1.0 (Hostinger)');
    
    // Configurações de cache específicas
    if (!headers_sent()) {
        header('Cache-Control: no-cache, no-store, must-revalidate', false);
        header('Pragma: no-cache', false);
        header('Expires: 0', false);
    }
}

// Função para log personalizado
function logHostingerError($message, $level = 'ERROR') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    error_log($logMessage, 3, __DIR__ . '/hostinger_debug.log');
}

// Função para verificar configurações críticas
function checkHostingerConfig() {
    $checks = [];
    
    // Verificar se sessões estão funcionando
    $checks['sessions'] = session_status() === PHP_SESSION_ACTIVE || session_start();
    
    // Verificar se mod_rewrite está disponível (aproximação)
    $checks['mod_rewrite'] = function_exists('apache_get_modules') ? 
        in_array('mod_rewrite', apache_get_modules()) : true;
    
    // Verificar permissões de escrita
    $checks['write_permissions'] = is_writable(__DIR__);
    
    // Verificar extensões PHP necessárias
    $checks['mysqli'] = extension_loaded('mysqli');
    $checks['json'] = extension_loaded('json');
    $checks['mbstring'] = extension_loaded('mbstring');
    
    return $checks;
}

// Executar verificações se em modo debug
if (defined('HOSTINGER_DEBUG') && HOSTINGER_DEBUG) {
    $config_check = checkHostingerConfig();
    foreach ($config_check as $check => $status) {
        if (!$status) {
            logHostingerError("Configuração falhou: {$check}", 'WARNING');
        }
    }
}

// Configurações de CORS se necessário
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowed_origins = [
        'https://' . $_SERVER['HTTP_HOST'],
        'http://' . $_SERVER['HTTP_HOST']
    ];
    
    if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
    }
}

// Tratar requisições OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

?>