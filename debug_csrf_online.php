<?php
/**
 * Diagnóstico CSRF para versão online
 * Verifica problemas de token de segurança
 */

// Configurações de debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico CSRF - Versão Online</h1>";
echo "<hr>";

// 1. Verificar configurações de sessão
echo "<h2>1. Configurações de Sessão</h2>";
echo "<ul>";
echo "<li><strong>Session Status:</strong> " . session_status() . " (1=disabled, 2=enabled, 3=none)</li>";
echo "<li><strong>Session ID:</strong> " . (session_id() ?: 'Não iniciada') . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Session Save Path:</strong> " . session_save_path() . "</li>";
echo "<li><strong>Session Cookie Lifetime:</strong> " . ini_get('session.cookie_lifetime') . "</li>";
echo "<li><strong>Session Cookie Domain:</strong> " . ini_get('session.cookie_domain') . "</li>";
echo "<li><strong>Session Cookie Secure:</strong> " . ini_get('session.cookie_secure') . "</li>";
echo "<li><strong>Session Cookie HTTPOnly:</strong> " . ini_get('session.cookie_httponly') . "</li>";
echo "<li><strong>Session Cookie SameSite:</strong> " . ini_get('session.cookie_samesite') . "</li>";
echo "</ul>";

// 2. Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    echo "<p><strong>Iniciando sessão...</strong></p>";
    session_start();
    echo "<p>✓ Sessão iniciada com ID: " . session_id() . "</p>";
} else {
    echo "<p>✓ Sessão já estava ativa</p>";
}

// 3. Verificar se auth.php existe e pode ser incluído
echo "<h2>2. Verificação do arquivo auth.php</h2>";
$auth_path = 'includes/auth.php';
if (file_exists($auth_path)) {
    echo "<p>✓ Arquivo auth.php encontrado</p>";
    try {
        require_once $auth_path;
        echo "<p>✓ Arquivo auth.php incluído com sucesso</p>";
    } catch (Exception $e) {
        echo "<p>✗ Erro ao incluir auth.php: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Arquivo auth.php não encontrado em: $auth_path</p>";
    // Tentar caminho alternativo
    $auth_path_alt = '../includes/auth.php';
    if (file_exists($auth_path_alt)) {
        echo "<p>✓ Arquivo auth.php encontrado em: $auth_path_alt</p>";
        require_once $auth_path_alt;
    }
}

// 4. Testar funções CSRF
echo "<h2>3. Teste das Funções CSRF</h2>";

if (function_exists('generateCSRFToken')) {
    echo "<p>✓ Função generateCSRFToken existe</p>";
    try {
        $token = generateCSRFToken();
        echo "<p>✓ Token gerado: " . htmlspecialchars(substr($token, 0, 20)) . "...</p>";
        echo "<p>✓ Token na sessão: " . htmlspecialchars(substr($_SESSION['csrf_token'] ?? 'não definido', 0, 20)) . "...</p>";
    } catch (Exception $e) {
        echo "<p>✗ Erro ao gerar token: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Função generateCSRFToken não existe</p>";
}

if (function_exists('verifyCSRFToken')) {
    echo "<p>✓ Função verifyCSRFToken existe</p>";
    if (isset($token)) {
        $valido = verifyCSRFToken($token);
        echo "<p>" . ($valido ? '✓' : '✗') . " Verificação do token: " . ($valido ? 'VÁLIDO' : 'INVÁLIDO') . "</p>";
    }
} else {
    echo "<p>✗ Função verifyCSRFToken não existe</p>";
}

if (function_exists('generateCsrfToken')) {
    echo "<p>✓ Função generateCsrfToken (alias) existe</p>";
    try {
        $html_token = generateCsrfToken();
        echo "<p>✓ HTML gerado: " . htmlspecialchars($html_token) . "</p>";
    } catch (Exception $e) {
        echo "<p>✗ Erro ao gerar HTML do token: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>✗ Função generateCsrfToken (alias) não existe</p>";
}

if (function_exists('verifyCsrfToken')) {
    echo "<p>✓ Função verifyCsrfToken (alias) existe</p>";
} else {
    echo "<p>✗ Função verifyCsrfToken (alias) não existe</p>";
}

// 5. Verificar variáveis de ambiente
echo "<h2>4. Variáveis de Ambiente</h2>";
echo "<ul>";
echo "<li><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "</li>";
echo "<li><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'não definido') . "</li>";
echo "<li><strong>REQUEST_SCHEME:</strong> " . ($_SERVER['REQUEST_SCHEME'] ?? 'não definido') . "</li>";
echo "<li><strong>HTTPS:</strong> " . ($_SERVER['HTTPS'] ?? 'não definido') . "</li>";
echo "<li><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'não definido') . "</li>";
echo "<li><strong>HTTP_REFERER:</strong> " . ($_SERVER['HTTP_REFERER'] ?? 'não definido') . "</li>";
echo "<li><strong>HTTP_USER_AGENT:</strong> " . htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'não definido', 0, 100)) . "...</li>";
echo "</ul>";

// 6. Verificar cookies
echo "<h2>5. Cookies</h2>";
if (!empty($_COOKIE)) {
    echo "<ul>";
    foreach ($_COOKIE as $name => $value) {
        echo "<li><strong>$name:</strong> " . htmlspecialchars(substr($value, 0, 50)) . "...</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nenhum cookie encontrado</p>";
}

// 7. Teste de formulário
echo "<h2>6. Teste de Formulário</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff;'>";
    echo "<h4>Resultado do POST:</h4>";
    
    $csrf_recebido = $_POST['csrf_token'] ?? '';
    echo "<p><strong>Token recebido:</strong> " . htmlspecialchars(substr($csrf_recebido, 0, 20)) . "...</p>";
    echo "<p><strong>Token na sessão:</strong> " . htmlspecialchars(substr($_SESSION['csrf_token'] ?? 'não definido', 0, 20)) . "...</p>";
    
    if (function_exists('verifyCsrfToken')) {
        $resultado = verifyCsrfToken($csrf_recebido);
        if ($resultado) {
            echo "<p style='color: green; font-weight: bold;'>✓ TOKEN CSRF VÁLIDO!</p>";
            echo "<p>✓ Formulário processado com sucesso!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ TOKEN CSRF INVÁLIDO!</p>";
            echo "<p>Possíveis causas:</p>";
            echo "<ul>";
            echo "<li>Sessão expirou</li>";
            echo "<li>Token não foi gerado corretamente</li>";
            echo "<li>Problema de configuração de cookies</li>";
            echo "<li>Diferença entre domínios (local vs online)</li>";
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>✗ Função verifyCsrfToken não disponível</p>";
    }
    
    echo "<p><strong>Dados recebidos:</strong></p>";
    echo "<pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre>";
    echo "</div>";
}

echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6;'>";
echo "<h4>Formulário de Teste</h4>";
if (function_exists('generateCsrfToken')) {
    echo generateCsrfToken();
} else {
    echo "<p style='color: red;'>Função generateCsrfToken não disponível</p>";
}
echo "<div style='margin: 10px 0;'>";
echo "<label>Campo de teste:</label><br>";
echo "<input type='text' name='teste' placeholder='Digite algo para testar' style='padding: 8px; width: 300px;'>";
echo "</div>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Testar CSRF</button>";
echo "</form>";

// 8. Informações do PHP
echo "<h2>7. Informações do PHP</h2>";
echo "<ul>";
echo "<li><strong>Versão PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Sistema Operacional:</strong> " . PHP_OS . "</li>";
echo "<li><strong>Timezone:</strong> " . date_default_timezone_get() . "</li>";
echo "<li><strong>Data/Hora atual:</strong> " . date('Y-m-d H:i:s') . "</li>";
echo "</ul>";

// 9. Verificar permissões de diretório de sessão
echo "<h2>8. Verificação de Permissões</h2>";
$session_path = session_save_path();
if ($session_path && is_dir($session_path)) {
    echo "<p>✓ Diretório de sessão existe: $session_path</p>";
    if (is_writable($session_path)) {
        echo "<p>✓ Diretório de sessão é gravável</p>";
    } else {
        echo "<p style='color: red;'>✗ Diretório de sessão NÃO é gravável</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ Diretório de sessão não especificado ou não existe</p>";
}

echo "<hr>";
echo "<p><strong>Diagnóstico concluído em:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='parceiro/salao.php'>← Voltar para página do salão</a></p>";
?>