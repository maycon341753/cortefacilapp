<?php
/**
 * Criar tabela horarios_funcionamento no banco local
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Criando Tabela horarios_funcionamento</h2>";
    echo "<style>body{font-family:Arial;padding:20px;}</style>";
    
    // Verificar se a tabela já existe
    $stmt = $conn->query("SHOW TABLES LIKE 'horarios_funcionamento'");
    $existe = $stmt->rowCount() > 0;
    
    if ($existe) {
        echo "<p style='color: orange;'>⚠️ Tabela 'horarios_funcionamento' já existe!</p>";
    } else {
        // SQL para criar a tabela
        $sql = "
            CREATE TABLE horarios_funcionamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_salao INT NOT NULL,
                dia_semana TINYINT NOT NULL COMMENT '0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado',
                hora_abertura TIME NOT NULL,
                hora_fechamento TIME NOT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
                UNIQUE KEY unique_salao_dia (id_salao, dia_semana)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Tabela 'horarios_funcionamento' criada com sucesso!</p>";
    }
    
    // Mostrar estrutura da tabela
    echo "<h3>Estrutura da tabela horarios_funcionamento:</h3>";
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
    
    echo "<h3>✅ Tabela pronta para uso!</h3>";
    echo "<p>Agora você pode cadastrar horários de funcionamento para os salões.</p>";
    
    echo "<h4>Exemplo de uso:</h4>";
    echo "<ul>";
    echo "<li><strong>dia_semana:</strong> 0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado</li>";
    echo "<li><strong>hora_abertura:</strong> Formato TIME (ex: '08:00:00')</li>";
    echo "<li><strong>hora_fechamento:</strong> Formato TIME (ex: '18:00:00')</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<hr>
<p><a href="cadastro.php?tipo=parceiro">← Voltar para cadastro de parceiro</a></p>