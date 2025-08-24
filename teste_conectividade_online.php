<?php
/**
 * Teste de conectividade com servidor online
 */

echo "<h2>🔍 Teste de Conectividade Online</h2>";

// 1. Testar conectividade básica
echo "<h3>1. Testando conectividade básica</h3>";
$host = 'srv486.hstgr.io';
$port = 3306;

echo "<p class='info'>Testando conexão com {$host}:{$port}...</p>";

$connection = @fsockopen($host, $port, $errno, $errstr, 10);
if ($connection) {
    echo "<p class='success'>✅ Conectividade TCP estabelecida</p>";
    fclose($connection);
} else {
    echo "<p class='error'>❌ Falha na conectividade TCP: {$errstr} ({$errno})</p>";
}

// 2. Testar resolução DNS
echo "<h3>2. Testando resolução DNS</h3>";
$ip = gethostbyname($host);
if ($ip !== $host) {
    echo "<p class='success'>✅ DNS resolvido: {$host} -> {$ip}</p>";
} else {
    echo "<p class='error'>❌ Falha na resolução DNS</p>";
}

// 3. Testar conexão MySQL com timeout maior
echo "<h3>3. Testando conexão MySQL com timeout maior</h3>";
try {
    $dsn = "mysql:host={$host};dbname=u690889028_cortefacil;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30 // Timeout maior
    ];
    
    echo "<p class='info'>Tentando conexão com timeout de 30 segundos...</p>";
    $start = microtime(true);
    
    $pdo = new PDO($dsn, 'u690889028_mayconwender', 'Maycon341753', $options);
    
    $end = microtime(true);
    $duration = round(($end - $start) * 1000, 2);
    
    echo "<p class='success'>✅ Conexão MySQL estabelecida em {$duration}ms</p>";
    
    // Testar query simples
    $stmt = $pdo->query("SELECT DATABASE() as db_name, NOW() as server_time");
    $result = $stmt->fetch();
    echo "<p class='success'>✅ Query executada - Banco: {$result['db_name']}, Hora: {$result['server_time']}</p>";
    
    // Verificar tabela agendamentos
    $stmt = $pdo->query("DESCRIBE agendamentos");
    $columns = $stmt->fetchAll();
    $hasStatus = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'status') {
            $hasStatus = true;
            echo "<p class='success'>✅ Coluna 'status' encontrada: {$column['Type']}</p>";
            break;
        }
    }
    
    if (!$hasStatus) {
        echo "<p class='error'>❌ Coluna 'status' não encontrada</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão MySQL: {$e->getMessage()}</p>";
    echo "<p class='error'>Código: {$e->getCode()}</p>";
}

// 4. Verificar configurações de rede
echo "<h3>4. Verificações de rede</h3>";

// Verificar se está atrás de firewall/proxy
echo "<p class='info'>User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</p>";
echo "<p class='info'>Remote Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "</p>";
echo "<p class='info'>Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</p>";

// 5. Testar com diferentes configurações
echo "<h3>5. Testando configurações alternativas</h3>";

// Tentar sem SSL
try {
    $dsn_no_ssl = "mysql:host={$host};dbname=u690889028_cortefacil;charset=utf8mb4";
    $options_no_ssl = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 15,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
    ];
    
    $pdo_no_ssl = new PDO($dsn_no_ssl, 'u690889028_mayconwender', 'Maycon341753', $options_no_ssl);
    echo "<p class='success'>✅ Conexão sem SSL funcionou</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Conexão sem SSL falhou: {$e->getMessage()}</p>";
}

echo "<hr>";
echo "<p><strong>Teste de conectividade concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>