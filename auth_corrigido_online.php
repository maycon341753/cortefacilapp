<?php
/**
 * Sistema de Autenticação - Versão Corrigida para Ambiente Online
 * Correção específica para resolver problemas de CSRF em produção
 */

// Configurações robustas para servidor online
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Configurações específicas para HTTPS (ambiente online)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para compatibilidade com navegadores modernos
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    // Definir nome da sessão específico
    session_name('CORTEFACIL_SESSION');
    
    // Iniciar sessão
    session_start();
    
    // Regenerar ID da sessão periodicamente
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica o tipo de usuário
 */
function getUserType() {
    return $_SESSION['tipo_usuario'] ?? null;
}

/**
 * Função melhorada para gerar token CSRF
 * Versão específica para ambiente online
 */
function generateCSRFToken() {
    // Garantir que a sessão está ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Gerar novo token se necessário
    $regenerate_token = false;
    
    if (!isset($_SESSION['csrf_token'])) {
        $regenerate_token = true;
    } elseif (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        // Token expira em 1 hora
        $regenerate_token = true;
    } elseif (!isset($_SESSION['csrf_token_time'])) {
        // Se não tem timestamp, regenerar
        $regenerate_token = true;
    }
    
    if ($regenerate_token) {
        // Usar método mais robusto para gerar token
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback para servidores mais antigos
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        }
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Função melhorada para verificar token CSRF
 * Versão específica para ambiente online
 */
function verifyCSRFToken($token) {
    // Garantir que a sessão está ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Verificações básicas
    if (empty($token)) {
        return false;
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Verificar se o token não expirou
    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    
    // Comparação segura
    if (function_exists('hash_equals')) {
        return hash_equals($_SESSION['csrf_token'], $token);
    } else {
        // Fallback para servidores sem hash_equals
        return $_SESSION['csrf_token'] === $token;
    }
}

/**
 * Função para gerar campo hidden do CSRF
 */
function generateCSRFField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Função para fazer logout
 */
function logout() {
    // Limpar todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir o cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
}

/**
 * Função para verificar permissões
 */
function checkPermission($required_type) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
    
    $user_type = getUserType();
    if ($user_type !== $required_type && $user_type !== 'admin') {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Função para redirecionar baseado no tipo de usuário
 */
function redirectByUserType() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    $user_type = getUserType();
    switch ($user_type) {
        case 'cliente':
            header('Location: cliente/');
            break;
        case 'parceiro':
            header('Location: parceiro/');
            break;
        case 'admin':
            header('Location: admin/');
            break;
        default:
            header('Location: login.php');
            break;
    }
    exit();
}

/**
 * Função para sanitizar dados de entrada
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Função para validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Função para hash de senha
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Função para verificar senha
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Função para gerar senha aleatória
 */
function generateRandomPassword($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Função para log de atividades
 */
function logActivity($action, $details = '') {
    $log_file = '../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['usuario_id'] ?? 'N/A';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
    
    $log_entry = "[$timestamp] User: $user_id | IP: $ip | Action: $action | Details: $details" . PHP_EOL;
    
    // Criar diretório de logs se não existir
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Função para verificar rate limiting
 */
function checkRateLimit($action, $max_attempts = 5, $time_window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $action . '_' . $ip;
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = array();
    }
    
    $now = time();
    
    // Limpar tentativas antigas
    if (isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = array_filter(
            $_SESSION['rate_limit'][$key],
            function($timestamp) use ($now, $time_window) {
                return ($now - $timestamp) < $time_window;
            }
        );
    } else {
        $_SESSION['rate_limit'][$key] = array();
    }
    
    // Verificar se excedeu o limite
    if (count($_SESSION['rate_limit'][$key]) >= $max_attempts) {
        return false;
    }
    
    // Registrar nova tentativa
    $_SESSION['rate_limit'][$key][] = $now;
    return true;
}

// Inicializar sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>