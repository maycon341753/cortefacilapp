<?php
// Teste de credenciais - Diagnóstico completo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Diagnóstico de Conexão - Hostinger</h2>";

$host = 'srv488.hstgr.io';
$db_name = 'u690889028_cortefacil';

// Teste com diferentes senhas também
$credentials = [
    ['username' => 'u690889028', 'password' => 'Brava1997?'],
    ['username' => 'u690889028', 'password' => 'brava1997?'],
    ['username' => 'u690889028', 'password' => 'Brava1997'],
    ['username' => 'u690889028_cortefacil', 'password' => 'Brava1997?'],
    ['username' => 'u690889028_admin', 'password' => 'Brava1997?']
];

echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>⚠️ IMPORTANTE:</strong> Se todas as combinações falharem, você precisa:<br>";
echo "1. Acessar o painel do Hostinger<br>";
echo "2. Ir em <strong>Websites → Manage → Databases Management</strong><br>";
echo "3. Copiar as credenciais exatas da seção 'List of Current MySQL Databases And Users'<br>";
echo "4. Verificar se a senha está correta<br>";
echo "</div>";

foreach ($credentials as $index => $cred) {
    $username = $cred['username'];
    $password = $cred['password'];
    
    echo "<h3>Teste #" . ($index + 1) . ": $username</h3>";
    echo "Senha: " . str_repeat('*', strlen($password)) . "<br>";
    
    try {
        $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10
        ]);
        
        echo "✅ <strong style='color: green;'>SUCESSO!</strong><br>";
        echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>CREDENCIAIS CORRETAS:</strong><br>";
        echo "Host: $host<br>";
        echo "Database: $db_name<br>";
        echo "Username: $username<br>";
        echo "Password: $password<br>";
        echo "</div>";
        
        // Teste uma query
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tabelas encontradas: " . implode(', ', $tables) . "<br>";
        
        break; // Para no primeiro sucesso
        
    } catch (PDOException $e) {
        echo "❌ <strong style='color: red;'>FALHOU</strong><br>";
        echo "Código do erro: " . $e->getCode() . "<br>";
        echo "Mensagem: " . $e->getMessage() . "<br><br>";
        
        // Análise específica do erro
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            echo "<span style='color: orange;'>→ Erro de credenciais (username/password incorretos)</span><br><br>";
        } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
            echo "<span style='color: orange;'>→ Nome do banco de dados incorreto</span><br><br>";
        } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
            echo "<span style='color: orange;'>→ Problema de conexão com o servidor</span><br><br>";
        }
    }
}

echo "<hr>";
echo "<h3>Próximos Passos:</h3>";
echo "<ol>";
echo "<li><strong>Se nenhuma combinação funcionou:</strong> As credenciais estão incorretas</li>";
echo "<li><strong>Acesse o painel Hostinger:</strong> hpanel.hostinger.com</li>";
echo "<li><strong>Vá para:</strong> Websites → Manage → Databases Management</li>";
echo "<li><strong>Copie as credenciais exatas</strong> da seção 'List of Current MySQL Databases And Users'</li>";
echo "<li><strong>Atualize o arquivo database.php</strong> com as credenciais corretas</li>";
echo "</ol>";

echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>🔍 DICA:</strong> O erro 'Access denied' indica que o username ou password estão incorretos. ";
echo "Verifique se não há espaços extras ou caracteres especiais nas credenciais.";  
echo "</div>";
?>