<?php
/**
 * Teste final com localhost
 * Verifica se a conexão funciona com host = localhost
 */

echo "<h2>🏠 Teste Final - Localhost</h2>";

// Credenciais com localhost
$host = 'localhost';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'vpH=yoc?0lL';

echo "<div style='background: #e7f3ff; padding: 10px; border: 1px solid #b3d9ff; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>📋 Credenciais finais:</strong><br>";
echo "Host: $host<br>";
echo "Database: $db_name<br>";
echo "Username: $username<br>";
echo "Password: " . str_repeat('*', strlen($password)) . "<br>";
echo "</div>";

echo "<h3>🔍 Teste de Conexão:</h3>";

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: green; margin: 0 0 10px 0;'>✅ CONEXÃO ESTABELECIDA COM SUCESSO!</h4>";
    
    // Informações do servidor
    $stmt = $pdo->query("SELECT DATABASE() as current_db, USER() as current_user, VERSION() as mysql_version, NOW() as current_time");
    $info = $stmt->fetch();
    
    echo "<strong>Informações do servidor:</strong><br>";
    echo "• Database atual: " . $info['current_db'] . "<br>";
    echo "• Usuário conectado: " . $info['current_user'] . "<br>";
    echo "• Versão MySQL: " . $info['mysql_version'] . "<br>";
    echo "• Data/Hora do servidor: " . $info['current_time'] . "<br><br>";
    
    // Listar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>Tabelas no banco (" . count($tables) . " encontradas):</strong><br>";
    if (count($tables) > 0) {
        echo "• " . implode("<br>• ", $tables) . "<br><br>";
    } else {
        echo "• Nenhuma tabela encontrada<br><br>";
    }
    
    // Teste da classe Database
    echo "<strong>Teste da classe Database:</strong><br>";
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "• ✅ Classe Database funcionando corretamente<br>";
        $stmt = $conn->query("SELECT 'Teste OK' as resultado");
        $result = $stmt->fetch();
        echo "• ✅ Query de teste: " . $result['resultado'] . "<br>";
    } else {
        echo "• ❌ Erro na classe Database<br>";
    }
    
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #0c5460; margin: 0 0 10px 0;'>🎉 PROBLEMA RESOLVIDO!</h4>";
    echo "<strong>O host correto é 'localhost'.</strong><br>";
    echo "O arquivo database.php já foi atualizado com as configurações corretas.<br><br>";
    echo "<strong>Próximos passos:</strong><br>";
    echo "1. ✅ Teste o login no sistema - deve funcionar agora<br>";
    echo "2. ✅ Acesse o dashboard após o login<br>";
    echo "3. 🗑️ Remova os arquivos de teste (test_*.php)<br>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #721c24; margin: 0 0 10px 0;'>❌ ERRO DE CONEXÃO</h4>";
    echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<strong>⚠️ O problema persiste. Possíveis causas:</strong><br>";
    echo "1. Credenciais incorretas no painel Hostinger<br>";
    echo "2. Usuário de banco não existe ou foi desabilitado<br>";
    echo "3. Senha foi alterada no painel mas não atualizada aqui<br>";
    echo "4. Banco de dados não existe<br><br>";
    
    echo "<strong>🔧 Ações recomendadas:</strong><br>";
    echo "1. Acesse o painel Hostinger → Databases<br>";
    echo "2. Verifique se existe o usuário 'u690889028_mayconwender'<br>";
    echo "3. Confirme a senha atual<br>";
    echo "4. Verifique se o banco 'u690889028_cortefacil' existe<br>";
    echo "5. Se necessário, crie um novo usuário de banco<br>";
    echo "</div>";
}
?>