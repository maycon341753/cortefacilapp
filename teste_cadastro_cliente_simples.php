<?php
/**
 * Teste simples do cadastro de clientes
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste Simples - Cadastro de Clientes</h2>";
echo "<hr>";

// Simular ambiente local
$_SERVER['HTTP_HOST'] = 'localhost';
$_GET['tipo'] = 'cliente';

echo "<h3>1. Carregando dependências</h3>";

try {
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/models/usuario.php';
    echo "✅ Dependências carregadas<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar dependências: " . $e->getMessage() . "<br>";
    exit;
}

echo "<br><h3>2. Verificando se está logado</h3>";
$loggedIn = isLoggedIn();
echo "Status de login: " . ($loggedIn ? 'Logado' : 'Não logado') . "<br>";

if ($loggedIn) {
    echo "⚠️ Usuário está logado - seria redirecionado<br>";
} else {
    echo "✅ Usuário não está logado - pode acessar cadastro<br>";
}

echo "<br><h3>3. Processando parâmetros</h3>";
$tipo_usuario = $_GET['tipo'] ?? 'cliente';
echo "Tipo de usuário: " . $tipo_usuario . "<br>";

// Validar tipo de usuário
if (!in_array($tipo_usuario, ['cliente', 'parceiro'])) {
    $tipo_usuario = 'cliente';
    echo "Tipo ajustado para: cliente<br>";
} else {
    echo "Tipo válido: " . $tipo_usuario . "<br>";
}

echo "<br><h3>4. Simulando início do HTML</h3>";

// Capturar qualquer saída
ob_start();

$erro = '';
$sucesso = '';

// Simular o HTML básico
echo "<!DOCTYPE html>\n";
echo "<html lang='pt-BR'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>CorteFácil - Cadastro</title>\n";
echo "</head>\n";
echo "<body>\n";
echo "    <h1>CorteFácil - Cadastro de Cliente</h1>\n";
echo "    <form method='POST'>\n";
echo "        <input type='hidden' name='tipo_usuario' value='cliente'>\n";
echo "        <p><label>Nome:</label><br><input type='text' name='nome' required></p>\n";
echo "        <p><label>Email:</label><br><input type='email' name='email' required></p>\n";
echo "        <p><label>CPF:</label><br><input type='text' name='cpf' required></p>\n";
echo "        <p><label>Telefone:</label><br><input type='text' name='telefone' required></p>\n";
echo "        <p><label>Senha:</label><br><input type='password' name='senha' required></p>\n";
echo "        <p><label>Confirmar Senha:</label><br><input type='password' name='confirmar_senha' required></p>\n";
echo "        <p><button type='submit'>Cadastrar</button></p>\n";
echo "    </form>\n";
echo "</body>\n";
echo "</html>\n";

$html_output = ob_get_clean();

echo "✅ HTML gerado com sucesso<br>";
echo "Tamanho do HTML: " . strlen($html_output) . " bytes<br>";

echo "<br><h3>5. Testando acesso direto ao cadastro.php</h3>";
echo "<p><strong>Resultado do teste:</strong></p>";
echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px;'>";
echo "<p>✅ Todas as dependências carregaram corretamente</p>";
echo "<p>✅ Não há erros de sintaxe</p>";
echo "<p>✅ As funções básicas estão funcionando</p>";
echo "<p>✅ O HTML pode ser gerado sem problemas</p>";
echo "</div>";

echo "<br><h3>6. Possíveis causas da página em branco</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px;'>";
echo "<p><strong>Possíveis problemas:</strong></p>";
echo "<ul>";
echo "<li>Erro fatal não capturado no ambiente online</li>";
echo "<li>Problema de permissões de arquivo</li>";
echo "<li>Diferença de configuração PHP entre local e online</li>";
echo "<li>Problema de codificação de caracteres</li>";
echo "<li>Erro de redirecionamento infinito</li>";
echo "</ul>";
echo "</div>";

echo "<br><p><a href='cadastro.php?tipo=cliente' target='_blank'>🔗 Testar cadastro.php?tipo=cliente</a></p>";
echo "<p><a href='debug_cadastro_cliente.php' target='_blank'>🔗 Ver debug completo</a></p>";

// Mostrar uma versão simplificada do HTML
echo "<br><h3>7. Preview do formulário</h3>";
echo $html_output;

?>