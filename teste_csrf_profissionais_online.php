<?php
/**
 * TESTE ESPECÍFICO PARA PROBLEMA DE CSRF NA PÁGINA DE PROFISSIONAIS ONLINE
 * Este script simula exatamente o que acontece na página de profissionais
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

echo "<h1>🔍 TESTE CSRF - PÁGINA DE PROFISSIONAIS (" . ($isOnline ? 'ONLINE' : 'LOCAL') . ")</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; color: #155724; }
    .error { background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; color: #721c24; }
    .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; color: #856404; }
    .info { background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; color: #0c5460; }
    .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 5px 0; }
    form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
    input, select, textarea { width: 100%; max-width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 3px; }
    button { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 3px; cursor: pointer; margin: 10px 5px; }
    .btn-success { background: #28a745; }
    .btn-danger { background: #dc3545; }
</style>";

// 1. VERIFICAR ARQUIVOS NECESSÁRIOS
echo "<h2>📁 1. Verificação de Arquivos</h2>";

$arquivos_necessarios = [
    'includes/auth.php' => 'Arquivo de autenticação principal',
    'includes/config.php' => 'Configurações do banco de dados',
    'parceiro/profissionais.php' => 'Página de profissionais'
];

foreach ($arquivos_necessarios as $arquivo => $descricao) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        echo "<div class='success'>✅ {$descricao}: {$arquivo}</div>";
    } else {
        echo "<div class='error'>❌ {$descricao}: {$arquivo} - NÃO ENCONTRADO</div>";
    }
}

// 2. INCLUIR ARQUIVOS E TESTAR FUNÇÕES
echo "<h2>🔧 2. Teste das Funções CSRF</h2>";

try {
    // Incluir arquivo de autenticação
    if (file_exists(__DIR__ . '/includes/auth.php')) {
        require_once __DIR__ . '/includes/auth.php';
        echo "<div class='success'>✅ Arquivo auth.php incluído</div>";
    } else {
        echo "<div class='error'>❌ Arquivo auth.php não encontrado</div>";
    }
    
    // Verificar funções disponíveis
    $funcoes_csrf = [
        'generateCSRFToken' => 'Gerar token CSRF',
        'verifyCSRFToken' => 'Verificar token CSRF',
        'generateCSRFTokenFixed' => 'Gerar token CSRF (versão corrigida)',
        'verifyCSRFTokenFixed' => 'Verificar token CSRF (versão corrigida)',
        'generateCsrfToken' => 'Gerar campo CSRF',
        'verifyCsrfToken' => 'Verificar token (alias)'
    ];
    
    $funcoes_disponiveis = [];
    foreach ($funcoes_csrf as $funcao => $descricao) {
        if (function_exists($funcao)) {
            echo "<div class='success'>✅ {$descricao}: {$funcao}()</div>";
            $funcoes_disponiveis[] = $funcao;
        } else {
            echo "<div class='warning'>⚠️ {$descricao}: {$funcao}() - NÃO DISPONÍVEL</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao incluir arquivos: " . $e->getMessage() . "</div>";
}

// 3. TESTAR GERAÇÃO E VERIFICAÇÃO DE TOKEN
echo "<h2>🧪 3. Teste de Geração e Verificação</h2>";

$token_gerado = null;
$funcao_usada = null;

// Tentar gerar token com diferentes funções
if (function_exists('generateCSRFTokenFixed')) {
    try {
        $token_gerado = generateCSRFTokenFixed();
        $funcao_usada = 'generateCSRFTokenFixed';
        echo "<div class='success'>✅ Token gerado com generateCSRFTokenFixed()</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro com generateCSRFTokenFixed(): " . $e->getMessage() . "</div>";
    }
} elseif (function_exists('generateCSRFToken')) {
    try {
        $token_gerado = generateCSRFToken();
        $funcao_usada = 'generateCSRFToken';
        echo "<div class='success'>✅ Token gerado com generateCSRFToken()</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro com generateCSRFToken(): " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ Nenhuma função de geração de token disponível</div>";
}

if ($token_gerado) {
    echo "<div class='info'>Token gerado: " . substr($token_gerado, 0, 20) . "... (" . strlen($token_gerado) . " caracteres)</div>";
    
    // Testar verificação
    $verificacao_ok = false;
    
    if (function_exists('verifyCSRFTokenFixed')) {
        try {
            $verificacao_ok = verifyCSRFTokenFixed($token_gerado);
            echo "<div class='" . ($verificacao_ok ? 'success' : 'error') . "">" . ($verificacao_ok ? '✅' : '❌') . " Verificação com verifyCSRFTokenFixed(): " . ($verificacao_ok ? 'SUCESSO' : 'FALHA') . "</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erro na verificação: " . $e->getMessage() . "</div>";
        }
    } elseif (function_exists('verifyCSRFToken')) {
        try {
            $verificacao_ok = verifyCSRFToken($token_gerado);
            echo "<div class='" . ($verificacao_ok ? 'success' : 'error') . "">" . ($verificacao_ok ? '✅' : '❌') . " Verificação com verifyCSRFToken(): " . ($verificacao_ok ? 'SUCESSO' : 'FALHA') . "</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Erro na verificação: " . $e->getMessage() . "</div>";
        }
    }
}

// 4. SIMULAR FORMULÁRIO DE PROFISSIONAIS
echo "<h2>👨‍💼 4. Simulação do Formulário de Profissionais</h2>";

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simular_cadastro'])) {
    echo "<h3>📊 Resultado da Simulação:</h3>";
    
    $token_recebido = $_POST['csrf_token'] ?? '';
    $nome_profissional = $_POST['nome'] ?? '';
    $email_profissional = $_POST['email'] ?? '';
    $telefone_profissional = $_POST['telefone'] ?? '';
    
    echo "<div class='info'>";
    echo "<h4>📝 Dados Recebidos:</h4>";
    echo "<p><strong>Nome:</strong> {$nome_profissional}</p>";
    echo "<p><strong>Email:</strong> {$email_profissional}</p>";
    echo "<p><strong>Telefone:</strong> {$telefone_profissional}</p>";
    echo "<p><strong>Token CSRF:</strong> " . substr($token_recebido, 0, 20) . "... (" . strlen($token_recebido) . " chars)</p>";
    echo "</div>";
    
    // Verificar token
    $token_valido = false;
    
    try {
        if (function_exists('verifyCSRFTokenFixed')) {
            $token_valido = verifyCSRFTokenFixed($token_recebido);
            $metodo_verificacao = 'verifyCSRFTokenFixed';
        } elseif (function_exists('verifyCSRFToken')) {
            $token_valido = verifyCSRFToken($token_recebido);
            $metodo_verificacao = 'verifyCSRFToken';
        } else {
            throw new Exception('Nenhuma função de verificação disponível');
        }
        
        if ($token_valido) {
            echo "<div class='success'>";
            echo "<h4>🎉 SIMULAÇÃO BEM-SUCEDIDA!</h4>";
            echo "<p>✅ Token CSRF válido (verificado com {$metodo_verificacao})</p>";
            echo "<p>✅ Dados do profissional recebidos corretamente</p>";
            echo "<p>✅ O problema de CSRF na página de profissionais está RESOLVIDO!</p>";
            echo "<p><strong>🚀 A página https://cortefacil.app/parceiro/profissionais.php deve funcionar normalmente agora!</strong></p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h4>❌ PROBLEMA PERSISTE</h4>";
            echo "<p>❌ Token CSRF inválido (verificado com {$metodo_verificacao})</p>";
            echo "<p>⚠️ O erro na página de profissionais ainda não foi resolvido</p>";
            echo "<p>🔧 Pode ser necessária intervenção manual adicional</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro na verificação: " . $e->getMessage() . "</div>";
    }
    
    // Informações de debug
    echo "<div class='info'>";
    echo "<h4>🔍 Informações de Debug:</h4>";
    echo "<p><strong>Sessão ID:</strong> " . session_id() . "</p>";
    echo "<p><strong>Ambiente:</strong> " . ($isOnline ? 'ONLINE' : 'LOCAL') . "</p>";
    echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<p><strong>Função usada para gerar:</strong> {$funcao_usada}</p>";
    echo "<p><strong>Função usada para verificar:</strong> {$metodo_verificacao}</p>";
    echo "</div>";
}

// Gerar formulário de teste
if ($token_gerado) {
    echo "<form method='POST'>";
    echo "<h4>🧪 Simular Cadastro de Profissional</h4>";
    echo "<input type='hidden' name='csrf_token' value='{$token_gerado}'>";
    echo "<input type='hidden' name='simular_cadastro' value='1'>";
    
    echo "<div>";
    echo "<label>Nome do Profissional:</label><br>";
    echo "<input type='text' name='nome' value='João Silva (Teste)' required>";
    echo "</div>";
    
    echo "<div>";
    echo "<label>Email:</label><br>";
    echo "<input type='email' name='email' value='joao.teste@email.com' required>";
    echo "</div>";
    
    echo "<div>";
    echo "<label>Telefone:</label><br>";
    echo "<input type='tel' name='telefone' value='(11) 99999-9999' required>";
    echo "</div>";
    
    echo "<div>";
    echo "<label>Token CSRF (oculto):</label><br>";
    echo "<input type='text' value='" . substr($token_gerado, 0, 40) . "...' readonly style='background: #f8f9fa;'>";
    echo "</div>";
    
    echo "<button type='submit' class='btn-success'>🚀 SIMULAR CADASTRO DE PROFISSIONAL</button>";
    echo "</form>";
} else {
    echo "<div class='error'>❌ Não foi possível gerar token para o teste</div>";
}

// 5. INSTRUÇÕES FINAIS
echo "<h2>📋 5. Próximos Passos</h2>";

if ($isOnline) {
    echo "<div class='success'>";
    echo "<h4>🌐 Teste no Ambiente Online</h4>";
    echo "<p>Se a simulação acima foi bem-sucedida, o problema está resolvido!</p>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h4>🏠 Ambiente Local</h4>";
    echo "<p>Para testar no servidor online:</p>";
    echo "<ol>";
    echo "<li>Faça upload deste arquivo para o servidor</li>";
    echo "<li>Acesse: https://cortefacil.app/teste_csrf_profissionais_online.php</li>";
    echo "<li>Execute o teste no ambiente de produção</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h4>🎯 Teste Final</h4>";
echo "<p><a href='/parceiro/profissionais.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px;'>🎯 TESTAR PÁGINA DE PROFISSIONAIS REAL</a></p>";
echo "<p><small>Se a simulação funcionou, a página real também deve funcionar!</small></p>";
echo "</div>";

echo "<hr>";
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<p><small>Teste executado em: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";
?>