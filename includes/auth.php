<?php
/**
 * Sistema de Autenticação
 * Gerencia sessões e controle de acesso
 */

session_start();

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
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica token CSRF
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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