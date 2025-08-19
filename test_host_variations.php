<?php
/**
 * Teste de diferentes variações de host para Hostinger
 * Tenta conectar com diferentes configurações de host
 */

echo "<h2>🌐 Teste de Variações de Host - Hostinger</h2>";

// Credenciais base
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'vpH=yoc?0lL';

// Lista de hosts para testar
$hosts_to_test = [
    'localhost',
    '127.0.0.1',
    'srv488.hstgr.io',
    'srv488.hstgr.io:3306',
    'mysql.hostinger.com',
    'mysql.hostinger.ro'
];

echo "<div style='background: #e7f3ff; padding: 10px; border: 1px solid #b3d9ff; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>📋 Credenciais base:</strong><br>";
echo "Database: $db_name<br>";
echo "Username: $username<br>";
echo "Password: " . str_repeat('*', strlen($password)) . "<br>";
echo "</div>";

echo "<h3>🔍 Testando diferentes hosts:</h3>";

foreach ($hosts_to_test as $index => $host) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<strong>Teste " . ($index + 1) . ": $host</strong><br>";
    
    try {
        $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_TIMEOUT => 10,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "<span style='color: green;'>✅ SUCESSO! Conexão estabelecida com $host</span><br>";
        
        // Teste uma query simples
        $stmt = $pdo->query("SELECT DATABASE() as current_db, USER() as current_user, NOW() as current_time");
        $result = $stmt->fetch();
        
        echo "<div style='background: #d4edda; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "Database atual: " . $result['current_db'] . "<br>";
        echo "Usuário atual: " . $result['current_user'] . "<br>";
        echo "Hora do servidor: " . $result['current_time'] . "<br>";
        echo "</div>";
        
        // Listar algumas tabelas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tabelas encontradas: " . count($tables) . " (" . implode(', ', array_slice($tables, 0, 5)) . (count($tables) > 5 ? '...' : '') . ")<br>";
        
        $pdo = null;
        
        echo "<div style='background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid #bee5eb;'>";
        echo "<strong>🎉 HOST CORRETO ENCONTRADO: $host</strong><br>";
        echo "Use este host no arquivo database.php";
        echo "</div>";
        
        break; // Para no primeiro sucesso
        
    } catch (PDOException $e) {
        echo "<span style='color: red;'>❌ FALHOU</span><br>";
        echo "<small style='color: #666;'>Erro: " . $e->getMessage() . "</small><br>";
    }
    
    echo "</div>";
}

echo "<br><div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<strong>💡 Informações importantes:</strong><br>";
echo "• No Hostinger, geralmente o host correto é 'localhost' quando o script roda no mesmo servidor<br>";
echo "• Se nenhum host funcionar, verifique as credenciais no painel Hostinger<br>";
echo "• Alguns provedores bloqueiam conexões externas por segurança<br>";
echo "• O erro 'Access denied' pode indicar senha incorreta ou usuário inexistente<br>";
echo "</div>";

echo "<br><div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
echo "<strong>⚠️ Se todos os testes falharem:</strong><br>";
echo "1. Acesse o painel Hostinger → Databases<br>";
echo "2. Verifique se o usuário 'u690889028_mayconwender' existe<br>";
echo "3. Confirme a senha no painel<br>";
echo "4. Verifique se há restrições de IP<br>";
echo "5. Tente criar um novo usuário de banco de dados<br>";
echo "</div>";
?>