<?php
/**
 * Teste específico para o problema de CSRF na página do salão
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h1>Teste CSRF - Página do Salão</h1>";
echo "<hr>";

// Simular dados de usuário logado (para teste)
if (!isLoggedIn()) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_nome'] = 'Teste Parceiro';
    $_SESSION['usuario_email'] = 'teste@teste.com';
    $_SESSION['tipo_usuario'] = 'parceiro';
    $_SESSION['usuario_telefone'] = '11999999999';
    echo "<p>✓ Usuário de teste logado</p>";
}

echo "<h2>1. Informações da Sessão</h2>";
echo "<ul>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "<li><strong>Usuário ID:</strong> " . ($_SESSION['usuario_id'] ?? 'não definido') . "</li>";
echo "<li><strong>Tipo Usuário:</strong> " . ($_SESSION['tipo_usuario'] ?? 'não definido') . "</li>";
echo "<li><strong>CSRF Token:</strong> " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 20) . '...' : 'não definido') . "</li>";
echo "<li><strong>CSRF Token Time:</strong> " . ($_SESSION['csrf_token_time'] ?? 'não definido') . "</li>";
echo "</ul>";

echo "<h2>2. Teste de Geração de Token</h2>";
$token = generateCSRFToken();
echo "<p><strong>Token gerado:</strong> " . htmlspecialchars(substr($token, 0, 20)) . "...</p>";
echo "<p><strong>Token na sessão:</strong> " . htmlspecialchars(substr($_SESSION['csrf_token'], 0, 20)) . "...</p>";
echo "<p><strong>Tokens são iguais:</strong> " . ($token === $_SESSION['csrf_token'] ? 'SIM' : 'NÃO') . "</p>";

echo "<h2>3. Teste de Verificação</h2>";
$verificacao1 = verifyCSRFToken($token);
echo "<p><strong>verifyCSRFToken:</strong> " . ($verificacao1 ? 'VÁLIDO' : 'INVÁLIDO') . "</p>";

$verificacao2 = verifyCsrfToken($token);
echo "<p><strong>verifyCsrfToken (alias):</strong> " . ($verificacao2 ? 'VÁLIDO' : 'INVÁLIDO') . "</p>";

echo "<h2>4. Simulação do Formulário do Salão</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border: 1px solid #dee2e6;'>";
    echo "<h4>Resultado do POST:</h4>";
    
    $csrf_recebido = $_POST['csrf_token'] ?? '';
    echo "<p><strong>Token recebido:</strong> " . htmlspecialchars(substr($csrf_recebido, 0, 20)) . "...</p>";
    echo "<p><strong>Token na sessão:</strong> " . htmlspecialchars(substr($_SESSION['csrf_token'] ?? '', 0, 20)) . "...</p>";
    
    // Testar com a mesma lógica da página do salão
    try {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        echo "<p style='color: green; font-weight: bold;'>✓ TOKEN CSRF VÁLIDO!</p>";
        echo "<p>✓ Dados do salão seriam processados normalmente</p>";
        
        // Simular processamento dos dados
        $nome = trim($_POST['nome'] ?? '');
        $rua = trim($_POST['rua'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        
        echo "<h5>Dados recebidos:</h5>";
        echo "<ul>";
        echo "<li><strong>Nome:</strong> " . htmlspecialchars($nome) . "</li>";
        echo "<li><strong>Endereço:</strong> " . htmlspecialchars("$rua, $numero, $bairro, $cidade, $estado, $cep") . "</li>";
        echo "<li><strong>Complemento:</strong> " . htmlspecialchars($complemento) . "</li>";
        echo "<li><strong>Telefone:</strong> " . htmlspecialchars($telefone) . "</li>";
        echo "</ul>";
        
    } catch (Exception $e) {
        echo "<p style='color: red; font-weight: bold;'>✗ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
        
        echo "<h5>Diagnóstico do Erro:</h5>";
        echo "<ul>";
        echo "<li><strong>Token recebido vazio:</strong> " . (empty($csrf_recebido) ? 'SIM' : 'NÃO') . "</li>";
        echo "<li><strong>Token na sessão existe:</strong> " . (isset($_SESSION['csrf_token']) ? 'SIM' : 'NÃO') . "</li>";
        echo "<li><strong>Sessão ativa:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'SIM' : 'NÃO') . "</li>";
        echo "<li><strong>Tokens são iguais:</strong> " . (isset($_SESSION['csrf_token']) && $csrf_recebido === $_SESSION['csrf_token'] ? 'SIM' : 'NÃO') . "</li>";
        
        if (isset($_SESSION['csrf_token_time'])) {
            $tempo_token = time() - $_SESSION['csrf_token_time'];
            echo "<li><strong>Idade do token:</strong> " . $tempo_token . " segundos</li>";
            echo "<li><strong>Token expirado:</strong> " . ($tempo_token > 3600 ? 'SIM' : 'NÃO') . "</li>";
        }
        echo "</ul>";
    }
    
    echo "</div>";
}

echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; margin: 20px 0;'>";
echo "<h4>Formulário de Teste (Simulando página do salão)</h4>";
echo generateCsrfToken();

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Nome do Salão:</strong></label><br>";
echo "<input type='text' name='nome' value='Salão Teste' style='padding: 8px; width: 300px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Rua:</strong></label><br>";
echo "<input type='text' name='rua' value='Rua Teste' style='padding: 8px; width: 200px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Número:</strong></label><br>";
echo "<input type='text' name='numero' value='123' style='padding: 8px; width: 100px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Bairro:</strong></label><br>";
echo "<input type='text' name='bairro' value='Centro' style='padding: 8px; width: 200px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Cidade:</strong></label><br>";
echo "<input type='text' name='cidade' value='São Paulo' style='padding: 8px; width: 200px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Estado:</strong></label><br>";
echo "<select name='estado' style='padding: 8px; width: 100px;'>";
echo "<option value='SP' selected>SP</option>";
echo "<option value='RJ'>RJ</option>";
echo "</select>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>CEP:</strong></label><br>";
echo "<input type='text' name='cep' value='01234-567' style='padding: 8px; width: 150px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Complemento:</strong></label><br>";
echo "<input type='text' name='complemento' value='Sala 1' style='padding: 8px; width: 200px;'>";
echo "</div>";

echo "<div style='margin: 10px 0;'>";
echo "<label><strong>Telefone:</strong></label><br>";
echo "<input type='text' name='telefone' value='(11) 99999-9999' style='padding: 8px; width: 200px;'>";
echo "</div>";

echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 10px;'>Salvar Salão (Teste)</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='parceiro/salao.php'>← Ir para página real do salão</a></p>";
echo "<p><a href='debug_csrf_online.php'>← Voltar para diagnóstico completo</a></p>";
?>