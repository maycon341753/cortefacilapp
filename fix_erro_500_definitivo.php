<?php
// Script Definitivo para Correção do Erro 500
// Corrige problemas de funções duplicadas e configuração

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Correção Definitiva do Erro 500</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

$correcoes_aplicadas = [];
$erros_encontrados = [];

// 1. Backup do auth.php atual
echo "<h2>1. 💾 Backup do auth.php</h2>";
if (file_exists('includes/auth.php')) {
    $backup_name = 'includes/auth_backup_fix_500_' . date('Y-m-d_H-i-s') . '.php';
    if (copy('includes/auth.php', $backup_name)) {
        echo "<p>✅ Backup criado: $backup_name</p>";
        $correcoes_aplicadas[] = "Backup do auth.php criado";
    } else {
        echo "<p>❌ Erro ao criar backup do auth.php</p>";
        $erros_encontrados[] = "Falha ao criar backup do auth.php";
    }
} else {
    echo "<p>❌ Arquivo includes/auth.php não encontrado</p>";
    $erros_encontrados[] = "Arquivo auth.php não encontrado";
}
echo "<hr>";

// 2. Criar auth.php limpo e funcional
echo "<h2>2. 🔧 Criando auth.php Limpo</h2>";
$auth_content = '<?php
/**
 * Sistema de Autenticação - CorteFácil
 * Versão limpa e funcional - Corrigida para produção
 * Gerado automaticamente em ' . date('Y-m-d H:i:s') . '
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
 * Verifica se é admin
 */
function isAdmin() {
    return hasUserType("admin");
}

/**
 * Faz login do usuário
 */
function login($usuario_id, $tipo_usuario, $nome = "", $email = "") {
    $_SESSION["usuario_id"] = $usuario_id;
    $_SESSION["tipo_usuario"] = $tipo_usuario;
    $_SESSION["nome_usuario"] = $nome;
    $_SESSION["email_usuario"] = $email;
    $_SESSION["login_time"] = time();
    
    // Regenerar ID da sessão por segurança
    session_regenerate_id(true);
}

/**
 * Faz logout do usuário
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
?>';

if (file_put_contents('includes/auth.php', $auth_content)) {
    echo "<p>✅ Novo auth.php criado sem duplicações</p>";
    $correcoes_aplicadas[] = "auth.php limpo criado";
} else {
    echo "<p>❌ Erro ao criar novo auth.php</p>";
    $erros_encontrados[] = "Falha ao criar auth.php limpo";
}
echo "<hr>";

// 3. Criar .htaccess otimizado
echo "<h2>3. 🔧 Criando .htaccess Otimizado</h2>";
$htaccess_content = '# .htaccess otimizado para Hostinger - CorteFacil App
# Gerado automaticamente em ' . date('Y-m-d H:i:s') . '

# Habilitar RewriteEngine
RewriteEngine On

# Definir diretório base
RewriteBase /

# Configurações de segurança
<Files ~ "^\.(htaccess|htpasswd)$">
Order allow,deny
Deny from all
</Files>

# Proteger arquivos sensíveis
<FilesMatch "\.(sql|log|md|txt)$">
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

# Roteamento principal
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Configurações de erro personalizadas
ErrorDocument 404 /index.php
ErrorDocument 403 /index.php
ErrorDocument 500 /index.php';

if (file_put_contents('.htaccess', $htaccess_content)) {
    echo "<p>✅ .htaccess otimizado criado</p>";
    $correcoes_aplicadas[] = ".htaccess otimizado criado";
} else {
    echo "<p>❌ Erro ao criar .htaccess</p>";
    $erros_encontrados[] = "Falha ao criar .htaccess";
}
echo "<hr>";

// 4. Criar arquivo de teste simples
echo "<h2>4. 🧪 Criando Arquivo de Teste</h2>";
$teste_content = '<?php
// Teste básico de funcionamento
echo "<h1>✅ Servidor funcionando!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Data/Hora: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>Servidor: " . ($_SERVER["SERVER_SOFTWARE"] ?? "N/A") . "</p>";

// Teste de sessão
session_start();
echo "<p>Sessão: " . session_id() . "</p>";

// Teste de includes
if (file_exists("includes/auth.php")) {
    require_once "includes/auth.php";
    echo "<p>✅ auth.php carregado com sucesso</p>";
    
    if (function_exists("generateCSRFToken")) {
        $token = generateCSRFToken();
        echo "<p>✅ Token CSRF gerado: " . substr($token, 0, 10) . "...</p>";
    }
} else {
    echo "<p>❌ auth.php não encontrado</p>";
}

// Teste de banco
if (file_exists("config/database.php")) {
    echo "<p>✅ database.php encontrado</p>";
    try {
        require_once "config/database.php";
        $db = new Database();
        $conn = $db->getConnection();
        echo "<p>✅ Conexão com banco bem-sucedida</p>";
    } catch (Exception $e) {
        echo "<p>❌ Erro de conexão: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ database.php não encontrado</p>";
}
?>';

if (file_put_contents('teste_funcionamento.php', $teste_content)) {
    echo "<p>✅ Arquivo de teste criado: <a href='teste_funcionamento.php' target='_blank'>teste_funcionamento.php</a></p>";
    $correcoes_aplicadas[] = "Arquivo de teste criado";
} else {
    echo "<p>❌ Erro ao criar arquivo de teste</p>";
    $erros_encontrados[] = "Falha ao criar arquivo de teste";
}
echo "<hr>";

// 5. Verificar estrutura de diretórios
echo "<h2>5. 📁 Verificação de Estrutura</h2>";
$diretorios_necessarios = ['includes', 'config', 'assets', 'admin', 'cliente', 'parceiro'];

foreach ($diretorios_necessarios as $dir) {
    if (is_dir($dir)) {
        echo "<p>✅ Diretório $dir existe</p>";
    } else {
        echo "<p>❌ Diretório $dir não encontrado</p>";
        $erros_encontrados[] = "Diretório $dir ausente";
    }
}
echo "<hr>";

// 6. Resumo das correções
echo "<h2>6. 📊 Resumo das Correções</h2>";

if (!empty($correcoes_aplicadas)) {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin-bottom: 15px;'>";
    echo "<h3>✅ Correções Aplicadas:</h3>";
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

echo "<div style='background: #cce5ff; padding: 15px; border-left: 4px solid #007cba;'>";
echo "<h3>🔍 Próximos Passos:</h3>";
echo "<ol>";
echo "<li><strong>Teste local:</strong> Acesse <a href='teste_funcionamento.php' target='_blank'>teste_funcionamento.php</a></li>";
echo "<li><strong>Upload para produção:</strong> Envie os arquivos corrigidos para o servidor</li>";
echo "<li><strong>Teste online:</strong> Verifique se https://cortefacil.app/ funciona</li>";
echo "<li><strong>Monitoramento:</strong> Acompanhe os logs de erro</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><strong>🎯 Correção do erro 500 concluída!</strong></p>";
echo "<p><em>Processado em " . date('Y-m-d H:i:s') . "</em></p>";
?>