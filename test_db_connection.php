<?php
/**
 * Teste de Conexão com Banco de Dados Hostinger
 * Para diagnosticar problemas de conexão após login
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão - CorteFácil</h1>";
echo "<hr>";

// Teste 1: Verificar se as extensões estão disponíveis
echo "<h2>1. Verificando Extensões PHP</h2>";
echo "PDO: " . (extension_loaded('pdo') ? '✅ OK' : '❌ ERRO') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ OK' : '❌ ERRO') . "<br>";
echo "MySQLi: " . (extension_loaded('mysqli') ? '✅ OK' : '❌ ERRO') . "<br>";
echo "<br>";

// Teste 2: Testar conexão direta
echo "<h2>2. Teste de Conexão Direta</h2>";

$host = 'srv488.hstgr.io';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'vpH=yoc?0lL';

try {
    echo "Tentando conectar com:<br>";
    echo "Host: $host<br>";
    echo "Database: $db_name<br>";
    echo "Username: $username<br>";
    echo "Password: " . str_repeat('*', strlen($password)) . "<br><br>";
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    echo "✅ <strong>Conexão estabelecida com sucesso!</strong><br><br>";
    
    // Teste 3: Verificar tabelas
    echo "<h2>3. Verificando Tabelas</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas encontradas:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
    echo "<br>";
    
    // Teste 4: Verificar usuários
    echo "<h2>4. Verificando Usuários</h2>";
    $stmt = $pdo->query("SELECT id, nome, email, tipo_usuario FROM usuarios LIMIT 5");
    $usuarios = $stmt->fetchAll();
    
    if ($usuarios) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['tipo_usuario']}</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ Nenhum usuário encontrado<br><br>";
    }
    
    // Teste 5: Testar função getConnection()
    echo "<h2>5. Testando função getConnection()</h2>";
    require_once __DIR__ . '/config/database.php';
    
    $conn = getConnection();
    if ($conn) {
        echo "✅ Função getConnection() funcionando<br>";
        
        // Testar uma consulta simples
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        echo "Total de usuários: {$result['total']}<br>";
    } else {
        echo "❌ Erro na função getConnection()<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong>Erro de conexão:</strong><br>";
    echo "Código: " . $e->getCode() . "<br>";
    echo "Mensagem: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "❌ <strong>Erro geral:</strong><br>";
    echo "Mensagem: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Instruções:</strong></p>";
echo "<ul>";
echo "<li>Se a conexão falhar, verifique as credenciais do banco</li>";
echo "<li>Se as tabelas não aparecerem, execute o schema.sql</li>";
echo "<li>Se não houver usuários, execute o usuarios_teste.sql</li>";
echo "</ul>";
?>