<?php
require_once 'config/database.php';

echo "<h2>Debug - Estrutura das Tabelas</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        die("Erro: Não foi possível conectar ao banco de dados.");
    }
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Verificar se a tabela saloes existe
    echo "<h3>1. Verificando se a tabela 'saloes' existe:</h3>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'saloes'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p style='color: green;'>✅ Tabela 'saloes' existe</p>";
        
        // Mostrar estrutura da tabela saloes
        echo "<h3>2. Estrutura da tabela 'saloes':</h3>";
        $stmt = $conn->prepare("DESCRIBE saloes");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'saloes' NÃO existe</p>";
    }
    
    // Verificar se a tabela usuarios existe
    echo "<h3>3. Verificando se a tabela 'usuarios' existe:</h3>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'usuarios'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p style='color: green;'>✅ Tabela 'usuarios' existe</p>";
        
        // Verificar se existe um usuário com ID específico
        echo "<h3>4. Verificando usuários na tabela:</h3>";
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>Total de usuários: {$result['total']}</p>";
        
        // Mostrar alguns usuários
        $stmt = $conn->prepare("SELECT id, nome, email, tipo FROM usuarios LIMIT 5");
        $stmt->execute();
        $usuarios = $stmt->fetchAll();
        
        if ($usuarios) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
            foreach ($usuarios as $usuario) {
                echo "<tr>";
                echo "<td>{$usuario['id']}</td>";
                echo "<td>{$usuario['nome']}</td>";
                echo "<td>{$usuario['email']}</td>";
                echo "<td>{$usuario['tipo']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Tabela 'usuarios' NÃO existe</p>";
    }
    
    // Verificar chaves estrangeiras
    echo "<h3>5. Verificando chaves estrangeiras da tabela 'saloes':</h3>";
    $stmt = $conn->prepare("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'saloes' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute();
    $foreign_keys = $stmt->fetchAll();
    
    if ($foreign_keys) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Constraint</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
        foreach ($foreign_keys as $fk) {
            echo "<tr>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_COLUMN_NAME']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma chave estrangeira encontrada na tabela 'saloes'</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>