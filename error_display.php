<?php
/**
 * Arquivo para ativar exibição de erros PHP
 * Use este arquivo para identificar a linha exata do problema
 */

// Ativar exibição completa de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar log de erros
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

echo "<h1>Teste com Exibição de Erros Ativada - CorteFácil</h1>";
echo "<p>Configurações de erro ativadas. Agora testando o cadastro.php...</p>";

try {
    echo "<h2>1. Testando Includes Básicos</h2>";
    
    // Testar cada include separadamente
    echo "<p>Carregando auth.php...</p>";
    require_once __DIR__ . '/includes/auth.php';
    echo "<p style='color: green;'>✓ auth.php carregado</p>";
    
    echo "<p>Carregando functions.php...</p>";
    require_once __DIR__ . '/includes/functions.php';
    echo "<p style='color: green;'>✓ functions.php carregado</p>";
    
    echo "<p>Carregando Usuario.php...</p>";
    require_once __DIR__ . '/models/usuario.php';
    echo "<p style='color: green;'>✓ usuario.php carregado com sucesso</p>";
    
    echo "<h2>2. Testando Funcionalidades do Cadastro</h2>";
    
    // Simular verificação de login
    echo "<p>Testando função isLoggedIn()...</p>";
    $loggedIn = isLoggedIn();
    echo "<p style='color: green;'>✓ isLoggedIn() funcionando: " . ($loggedIn ? 'true' : 'false') . "</p>";
    
    // Testar criação do objeto Usuario
    echo "<p>Testando criação do objeto Usuario...</p>";
    $usuario = new Usuario();
    echo "<p style='color: green;'>✓ Objeto Usuario criado com sucesso</p>";
    
    // Testar funções de validação
    echo "<p>Testando funções de validação...</p>";
    if (function_exists('sanitizeInput')) {
        $teste = sanitizeInput('teste@email.com');
        echo "<p style='color: green;'>✓ sanitizeInput() funcionando</p>";
    } else {
        echo "<p style='color: red;'>✗ sanitizeInput() não encontrada</p>";
    }
    
    if (function_exists('validateEmail')) {
        $emailValido = validateEmail('teste@email.com');
        echo "<p style='color: green;'>✓ validateEmail() funcionando: " . ($emailValido ? 'true' : 'false') . "</p>";
    } else {
        echo "<p style='color: red;'>✗ validateEmail() não encontrada</p>";
    }
    
    if (function_exists('validateTelefone')) {
        $telefoneValido = validateTelefone('11999999999');
        echo "<p style='color: green;'>✓ validateTelefone() funcionando: " . ($telefoneValido ? 'true' : 'false') . "</p>";
    } else {
        echo "<p style='color: red;'>✗ validateTelefone() não encontrada</p>";
    }
    
    echo "<h2>3. Simulando Processamento POST</h2>";
    
    // Simular dados POST
    $_POST = [
        'nome' => 'Teste Usuario',
        'email' => 'teste@email.com',
        'telefone' => '11999999999',
        'senha' => '123456',
        'confirmar_senha' => '123456',
        'tipo_usuario' => 'cliente'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    echo "<p>Simulando dados POST...</p>";
    
    $nome = sanitizeInput($_POST['nome'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $tipo_usuario = sanitizeInput($_POST['tipo_usuario'] ?? 'cliente');
    
    echo "<p style='color: green;'>✓ Dados POST processados:</p>";
    echo "<ul>";
    echo "<li>Nome: " . htmlspecialchars($nome) . "</li>";
    echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    echo "<li>Telefone: " . htmlspecialchars($telefone) . "</li>";
    echo "<li>Tipo: " . htmlspecialchars($tipo_usuario) . "</li>";
    echo "</ul>";
    
    // Testar validações
    $erro = '';
    if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (!validateEmail($email)) {
        $erro = 'Digite um email válido.';
    } elseif (!validateTelefone($telefone)) {
        $erro = 'Digite um telefone válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    }
    
    if ($erro) {
        echo "<p style='color: orange;'>⚠ Erro de validação: " . htmlspecialchars($erro) . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Todas as validações passaram</p>";
        
        // Testar verificação de email existente
        echo "<p>Testando verificação de email existente...</p>";
        $emailExiste = $usuario->emailExiste($email);
        echo "<p style='color: green;'>✓ Verificação de email: " . ($emailExiste ? 'Email já existe' : 'Email disponível') . "</p>";
    }
    
    echo "<h2>✅ Teste Concluído com Sucesso!</h2>";
    echo "<p>Se você está vendo esta mensagem, o problema não está nos includes ou na lógica básica do cadastro.</p>";
    echo "<p>O erro 500 pode estar relacionado a:</p>";
    echo "<ul>";
    echo "<li>Problemas de conexão com banco de dados no servidor</li>";
    echo "<li>Configurações PHP diferentes no servidor</li>";
    echo "<li>Problemas de permissões de arquivo</li>";
    echo "<li>Configurações do .htaccess</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='color: #f44336;'>🚨 ERRO CAPTURADO:</h3>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 15px; margin: 10px 0;'>";
    echo "<h3 style='color: #f44336;'>🚨 ERRO FATAL CAPTURADO:</h3>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

// Verificar se há arquivo de log de erros
if (file_exists(__DIR__ . '/error.log')) {
    echo "<h2>📋 Log de Erros</h2>";
    $errorLog = file_get_contents(__DIR__ . '/error.log');
    if (!empty($errorLog)) {
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>" . htmlspecialchars($errorLog) . "</pre>";
    } else {
        echo "<p>Nenhum erro registrado no log.</p>";
    }
}

echo "<hr>";
echo "<p><strong>Próximo passo:</strong> Faça upload deste arquivo para o servidor e acesse <code>https://cortefacil.app/error_display.php</code></p>";
echo "<p><strong>Importante:</strong> Remova este arquivo após identificar o problema por questões de segurança.</p>";
?>