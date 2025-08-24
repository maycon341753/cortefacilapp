<?php
/**
 * Diagnóstico específico para problema de CSRF no ambiente de produção
 * Testa configurações de sessão, cookies e geração de tokens
 */

// Configurações de debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão com configurações específicas para produção
session_start();

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Debug CSRF - Produção</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo ".success { background: #d4edda; border-color: #c3e6cb; color: #155724; }";
echo ".error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }";
echo ".warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }";
echo ".info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }";
echo "pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background-color: #f2f2f2; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔍 Diagnóstico CSRF - Ambiente de Produção</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";

// 1. Verificar configurações de sessão
echo "<div class='section info'>";
echo "<h2>📋 1. Configurações de Sessão</h2>";
echo "<table>";
echo "<tr><th>Configuração</th><th>Valor</th><th>Status</th></tr>";

$sessionConfigs = [
    'session.name' => session_name(),
    'session.id' => session_id(),
    'session.save_path' => session_save_path(),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_secure' => ini_get('session.cookie_secure'),
    'session.cookie_httponly' => ini_get('session.cookie_httponly'),
    'session.cookie_samesite' => ini_get('session.cookie_samesite'),
    'session.use_cookies' => ini_get('session.use_cookies'),
    'session.use_only_cookies' => ini_get('session.use_only_cookies'),
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime')
];

foreach ($sessionConfigs as $config => $value) {
    $status = 'info';
    $statusText = 'OK';
    
    // Verificar configurações problemáticas
    if ($config === 'session.cookie_secure' && $value && !isset($_SERVER['HTTPS'])) {
        $status = 'error';
        $statusText = 'PROBLEMA: Cookie seguro sem HTTPS';
    } elseif ($config === 'session.cookie_domain' && !empty($value) && strpos($_SERVER['HTTP_HOST'], $value) === false) {
        $status = 'warning';
        $statusText = 'ATENÇÃO: Domínio pode não coincidir';
    } elseif ($config === 'session.gc_maxlifetime' && $value < 1800) {
        $status = 'warning';
        $statusText = 'ATENÇÃO: Tempo de vida muito baixo';
    }
    
    echo "<tr class='$status'>";
    echo "<td>$config</td>";
    echo "<td>" . ($value ?: 'vazio') . "</td>";
    echo "<td>$statusText</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 2. Verificar cookies
echo "<div class='section info'>";
echo "<h2>🍪 2. Cookies Recebidos</h2>";
if (!empty($_COOKIE)) {
    echo "<table>";
    echo "<tr><th>Nome</th><th>Valor</th><th>Tamanho</th></tr>";
    foreach ($_COOKIE as $name => $value) {
        echo "<tr>";
        echo "<td>$name</td>";
        echo "<td>" . (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value) . "</td>";
        echo "<td>" . strlen($value) . " bytes</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ Nenhum cookie encontrado!</div>";
}
echo "</div>";

// 3. Verificar cabeçalhos HTTP
echo "<div class='section info'>";
echo "<h2>📡 3. Cabeçalhos HTTP Relevantes</h2>";
echo "<table>";
echo "<tr><th>Cabeçalho</th><th>Valor</th></tr>";

$relevantHeaders = [
    'HTTP_HOST',
    'HTTP_USER_AGENT',
    'HTTP_REFERER',
    'HTTP_ORIGIN',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_REAL_IP',
    'HTTPS',
    'SERVER_PORT',
    'REQUEST_SCHEME'
];

foreach ($relevantHeaders as $header) {
    $value = $_SERVER[$header] ?? 'não definido';
    echo "<tr>";
    echo "<td>$header</td>";
    echo "<td>$value</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

// 4. Testar geração de token CSRF
echo "<div class='section'>";
echo "<h2>🔐 4. Teste de Geração de Token CSRF</h2>";

try {
    // Incluir arquivos necessários
    $authPath = __DIR__ . '/includes/auth.php';
    if (file_exists($authPath)) {
        require_once $authPath;
        echo "<div class='success'>✅ Arquivo auth.php carregado com sucesso</div>";
        
        // Testar geração de token
        if (function_exists('generateCSRFToken')) {
            $token1 = generateCSRFToken();
            echo "<div class='success'>✅ Token 1 gerado: " . substr($token1, 0, 20) . "...</div>";
            
            // Aguardar um pouco e gerar outro token
            usleep(100000); // 0.1 segundo
            $token2 = generateCSRFToken();
            echo "<div class='info'>ℹ️ Token 2 gerado: " . substr($token2, 0, 20) . "...</div>";
            
            if ($token1 === $token2) {
                echo "<div class='success'>✅ Tokens são iguais (reutilização correta)</div>";
            } else {
                echo "<div class='warning'>⚠️ Tokens são diferentes (pode indicar problema)</div>";
            }
            
            // Testar validação
            if (function_exists('verifyCsrfToken')) {
                $isValid = verifyCsrfToken($token1);
                if ($isValid) {
                    echo "<div class='success'>✅ Validação do token funcionando</div>";
                } else {
                    echo "<div class='error'>❌ Falha na validação do token</div>";
                }
            } else {
                echo "<div class='error'>❌ Função verifyCsrfToken não encontrada</div>";
            }
            
        } else {
            echo "<div class='error'>❌ Função generateCSRFToken não encontrada</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Arquivo auth.php não encontrado em: $authPath</div>";
        
        // Tentar implementação básica de CSRF
        echo "<div class='warning'>⚠️ Implementando CSRF básico para teste...</div>";
        
        if (!isset($_SESSION['csrf_token_test'])) {
            $_SESSION['csrf_token_test'] = bin2hex(random_bytes(32));
        }
        
        $testToken = $_SESSION['csrf_token_test'];
        echo "<div class='info'>ℹ️ Token de teste: " . substr($testToken, 0, 20) . "...</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 5. Verificar dados da sessão
echo "<div class='section info'>";
echo "<h2>💾 5. Dados da Sessão</h2>";
if (!empty($_SESSION)) {
    echo "<table>";
    echo "<tr><th>Chave</th><th>Valor</th><th>Tipo</th></tr>";
    foreach ($_SESSION as $key => $value) {
        $displayValue = is_string($value) ? 
            (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) : 
            print_r($value, true);
        
        echo "<tr>";
        echo "<td>$key</td>";
        echo "<td><pre>" . htmlspecialchars($displayValue) . "</pre></td>";
        echo "<td>" . gettype($value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='warning'>⚠️ Sessão vazia</div>";
}
echo "</div>";

// 6. Teste de formulário com CSRF
echo "<div class='section'>";
echo "<h2>📝 6. Teste de Formulário com CSRF</h2>";

// Processar POST se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div class='info'>📨 Dados POST recebidos:</div>";
    echo "<pre>" . htmlspecialchars(print_r($_POST, true)) . "</pre>";
    
    $tokenRecebido = $_POST['csrf_token'] ?? '';
    $tokenSessao = $_SESSION['csrf_token'] ?? $_SESSION['csrf_token_test'] ?? '';
    
    echo "<div class='info'>";
    echo "<p><strong>Token recebido:</strong> " . substr($tokenRecebido, 0, 30) . "...</p>";
    echo "<p><strong>Token da sessão:</strong> " . substr($tokenSessao, 0, 30) . "...</p>";
    echo "</div>";
    
    if (empty($tokenRecebido)) {
        echo "<div class='error'>❌ Token não encontrado no POST</div>";
    } elseif (empty($tokenSessao)) {
        echo "<div class='error'>❌ Token não encontrado na sessão</div>";
    } elseif ($tokenRecebido === $tokenSessao) {
        echo "<div class='success'>✅ Tokens coincidem! CSRF funcionando</div>";
    } else {
        echo "<div class='error'>❌ Tokens não coincidem</div>";
        echo "<div class='info'>Comparação detalhada:</div>";
        echo "<pre>Recebido: $tokenRecebido</pre>";
        echo "<pre>Sessão:   $tokenSessao</pre>";
    }
}

// Gerar token para o formulário
$formToken = $_SESSION['csrf_token'] ?? $_SESSION['csrf_token_test'] ?? bin2hex(random_bytes(32));
if (!isset($_SESSION['csrf_token']) && !isset($_SESSION['csrf_token_test'])) {
    $_SESSION['csrf_token_test'] = $formToken;
}

echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";
echo "<input type='hidden' name='csrf_token' value='$formToken'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Nome de teste:</label><br>";
echo "<input type='text' name='nome_teste' value='Teste CSRF' style='width: 300px; padding: 5px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Token CSRF (visível para debug):</label><br>";
echo "<input type='text' value='" . substr($formToken, 0, 50) . "...' readonly style='width: 400px; padding: 5px; background: #e9ecef;'>";
echo "</div>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;'>Testar CSRF</button>";
echo "</form>";
echo "</div>";

// 7. Recomendações
echo "<div class='section warning'>";
echo "<h2>💡 7. Recomendações para Produção</h2>";
echo "<ul>";
echo "<li><strong>Configuração de Sessão:</strong> Verifique se as configurações de cookie estão adequadas para HTTPS</li>";
echo "<li><strong>Domínio:</strong> Certifique-se de que o domínio do cookie está correto</li>";
echo "<li><strong>HTTPS:</strong> Use sempre HTTPS em produção com cookies seguros</li>";
echo "<li><strong>Tempo de Vida:</strong> Configure um tempo de vida adequado para as sessões</li>";
echo "<li><strong>Logs:</strong> Monitore os logs de erro para identificar problemas</li>";
echo "<li><strong>Cache:</strong> Desabilite cache para páginas com formulários CSRF</li>";
echo "</ul>";
echo "</div>";

// 8. Informações do ambiente
echo "<div class='section info'>";
echo "<h2>🖥️ 8. Informações do Ambiente</h2>";
echo "<table>";
echo "<tr><th>Informação</th><th>Valor</th></tr>";
echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Script Name</td><td>" . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Request URI</td><td>" . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Request Method</td><td>" . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Remote Address</td><td>" . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</td></tr>";
echo "</table>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>