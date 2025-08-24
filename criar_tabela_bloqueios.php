<?php
require_once 'config/database.php';

echo "<h2>Criando Tabela de Bloqueios Temporários</h2>";

try {
    $database = Database::getInstance();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception("Erro na conexão com banco de dados");
    }
    
    // Criar tabela bloqueios_temporarios
    $sql = "
        CREATE TABLE IF NOT EXISTS bloqueios_temporarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_profissional INT NOT NULL,
            id_salao INT NOT NULL,
            data_bloqueio DATE NOT NULL,
            hora_inicio TIME NOT NULL,
            hora_fim TIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_profissional_data (id_profissional, data_bloqueio),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela 'bloqueios_temporarios' criada com sucesso!</p>";
    
    // Verificar estrutura
    $stmt = $pdo->query("DESCRIBE bloqueios_temporarios");
    echo "<h3>Estrutura da tabela:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color: green;'><strong>✅ Tabela criada e pronta para uso!</strong></p>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>