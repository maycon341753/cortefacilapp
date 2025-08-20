<?php
/**
 * Debug do cadastro de clientes
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug - Cadastro de Clientes</h2>";
echo "<hr>";

echo "<h3>1. Testando includes</h3>";

try {
    echo "Carregando auth.php... ";
    require_once __DIR__ . '/includes/auth.php';
    echo "✅ OK<br>";
    
    echo "Carregando functions.php... ";
    require_once __DIR__ . '/includes/functions.php';
    echo "✅ OK<br>";
    
    echo "Carregando usuario.php... ";
    require_once __DIR__ . '/models/usuario.php';
    echo "✅ OK<br>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<br><h3>2. Testando função isLoggedIn()</h3>";
try {
    $loggedIn = isLoggedIn();
    echo "isLoggedIn(): " . ($loggedIn ? 'true' : 'false') . "<br>";
} catch (Exception $e) {
    echo "❌ Erro em isLoggedIn(): " . $e->getMessage() . "<br>";
}

echo "<br><h3>3. Testando parâmetros GET</h3>";
$_GET['tipo'] = 'cliente';
$tipo_usuario = $_GET['tipo'] ?? 'cliente';
echo "Tipo de usuário: " . $tipo_usuario . "<br>";

echo "<br><h3>4. Testando validação de tipo</h3>";
if (!in_array($tipo_usuario, ['cliente', 'parceiro'])) {
    $tipo_usuario = 'cliente';
    echo "Tipo ajustado para: cliente<br>";
} else {
    echo "Tipo válido: " . $tipo_usuario . "<br>";
}

echo "<br><h3>5. Testando conexão com banco</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->connect();
    
    if ($db) {
        echo "✅ Conexão com banco estabelecida<br>";
    } else {
        echo "❌ Falha na conexão com banco<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
}

echo "<br><h3>6. Simulando carregamento da página</h3>";

// Simular o início do HTML
echo "Iniciando HTML...<br>";

// Verificar se há algum erro fatal
ob_start();

$erro = '';
$sucesso = '';
$tipo_usuario = 'cliente';

// Tentar incluir o início do HTML do cadastro
try {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<h4>Simulação do formulário de cadastro de cliente:</h4>";
    echo "<form>";
    echo "<p>Nome: <input type='text' name='nome' placeholder='Digite seu nome'></p>";
    echo "<p>Email: <input type='email' name='email' placeholder='Digite seu email'></p>";
    echo "<p>CPF: <input type='text' name='cpf' placeholder='Digite seu CPF'></p>";
    echo "<p>Telefone: <input type='text' name='telefone' placeholder='Digite seu telefone'></p>";
    echo "<p>Senha: <input type='password' name='senha' placeholder='Digite sua senha'></p>";
    echo "<p><button type='submit'>Cadastrar Cliente</button></p>";
    echo "</form>";
    echo "</div>";
    
    echo "✅ Formulário renderizado com sucesso<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao renderizar formulário: " . $e->getMessage() . "<br>";
}

$output = ob_get_clean();
echo $output;

echo "<br><h3>7. Verificando arquivo cadastro.php</h3>";
$cadastro_file = __DIR__ . '/cadastro.php';
if (file_exists($cadastro_file)) {
    echo "✅ Arquivo cadastro.php existe<br>";
    echo "Tamanho: " . filesize($cadastro_file) . " bytes<br>";
    echo "Última modificação: " . date('Y-m-d H:i:s', filemtime($cadastro_file)) . "<br>";
} else {
    echo "❌ Arquivo cadastro.php não encontrado<br>";
}

echo "<br><h3>8. Testando sintaxe do arquivo cadastro.php</h3>";
$output = shell_exec('php -l cadastro.php 2>&1');
echo "<pre>" . htmlspecialchars($output) . "</pre>";

echo "<br><p><a href='cadastro.php?tipo=cliente'>🔗 Tentar acessar cadastro.php?tipo=cliente</a></p>";
echo "<p><a href='cadastro.php?tipo=parceiro'>🔗 Tentar acessar cadastro.php?tipo=parceiro</a></p>";
?>