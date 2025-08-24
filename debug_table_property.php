<?php
/**
 * Debug da propriedade table da classe Agendamento
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/agendamento.php';

echo "<h2>🔍 Debug da Propriedade Table</h2>";

// 1. Criar instância e verificar propriedades
echo "<h3>1. Verificando propriedades da classe Agendamento</h3>";
$agendamento = new Agendamento();

// Usar reflexão para acessar propriedades privadas
$reflection = new ReflectionClass($agendamento);

echo "<h4>Propriedades da classe:</h4>";
$properties = $reflection->getProperties();
foreach ($properties as $property) {
    $property->setAccessible(true);
    $value = $property->getValue($agendamento);
    $type = gettype($value);
    
    if ($type === 'object' && $value instanceof PDO) {
        echo "<p><strong>{$property->getName()}:</strong> PDO Connection Object</p>";
        
        // Verificar qual banco está conectado
        try {
            $stmt = $value->query("SELECT DATABASE() as db_name");
            $result = $stmt->fetch();
            echo "<p class='info'>  └─ Banco conectado: {$result['db_name']}</p>";
        } catch (Exception $e) {
            echo "<p class='error'>  └─ Erro ao verificar banco: {$e->getMessage()}</p>";
        }
    } else {
        echo "<p><strong>{$property->getName()}:</strong> {$value} ({$type})</p>";
    }
}

echo "<hr>";

// 2. Testar query direta com a conexão da instância
echo "<h3>2. Testando query direta com conexão da instância</h3>";
$connProperty = $reflection->getProperty('conn');
$connProperty->setAccessible(true);
$conn = $connProperty->getValue($agendamento);

$tableProperty = $reflection->getProperty('table');
$tableProperty->setAccessible(true);
$table = $tableProperty->getValue($agendamento);

echo "<p class='info'>Tabela: {$table}</p>";

if ($conn) {
    try {
        // Verificar se a tabela existe
        $stmt = $conn->query("SHOW TABLES LIKE '{$table}'");
        $tableExists = $stmt->fetch();
        
        if ($tableExists) {
            echo "<p class='success'>✅ Tabela '{$table}' existe</p>";
            
            // Verificar estrutura da tabela
            $stmt = $conn->query("DESCRIBE {$table}");
            $columns = $stmt->fetchAll();
            
            echo "<h4>Colunas da tabela '{$table}':</h4>";
            $hasStatus = false;
            foreach ($columns as $column) {
                echo "<p>- {$column['Field']} ({$column['Type']})</p>";
                if ($column['Field'] === 'status') {
                    $hasStatus = true;
                }
            }
            
            if ($hasStatus) {
                echo "<p class='success'>✅ Coluna 'status' encontrada</p>";
                
                // Testar query problemática
                echo "<h4>Testando query problemática:</h4>";
                $sql = "SELECT hora FROM {$table} 
                        WHERE id_profissional = :id_profissional 
                        AND data = :data 
                        AND status != 'cancelado' 
                        ORDER BY hora";
                
                echo "<p class='info'>SQL: {$sql}</p>";
                
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':id_profissional', 1);
                $stmt->bindValue(':data', '2025-08-23');
                $stmt->execute();
                
                $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "<p class='success'>✅ Query executada com sucesso! Resultados: " . count($result) . "</p>";
                
            } else {
                echo "<p class='error'>❌ Coluna 'status' NÃO encontrada</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Tabela '{$table}' não existe</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao verificar tabela: {$e->getMessage()}</p>";
    }
} else {
    echo "<p class='error'>❌ Conexão não disponível</p>";
}

echo "<hr>";

// 3. Comparar com conexão direta
echo "<h3>3. Comparando com conexão direta</h3>";
$directConn = getConnection();

if ($directConn) {
    try {
        $stmt = $directConn->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "<p class='info'>Conexão direta - Banco: {$result['db_name']}</p>";
        
        // Verificar se são a mesma conexão
        if ($conn === $directConn) {
            echo "<p class='success'>✅ Mesma instância de conexão</p>";
        } else {
            echo "<p class='warning'>⚠️ Instâncias de conexão diferentes</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro na conexão direta: {$e->getMessage()}</p>";
    }
}

echo "<hr>";
echo "<p><strong>Debug da propriedade table concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
.warning { color: orange; }
</style>