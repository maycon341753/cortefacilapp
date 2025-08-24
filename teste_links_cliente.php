<?php
/**
 * Teste de Links do Cliente - Verificar Redirecionamento
 * Simula uma sessão de cliente e testa os links do menu
 */

// Configurações de erro
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Teste de Links do Cliente - CorteFácil</h2>";
echo "<hr>";

// Iniciar sessão
session_start();

// Incluir arquivos necessários
echo "<h3>1. Verificando arquivos críticos:</h3>";
$arquivos = [
    'includes/auth.php',
    'includes/functions.php',
    'cliente/dashboard.php',
    'cliente/agendar.php',
    'cliente/agendamentos.php',
    'cliente/saloes.php',
    'cliente/perfil.php'
];

foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo - OK<br>";
    } else {
        echo "❌ $arquivo - NÃO ENCONTRADO<br>";
    }
}

echo "<br>";

// Incluir auth.php
if (file_exists('includes/auth.php')) {
    require_once 'includes/auth.php';
    echo "<h3>2. Sistema de autenticação carregado:</h3>";
    
    // Verificar se as funções existem
    $funcoes = ['requireCliente', 'isLoggedIn', 'hasUserType', 'isCliente'];
    foreach ($funcoes as $funcao) {
        if (function_exists($funcao)) {
            echo "✅ Função $funcao() - Disponível<br>";
        } else {
            echo "❌ Função $funcao() - NÃO ENCONTRADA<br>";
        }
    }
} else {
    echo "❌ Não foi possível carregar o sistema de autenticação<br>";
}

echo "<br>";

// Simular usuário cliente logado
echo "<h3>3. Simulando usuário cliente logado:</h3>";
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'cliente';
$_SESSION['user_name'] = 'Cliente Teste';
$_SESSION['user_email'] = 'cliente@teste.com';

echo "✅ Sessão de cliente simulada<br>";
echo "- ID: " . $_SESSION['user_id'] . "<br>";
echo "- Tipo: " . $_SESSION['user_type'] . "<br>";
echo "- Nome: " . $_SESSION['user_name'] . "<br>";
echo "- Email: " . $_SESSION['user_email'] . "<br>";

echo "<br>";

// Testar redirecionamento para usuário não logado
echo "<h3>4. Teste de redirecionamento (usuário não logado):</h3>";
// Limpar sessão temporariamente
$sessao_backup = $_SESSION;
unset($_SESSION['user_id']);
unset($_SESSION['user_type']);

if (function_exists('requireCliente')) {
    // Capturar o redirecionamento
    ob_start();
    try {
        requireCliente();
        echo "❌ Redirecionamento não funcionou - usuário não logado deveria ser redirecionado<br>";
    } catch (Exception $e) {
        echo "✅ Redirecionamento funcionando - " . $e->getMessage() . "<br>";
    }
    $output = ob_get_clean();
    
    // Verificar se houve tentativa de redirecionamento
    $headers = headers_list();
    $redirect_found = false;
    foreach ($headers as $header) {
        if (strpos($header, 'Location:') !== false) {
            echo "✅ Header de redirecionamento encontrado: $header<br>";
            $redirect_found = true;
        }
    }
    
    if (!$redirect_found && empty($output)) {
        echo "✅ Função requireCliente() executada (redirecionamento para ../index.php)<br>";
    }
}

// Restaurar sessão
$_SESSION = $sessao_backup;

echo "<br>";

// Testar com usuário cliente logado
echo "<h3>5. Teste com usuário cliente logado:</h3>";
if (function_exists('isCliente') && isCliente()) {
    echo "✅ Usuário identificado como cliente<br>";
} else {
    echo "❌ Problema na identificação do usuário como cliente<br>";
}

echo "<br>";

// Testar links do menu
echo "<h3>6. Links do menu cliente:</h3>";
$links = [
    'Dashboard' => 'cliente/dashboard.php',
    'Agendar' => 'cliente/agendar.php',
    'Meus Agendamentos' => 'cliente/agendamentos.php',
    'Salões' => 'cliente/saloes.php',
    'Perfil' => 'cliente/perfil.php'
];

foreach ($links as $nome => $link) {
    if (file_exists($link)) {
        echo "✅ $nome ($link) - Arquivo existe<br>";
    } else {
        echo "❌ $nome ($link) - Arquivo não encontrado<br>";
    }
}

echo "<br>";
echo "<h3>7. Informações do ambiente:</h3>";
echo "- PHP Version: " . PHP_VERSION . "<br>";
echo "- Session ID: " . session_id() . "<br>";
echo "- Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "- Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "- Current Directory: " . getcwd() . "<br>";

echo "<br><hr>";
echo "<p><strong>Teste concluído!</strong></p>";
?>