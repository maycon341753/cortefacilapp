<?php
/**
 * Teste do sistema CSRF
 */

require_once 'includes/auth.php';

// Iniciar sessão
session_start();

echo "<h2>Teste do Sistema CSRF</h2>";

// Testar geração do token
echo "<h3>1. Geração do Token</h3>";
$token = generateCSRFToken();
echo "Token gerado: " . htmlspecialchars($token) . "<br>";
echo "Token na sessão: " . htmlspecialchars($_SESSION['csrf_token'] ?? 'não definido') . "<br>";

// Testar função de compatibilidade
echo "<h3>2. Função de Compatibilidade</h3>";
echo "HTML gerado por generateCsrfToken(): " . htmlspecialchars(generateCsrfToken()) . "<br>";

// Testar verificação
echo "<h3>3. Verificação do Token</h3>";
$token_valido = verifyCsrfToken($token);
echo "Token válido (verifyCsrfToken): " . ($token_valido ? 'SIM' : 'NÃO') . "<br>";

$token_valido2 = verifyCSRFToken($token);
echo "Token válido (verifyCSRFToken): " . ($token_valido2 ? 'SIM' : 'NÃO') . "<br>";

// Testar token inválido
echo "<h3>4. Teste com Token Inválido</h3>";
$token_invalido = verifyCsrfToken('token_falso');
echo "Token falso válido: " . ($token_invalido ? 'SIM' : 'NÃO') . "<br>";

echo "<h3>5. Formulário de Teste</h3>";
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Resultado do POST:</strong><br>";
    
    $csrf_recebido = $_POST['csrf_token'] ?? '';
    echo "Token recebido: " . htmlspecialchars($csrf_recebido) . "<br>";
    
    if (verifyCsrfToken($csrf_recebido)) {
        echo "<span style='color: green;'>✓ Token CSRF válido!</span><br>";
        echo "Dados processados com sucesso!<br>";
    } else {
        echo "<span style='color: red;'>✗ Token CSRF inválido!</span><br>";
    }
    echo "</div>";
}

echo '<form method="POST">';
echo generateCsrfToken();
echo '<input type="text" name="teste" placeholder="Digite algo" required><br><br>';
echo '<button type="submit">Testar Envio</button>';
echo '</form>';

echo "<br><a href='parceiro/salao.php'>Ir para página do salão</a>";
?>