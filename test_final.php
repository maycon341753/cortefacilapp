<?php
// Teste final com as credenciais corretas do Hostinger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>✅ Teste Final - Credenciais Corretas</h2>";

// Credenciais corretas do painel Hostinger
$host = 'srv488.hstgr.io';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'vpH=yoc?0lL';

echo "<div style='background: #e7f3ff; padding: 10px; border: 1px solid #b3d9ff; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>🔧 Credenciais Atualizadas:</strong><br>";
echo "Host: $host<br>";
echo "Database: $db_name<br>";
echo "Username: $username<br>";
echo "Password: " . str_repeat('*', strlen($password)) . "<br>";
echo "</div>";

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: green; margin: 0 0 10px 0;'>🎉 CONEXÃO ESTABELECIDA COM SUCESSO!</h3>";
    echo "<p>O banco de dados está funcionando perfeitamente.</p>";
    
    // Teste das tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<strong>Tabelas encontradas:</strong> " . implode(', ', $tables) . "<br>";
    
    // Teste de usuários
    if (in_array('usuarios', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        echo "<strong>Total de usuários:</strong> " . $result['total'] . "<br>";
    }
    
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>✅ Próximos Passos:</strong><br>";
    echo "1. O arquivo database.php foi atualizado com as credenciais corretas<br>";
    echo "2. Teste o login no sistema: <a href='https://cortefacil.app/login.php' target='_blank'>https://cortefacil.app/login.php</a><br>";
    echo "3. Use as credenciais de teste criadas anteriormente<br>";
    echo "4. Remova os arquivos de teste após confirmar que tudo funciona<br>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: red; margin: 0 0 10px 0;'>❌ ERRO DE CONEXÃO</h3>";
    echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Arquivo criado para teste final das credenciais do Hostinger.</em></p>";
?>