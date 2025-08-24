<?php
/**
 * Debug específico para problema de CSRF na página de profissionais
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h2>Debug CSRF - Profissionais</h2>";

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h3>1. Status da Sessão</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";

echo "<h3>2. Conteúdo da Sessão</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>3. Teste de Geração de Token</h3>";
$token1 = generateCSRFToken();
echo "Token gerado (função): " . $token1 . "<br>";

$tokenHtml = generateCsrfToken();
echo "Token HTML gerado: " . htmlspecialchars($tokenHtml) . "<br>";

echo "<h3>4. Teste de Validação</h3>";
$isValid = verifyCSRFToken($token1);
echo "Token válido: " . ($isValid ? 'SIM' : 'NÃO') . "<br>";

echo "<h3>5. Simulação POST</h3>";
// Simular dados POST
$_POST['csrf_token'] = $token1;
$_POST['nome'] = 'Teste';
$_POST['especialidade'] = 'Teste';
$_POST['acao'] = 'cadastrar';

echo "Dados POST simulados:<br>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Testar validação como no arquivo original
$csrfValid = verifyCsrfToken($_POST['csrf_token'] ?? '');
echo "Validação CSRF (como no código): " . ($csrfValid ? 'VÁLIDO' : 'INVÁLIDO') . "<br>";

if (!$csrfValid) {
    echo "<strong style='color: red;'>ERRO: Token CSRF inválido!</strong><br>";
    echo "Token recebido: " . ($_POST['csrf_token'] ?? 'VAZIO') . "<br>";
    echo "Token da sessão: " . ($_SESSION['csrf_token_final_fix'] ?? 'VAZIO') . "<br>";
    
    // Verificar se são iguais
    $received = trim($_POST['csrf_token'] ?? '');
    $session = trim($_SESSION['csrf_token_final_fix'] ?? '');
    echo "Tokens são iguais: " . ($received === $session ? 'SIM' : 'NÃO') . "<br>";
    echo "Tamanho token recebido: " . strlen($received) . "<br>";
    echo "Tamanho token sessão: " . strlen($session) . "<br>";
}

echo "<h3>6. Teste de Formulário Real</h3>";
echo '<form method="POST" action="">';
echo generateCsrfToken();
echo '<input type="hidden" name="acao" value="test">';
echo '<input type="text" name="nome" placeholder="Nome" required>';
echo '<input type="text" name="especialidade" placeholder="Especialidade" required>';
echo '<button type="submit">Testar Envio</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'test') {
    echo "<h4>Resultado do Teste Real:</h4>";
    $realTest = verifyCsrfToken($_POST['csrf_token'] ?? '');
    echo "CSRF válido no teste real: " . ($realTest ? 'SIM' : 'NÃO') . "<br>";
    
    if ($realTest) {
        echo "<strong style='color: green;'>✅ CSRF funcionando corretamente!</strong><br>";
    } else {
        echo "<strong style='color: red;'>❌ CSRF com problema!</strong><br>";
        echo "Debug detalhado:<br>";
        echo "- Token POST: '" . ($_POST['csrf_token'] ?? '') . "'<br>";
        echo "- Token SESSION: '" . ($_SESSION['csrf_token_final_fix'] ?? '') . "'<br>";
        echo "- Tempo do token: " . ($_SESSION['csrf_token_final_fix_time'] ?? 'N/A') . "<br>";
        echo "- Tempo atual: " . time() . "<br>";
        echo "- Idade do token: " . (time() - ($_SESSION['csrf_token_final_fix_time'] ?? 0)) . " segundos<br>";
    }
}

echo "<h3>7. Informações do Ambiente</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Server: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "<br>";
echo "Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "<br>";
echo "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "<br>";

echo "<h3>8. Headers</h3>";
echo "<pre>";
print_r(getallheaders());
echo "</pre>";
?>