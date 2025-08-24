<?php
/**
 * Sistema de Autenticação
 * Gerencia sessões e controle de acesso
 */

// Configurações de sessão para compatibilidade local/online
if (session_status() == PHP_SESSION_NONE) {
    // Configurar parâmetros de sessão antes de iniciar
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    
    // Configurações específicas para ambiente online
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para compatibilidade
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    session_start();
    
    // Regenerar ID da sessão periodicamente para segurança
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Verifica se o usuário está logado
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica se o usuário tem o tipo específico
 * @param string $tipo
 * @return bool
 */
function hasUserType($tipo) {
    return isLoggedIn() && $_SESSION['tipo_usuario'] === $tipo;
}

/**
 * Verifica se é cliente
 * @return bool
 */
function isCliente() {
    return hasUserType('cliente');
}

/**
 * Verifica se é parceiro
 * @return bool
 */
function isParceiro() {
    return hasUserType('parceiro');
}

/**
 * Verifica se é administrador
 * @return bool
 */
function isAdmin() {
    return hasUserType('admin');
}

/**
 * Realiza login do usuário
 * @param array $usuario
 */
function login($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
    $_SESSION['usuario_telefone'] = $usuario['telefone'];
}

/**
 * Realiza logout do usuário
 */
function logout() {
    session_unset();
    session_destroy();
}

/**
 * Redireciona usuário não autenticado
 * @param string $redirect_to
 */
function requireLogin($redirect_to = '/login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Redireciona usuário sem permissão
 * @param string $tipo_requerido
 * @param string $redirect_to
 */
function requireUserType($tipo_requerido, $redirect_to = '/index.php') {
    requireLogin();
    
    if (!hasUserType($tipo_requerido)) {
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Redireciona cliente
 * @param string $redirect_to
 */
function requireCliente($redirect_to = '/index.php') {
    requireUserType('cliente', $redirect_to);
}

/**
 * Redireciona parceiro
 * @param string $redirect_to
 */
function requireParceiro($redirect_to = '/index.php') {
    requireUserType('parceiro', $redirect_to);
}

/**
 * Redireciona administrador
 * @param string $redirect_to
 */
function requireAdmin($redirect_to = '/index.php') {
    requireUserType('admin', $redirect_to);
}

/**
 * Obtém dados do usuário logado
 * @return array|null
 */
function getLoggedUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
        'tipo_usuario' => $_SESSION['tipo_usuario'],
        'telefone' => $_SESSION['usuario_telefone']
    ];
}

/**
 * Gera token CSRF
 * @return string
 */
function generateCSRFToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Usar chave padrão para compatibilidade
    $tokenKey = 'csrf_token';
    
    // Verificar se já existe um token válido
    if (isset($_SESSION[$tokenKey]) && isset($_SESSION['csrf_token_time'])) {
        $age = time() - $_SESSION['csrf_token_time'];
        if ($age < 7200) { // Token válido por 2 horas (produção)
            return $_SESSION[$tokenKey];
        }
    }
    
    // Gerar novo token
    try {
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            $token = md5(uniqid(mt_rand(), true));
        }
        
        // Armazenar na sessão com timestamp
        $_SESSION[$tokenKey] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
        
    } catch (Exception $e) {
        error_log("Erro ao gerar token CSRF: " . $e->getMessage());
        // Fallback de emergência
        $token = md5(session_id() . time() . mt_rand());
        $_SESSION[$tokenKey] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }
}

/**
 * Verifica token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Usar chave padrão para compatibilidade
    $tokenKey = 'csrf_token';
    
    // Normalizar tokens (remover espaços e quebras de linha)
    $receivedToken = trim($token);
    $sessionToken = isset($_SESSION[$tokenKey]) ? trim($_SESSION[$tokenKey]) : '';
    
    // Verificar se tokens existem
    if (empty($receivedToken) || empty($sessionToken)) {
        return false;
    }
    
    // Verificar expiração (2 horas para produção)
    $tokenTime = isset($_SESSION['csrf_token_time']) ? $_SESSION['csrf_token_time'] : 0;
    $age = time() - $tokenTime;
    
    if ($age > 7200) { // 2 horas
        return false;
    }
    
    // Validação final com comparação segura
    return function_exists('hash_equals') ? 
           hash_equals($sessionToken, $receivedToken) : 
           ($receivedToken === $sessionToken);
}

/**
 * Alias para generateCSRFToken (compatibilidade)
 * @return string
 */
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        $token = generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

/**
 * Alias para verifyCSRFToken (compatibilidade)
 * @param string $token
 * @return bool
 */
if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        return verifyCSRFToken($token);
    }
}

/**
 * Sanitiza entrada do usuário
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida telefone brasileiro
 * @param string $telefone
 * @return bool
 */
function validateTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    return strlen($telefone) >= 10 && strlen($telefone) <= 11;
}

/**
 * Formata telefone
 * @param string $telefone
 * @return string
 */
function formatTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) == 11) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    } elseif (strlen($telefone) == 10) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
    }
    
    return $telefone;
}

/**
 * Exibe mensagens de sucesso/erro
 * @param string $type
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtém e remove mensagem flash
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
?>