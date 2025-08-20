<?php
/**
 * Debug específico para redirecionamentos no cadastro
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug - Redirecionamentos do Cadastro</h2>";
echo "<hr>";

// Simular diferentes cenários
echo "<h3>1. Testando includes</h3>";

try {
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/models/usuario.php';
    echo "✅ Includes carregados<br>";
} catch (Exception $e) {
    echo "❌ Erro nos includes: " . $e->getMessage() . "<br>";
    exit;
}

echo "<br><h3>2. Testando função isLoggedIn()</h3>";
$loggedIn = isLoggedIn();
echo "Status: " . ($loggedIn ? 'Logado' : 'Não logado') . "<br>";

if ($loggedIn) {
    echo "⚠️ Usuário logado - seria redirecionado para index.php<br>";
    echo "<p><strong>PROBLEMA IDENTIFICADO:</strong> Se o usuário estiver logado, ele será redirecionado e a página ficará em branco.</p>";
} else {
    echo "✅ Usuário não logado - pode acessar cadastro<br>";
}

echo "<br><h3>3. Testando parâmetros</h3>";
$_GET['tipo'] = 'cliente';
$tipo_usuario = $_GET['tipo'] ?? 'cliente';
echo "Tipo de usuário: " . $tipo_usuario . "<br>";

// Validar tipo de usuário
if (!in_array($tipo_usuario, ['cliente', 'parceiro'])) {
    $tipo_usuario = 'cliente';
    echo "Tipo ajustado para: cliente<br>";
} else {
    echo "Tipo válido: " . $tipo_usuario . "<br>";
}

echo "<br><h3>4. Testando função setFlashMessage</h3>";
try {
    setFlashMessage('test', 'Mensagem de teste');
    echo "✅ setFlashMessage funcionou<br>";
    
    $flash = getFlashMessage();
    if ($flash) {
        echo "✅ getFlashMessage funcionou: " . $flash['message'] . "<br>";
    } else {
        echo "❌ getFlashMessage não retornou dados<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro em setFlashMessage: " . $e->getMessage() . "<br>";
}

echo "<br><h3>5. Simulando POST de cadastro</h3>";

// Simular dados POST
$_POST = [
    'nome' => 'Teste Cliente',
    'email' => 'teste@exemplo.com',
    'telefone' => '11999999999',
    'cpf' => '12345678901',
    'senha' => '123456',
    'confirmar_senha' => '123456',
    'tipo_usuario' => 'cliente'
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Dados POST simulados:<br>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

echo "<br><h3>6. Testando validações</h3>";

$nome = sanitizeInput($_POST['nome'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$telefone = sanitizeInput($_POST['telefone'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';
$tipo_usuario = sanitizeInput($_POST['tipo_usuario'] ?? 'cliente');

echo "Nome: " . $nome . "<br>";
echo "Email: " . $email . "<br>";
echo "Telefone: " . $telefone . "<br>";
echo "Tipo: " . $tipo_usuario . "<br>";

// Campos específicos por tipo de usuário
$cpf = '';
if ($tipo_usuario === 'cliente') {
    $cpf = sanitizeInput($_POST['cpf'] ?? '');
    echo "CPF: " . $cpf . "<br>";
}

echo "<br><h3>7. Testando validações básicas</h3>";

$erro = '';

if (empty($nome) || empty($email) || empty($senha)) {
    $erro = 'Campos obrigatórios não preenchidos.';
} elseif ($senha !== $confirmar_senha) {
    $erro = 'Senhas não coincidem.';
} elseif ($tipo_usuario === 'cliente' && !validarCPF($cpf)) {
    $erro = 'CPF inválido.';
}

if ($erro) {
    echo "❌ Erro de validação: " . $erro . "<br>";
} else {
    echo "✅ Validações básicas passaram<br>";
}

echo "<br><h3>8. Testando conexão com banco</h3>";

try {
    $usuario = new Usuario();
    echo "✅ Objeto Usuario criado<br>";
    
    // Testar verificação de email (sem cadastrar)
    $emailExiste = $usuario->emailExiste($email);
    echo "Email existe: " . ($emailExiste ? 'Sim' : 'Não') . "<br>";
    
    if ($tipo_usuario === 'cliente') {
        $cpfExiste = $usuario->cpfExiste($cpf);
        echo "CPF existe: " . ($cpfExiste ? 'Sim' : 'Não') . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao testar banco: " . $e->getMessage() . "<br>";
}

echo "<br><h3>9. Análise do Problema</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px;'>";
echo "<h4>Possíveis causas da página em branco:</h4>";
echo "<ol>";
echo "<li><strong>Usuário já logado:</strong> Se isLoggedIn() retorna true, há redirecionamento para index.php</li>";
echo "<li><strong>Erro fatal não capturado:</strong> Algum erro PHP que não está sendo exibido</li>";
echo "<li><strong>Problema de sessão:</strong> Sessão não iniciada ou corrompida</li>";
echo "<li><strong>Erro de include:</strong> Algum arquivo não encontrado</li>";
echo "<li><strong>Problema de codificação:</strong> Caracteres especiais causando erro</li>";
echo "</ol>";
echo "</div>";

echo "<br><h3>10. Soluções Recomendadas</h3>";
echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
echo "<h4>Para corrigir o problema:</h4>";
echo "<ol>";
echo "<li>Verificar se o usuário não está logado inadvertidamente</li>";
echo "<li>Adicionar mais logs de debug no arquivo original</li>";
echo "<li>Usar a versão simplificada como alternativa</li>";
echo "<li>Verificar configurações do servidor online</li>";
echo "</ol>";
echo "</div>";

echo "<br><p><a href='cadastro_cliente_simples.php'>🔗 Usar versão simplificada (funcional)</a></p>";
echo "<p><a href='cadastro.php?tipo=cliente'>🔗 Tentar versão original</a></p>";

?>