<?php
// Debug do Dashboard - Verificar erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Debug Dashboard - Verificando Dependências</h2>";

// Testar includes
echo "<h3>1. Testando includes:</h3>";

try {
    echo "Carregando auth.php... ";
    require_once 'includes/auth.php';
    echo "✓ OK<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
}

try {
    echo "Carregando functions.php... ";
    require_once 'includes/functions.php';
    echo "✓ OK<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
}

try {
    echo "Carregando agendamento.php... ";
    require_once 'models/agendamento.php';
    echo "✓ OK<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
}

try {
    echo "Carregando salao.php... ";
    require_once 'models/salao.php';
    echo "✓ OK<br>";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "<br>";
}

// Testar sessão
echo "<h3>2. Testando sessão:</h3>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "Status da sessão: " . session_status() . "<br>";
echo "Dados da sessão: ";
var_dump($_SESSION);
echo "<br>";

// Testar conexão com banco
echo "<h3>3. Testando conexão com banco:</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->connect();
    if ($conn) {
        echo "✓ Conexão com banco OK<br>";
        
        // Testar query simples
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total de usuários: " . $result['total'] . "<br>";
    } else {
        echo "❌ Falha na conexão com banco<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO na conexão: " . $e->getMessage() . "<br>";
}

// Testar funções específicas
echo "<h3>4. Testando funções:</h3>";

if (function_exists('gerarBadgeStatus')) {
    echo "✓ gerarBadgeStatus existe<br>";
    echo "Teste: " . gerarBadgeStatus('pendente') . "<br>";
} else {
    echo "❌ gerarBadgeStatus não encontrada<br>";
}

if (function_exists('formatTelefone')) {
    echo "✓ formatTelefone existe<br>";
    echo "Teste: " . formatTelefone('11999999999') . "<br>";
} else {
    echo "❌ formatTelefone não encontrada<br>";
}

if (function_exists('formatarData')) {
    echo "✓ formatarData existe<br>";
    echo "Teste: " . formatarData(date('Y-m-d')) . "<br>";
} else {
    echo "❌ formatarData não encontrada<br>";
}

echo "<h3>5. Testando autenticação:</h3>";
if (function_exists('getLoggedUser')) {
    $usuario = getLoggedUser();
    if ($usuario) {
        echo "✓ Usuário logado: " . $usuario['nome'] . " (" . $usuario['tipo'] . ")<br>";
    } else {
        echo "❌ Nenhum usuário logado<br>";
    }
} else {
    echo "❌ Função getLoggedUser não encontrada<br>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";
echo "<p><a href='cliente/dashboard.php'>Ir para Dashboard</a></p>";
?>