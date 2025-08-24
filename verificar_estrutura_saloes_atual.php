<?php
/**
 * Script para verificar a estrutura atual da tabela saloes
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Estrutura da tabela saloes:</h2>";
    
    // Verificar estrutura da tabela
    $stmt = $conn->query("DESCRIBE saloes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Chaves estrangeiras:</h3>";
    
    // Verificar chaves estrangeiras
    $stmt = $conn->query("SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'saloes' 
    AND TABLE_SCHEMA = DATABASE()
    AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    $foreign_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($foreign_keys)) {
        echo "<p>Nenhuma chave estrangeira encontrada.</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>Nome da Constraint</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
        
        foreach ($foreign_keys as $fk) {
            echo "<tr>";
            echo "<td>" . $fk['CONSTRAINT_NAME'] . "</td>";
            echo "<td>" . $fk['COLUMN_NAME'] . "</td>";
            echo "<td>" . $fk['REFERENCED_TABLE_NAME'] . "</td>";
            echo "<td>" . $fk['REFERENCED_COLUMN_NAME'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Dados dos parceiros sem salão:</h3>";
    
    // Verificar parceiros sem salão
    $stmt = $conn->query("SELECT u.id, u.nome, u.email 
                         FROM usuarios u 
                         LEFT JOIN saloes s ON u.id = s.usuario_id 
                         WHERE u.tipo = 'Parceiro' AND s.id IS NULL");
    
    $parceiros_sem_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($parceiros_sem_salao)) {
        echo "<p>Todos os parceiros já têm salão cadastrado.</p>";
    } else {
        echo "<p>Parceiros sem salão cadastrado: " . count($parceiros_sem_salao) . "</p>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th></tr>";
        
        foreach ($parceiros_sem_salao as $parceiro) {
            echo "<tr>";
            echo "<td>" . $parceiro['id'] . "</td>";
            echo "<td>" . $parceiro['nome'] . "</td>";
            echo "<td>" . $parceiro['email'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>