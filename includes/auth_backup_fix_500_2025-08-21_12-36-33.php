<?php

/**
 * Sistema de Autenticação - CorteFácil
 * Versão corrigida sem duplicatas de funções CSRF
 */

/**
 * FUNÇÕES CSRF CORRIGIDAS
 * Versão definitiva que resolve problemas de token
 */

/**
 * Gera token CSRF de forma consistente
 */
function generateCSRFToken() {
    // Garantir sessão ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = "csrf_token";
    $time_key = "csrf_token_time";
    
    // Verificar se precisa gerar novo token
    $need_new_token = false;
    
    if (!isset($_SESSION[$token_key])) {
        $need_new_token = true;
    } elseif (isset($_SESSION[$time_key]) && (time() - $_SESSION[$time_key]) > 7200) {
        // Token expira em 2 horas
        $need_new_token = true;
        unset($_SESSION[$token_key], $_SESSION[$time_key]);
    } elseif (!isset($_SESSION[$time_key])) {
        $need_new_token = true;
    }
    
    if ($need_new_token) {
        // Gerar token seguro
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
 * Verifica token CSRF de forma robusta
 */
function verifyCSRFToken($token) {
    // Garantir sessão ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = "csrf_token";
    $time_key = "csrf_token_time";
    
    // Normalizar tokens
    $received_token = trim($token);
    $session_token = isset($_SESSION[$token_key]) ? trim($_SESSION[$token_key]) : "";
    
    // Verificações básicas
    if (empty($received_token) || empty($session_token)) {
        return false;
    }
    
    // Verificar expiração
    if (isset($_SESSION[$time_key])) {
        $age = time() - $_SESSION[$time_key];
        if ($age > 7200) { // 2 horas
            unset($_SESSION[$token_key], $_SESSION[$time_key]);
            return false;
        }
    }
    
    // Comparação segura
    if (function_exists("hash_equals")) {
        return hash_equals($session_token, $received_token);
    } else {
        return $session_token === $received_token;
    }
}

/**
 * Gera campo HTML com token CSRF
 */
function generateCsrfToken() {
    $token = generateCSRFToken();
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($token) . "\">";
}

/**
 * Alias para verifyCSRFToken (compatibilidade)
 */
function verifyCsrfToken($token) {
    return verifyCSRFToken($token);
}


/**
 * Configurações de Sessão
 */
if (session_status() == PHP_SESSION_NONE) {
    // Configurar parâmetros de sessão antes de iniciar
    ini_set("session.cookie_httponly", 1);
    ini_set("session.use_only_cookies", 1);
    ini_set("session.cookie_lifetime", 0);
    
    // Configurações específicas para ambiente online
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") {
        ini_set("session.cookie_secure", 1);
    }
    
    // Configurar SameSite para compatibilidade
    if (PHP_VERSION_ID >= 70300) {
        ini_set("session.cookie_samesite", "Lax");
    }
    
    session_start();
    
    // Regenerar ID da sessão periodicamente para segurança
    if (!isset($_SESSION["last_regeneration"])) {
        $_SESSION["last_regeneration"] = time();
    } elseif (time() - $_SESSION["last_regeneration"] > 300) { // 5 minutos
        session_regenerate_id(true);
        $_SESSION["last_regeneration"] = time();
    }
}

/**
 * Verifica se o usuário está logado
 */
function isLoggedIn() {
    return isset($_SESSION["usuario_id"]) && !empty($_SESSION["usuario_id"]);
}

/**
 * Verifica se o usuário tem o tipo específico
 */
function hasUserType($tipo) {
    return isLoggedIn() && $_SESSION["tipo_usuario"] === $tipo;
}

/**
 * Verifica se é cliente
 */
function isCliente() {
    return hasUserType("cliente");
}

/**
 * Verifica se é parceiro
 */
function isParceiro() {
    return hasUserType("parceiro");
}

/**
 * Verifica se é administrador
 */
function isAdmin() {
    return hasUserType("admin");
}

/**
 * Realiza login do usuário
 */
function login($usuario) {
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION["usuario_email"] = $usuario["email"];
    $_SESSION["tipo_usuario"] = $usuario["tipo"];
    
    // Regenerar ID da sessão por segurança
    session_regenerate_id(true);
}

/**
 * Realiza logout do usuário
 */
function logout() {
    // Limpar todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir sessão
    session_destroy();
}

/**
 * Sanitiza entrada do usuário
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, "UTF-8");
}

/**
 * Valida email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redireciona para uma página
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Define mensagem de sucesso
 */
function setSuccessMessage($message) {
    $_SESSION["success_message"] = $message;
}

/**
 * Define mensagem de erro
 */
function setErrorMessage($message) {
    $_SESSION["error_message"] = $message;
}

/**
 * Obtém e limpa mensagem de sucesso
 */
function getSuccessMessage() {
    if (isset($_SESSION["success_message"])) {
        $message = $_SESSION["success_message"];
        unset($_SESSION["success_message"]);
        return $message;
    }
    return null;
}

/**
 * Obtém e limpa mensagem de erro
 */
function getErrorMessage() {
    if (isset($_SESSION["error_message"])) {
        $message = $_SESSION["error_message"];
        unset($_SESSION["error_message"]);
        return $message;
    }
    return null;
}
?>