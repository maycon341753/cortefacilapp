<?php
/**
 * Teste Final da Correção CSRF
 * Verifica se o problema foi resolvido definitivamente
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h1>🎯 Teste Final - Correção CSRF</h1>";
echo "<p><strong>Objetivo:</strong> Verificar se o problema do token inválido foi resolvido</p>";
echo "<hr>";

// Simular usuário logado
if (!isLoggedIn()) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Teste Final';
    $_SESSION['usuario_email'] = 'teste@cortefacil.app';
    $_SESSION['tipo_usuario'] = 'parceiro';
    $_SESSION['usuario_telefone'] = '11999999999';
}

echo "<h2>🔍 Diagnóstico das Funções Corrigidas</h2>";

// Limpar tokens para teste limpo
unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);

echo "<h3>1. Teste de Geração de Token</h3>";
$token1 = generateCSRFToken();
echo "<p><strong>Primeiro token:</strong> " . substr($token1, 0, 30) . "...</p>";

$token2 = generateCSRFToken();
echo "<p><strong>Segundo token (deve ser igual):</strong> " . substr($token2, 0, 30) . "...</p>";
echo "<p><strong>Tokens iguais:</strong> " . ($token1 === $token2 ? 'SIM ✅' : 'NÃO ❌') . "</p>";

echo "<h3>2. Teste de Validação</h3>";
$validacao1 = verifyCSRFToken($token1);
echo "<p><strong>Token 1 válido:</strong> " . ($validacao1 ? 'SIM ✅' : 'NÃO ❌') . "</p>";

$validacao2 = verifyCsrfToken($token1);
echo "<p><strong>Token 1 válido (alias):</strong> " . ($validacao2 ? 'SIM ✅' : 'NÃO ❌') . "</p>";

$validacao_falsa = verifyCsrfToken('token_invalido_123');
echo "<p><strong>Token inválido (deve ser falso):</strong> " . ($validacao_falsa ? 'SIM ❌' : 'NÃO ✅') . "</p>";

echo "<h3>3. Teste de Persistência</h3>";
echo "<p><strong>Token na sessão:</strong> " . (isset($_SESSION['csrf_token']) ? 'EXISTE ✅' : 'NÃO EXISTE ❌') . "</p>";
echo "<p><strong>Timestamp na sessão:</strong> " . (isset($_SESSION['csrf_token_time']) ? 'EXISTE ✅' : 'NÃO EXISTE ❌') . "</p>";

if (isset($_SESSION['csrf_token_time'])) {
    $idade = time() - $_SESSION['csrf_token_time'];
    echo "<p><strong>Idade do token:</strong> " . $idade . " segundos</p>";
}

echo "<h2>🧪 Teste do Formulário</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; margin: 15px 0;'>";
    echo "<h4>📋 Resultado do Teste POST</h4>";
    
    $csrf_recebido = $_POST['csrf_token'] ?? '';
    
    echo "<h5>🔍 Debug Detalhado:</h5>";
    echo "<ul>";
    echo "<li><strong>Token recebido:</strong> " . (empty($csrf_recebido) ? 'VAZIO ❌' : substr($csrf_recebido, 0, 30) . '... ✅') . "</li>";
    echo "<li><strong>Token na sessão:</strong> " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 30) . '... ✅' : 'NÃO EXISTE ❌') . "</li>";
    echo "<li><strong>Tamanhos:</strong> Recebido=" . strlen($csrf_recebido) . ", Sessão=" . (isset($_SESSION['csrf_token']) ? strlen($_SESSION['csrf_token']) : 0) . "</li>";
    
    if (!empty($csrf_recebido) && isset($_SESSION['csrf_token'])) {
        echo "<li><strong>Comparação direta:</strong> " . ($csrf_recebido === $_SESSION['csrf_token'] ? 'IGUAIS ✅' : 'DIFERENTES ❌') . "</li>";
        if (function_exists('hash_equals')) {
            echo "<li><strong>hash_equals:</strong> " . (hash_equals($_SESSION['csrf_token'], $csrf_recebido) ? 'IGUAIS ✅' : 'DIFERENTES ❌') . "</li>";
        }
    }
    echo "</ul>";
    
    try {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;'>";
        echo "<h5>🎉 SUCESSO TOTAL!</h5>";
        echo "<p>✅ Token CSRF validado com sucesso!</p>";
        echo "<p>✅ A correção funcionou perfeitamente!</p>";
        echo "<p>✅ O problema foi resolvido definitivamente!</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;'>";
        echo "<h5>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</h5>";
        echo "<p>⚠️ O problema ainda não foi completamente resolvido.</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

echo "<h3>Formulário de Teste Final</h3>";
echo "<form method='POST' style='background: #ffffff; padding: 25px; border: 2px solid #28a745; border-radius: 10px; margin: 20px 0;'>";
echo "<h4 style='color: #28a745;'>🎯 Teste Final da Correção</h4>";

// Gerar token para o formulário
$token_form = generateCSRFToken();
echo "<input type='hidden' name='csrf_token' value='" . $token_form . "'>";

echo "<div style='background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
echo "<small><strong>Token do formulário:</strong> " . substr($token_form, 0, 40) . "...</small>";
echo "</div>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Nome do Salão *</label>";
echo "<input type='text' name='nome' value='Salão Teste Final' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Telefone *</label>";
echo "<input type='text' name='telefone' value='(11) 99999-9999' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";

echo "<button type='submit' style='background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>🎯 Testar Correção Final</button>";
echo "</form>";

echo "<h2>📋 Resumo da Correção</h2>";
echo "<div style='background: #d1ecf1; padding: 20px; border: 1px solid #bee5eb; border-radius: 8px;'>";
echo "<h4>🔧 Alterações Implementadas:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Geração de token:</strong> Token só é gerado uma vez por sessão (não regenera a cada chamada)</li>";
echo "<li>✅ <strong>Validação robusta:</strong> Fallback para servidores sem hash_equals()</li>";
echo "<li>✅ <strong>Tempo de expiração:</strong> Aumentado para 2 horas (7200 segundos)</li>";
echo "<li>✅ <strong>Compatibilidade:</strong> Suporte para diferentes versões do PHP</li>";
echo "</ul>";
echo "</div>";

echo "<h2>🌐 Próximos Passos</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border: 1px solid #ffeaa7; border-radius: 8px;'>";
echo "<h4>Para aplicar no servidor online:</h4>";
echo "<ol>";
echo "<li>Se este teste passou, a correção está funcionando localmente</li>";
echo "<li>Faça backup do arquivo <code>includes/auth.php</code> no servidor online</li>";
echo "<li>Substitua o arquivo online pela versão corrigida</li>";
echo "<li>Teste a página <code>https://cortefacil.app/parceiro/salao.php</code></li>";
echo "<li>Se ainda houver problemas, verifique as configurações específicas do servidor</li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 10px;'>";
echo "<h3>🎯 Status da Correção</h3>";
echo "<p style='font-size: 18px; font-weight: bold; color: #28a745;'>";
if (isset($_POST['csrf_token']) && verifyCsrfToken($_POST['csrf_token'])) {
    echo "✅ CORREÇÃO APLICADA COM SUCESSO!";
} else {
    echo "⏳ Aguardando teste do formulário...";
}
echo "</p>";
echo "</div>";
?>