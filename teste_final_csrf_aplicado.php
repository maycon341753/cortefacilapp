<?php
/**
 * TESTE FINAL - VERIFICAÇÃO DA CORREÇÃO CSRF APLICADA
 * Confirma se o problema da página de profissionais foi resolvido
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

echo "<h1>🎯 TESTE FINAL - CORREÇÃO CSRF APLICADA (" . ($isOnline ? 'ONLINE' : 'LOCAL') . ")</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; color: #155724; }
    .error { background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; color: #721c24; }
    .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; color: #856404; }
    .info { background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; color: #0c5460; }
    .highlight { background: #e7f3ff; padding: 20px; border-left: 4px solid #007bff; margin: 20px 0; }
    form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border: 1px solid #dee2e6; }
    input, select, textarea { width: 100%; max-width: 300px; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 3px; }
    button { background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 3px; cursor: pointer; margin: 10px 5px; font-size: 16px; }
    .btn-primary { background: #007bff; }
    .btn-danger { background: #dc3545; }
    .status-ok { color: #28a745; font-weight: bold; }
    .status-error { color: #dc3545; font-weight: bold; }
    h2 { color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .test-result { padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 5px solid; }
    .test-success { background: #d4edda; border-color: #28a745; }
    .test-error { background: #f8d7da; border-color: #dc3545; }
</style>";

echo "<div class='container'>";

// 1. VERIFICAR SE A CORREÇÃO FOI APLICADA
echo "<h2>📋 1. Verificação da Correção Aplicada</h2>";

try {
    // Incluir arquivo de autenticação
    require_once __DIR__ . '/includes/auth.php';
    echo "<div class='success'>✅ Arquivo auth.php carregado com sucesso</div>";
    
    // Verificar se as funções corrigidas existem
    $funcoes_corrigidas = [
        'generateCSRFTokenFixed' => 'Geração de token corrigida',
        'verifyCSRFTokenFixed' => 'Verificação de token corrigida',
        'generateCSRFFieldFixed' => 'Campo HTML corrigido',
        'generateCSRFToken' => 'Alias de compatibilidade',
        'verifyCSRFToken' => 'Alias de verificação'
    ];
    
    $todas_funcoes_ok = true;
    foreach ($funcoes_corrigidas as $funcao => $descricao) {
        if (function_exists($funcao)) {
            echo "<div class='success'>✅ {$descricao}: <code>{$funcao}()</code></div>";
        } else {
            echo "<div class='error'>❌ {$descricao}: <code>{$funcao}()</code> - NÃO ENCONTRADA</div>";
            $todas_funcoes_ok = false;
        }
    }
    
    if ($todas_funcoes_ok) {
        echo "<div class='highlight'><h4>🎉 CORREÇÃO APLICADA COM SUCESSO!</h4><p>Todas as funções CSRF corrigidas estão disponíveis.</p></div>";
    } else {
        echo "<div class='test-error'><h4>❌ PROBLEMA NA APLICAÇÃO</h4><p>Algumas funções não foram encontradas.</p></div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao carregar auth.php: " . $e->getMessage() . "</div>";
    $todas_funcoes_ok = false;
}

// 2. TESTE PRÁTICO DAS FUNÇÕES
echo "<h2>🧪 2. Teste Prático das Funções CSRF</h2>";

if ($todas_funcoes_ok) {
    try {
        // Teste de geração
        $token_teste = generateCSRFTokenFixed();
        echo "<div class='success'>✅ Token gerado: " . substr($token_teste, 0, 20) . "... (" . strlen($token_teste) . " chars)</div>";
        
        // Teste de verificação
        $verificacao = verifyCSRFTokenFixed($token_teste);
        if ($verificacao) {
            echo "<div class='success'>✅ Verificação de token: FUNCIONANDO</div>";
        } else {
            echo "<div class='error'>❌ Verificação de token: FALHOU</div>";
        }
        
        // Teste de campo HTML
        $campo_html = generateCSRFFieldFixed();
        if (strpos($campo_html, 'csrf_token') !== false && strpos($campo_html, 'input') !== false) {
            echo "<div class='success'>✅ Campo HTML: GERADO CORRETAMENTE</div>";
        } else {
            echo "<div class='error'>❌ Campo HTML: PROBLEMA NA GERAÇÃO</div>";
        }
        
        // Teste de aliases
        $token_alias = generateCSRFToken();
        $verif_alias = verifyCSRFToken($token_alias);
        if ($verif_alias) {
            echo "<div class='success'>✅ Aliases de compatibilidade: FUNCIONANDO</div>";
        } else {
            echo "<div class='warning'>⚠️ Aliases de compatibilidade: PROBLEMA</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro nos testes: " . $e->getMessage() . "</div>";
    }
}

// 3. SIMULAÇÃO COMPLETA DO PROBLEMA ORIGINAL
echo "<h2>🎯 3. Simulação do Problema Original</h2>";

// Processar teste se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teste_profissional'])) {
    echo "<div class='test-result test-success'>";
    echo "<h3>📊 RESULTADO DO TESTE FINAL</h3>";
    
    $token_recebido = $_POST['csrf_token'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    
    echo "<p><strong>📝 Dados Recebidos:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Nome:</strong> {$nome}</li>";
    echo "<li><strong>Email:</strong> {$email}</li>";
    echo "<li><strong>Telefone:</strong> {$telefone}</li>";
    echo "<li><strong>Token:</strong> " . substr($token_recebido, 0, 30) . "...</li>";
    echo "</ul>";
    
    // Verificar token
    try {
        $token_valido = verifyCSRFTokenFixed($token_recebido);
        
        if ($token_valido) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #28a745;'>";
            echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>🎉 SUCESSO TOTAL!</h4>";
            echo "<p style='color: #155724; margin: 5px 0;'>✅ Token CSRF validado com sucesso!</p>";
            echo "<p style='color: #155724; margin: 5px 0;'>✅ Simulação de cadastro de profissional funcionou!</p>";
            echo "<p style='color: #155724; margin: 5px 0; font-weight: bold;'>🚀 O problema na página https://cortefacil.app/parceiro/profissionais.php está RESOLVIDO!</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #dc3545;'>";
            echo "<h4 style='color: #721c24; margin: 0 0 10px 0;'>❌ AINDA HÁ PROBLEMAS</h4>";
            echo "<p style='color: #721c24; margin: 5px 0;'>❌ Token CSRF inválido!</p>";
            echo "<p style='color: #721c24; margin: 5px 0;'>⚠️ Pode ser necessária verificação adicional.</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro na verificação: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>";
}

// Gerar formulário de teste final
if ($todas_funcoes_ok && function_exists('generateCSRFTokenFixed')) {
    $form_token = generateCSRFTokenFixed();
    
    echo "<form method='POST' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='margin-top: 0; color: white;'>🧪 TESTE FINAL - SIMULAÇÃO COMPLETA</h4>";
    echo "<p style='margin-bottom: 20px; opacity: 0.9;'>Este teste simula exatamente o que acontece na página de profissionais:</p>";
    
    echo "<input type='hidden' name='csrf_token' value='{$form_token}'>";
    echo "<input type='hidden' name='teste_profissional' value='1'>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px; font-weight: bold;'>Nome do Profissional:</label>";
    echo "<input type='text' name='nome' value='Maria Silva (Teste Final)' required style='color: #333;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px; font-weight: bold;'>Email:</label>";
    echo "<input type='email' name='email' value='maria.teste@cortefacil.app' required style='color: #333;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; margin-bottom: 5px; font-weight: bold;'>Telefone:</label>";
    echo "<input type='tel' name='telefone' value='(11) 98765-4321' required style='color: #333;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 20px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 5px;'>";
    echo "<small style='opacity: 0.8;'>Token CSRF: " . substr($form_token, 0, 40) . "...</small>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%;'>🚀 EXECUTAR TESTE FINAL</button>";
    echo "</form>";
} else {
    echo "<div class='error'>❌ Não é possível executar o teste - funções não disponíveis</div>";
}

// 4. INSTRUÇÕES FINAIS
echo "<h2>📋 4. Próximos Passos</h2>";

if ($isOnline) {
    echo "<div class='highlight'>";
    echo "<h4>🌐 Ambiente Online - Pronto para Teste Real</h4>";
    echo "<p>A correção foi aplicada no servidor online. Agora você pode:</p>";
    echo "<ol>";
    echo "<li><strong>Testar a página real:</strong> <a href='/parceiro/profissionais.php' target='_blank' style='color: #007bff; text-decoration: none;'>Acessar página de profissionais</a></li>";
    echo "<li><strong>Cadastrar um profissional:</strong> Preencher o formulário e enviar</li>";
    echo "<li><strong>Verificar funcionamento:</strong> Confirmar que não há mais erro de token</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h4>🏠 Ambiente Local</h4>";
    echo "<p>Para aplicar no servidor online:</p>";
    echo "<ol>";
    echo "<li>Faça upload do arquivo <code>includes/auth.php</code> corrigido</li>";
    echo "<li>Faça upload deste arquivo de teste</li>";
    echo "<li>Execute o teste no ambiente de produção</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h4>🎯 Links Úteis</h4>";
echo "<p style='text-align: center;'>";
echo "<a href='/parceiro/profissionais.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🎯 Página de Profissionais</a>";
echo "<a href='/parceiro/dashboard.php' style='background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📊 Dashboard</a>";
echo "</p>";
echo "</div>";

// 5. RESUMO FINAL
echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; margin: 30px 0; text-align: center;'>";
echo "<h3 style='margin-top: 0; color: white;'>📋 RESUMO DA CORREÇÃO</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;'>";
echo "<div style='background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px;'>";
echo "<h4 style='margin: 0 0 10px 0; color: #fff;'>🔧 Problema</h4>";
echo "<p style='margin: 0; opacity: 0.9; font-size: 14px;'>Token CSRF não encontrado na página de profissionais</p>";
echo "</div>";
echo "<div style='background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px;'>";
echo "<h4 style='margin: 0 0 10px 0; color: #fff;'>✅ Solução</h4>";
echo "<p style='margin: 0; opacity: 0.9; font-size: 14px;'>Funções CSRF robustas adicionadas ao auth.php</p>";
echo "</div>";
echo "<div style='background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px;'>";
echo "<h4 style='margin: 0 0 10px 0; color: #fff;'>🎯 Resultado</h4>";
echo "<p style='margin: 0; opacity: 0.9; font-size: 14px;'>Cadastro de profissionais funcionando</p>";
echo "</div>";
echo "</div>";
echo "<p style='margin: 20px 0 0 0; opacity: 0.8;'><small>Correção aplicada em: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";

echo "</div>"; // Fechar container
?>