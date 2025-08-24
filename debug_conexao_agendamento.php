<?php
/**
 * Debug da conexão da classe Agendamento
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/agendamento.php';

echo "<h2>🔍 Debug da Conexão - Classe Agendamento</h2>";

// 1. Testar função getConnection diretamente
echo "<h3>1. Testando função getConnection()</h3>";
$conn = getConnection();
if ($conn) {
    echo "<p class='success'>✅ getConnection() funcionou</p>";
    
    // Verificar qual banco está conectado
    try {
        $stmt = $conn->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "<p class='info'>📊 Banco conectado: {$result['db_name']}</p>";
        
        // Verificar se a coluna status existe nesta conexão
        $stmt = $conn->query("DESCRIBE agendamentos");
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
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao verificar banco: {$e->getMessage()}</p>";
    }
} else {
    echo "<p class='error'>❌ getConnection() falhou</p>";
}

echo "<hr>";

// 2. Testar Database::getInstance diretamente
echo "<h3>2. Testando Database::getInstance()</h3>";
$database = Database::getInstance();
$conn2 = $database->connect();
if ($conn2) {
    echo "<p class='success'>✅ Database::getInstance()->connect() funcionou</p>";
    
    // Verificar qual banco está conectado
    try {
        $stmt = $conn2->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "<p class='info'>📊 Banco conectado: {$result['db_name']}</p>";
        
        // Verificar se a coluna status existe nesta conexão
        $stmt = $conn2->query("DESCRIBE agendamentos");
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
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao verificar banco: {$e->getMessage()}</p>";
    }
} else {
    echo "<p class='error'>❌ Database::getInstance()->connect() falhou</p>";
}

echo "<hr>";

// 3. Forçar conexão online diretamente
echo "<h3>3. Forçando conexão online diretamente</h3>";
try {
    $host = 'srv486.hstgr.io';
    $db_name = 'u690889028_cortefacil';
    $username = 'u690889028_mayconwender';
    $password = 'Maycon341753';
    
    $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $connOnline = new PDO($dsn, $username, $password, $options);
    echo "<p class='success'>✅ Conexão online direta funcionou</p>";
    
    // Verificar qual banco está conectado
    $stmt = $connOnline->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p class='info'>📊 Banco conectado: {$result['db_name']}</p>";
    
    // Verificar se a coluna status existe nesta conexão
    $stmt = $connOnline->query("DESCRIBE agendamentos");
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
    }
    
    // Testar query com status
    echo "<h4>Testando query com status:</h4>";
    $stmt = $connOnline->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE status != 'cancelado'");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p class='success'>✅ Query com status funcionou! Total: {$result['total']}</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão online direta: {$e->getMessage()}</p>";
}

echo "<hr>";

// 4. Testar classe Agendamento com conexão forçada
echo "<h3>4. Testando classe Agendamento com conexão forçada</h3>";
try {
    // Criar instância da classe Agendamento passando a conexão online
    $agendamento = new Agendamento($connOnline);
    echo "<p class='success'>✅ Classe Agendamento instanciada com conexão online</p>";
    
    // Testar listarHorariosOcupados
    $horariosOcupados = $agendamento->listarHorariosOcupados(1, '2025-08-23');
    echo "<p class='success'>✅ listarHorariosOcupados funcionou! Horários: " . count($horariosOcupados) . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na classe Agendamento: {$e->getMessage()}</p>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>