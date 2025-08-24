<?php
/**
 * Teste específico para CSRF na página de profissionais
 * Identifica problemas com token CSRF
 */

// Configurações de erro
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Teste CSRF Profissionais</h1>";
echo "<hr>";

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>1. 📁 Verificação de Arquivos</h2>";

try {
    require_once 'includes/auth.php';
    echo "<p>✅ auth.php carregado</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar auth.php: " . $e->getMessage() . "</p>";
    exit;
}

echo "<hr><h2>2. 🔧 Teste de Funções CSRF</h2>";

if (function_exists('generateCSRFToken')) {
    echo "<p>✅ Função generateCSRFToken existe</p>";
} else {
    echo "<p>❌ Função generateCSRFToken NÃO existe</p>";
}

if (function_exists('generateCsrfToken')) {
    echo "<p>✅ Função generateCsrfToken existe</p>";
} else {
    echo "<p>❌ Função generateCsrfToken NÃO existe</p>";
}

if (function_exists('verifyCSRFToken')) {
    echo "<p>✅ Função verifyCSRFToken existe</p>";
} else {
    echo "<p>❌ Função verifyCSRFToken NÃO existe</p>";
}

if (function_exists('verifyCsrfToken')) {
    echo "<p>✅ Função verifyCsrfToken existe</p>";
} else {
    echo "<p>❌ Função verifyCsrfToken NÃO existe</p>";
}

echo "<hr><h2>3. 🎫 Geração de Token</h2>";

try {
    // Testar generateCSRFToken
    if (function_exists('generateCSRFToken')) {
        $token1 = generateCSRFToken();
        echo "<p>✅ generateCSRFToken() executado</p>";
        echo "<p><strong>Token gerado:</strong> " . substr($token1, 0, 20) . "...</p>";
        echo "<p><strong>Tamanho:</strong> " . strlen($token1) . " caracteres</p>";
    }
    
    // Testar generateCsrfToken (HTML)
    if (function_exists('generateCsrfToken')) {
        $html_token = generateCsrfToken();
        echo "<p>✅ generateCsrfToken() executado</p>";
        echo "<p><strong>HTML gerado:</strong></p>";
        echo "<pre>" . htmlspecialchars($html_token) . "</pre>";
        echo "<p><strong>Renderizado:</strong></p>";
        echo $html_token;
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao gerar token: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>4. 🔍 Verificação de Sessão</h2>";

echo "<p><strong>Status da sessão:</strong> " . session_status() . "</p>";
echo "<p><strong>ID da sessão:</strong> " . session_id() . "</p>";

if (isset($_SESSION['csrf_token'])) {
    echo "<p>✅ Token CSRF existe na sessão</p>";
    echo "<p><strong>Token na sessão:</strong> " . substr($_SESSION['csrf_token'], 0, 20) . "...</p>";
    
    if (isset($_SESSION['csrf_token_time'])) {
        $idade = time() - $_SESSION['csrf_token_time'];
        echo "<p><strong>Idade do token:</strong> " . $idade . " segundos</p>";
        
        if ($idade > 7200) {
            echo "<p>⚠️ Token expirado (mais de 2 horas)</p>";
        } else {
            echo "<p>✅ Token válido</p>";
        }
    } else {
        echo "<p>⚠️ Timestamp do token não encontrado</p>";
    }
} else {
    echo "<p>❌ Token CSRF NÃO existe na sessão</p>";
}

echo "<hr><h2>5. 🧪 Teste de Validação</h2>";

if (isset($_SESSION['csrf_token']) && function_exists('verifyCSRFToken')) {
    $token_sessao = $_SESSION['csrf_token'];
    
    // Teste 1: Token correto
    $resultado1 = verifyCSRFToken($token_sessao);
    echo "<p><strong>Teste 1 (token correto):</strong> " . ($resultado1 ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "</p>";
    
    // Teste 2: Token incorreto
    $resultado2 = verifyCSRFToken('token_falso');
    echo "<p><strong>Teste 2 (token incorreto):</strong> " . ($resultado2 ? "❌ VÁLIDO (ERRO!)" : "✅ INVÁLIDO (correto)") . "</p>";
    
    // Teste 3: Token vazio
    $resultado3 = verifyCSRFToken('');
    echo "<p><strong>Teste 3 (token vazio):</strong> " . ($resultado3 ? "❌ VÁLIDO (ERRO!)" : "✅ INVÁLIDO (correto)") . "</p>";
}

echo "<hr><h2>6. 📝 Formulário de Teste</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>📨 Dados Recebidos via POST:</h4>";
    
    $token_recebido = $_POST['csrf_token'] ?? '';
    echo "<p><strong>Token recebido:</strong> " . ($token_recebido ? substr($token_recebido, 0, 20) . "..." : "VAZIO") . "</p>";
    
    if (function_exists('verifyCSRFToken')) {
        $valido = verifyCSRFToken($token_recebido);
        echo "<p><strong>Validação:</strong> " . ($valido ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "</p>";
        
        if (!$valido) {
            echo "<p><strong>Motivo possível:</strong></p>";
            if (empty($token_recebido)) {
                echo "<p>- Token não foi enviado</p>";
            } elseif (!isset($_SESSION['csrf_token'])) {
                echo "<p>- Token não existe na sessão</p>";
            } elseif (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > 7200)) {
                echo "<p>- Token expirado</p>";
            } else {
                echo "<p>- Token não confere com o da sessão</p>";
            }
        }
    }
    echo "</div>";
}

echo "<form method='POST' style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo "<h4>🧪 Teste de Envio:</h4>";
if (function_exists('generateCsrfToken')) {
    echo generateCsrfToken();
}
echo "<input type='text' name='teste' value='dados_teste' placeholder='Campo de teste'>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 3px; margin-left: 10px;'>Enviar Teste</button>";
echo "</form>";

echo "<hr><h2>7. 🔗 Links Úteis</h2>";
echo "<p><a href='parceiro/profissionais.php'>📊 Página de Profissionais</a></p>";
echo "<p><a href='login.php'>🔑 Login</a></p>";
echo "<p><a href='index.php'>🏠 Página Inicial</a></p>";

echo "<hr><p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
?>