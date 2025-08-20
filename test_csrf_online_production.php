<?php
/**
 * Teste específico para verificar CSRF no ambiente de produção
 * Simula as condições exatas do servidor online
 */

// Simular condições do ambiente online
$_SERVER['HTTPS'] = 'on';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['REQUEST_SCHEME'] = 'https';

require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h1>🔒 Teste CSRF - Ambiente de Produção</h1>";
echo "<p><strong>Objetivo:</strong> Verificar se o problema persiste no ambiente online</p>";
echo "<hr>";

// Simular usuário logado
if (!isLoggedIn()) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Parceiro Online';
    $_SESSION['usuario_email'] = 'parceiro@cortefacil.app';
    $_SESSION['tipo_usuario'] = 'parceiro';
    $_SESSION['usuario_telefone'] = '11999999999';
}

echo "<h2>📊 Diagnóstico do Ambiente</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px 0;'>";
echo "<table style='width: 100%; border-collapse: collapse;'>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Servidor</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>HTTPS</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'SIM ✓' : 'NÃO ✗') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Session ID</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . session_id() . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Session Status</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . (session_status() === PHP_SESSION_ACTIVE ? 'ATIVA ✓' : 'INATIVA ✗') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Cookie Secure</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . (ini_get('session.cookie_secure') ? 'SIM ✓' : 'NÃO ✗') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Cookie HttpOnly</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . (ini_get('session.cookie_httponly') ? 'SIM ✓' : 'NÃO ✗') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>Cookie SameSite</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . (ini_get('session.cookie_samesite') ?: 'Não definido') . "</td></tr>";
echo "<tr><td style='padding: 5px; border: 1px solid #ddd;'><strong>PHP Version</strong></td><td style='padding: 5px; border: 1px solid #ddd;'>" . PHP_VERSION . "</td></tr>";
echo "</table>";
echo "</div>";

echo "<h2>🔑 Teste das Funções CSRF</h2>";

// Limpar tokens existentes para teste limpo
unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);

echo "<h3>1. Geração de Token</h3>";
$token_novo = generateCSRFToken();
echo "<p><strong>Token gerado:</strong> " . substr($token_novo, 0, 30) . "...</p>";
echo "<p><strong>Token na sessão:</strong> " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 30) . '...' : 'NÃO EXISTE') . "</p>";
echo "<p><strong>Timestamp do token:</strong> " . ($_SESSION['csrf_token_time'] ?? 'NÃO DEFINIDO') . "</p>";

echo "<h3>2. Verificação de Token</h3>";
$verificacao_valida = verifyCSRFToken($token_novo);
echo "<p><strong>Token válido:</strong> " . ($verificacao_valida ? 'SIM ✓' : 'NÃO ✗') . "</p>";

$verificacao_alias = verifyCsrfToken($token_novo);
echo "<p><strong>Alias válido:</strong> " . ($verificacao_alias ? 'SIM ✓' : 'NÃO ✗') . "</p>";

// Teste com token inválido
$verificacao_invalida = verifyCsrfToken('token_fake_123');
echo "<p><strong>Token inválido (esperado falso):</strong> " . ($verificacao_invalida ? 'SIM ✗' : 'NÃO ✓') . "</p>";

echo "<h2>🧪 Simulação do Problema Online</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #fff3cd; padding: 20px; border: 1px solid #ffeaa7; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📋 Processamento do Formulário</h4>";
    
    $csrf_recebido = $_POST['csrf_token'] ?? '';
    
    echo "<h5>🔍 Debug Detalhado:</h5>";
    echo "<ul>";
    echo "<li><strong>Token recebido:</strong> " . (empty($csrf_recebido) ? 'VAZIO ✗' : substr($csrf_recebido, 0, 30) . '... ✓') . "</li>";
    echo "<li><strong>Token na sessão:</strong> " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 30) . '... ✓' : 'NÃO EXISTE ✗') . "</li>";
    echo "<li><strong>Tamanho token recebido:</strong> " . strlen($csrf_recebido) . " caracteres</li>";
    echo "<li><strong>Tamanho token sessão:</strong> " . (isset($_SESSION['csrf_token']) ? strlen($_SESSION['csrf_token']) : 0) . " caracteres</li>";
    
    if (!empty($csrf_recebido) && isset($_SESSION['csrf_token'])) {
        echo "<li><strong>Comparação direta (===):</strong> " . ($csrf_recebido === $_SESSION['csrf_token'] ? 'IGUAIS ✓' : 'DIFERENTES ✗') . "</li>";
        echo "<li><strong>hash_equals():</strong> " . (hash_equals($_SESSION['csrf_token'], $csrf_recebido) ? 'IGUAIS ✓' : 'DIFERENTES ✗') . "</li>";
    }
    
    if (isset($_SESSION['csrf_token_time'])) {
        $idade_token = time() - $_SESSION['csrf_token_time'];
        echo "<li><strong>Idade do token:</strong> " . $idade_token . " segundos</li>";
        echo "<li><strong>Token expirado:</strong> " . ($idade_token > 3600 ? 'SIM (>1h) ✗' : 'NÃO ✓') . "</li>";
    }
    echo "</ul>";
    
    try {
        // Usar exatamente a mesma lógica da página real
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; color: #155724;'>";
        echo "<h5>🎉 SUCESSO!</h5>";
        echo "<p>✅ Token CSRF validado com sucesso!</p>";
        echo "<p>✅ O problema do CSRF foi resolvido!</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; color: #721c24;'>";
        echo "<h5>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</h5>";
        echo "<p>⚠️ O problema ainda persiste no ambiente online!</p>";
        
        echo "<h6>🔧 Possíveis Soluções:</h6>";
        echo "<ol>";
        echo "<li>Verificar se o servidor online tem as mesmas configurações de sessão</li>";
        echo "<li>Confirmar se o HTTPS está configurado corretamente</li>";
        echo "<li>Verificar se não há cache ou proxy interferindo</li>";
        echo "<li>Confirmar se as permissões de diretório de sessão estão corretas</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    echo "</div>";
}

echo "<h3>🧪 Formulário de Teste (Ambiente de Produção)</h3>";
echo "<p>Este formulário simula as condições exatas do servidor online:</p>";

echo "<form method='POST' style='background: #ffffff; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; margin: 20px 0;'>";
echo "<h4 style='color: #dc3545; margin-bottom: 20px;'>🌐 Teste Ambiente Online</h4>";

// Gerar token CSRF
$token_formulario = generateCSRFToken();
echo "<input type='hidden' name='csrf_token' value='" . $token_formulario . "'>";

echo "<div style='background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
echo "<small><strong>Token do formulário:</strong> " . substr($token_formulario, 0, 40) . "...</small>";
echo "</div>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Nome do Salão *</label>";
echo "<input type='text' name='nome' value='Salão Online Test' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Telefone *</label>";
echo "<input type='text' name='telefone' value='(11) 99999-9999' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";

echo "<button type='submit' style='background: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>🧪 Testar CSRF Online</button>";
echo "</form>";

echo "<h2>📋 Instruções para Correção Online</h2>";
echo "<div style='background: #d1ecf1; padding: 20px; border: 1px solid #bee5eb; border-radius: 8px; margin: 20px 0;'>";
echo "<h4>🔧 Se o teste falhar, siga estes passos:</h4>";
echo "<ol>";
echo "<li><strong>Verificar configurações do servidor:</strong> Confirme se o PHP no servidor online tem as mesmas configurações de sessão</li>";
echo "<li><strong>Permissões de diretório:</strong> Verifique se o diretório de sessões tem permissões corretas (geralmente /tmp ou /var/lib/php/sessions)</li>";
echo "<li><strong>Configurações de HTTPS:</strong> Confirme se o SSL está configurado corretamente e se os cookies seguros estão funcionando</li>";
echo "<li><strong>Cache e CDN:</strong> Desative temporariamente qualquer cache ou CDN que possa estar interferindo</li>";
echo "<li><strong>Logs do servidor:</strong> Verifique os logs de erro do PHP no servidor para identificar problemas específicos</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🔗 Links Úteis</h2>";
echo "<ul>";
echo "<li><a href='parceiro/salao.php' target='_blank'>🔗 Página Real do Salão (Local)</a></li>";
echo "<li><a href='debug_salao_online.php' target='_blank'>🔗 Debug Completo do Salão</a></li>";
echo "<li><a href='https://cortefacil.app/parceiro/salao.php' target='_blank'>🌐 Página Online (Produção)</a></li>";
echo "</ul>";

echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>⚠️ Importante</h4>";
echo "<p>Este teste simula as condições do ambiente online. Se funcionar aqui mas não funcionar no servidor real, o problema está nas configurações específicas do servidor de produção.</p>";
echo "</div>";
?>