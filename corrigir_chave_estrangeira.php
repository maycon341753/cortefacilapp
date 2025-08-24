<?php
require_once 'config/database.php';

echo "<h2>Correção da Chave Estrangeira da Tabela Salões</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        die("Erro: Não foi possível conectar ao banco de dados.");
    }
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Primeiro, vamos verificar as chaves estrangeiras existentes
    echo "<h3>1. Verificando chaves estrangeiras existentes:</h3>";
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
        
        // Remover chaves estrangeiras antigas
        echo "<h3>2. Removendo chaves estrangeiras antigas:</h3>";
        foreach ($foreign_keys as $fk) {
            if ($fk['COLUMN_NAME'] === 'usuario_id') {
                echo "<p>Removendo constraint: {$fk['CONSTRAINT_NAME']}</p>";
                $sql = "ALTER TABLE saloes DROP FOREIGN KEY {$fk['CONSTRAINT_NAME']}";
                if ($conn->exec($sql) !== false) {
                    echo "<p style='color: green;'>✅ Constraint {$fk['CONSTRAINT_NAME']} removida!</p>";
                } else {
                    echo "<p style='color: red;'>❌ Erro ao remover constraint {$fk['CONSTRAINT_NAME']}</p>";
                }
            }
        }
    } else {
        echo "<p>Nenhuma chave estrangeira encontrada.</p>";
    }
    
    // Verificar se a coluna usuario_id existe e removê-la
    echo "<h3>3. Verificando estrutura da tabela:</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $has_usuario_id = false;
    $has_id_dono = false;
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'usuario_id') {
            $has_usuario_id = true;
        }
        if ($column['Field'] === 'id_dono') {
            $has_id_dono = true;
        }
    }
    echo "</table>";
    
    // Remover coluna usuario_id se existir
    if ($has_usuario_id) {
        echo "<h3>4. Removendo coluna 'usuario_id':</h3>";
        $sql = "ALTER TABLE saloes DROP COLUMN usuario_id";
        if ($conn->exec($sql) !== false) {
            echo "<p style='color: green;'>✅ Coluna 'usuario_id' removida!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao remover coluna 'usuario_id'</p>";
        }
    }
    
    // Adicionar coluna id_dono se não existir
    if (!$has_id_dono) {
        echo "<h3>5. Adicionando coluna 'id_dono':</h3>";
        $sql = "ALTER TABLE saloes ADD COLUMN id_dono INT NOT NULL AFTER id";
        if ($conn->exec($sql) !== false) {
            echo "<p style='color: green;'>✅ Coluna 'id_dono' adicionada!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao adicionar coluna 'id_dono'</p>";
        }
    }
    
    // Adicionar chave estrangeira correta
    echo "<h3>6. Adicionando chave estrangeira correta:</h3>";
    $sql = "ALTER TABLE saloes ADD CONSTRAINT fk_saloes_id_dono FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE";
    if ($conn->exec($sql) !== false) {
        echo "<p style='color: green;'>✅ Chave estrangeira correta adicionada!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chave estrangeira pode já existir ou houve erro</p>";
    }
    
    echo "<h3>✅ Correção concluída!</h3>";
    echo "<p><a href='teste_cadastro_parceiro.php'>Testar cadastro de parceiro novamente</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>