<?php
/**
 * Verificar se existe banco local com mesma estrutura
 */

echo "<h2>🔍 Verificando Banco Local</h2>";

// 1. Testar conexão local direta
echo "<h3>1. Testando conexão local direta</h3>";
try {
    $host = 'localhost';
    $db_name = 'u690889028_cortefacil';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $connLocal = new PDO($dsn, $username, $password, $options);
    echo "<p class='success'>✅ Conexão local direta funcionou</p>";
    
    // Verificar qual banco está conectado
    $stmt = $connLocal->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p class='info'>📊 Banco conectado: {$result['db_name']}</p>";
    
    // Verificar se a coluna status existe nesta conexão
    $stmt = $connLocal->query("DESCRIBE agendamentos");
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
        echo "<p class='error'>❌ Coluna 'status' NÃO encontrada!</p>";
        
        // Listar todas as colunas
        echo "<h4>Colunas existentes na tabela agendamentos:</h4>";
        foreach ($columns as $column) {
            echo "<p>- {$column['Field']} ({$column['Type']})</p>";
        }
    }
    
    // Testar query com status se existir
    if ($hasStatus) {
        echo "<h4>Testando query com status:</h4>";
        $stmt = $connLocal->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE status != 'cancelado'");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p class='success'>✅ Query com status funcionou! Total: {$result['total']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão local: {$e->getMessage()}</p>";
    
    // Tentar sem especificar o banco
    echo "<h4>Tentando conexão sem especificar banco:</h4>";
    try {
        $dsn = "mysql:host=localhost;charset=utf8mb4";
        $connLocalNoDb = new PDO($dsn, 'root', '', $options);
        echo "<p class='success'>✅ Conexão local sem banco funcionou</p>";
        
        // Listar bancos disponíveis
        $stmt = $connLocalNoDb->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h4>Bancos disponíveis:</h4>";
        foreach ($databases as $db) {
            echo "<p>- {$db}</p>";
        }
        
    } catch (Exception $e2) {
        echo "<p class='error'>❌ Erro na conexão local sem banco: {$e2->getMessage()}</p>";
    }
}

echo "<hr>";

// 2. Verificar ambiente detectado pela classe Database
echo "<h3>2. Verificando detecção de ambiente</h3>";
require_once __DIR__ . '/config/database.php';

$database = Database::getInstance();
$debugInfo = $database->getDebugInfo();

echo "<h4>Informações de debug:</h4>";
foreach ($debugInfo as $key => $value) {
    echo "<p><strong>{$key}:</strong> {$value}</p>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>