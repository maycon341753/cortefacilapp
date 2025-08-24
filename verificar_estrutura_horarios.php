<?php
require_once 'config/database.php';

try {
    $database = Database::getInstance();
    $pdo = $database->connect();
    
    echo "<h2>Estrutura da tabela 'horarios':</h2>";
    
    $stmt = $pdo->query("DESCRIBE horarios");
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
    
    echo "<h2>Dados na tabela 'horarios':</h2>";
    $stmt = $pdo->query("SELECT * FROM horarios LIMIT 5");
    echo "<table border='1'>";
    
    $first = true;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($first) {
            echo "<tr>";
            foreach(array_keys($row) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $first = false;
        }
        
        echo "<tr>";
        foreach($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch(Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>