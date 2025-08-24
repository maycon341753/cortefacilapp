<?php
/**
 * Verificar se a tabela bloqueios_temporarios existe
 */

require_once 'config/database.php';

echo "<h2>🔍 Verificação da Tabela bloqueios_temporarios</h2>";

// Conectar ao banco
$database = Database::getInstance();
$conn = $database->connect();

echo "<h3>1. Verificando se a tabela bloqueios_temporarios existe</h3>";

try {
    // Verificar se a tabela existe
    $stmt = $conn->query("SHOW TABLES LIKE 'bloqueios_temporarios'");
    $tabela_existe = $stmt->rowCount() > 0;
    
    if ($tabela_existe) {
        echo "<p class='success'>✅ Tabela bloqueios_temporarios existe</p>";
        
        // Verificar estrutura da tabela
        echo "<h3>2. Estrutura da tabela bloqueios_temporarios</h3>";
        $stmt = $conn->query("DESCRIBE bloqueios_temporarios");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>{$coluna['Field']}</td>";
            echo "<td>{$coluna['Type']}</td>";
            echo "<td>{$coluna['Null']}</td>";
            echo "<td>{$coluna['Key']}</td>";
            echo "<td>{$coluna['Default']}</td>";
            echo "<td>{$coluna['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar registros na tabela
        echo "<h3>3. Registros na tabela bloqueios_temporarios</h3>";
        $stmt = $conn->query("SELECT COUNT(*) FROM bloqueios_temporarios");
        $total_registros = $stmt->fetchColumn();
        echo "<p class='info'>📊 Total de registros: {$total_registros}</p>";
        
        if ($total_registros > 0) {
            $stmt = $conn->query("SELECT * FROM bloqueios_temporarios ORDER BY created_at DESC LIMIT 5");
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p class='info'>🔍 Últimos 5 registros:</p>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Profissional</th><th>Data</th><th>Hora</th><th>Session ID</th><th>Expira em</th></tr>";
            foreach ($registros as $registro) {
                echo "<tr>";
                echo "<td>{$registro['id']}</td>";
                echo "<td>{$registro['id_profissional']}</td>";
                echo "<td>{$registro['data']}</td>";
                echo "<td>{$registro['hora']}</td>";
                echo "<td>{$registro['session_id']}</td>";
                echo "<td>{$registro['expires_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p class='error'>❌ Tabela bloqueios_temporarios NÃO existe</p>";
        echo "<p class='info'>💡 Isso explica o erro no método gerarHorariosDisponiveisComBloqueios</p>";
        
        echo "<h3>4. Criando tabela bloqueios_temporarios</h3>";
        
        $sql_create = "
            CREATE TABLE bloqueios_temporarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_profissional INT NOT NULL,
                data DATE NOT NULL,
                hora TIME NOT NULL,
                session_id VARCHAR(255) NOT NULL,
                ip_cliente VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                INDEX idx_profissional_data (id_profissional, data),
                INDEX idx_expires (expires_at),
                UNIQUE KEY unique_horario_ativo (id_profissional, data, hora, session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        try {
            $conn->exec($sql_create);
            echo "<p class='success'>✅ Tabela bloqueios_temporarios criada com sucesso!</p>";
            
            // Verificar se foi criada
            $stmt = $conn->query("SHOW TABLES LIKE 'bloqueios_temporarios'");
            if ($stmt->rowCount() > 0) {
                echo "<p class='success'>✅ Confirmado: Tabela criada e disponível</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Erro ao criar tabela: {$e->getMessage()}</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro ao verificar tabela: {$e->getMessage()}</p>";
}

echo "<hr>";
echo "<p><strong>Verificação concluída!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
table { border-collapse: collapse; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>