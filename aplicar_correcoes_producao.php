<?php
// Script para Aplicar Correções no Ambiente de Produção (Hostinger)
// Este arquivo deve ser enviado para o servidor e executado uma única vez

// Configurações de erro para produção
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚀 Aplicando Correções em Produção - Hostinger</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</p>";
echo "<hr>";

$correcoes_aplicadas = [];
$erros_encontrados = [];
$ambiente_producao = !in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($ambiente_producao) {
    echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 15px;'>";
    echo "<strong>⚠️ AMBIENTE DE PRODUÇÃO DETECTADO</strong><br>";
    echo "Servidor: " . $_SERVER['SERVER_NAME'] . "<br>";
    echo "Aplicando correções com cuidado extra...";
    echo "</div>";
} else {
    echo "<div style='background: #d1ecf1; padding: 10px; border-left: 4px solid #bee5eb; margin-bottom: 15px;'>";
    echo "<strong>ℹ️ AMBIENTE LOCAL DETECTADO</strong><br>";
    echo "Testando correções antes do deploy...";
    echo "</div>";
}

// 1. Verificar ambiente e criar backups
echo "<h2>1. 💾 Criando Backups de Segurança</h2>";

// Backup do auth.php
if (file_exists('includes/auth.php')) {
    $backup_auth = 'includes/auth_backup_producao_' . date('Y-m-d_H-i-s') . '.php';
    if (copy('includes/auth.php', $backup_auth)) {
        echo "<p>✅ Backup do auth.php: $backup_auth</p>";
        $correcoes_aplicadas[] = "Backup auth.php criado";
    } else {
        echo "<p>❌ Erro ao criar backup do auth.php</p>";
        $erros_encontrados[] = "Falha no backup auth.php";
    }
}

// Backup do .htaccess
if (file_exists('.htaccess')) {
    $backup_htaccess = '.htaccess_backup_producao_' . date('Y-m-d_H-i-s');
    if (copy('.htaccess', $backup_htaccess)) {
        echo "<p>✅ Backup do .htaccess: $backup_htaccess</p>";
        $correcoes_aplicadas[] = "Backup .htaccess criado";
    } else {
        echo "<p>❌ Erro ao criar backup do .htaccess</p>";
        $erros_encontrados[] = "Falha no backup .htaccess";
    }
}
echo "<hr>";

// 2. Aplicar correção do auth.php
echo "<h2>2. 🔧 Aplicando Correção do auth.php</h2>";

$auth_content_producao = '<?php
/**
 * Sistema de Autenticação - CorteFácil
 * Versão para PRODUÇÃO - Hostinger
 * Aplicado automaticamente em ' . date('Y-m-d H:i:s') . '
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
 * Gera campo HTML com token CSRF
 */
function generateCsrfToken() {
    $token = generateCSRFToken();
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($token) . "\">";
}

/**
 * Alias para compatibilidade
 */
function verifyCsrfToken($token) {
    return verifyCSRFToken($token);
}

/**
 * Configurações de Sessão para Produção
 */
if (session_status() == PHP_SESSION_NONE) {
    ini_set("session.cookie_httponly", 1);
    ini_set("session.use_only_cookies", 1);
    ini_set("session.cookie_lifetime", 0);
    
    // Configurações específicas para HTTPS em produção
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") {
        ini_set("session.cookie_secure", 1);
    }
    
    if (PHP_VERSION_ID >= 70300) {
        ini_set("session.cookie_samesite", "Lax");
    }
    
    session_start();
    
    if (!isset($_SESSION["last_regeneration"])) {
        $_SESSION["last_regeneration"] = time();
    } elseif (time() - $_SESSION["last_regeneration"] > 300) {
        session_regenerate_id(true);
        $_SESSION["last_regeneration"] = time();
    }
}

function isLoggedIn() {
    return isset($_SESSION["usuario_id"]) && !empty($_SESSION["usuario_id"]);
}

function hasUserType($tipo) {
    return isLoggedIn() && $_SESSION["tipo_usuario"] === $tipo;
}

function isCliente() {
    return hasUserType("cliente");
}

function isParceiro() {
    return hasUserType("parceiro");
}

function isAdmin() {
    return hasUserType("admin");
}

function login($usuario_id, $tipo_usuario, $nome = "", $email = "") {
    $_SESSION["usuario_id"] = $usuario_id;
    $_SESSION["tipo_usuario"] = $tipo_usuario;
    $_SESSION["nome_usuario"] = $nome;
    $_SESSION["email_usuario"] = $email;
    $_SESSION["login_time"] = time();
    session_regenerate_id(true);
}

function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, "UTF-8");
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function setSuccessMessage($message) {
    $_SESSION["success_message"] = $message;
}

function setErrorMessage($message) {
    $_SESSION["error_message"] = $message;
}

function getSuccessMessage() {
    if (isset($_SESSION["success_message"])) {
        $message = $_SESSION["success_message"];
        unset($_SESSION["success_message"]);
        return $message;
    }
    return null;
}

function getErrorMessage() {
    if (isset($_SESSION["error_message"])) {
        $message = $_SESSION["error_message"];
        unset($_SESSION["error_message"]);
        return $message;
    }
    return null;
}
?>';

if (file_put_contents('includes/auth.php', $auth_content_producao)) {
    echo "<p>✅ auth.php atualizado para produção</p>";
    $correcoes_aplicadas[] = "auth.php corrigido";
} else {
    echo "<p>❌ Erro ao atualizar auth.php</p>";
    $erros_encontrados[] = "Falha ao corrigir auth.php";
}
echo "<hr>";

// 3. Aplicar .htaccess otimizado para Hostinger
echo "<h2>3. 🔧 Aplicando .htaccess Otimizado</h2>";

$htaccess_producao = '# .htaccess para Produção - Hostinger CorteFacil
# Aplicado automaticamente em ' . date('Y-m-d H:i:s') . '

RewriteEngine On
RewriteBase /

# Segurança
<Files ~ "^\.(htaccess|htpasswd)$">
Order allow,deny
Deny from all
</Files>

<FilesMatch "\.(sql|log|md|txt|backup)$">
Order allow,deny
Deny from all
</FilesMatch>

# Configurações PHP para Hostinger
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 300
    php_value upload_max_filesize 32M
    php_value post_max_size 32M
    php_flag display_errors Off
    php_flag log_errors On
</IfModule>

# Redirecionamento HTTPS (descomente se necessário)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Roteamento principal
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Páginas de erro
ErrorDocument 404 /index.php
ErrorDocument 403 /index.php
ErrorDocument 500 /index.php

# Cache e compressão
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>';

if (file_put_contents('.htaccess', $htaccess_producao)) {
    echo "<p>✅ .htaccess otimizado para Hostinger</p>";
    $correcoes_aplicadas[] = ".htaccess otimizado";
} else {
    echo "<p>❌ Erro ao criar .htaccess</p>";
    $erros_encontrados[] = "Falha no .htaccess";
}
echo "<hr>";

// 4. Teste de funcionamento
echo "<h2>4. 🧪 Teste de Funcionamento</h2>";

// Teste de PHP
echo "<p>✅ PHP Version: " . phpversion() . "</p>";

// Teste de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p>✅ Sessão: " . session_id() . "</p>";

// Teste do auth.php
try {
    if (file_exists('includes/auth.php')) {
        require_once 'includes/auth.php';
        echo "<p>✅ auth.php carregado com sucesso</p>";
        
        if (function_exists('generateCSRFToken')) {
            $token = generateCSRFToken();
            echo "<p>✅ Token CSRF gerado: " . substr($token, 0, 10) . "...</p>";
            $correcoes_aplicadas[] = "Funções CSRF funcionando";
        } else {
            echo "<p>❌ Função generateCSRFToken não encontrada</p>";
            $erros_encontrados[] = "Função CSRF ausente";
        }
    } else {
        echo "<p>❌ auth.php não encontrado</p>";
        $erros_encontrados[] = "auth.php ausente";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar auth.php: " . $e->getMessage() . "</p>";
    $erros_encontrados[] = "Erro no auth.php: " . $e->getMessage();
}

// Teste de banco (se disponível)
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        echo "<p>✅ Conexão com banco bem-sucedida</p>";
        $correcoes_aplicadas[] = "Banco de dados conectado";
    }
} catch (Exception $e) {
    echo "<p>⚠️ Banco de dados: " . $e->getMessage() . "</p>";
    // Não consideramos erro crítico para esta correção
}
echo "<hr>";

// 5. Resumo final
echo "<h2>5. 📊 Resumo da Aplicação</h2>";

if (!empty($correcoes_aplicadas)) {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-bottom: 15px;'>";
    echo "<h3>✅ Correções Aplicadas com Sucesso:</h3>";
    echo "<ul>";
    foreach ($correcoes_aplicadas as $correcao) {
        echo "<li>$correcao</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($erros_encontrados)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin-bottom: 15px;'>";
    echo "<h3>❌ Problemas Encontrados:</h3>";
    echo "<ul>";
    foreach ($erros_encontrados as $erro) {
        echo "<li>$erro</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Status final
if (empty($erros_encontrados)) {
    echo "<div style='background: #d1ecf1; padding: 20px; border-left: 4px solid #0c5460; text-align: center;'>";
    echo "<h2>🎉 CORREÇÕES APLICADAS COM SUCESSO!</h2>";
    echo "<p><strong>O erro 500 deve estar resolvido.</strong></p>";
    echo "<p>Teste agora: <a href='/' target='_blank' style='color: #0c5460; font-weight: bold;'>Página Principal</a></p>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #856404; text-align: center;'>";
    echo "<h2>⚠️ CORREÇÕES APLICADAS COM ALERTAS</h2>";
    echo "<p>Algumas correções foram aplicadas, mas há problemas que precisam de atenção.</p>";
    echo "<p>Verifique os itens marcados em vermelho acima.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #e2e3e5; padding: 15px; border-left: 4px solid #6c757d;'>";
echo "<h3>📋 Próximos Passos:</h3>";
echo "<ol>";
echo "<li><strong>Teste imediato:</strong> Acesse a página principal do site</li>";
echo "<li><strong>Verificar logs:</strong> Monitore os logs de erro no hPanel</li>";
echo "<li><strong>Funcionalidades:</strong> Teste login, cadastro e agendamentos</li>";
echo "<li><strong>Remover este arquivo:</strong> Delete este script após confirmar que tudo funciona</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Correções aplicadas em " . date('Y-m-d H:i:s') . " no servidor " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</em></p>";

// Auto-remoção do script (opcional, descomente se desejar)
// if ($ambiente_producao && empty($erros_encontrados)) {
//     echo "<p><small>🗑️ Este script será removido automaticamente em 5 segundos...</small></p>";
//     echo "<script>setTimeout(function(){ window.location.href = '/'; }, 5000);</script>";
// }
?>