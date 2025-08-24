<?php
/**
 * Verificar se a tabela horarios_funcionamento existe
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Verificação da Tabela horarios_funcionamento</h2>";
    echo "<style>body{font-family:Arial;padding:20px;}</style>";
    
    // Verificar se a tabela existe
    $stmt = $conn->query("SHOW TABLES LIKE 'horarios_funcionamento'");
    $existe = $stmt->rowCount() > 0;
    
    if ($existe) {
        echo "<p style='color: green;'>✅ Tabela 'horarios_funcionamento' existe!</p>";
        
        // Mostrar estrutura
        echo "<h3>Estrutura da tabela:</h3>";
        $stmt = $conn->query("DESCRIBE horarios_funcionamento");
        $estrutura = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($estrutura as $campo) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($campo['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($campo['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar dados existentes
        $stmt = $conn->query("SELECT * FROM horarios_funcionamento");
        $dados = $stmt->fetchAll();
        
        if (!empty($dados)) {
            echo "<h3>Dados existentes (" . count($dados) . " registros):</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>ID Salão</th><th>Dia Semana</th><th>Hora Abertura</th><th>Hora Fechamento</th><th>Ativo</th></tr>";
            foreach ($dados as $registro) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($registro['id']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['id_salao']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['dia_semana']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['hora_abertura']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['hora_fechamento']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['ativo'] ? 'Sim' : 'Não') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhum horário cadastrado ainda.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Tabela 'horarios_funcionamento' NÃO existe!</p>";
        echo "<p>Será necessário criar a tabela.</p>";
        
        // SQL para criar a tabela
        echo "<h3>SQL para criar a tabela:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo "CREATE TABLE horarios_funcionamento (\n";
        echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        echo "    id_salao INT NOT NULL,\n";
        echo "    dia_semana TINYINT NOT NULL COMMENT '0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado',\n";
        echo "    hora_abertura TIME NOT NULL,\n";
        echo "    hora_fechamento TIME NOT NULL,\n";
        echo "    ativo BOOLEAN DEFAULT TRUE,\n";
        echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        echo "    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
        echo "    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,\n";
        echo "    UNIQUE KEY unique_salao_dia (id_salao, dia_semana)\n";
        echo ");";
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>