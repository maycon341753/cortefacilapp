<?php
/**
 * Teste de debug específico para Hostinger
 * Verifica diferentes aspectos da conexão
 */

echo "<h2>🔍 Debug de Conexão Hostinger</h2>";

// Credenciais atuais
$host = 'srv488.hstgr.io';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'vpH=yoc?0lL';

echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px 0; border-left: 4px solid #007acc;'>";
echo "<strong>📋 Credenciais em uso:</strong><br>";
echo "Host: $host<br>";
echo "Database: $db_name<br>";
echo "Username: $username<br>";
echo "Password: " . str_repeat('*', strlen($password)) . "<br>";
echo "</div>";

// Teste 1: Verificar se extensões PDO estão disponíveis
echo "<h3>1️⃣ Verificação de Extensões PHP</h3>";
echo "PDO disponível: " . (extension_loaded('pdo') ? '✅ Sim' : '❌ Não') . "<br>";
echo "PDO MySQL disponível: " . (extension_loaded('pdo_mysql') ? '✅ Sim' : '❌ Não') . "<br>";
echo "MySQLi disponível: " . (extension_loaded('mysqli') ? '✅ Sim' : '❌ Não') . "<br><br>";

// Teste 2: Teste de resolução DNS
echo "<h3>2️⃣ Teste de Resolução DNS</h3>";
$ip = gethostbyname($host);
echo "IP do host $host: $ip<br>";
if ($ip === $host) {
    echo "❌ Falha na resolução DNS<br>";
} else {
    echo "✅ DNS resolvido com sucesso<br>";
}
echo "<br>";

// Teste 3: Teste de conexão TCP
echo "<h3>3️⃣ Teste de Conexão TCP (Porta 3306)</h3>";
$connection = @fsockopen($host, 3306, $errno, $errstr, 10);
if ($connection) {
    echo "✅ Conexão TCP estabelecida com sucesso<br>";
    fclose($connection);
} else {
    echo "❌ Falha na conexão TCP: $errstr ($errno)<br>";
}
echo "<br>";

// Teste 4: Teste com diferentes configurações PDO
echo "<h3>4️⃣ Teste de Conexão PDO</h3>";

// Configuração básica
echo "<strong>Teste A - Configuração Básica:</strong><br>";
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    echo "✅ Conexão básica estabelecida<br>";
    $pdo = null;
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Configuração com porta explícita
echo "<br><strong>Teste B - Com Porta Explícita (3306):</strong><br>";
try {
    $dsn = "mysql:host=$host;port=3306;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    echo "✅ Conexão com porta explícita estabelecida<br>";
    $pdo = null;
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Configuração com timeout
echo "<br><strong>Teste C - Com Timeout:</strong><br>";
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✅ Conexão com timeout estabelecida<br>";
    $pdo = null;
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Teste 5: Informações do ambiente
echo "<br><h3>5️⃣ Informações do Ambiente</h3>";
echo "Versão PHP: " . phpversion() . "<br>";
echo "Sistema Operacional: " . php_uname() . "<br>";
echo "Timezone: " . date_default_timezone_get() . "<br>";
echo "Data/Hora atual: " . date('Y-m-d H:i:s') . "<br>";

echo "<br><div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<strong>💡 Próximos passos se todos os testes falharem:</strong><br>";
echo "1. Verifique no painel Hostinger se o usuário '$username' existe<br>";
echo "2. Confirme se a senha está correta no painel<br>";
echo "3. Verifique se o IP do seu servidor está na lista de IPs permitidos<br>";
echo "4. Teste com 'localhost' como host se estiver no mesmo servidor<br>";
echo "</div>";
?>