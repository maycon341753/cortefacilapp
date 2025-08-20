<?php
/**
 * Debug específico para testar a página do salão online
 * Simula exatamente o comportamento da página real
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h1>Debug - Página do Salão Online</h1>";
echo "<p><strong>Objetivo:</strong> Testar se o problema 'Token de segurança inválido' foi resolvido</p>";
echo "<hr>";

// Simular usuário logado como parceiro
if (!isLoggedIn()) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Parceiro Teste';
    $_SESSION['usuario_email'] = 'parceiro@teste.com';
    $_SESSION['tipo_usuario'] = 'parceiro';
    $_SESSION['usuario_telefone'] = '11999999999';
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "✓ Usuário parceiro simulado logado com sucesso";
    echo "</div>";
}

echo "<h2>1. Status da Sessão</h2>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><td><strong>Session ID</strong></td><td>" . session_id() . "</td></tr>";
echo "<tr><td><strong>Usuário ID</strong></td><td>" . ($_SESSION['usuario_id'] ?? 'não definido') . "</td></tr>";
echo "<tr><td><strong>Tipo Usuário</strong></td><td>" . ($_SESSION['tipo_usuario'] ?? 'não definido') . "</td></tr>";
echo "<tr><td><strong>Usuário Logado</strong></td><td>" . (isLoggedIn() ? 'SIM' : 'NÃO') . "</td></tr>";
echo "<tr><td><strong>CSRF Token Existe</strong></td><td>" . (isset($_SESSION['csrf_token']) ? 'SIM' : 'NÃO') . "</td></tr>";
if (isset($_SESSION['csrf_token'])) {
    echo "<tr><td><strong>CSRF Token</strong></td><td>" . substr($_SESSION['csrf_token'], 0, 20) . "...</td></tr>";
    echo "<tr><td><strong>CSRF Token Time</strong></td><td>" . ($_SESSION['csrf_token_time'] ?? 'não definido') . "</td></tr>";
}
echo "</table>";

echo "<h2>2. Teste das Funções CSRF</h2>";

// Testar geração de token
echo "<h3>2.1. Geração de Token</h3>";
$token1 = generateCSRFToken();
echo "<p><strong>Token gerado (generateCSRFToken):</strong> " . substr($token1, 0, 30) . "...</p>";

$token2 = generateCsrfToken();
echo "<p><strong>Token gerado (generateCsrfToken - alias):</strong> " . substr($token2, 0, 30) . "...</p>";

echo "<p><strong>Tokens são iguais:</strong> " . ($token1 === $token2 ? 'SIM ✓' : 'NÃO ✗') . "</p>";

// Testar verificação
echo "<h3>2.2. Verificação de Token</h3>";
$verif1 = verifyCSRFToken($token1);
echo "<p><strong>verifyCSRFToken:</strong> " . ($verif1 ? 'VÁLIDO ✓' : 'INVÁLIDO ✗') . "</p>";

$verif2 = verifyCsrfToken($token1);
echo "<p><strong>verifyCsrfToken (alias):</strong> " . ($verif2 ? 'VÁLIDO ✓' : 'INVÁLIDO ✗') . "</p>";

// Testar com token inválido
$verif3 = verifyCsrfToken('token_invalido');
echo "<p><strong>Token inválido:</strong> " . ($verif3 ? 'VÁLIDO ✗' : 'INVÁLIDO ✓') . "</p>";

echo "<h2>3. Simulação do Formulário Real</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #f8f9fa; padding: 20px; margin: 15px 0; border: 1px solid #dee2e6; border-radius: 8px;'>";
    echo "<h4>📋 Resultado do Processamento</h4>";
    
    try {
        // Exatamente a mesma lógica da página real
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        // Validações dos campos (como na página real)
        $nome = trim($_POST['nome'] ?? '');
        $rua = trim($_POST['rua'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        
        if (empty($nome) || strlen($nome) < 3) {
            throw new Exception('Nome do salão deve ter pelo menos 3 caracteres.');
        }
        
        if (empty($rua)) {
            throw new Exception('Rua/Avenida é obrigatória.');
        }
        
        if (empty($numero)) {
            throw new Exception('Número é obrigatório.');
        }
        
        if (empty($bairro)) {
            throw new Exception('Bairro é obrigatório.');
        }
        
        if (empty($cidade)) {
            throw new Exception('Cidade é obrigatória.');
        }
        
        if (empty($telefone)) {
            throw new Exception('Telefone é obrigatório.');
        }
        
        echo "<div style='color: #155724; background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "<h5>🎉 SUCESSO! Dados processados com sucesso!</h5>";
        echo "<p><strong>✓ Token CSRF:</strong> Válido</p>";
        echo "<p><strong>✓ Validações:</strong> Todas passaram</p>";
        echo "<p><strong>✓ Dados:</strong> Prontos para salvar no banco</p>";
        echo "</div>";
        
        echo "<h5>📊 Dados Recebidos:</h5>";
        echo "<ul>";
        echo "<li><strong>Nome:</strong> " . htmlspecialchars($nome) . "</li>";
        echo "<li><strong>Endereço:</strong> " . htmlspecialchars("$rua, $numero, $bairro, $cidade/$estado, $cep") . "</li>";
        echo "<li><strong>Complemento:</strong> " . htmlspecialchars($complemento) . "</li>";
        echo "<li><strong>Telefone:</strong> " . htmlspecialchars($telefone) . "</li>";
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<div style='color: #721c24; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "<h5>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</h5>";
        
        // Debug detalhado do erro
        echo "<h6>🔍 Diagnóstico Detalhado:</h6>";
        echo "<ul>";
        
        $csrf_recebido = $_POST['csrf_token'] ?? '';
        echo "<li><strong>Token recebido:</strong> " . (empty($csrf_recebido) ? 'VAZIO' : substr($csrf_recebido, 0, 20) . '...') . "</li>";
        echo "<li><strong>Token na sessão:</strong> " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 20) . '...' : 'NÃO EXISTE') . "</li>";
        echo "<li><strong>Sessão ativa:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'SIM' : 'NÃO') . "</li>";
        
        if (!empty($csrf_recebido) && isset($_SESSION['csrf_token'])) {
            echo "<li><strong>Tokens idênticos:</strong> " . ($csrf_recebido === $_SESSION['csrf_token'] ? 'SIM' : 'NÃO') . "</li>";
            echo "<li><strong>hash_equals resultado:</strong> " . (hash_equals($_SESSION['csrf_token'], $csrf_recebido) ? 'TRUE' : 'FALSE') . "</li>";
        }
        
        if (isset($_SESSION['csrf_token_time'])) {
            $idade = time() - $_SESSION['csrf_token_time'];
            echo "<li><strong>Idade do token:</strong> " . $idade . " segundos</li>";
            echo "<li><strong>Token expirado:</strong> " . ($idade > 3600 ? 'SIM (>1h)' : 'NÃO') . "</li>";
        }
        
        echo "</ul>";
        echo "</div>";
    }
    
    echo "</div>";
}

echo "<h3>3.1. Formulário de Teste</h3>";
echo "<p>Este formulário simula exatamente o comportamento da página real do salão:</p>";

echo "<form method='POST' style='background: #ffffff; padding: 25px; border: 2px solid #007bff; border-radius: 10px; margin: 20px 0;'>";
echo "<h4 style='color: #007bff; margin-bottom: 20px;'>🏪 Editar Salão (Teste)</h4>";

// Gerar token CSRF (como na página real)
echo "<input type='hidden' name='csrf_token' value='" . generateCSRFToken() . "'>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Nome do Salão *</label>";
echo "<input type='text' name='nome' value='Salão Teste Online' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";

echo "<div style='display: flex; gap: 15px; margin-bottom: 15px;'>";
echo "<div style='flex: 2;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Rua/Avenida *</label>";
echo "<input type='text' name='rua' value='Rua das Flores' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";
echo "<div style='flex: 1;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Número *</label>";
echo "<input type='text' name='numero' value='123' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";
echo "</div>";

echo "<div style='display: flex; gap: 15px; margin-bottom: 15px;'>";
echo "<div style='flex: 1;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Bairro *</label>";
echo "<input type='text' name='bairro' value='Centro' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";
echo "<div style='flex: 1;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Cidade *</label>";
echo "<input type='text' name='cidade' value='São Paulo' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";
echo "</div>";

echo "<div style='display: flex; gap: 15px; margin-bottom: 15px;'>";
echo "<div style='flex: 1;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Estado *</label>";
echo "<select name='estado' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "<option value='SP' selected>São Paulo</option>";
echo "<option value='RJ'>Rio de Janeiro</option>";
echo "<option value='MG'>Minas Gerais</option>";
echo "</select>";
echo "</div>";
echo "<div style='flex: 1;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>CEP</label>";
echo "<input type='text' name='cep' value='01234-567' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;'>";
echo "</div>";
echo "</div>";

echo "<div style='margin-bottom: 15px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Complemento</label>";
echo "<input type='text' name='complemento' value='Sala 1' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;'>";
echo "</div>";

echo "<div style='margin-bottom: 20px;'>";
echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Telefone *</label>";
echo "<input type='text' name='telefone' value='(11) 99999-9999' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
echo "</div>";

echo "<button type='submit' style='background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>💾 Salvar Salão</button>";
echo "</form>";

echo "<hr>";
echo "<h2>4. Links Úteis</h2>";
echo "<ul>";
echo "<li><a href='parceiro/salao.php' target='_blank'>🔗 Página Real do Salão</a></li>";
echo "<li><a href='teste_salao_csrf.php' target='_blank'>🔗 Teste CSRF Simples</a></li>";
echo "<li><a href='debug_csrf_online.php' target='_blank'>🔗 Debug CSRF Completo</a></li>";
echo "</ul>";

echo "<div style='background: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
echo "<h4>📝 Instruções para Teste:</h4>";
echo "<ol>";
echo "<li>Preencha o formulário acima e clique em 'Salvar Salão'</li>";
echo "<li>Se aparecer 'SUCESSO', o problema do CSRF foi resolvido</li>";
echo "<li>Se aparecer 'Token de segurança inválido', ainda há problemas</li>";
echo "<li>Verifique também a página real do salão para confirmar</li>";
echo "</ol>";
echo "</div>";
?>