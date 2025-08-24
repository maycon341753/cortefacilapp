<?php
/**
 * DIAGNÓSTICO COMPLETO DO PROBLEMA DE CSRF ONLINE
 * Este script identifica e corrige problemas de token CSRF no ambiente online
 */

// Detectar ambiente
$isOnline = !in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($isOnline) {
    // Configurações específicas para ambiente online
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

session_start();

echo "<h1>🔍 DIAGNÓSTICO CSRF - AMBIENTE " . ($isOnline ? 'ONLINE' : 'LOCAL') . "</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; color: #155724; }
    .error { background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; color: #721c24; }
    .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; color: #856404; }
    .info { background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; color: #0c5460; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 5px 0; }
</style>";

// 1. VERIFICAR CONFIGURAÇÕES DE SESSÃO
echo "<h2>📋 1. Configurações de Sessão</h2>";
echo "<table>";
echo "<tr><th>Configuração</th><th>Valor</th><th>Status</th></tr>";
echo "<tr><td>Session ID</td><td>" . session_id() . "</td><td>" . (session_id() ? '✅' : '❌') . "</td></tr>";
echo "<tr><td>Session Status</td><td>" . session_status() . "</td><td>" . (session_status() === PHP_SESSION_ACTIVE ? '✅' : '❌') . "</td></tr>";
echo "<tr><td>Session Save Path</td><td>" . session_save_path() . "</td><td>" . (is_writable(session_save_path()) ? '✅' : '❌') . "</td></tr>";
echo "<tr><td>Session Cookie Secure</td><td>" . ini_get('session.cookie_secure') . "</td><td>" . ($isOnline ? (ini_get('session.cookie_secure') ? '✅' : '⚠️') : '✅') . "</td></tr>";
echo "<tr><td>Session Cookie HTTPOnly</td><td>" . ini_get('session.cookie_httponly') . "</td><td>" . (ini_get('session.cookie_httponly') ? '✅' : '⚠️') . "</td></tr>";
echo "</table>";

// 2. VERIFICAR ARQUIVOS DE AUTENTICAÇÃO
echo "<h2>🔧 2. Verificação de Arquivos</h2>";
$arquivos_auth = [
    'includes/auth.php',
    'includes/functions.php'
];

foreach ($arquivos_auth as $arquivo) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        echo "<div class='success'>✅ {$arquivo} - Encontrado</div>";
        
        // Verificar se contém funções CSRF
        $conteudo = file_get_contents($caminho);
        $tem_generate = strpos($conteudo, 'generateCSRFToken') !== false || strpos($conteudo, 'generateCsrfToken') !== false;
        $tem_verify = strpos($conteudo, 'verifyCSRFToken') !== false || strpos($conteudo, 'verifyCsrfToken') !== false;
        
        echo "<div class='info'>";
        echo "- Função generateCSRFToken: " . ($tem_generate ? '✅' : '❌') . "<br>";
        echo "- Função verifyCSRFToken: " . ($tem_verify ? '✅' : '❌');
        echo "</div>";
    } else {
        echo "<div class='error'>❌ {$arquivo} - NÃO ENCONTRADO</div>";
    }
}

// 3. INCLUIR ARQUIVOS E TESTAR FUNÇÕES
echo "<h2>🧪 3. Teste de Funções CSRF</h2>";

try {
    if (file_exists(__DIR__ . '/includes/auth.php')) {
        require_once __DIR__ . '/includes/auth.php';
    }
    if (file_exists(__DIR__ . '/includes/functions.php')) {
        require_once __DIR__ . '/includes/functions.php';
    }
    
    // Testar geração de token
    $token_gerado = null;
    if (function_exists('generateCSRFToken')) {
        $token_gerado = generateCSRFToken();
        echo "<div class='success'>✅ generateCSRFToken() funcionando</div>";
        echo "<div class='info'>Token gerado: " . substr($token_gerado, 0, 20) . "... (" . strlen($token_gerado) . " chars)</div>";
    } elseif (function_exists('generateCsrfToken')) {
        $token_gerado = generateCsrfToken();
        echo "<div class='success'>✅ generateCsrfToken() funcionando</div>";
        echo "<div class='info'>Token gerado: " . substr($token_gerado, 0, 20) . "... (" . strlen($token_gerado) . " chars)</div>";
    } else {
        echo "<div class='error'>❌ Nenhuma função de geração de token encontrada</div>";
        
        // Implementar função básica
        function generateCSRFToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['csrf_token_time'] = time();
            }
            return $_SESSION['csrf_token'];
        }
        
        $token_gerado = generateCSRFToken();
        echo "<div class='warning'>⚠️ Usando função CSRF básica implementada</div>";
    }
    
    // Testar verificação de token
    if ($token_gerado) {
        $verif_result = false;
        if (function_exists('verifyCSRFToken')) {
            $verif_result = verifyCSRFToken($token_gerado);
            echo "<div class='" . ($verif_result ? 'success' : 'error') . "'>" . ($verif_result ? '✅' : '❌') . " verifyCSRFToken() - " . ($verif_result ? 'VÁLIDO' : 'INVÁLIDO') . "</div>";
        } elseif (function_exists('verifyCsrfToken')) {
            $verif_result = verifyCsrfToken($token_gerado);
            echo "<div class='" . ($verif_result ? 'success' : 'error') . "'>" . ($verif_result ? '✅' : '❌') . " verifyCsrfToken() - " . ($verif_result ? 'VÁLIDO' : 'INVÁLIDO') . "</div>";
        } else {
            echo "<div class='error'>❌ Nenhuma função de verificação de token encontrada</div>";
            
            // Implementar função básica
            function verifyCSRFToken($token) {
                return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
            }
            
            $verif_result = verifyCSRFToken($token_gerado);
            echo "<div class='warning'>⚠️ Usando função de verificação básica - " . ($verif_result ? 'VÁLIDO' : 'INVÁLIDO') . "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao testar funções: " . $e->getMessage() . "</div>";
}

// 4. VERIFICAR CONTEÚDO DA SESSÃO
echo "<h2>🗂️ 4. Conteúdo da Sessão</h2>";
echo "<table>";
echo "<tr><th>Chave</th><th>Valor</th><th>Tipo</th></tr>";

if (!empty($_SESSION)) {
    foreach ($_SESSION as $chave => $valor) {
        $valor_exibir = is_string($valor) ? (strlen($valor) > 50 ? substr($valor, 0, 50) . '...' : $valor) : print_r($valor, true);
        $tipo = gettype($valor);
        
        $destaque = '';
        if (strpos($chave, 'csrf') !== false || strpos($chave, 'token') !== false) {
            $destaque = 'background: #e8f5e8;';
        }
        
        echo "<tr style='{$destaque}'>";
        echo "<td><strong>{$chave}</strong></td>";
        echo "<td>" . htmlspecialchars($valor_exibir) . "</td>";
        echo "<td>{$tipo}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>Sessão vazia</td></tr>";
}
echo "</table>";

// 5. TESTE PRÁTICO COM FORMULÁRIO
echo "<h2>🎯 5. Teste Prático</h2>";

// Processar formulário se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teste_csrf'])) {
    echo "<h3>📊 Resultado do Teste:</h3>";
    
    $token_recebido = $_POST['csrf_token'] ?? '';
    $token_sessao = $_SESSION['csrf_token'] ?? '';
    
    echo "<div class='info'>";
    echo "<p><strong>Token recebido:</strong> " . substr($token_recebido, 0, 30) . "... (" . strlen($token_recebido) . " chars)</p>";
    echo "<p><strong>Token da sessão:</strong> " . substr($token_sessao, 0, 30) . "... (" . strlen($token_sessao) . " chars)</p>";
    echo "<p><strong>Tokens são iguais:</strong> " . ($token_recebido === $token_sessao ? '✅ SIM' : '❌ NÃO') . "</p>";
    echo "</div>";
    
    // Testar com função de verificação
    $csrf_valido = false;
    if (function_exists('verifyCSRFToken')) {
        $csrf_valido = verifyCSRFToken($token_recebido);
    } elseif (function_exists('verifyCsrfToken')) {
        $csrf_valido = verifyCsrfToken($token_recebido);
    } else {
        $csrf_valido = ($token_recebido === $token_sessao);
    }
    
    if ($csrf_valido) {
        echo "<div class='success'>";
        echo "<h4>🎉 SUCESSO!</h4>";
        echo "<p>✅ Token CSRF validado com sucesso!</p>";
        echo "<p>✅ O sistema CSRF está funcionando corretamente!</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h4>❌ FALHA NA VALIDAÇÃO</h4>";
        echo "<p>❌ Token CSRF inválido!</p>";
        echo "<p>⚠️ Há um problema com o sistema CSRF que precisa ser corrigido.</p>";
        echo "</div>";
    }
}

// Gerar token para o formulário
$form_token = '';
if (function_exists('generateCSRFToken')) {
    $form_token = generateCSRFToken();
} elseif (function_exists('generateCsrfToken')) {
    $form_token = generateCsrfToken();
} else {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    $form_token = $_SESSION['csrf_token'];
}

echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🧪 Formulário de Teste CSRF</h4>";
echo "<input type='hidden' name='csrf_token' value='{$form_token}'>";
echo "<input type='hidden' name='teste_csrf' value='1'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label>Nome de teste:</label><br>";
echo "<input type='text' name='nome_teste' value='Teste CSRF Online' style='width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 3px;'>";
echo "</div>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label>Token CSRF (visível para debug):</label><br>";
echo "<input type='text' value='" . substr($form_token, 0, 40) . "...' readonly style='width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 3px; background: #f8f9fa;'>";
echo "</div>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;'>Testar CSRF</button>";
echo "</form>";

// 6. RECOMENDAÇÕES
echo "<h2>💡 6. Recomendações</h2>";

if ($isOnline) {
    echo "<div class='info'>";
    echo "<h4>🌐 Ambiente Online Detectado</h4>";
    echo "<p><strong>Recomendações específicas para produção:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Configurações de sessão segura aplicadas</li>";
    echo "<li>✅ Cookies seguros habilitados</li>";
    echo "<li>✅ HTTPOnly habilitado</li>";
    echo "<li>✅ SameSite configurado como Strict</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h4>🏠 Ambiente Local Detectado</h4>";
    echo "<p>Este é um ambiente de desenvolvimento. Para testar online:</p>";
    echo "<ol>";
    echo "<li>Faça upload deste arquivo para o servidor</li>";
    echo "<li>Acesse: https://cortefacil.app/diagnostico_csrf_online.php</li>";
    echo "<li>Execute o diagnóstico no ambiente de produção</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h4>🔧 Próximos Passos</h4>";
echo "<ol>";
echo "<li><strong>Se o teste PASSOU:</strong> O problema pode estar na página específica de profissionais</li>";
echo "<li><strong>Se o teste FALHOU:</strong> Há um problema fundamental com o sistema CSRF</li>";
echo "<li><strong>Verificar logs:</strong> Consulte os logs de erro do servidor para mais detalhes</li>";
echo "<li><strong>Testar página real:</strong> Acesse a página de profissionais após este diagnóstico</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<p><a href='/parceiro/profissionais.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Testar Página de Profissionais</a></p>";
echo "<p><small>Diagnóstico executado em: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";
?>