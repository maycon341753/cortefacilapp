<?php
/**
 * Sistema de Autenticação - CorteFácil
 * Versão FINAL LIMPA para PRODUÇÃO - Hostinger
 * Corrigido definitivamente em 2025-08-21
 */

/**
 * Gera token CSRF de forma consistente
 */
function generateCSRFToken() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = "csrf_token";
    $time_key = "csrf_token_time";
    
    $need_new_token = false;
    
    if (!isset($_SESSION[$token_key])) {
        $need_new_token = true;
    } elseif (isset($_SESSION[$time_key]) && (time() - $_SESSION[$time_key]) > 7200) {
        $need_new_token = true;
        unset($_SESSION[$token_key], $_SESSION[$time_key]);
    } elseif (!isset($_SESSION[$time_key])) {
        $need_new_token = true;
    }
    
    if ($need_new_token) {
        if (function_exists("random_bytes")) {
            $_SESSION[$token_key] = bin2hex(random_bytes(32));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $_SESSION[$token_key] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            $_SESSION[$token_key] = hash("sha256", uniqid(mt_rand(), true) . microtime(true));
        }
        $_SESSION[$time_key] = time();
    }
    
    return $_SESSION[$token_key];
}

/**
 * Verifica token CSRF
 */
function verifyCSRFToken($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = "csrf_token";
    $time_key = "csrf_token_time";
    
    $received_token = trim($token);
    $session_token = isset($_SESSION[$token_key]) ? trim($_SESSION[$token_key]) : "";
    
    if (empty($received_token) || empty($session_token)) {
        return false;
    }
    
    if (isset($_SESSION[$time_key])) {
        $age = time() - $_SESSION[$time_key];
        if ($age > 7200) {
            unset($_SESSION[$token_key], $_SESSION[$time_key]);
            return false;
        }
    }
    
    if (function_exists("hash_equals")) {
        return hash_equals($session_token, $received_token);
    } else {
        return $session_token === $received_token;
    }
}

/**
 * Alias para compatibilidade - gera campo HTML com token CSRF
 */
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        $token = generateCSRFToken();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($token) . "\">";
    }
}

/**
 * Alias para compatibilidade - verifica token
 */
if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        return verifyCSRFToken($token);
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
function requireLogin($redirect_to = '../login.php') {
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
function requireUserType($tipo_requerido, $redirect_to = '../index.php') {
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
function requireCliente($redirect_to = '../index.php') {
    requireUserType('cliente', $redirect_to);
}

/**
 * Redireciona parceiro
 * @param string $redirect_to
 */
function requireParceiro($redirect_to = '../login.php') {
    requireUserType('parceiro', $redirect_to);
}

/**
 * Redireciona administrador
 * @param string $redirect_to
 */
function requireAdmin($redirect_to = '../index.php') {
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
 * Configurações de Sessão para Produção
 */
if (session_status() == PHP_SESSION_NONE) {
    ini_set("session.cookie_httponly", 1);
    ini_set("session.cookie_secure", 0); // Para desenvolvimento local
    ini_set("session.use_strict_mode", 1);
    
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
 * Funções auxiliares para mensagens flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Funções de validação
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateTelefone($telefone) {
    // Remove caracteres não numéricos
    $telefone_limpo = preg_replace('/\D/', '', $telefone);
    
    // Verifica se tem 10 ou 11 dígitos (telefone fixo ou celular)
    return strlen($telefone_limpo) >= 10 && strlen($telefone_limpo) <= 11;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

?>