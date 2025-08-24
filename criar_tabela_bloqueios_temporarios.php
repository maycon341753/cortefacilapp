<?php
/**
 * Script para criar tabela de bloqueios temporários de horários
 * Esta tabela armazena horários que estão sendo selecionados por clientes
 * para evitar conflitos durante o processo de agendamento
 */

require_once 'config/database.php';

try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    echo "<h2>Criando Tabela de Bloqueios Temporários</h2>";
    
    // Criar tabela de bloqueios temporários
    $sql_create_table = "
        CREATE TABLE IF NOT EXISTS bloqueios_temporarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_profissional INT NOT NULL,
            data DATE NOT NULL,
            hora TIME NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            ip_cliente VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            INDEX idx_profissional_data (id_profissional, data),
            INDEX idx_expires (expires_at),
            UNIQUE KEY unique_horario (id_profissional, data, hora)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $conn->exec($sql_create_table);
    echo "<p style='color: green;'>✅ Tabela 'bloqueios_temporarios' criada com sucesso!</p>";
    
    // Verificar estrutura da tabela
    echo "<h3>Estrutura da Tabela:</h3>";
    $result = $conn->query("DESCRIBE bloqueios_temporarios");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Funcionalidades da Tabela:</h3>";
    echo "<ul>";
    echo "<li><strong>Bloqueio Temporário:</strong> Horários ficam bloqueados por 10 minutos quando selecionados</li>";
    echo "<li><strong>Sessão Única:</strong> Cada cliente tem sua própria sessão para evitar conflitos</li>";
    echo "<li><strong>Expiração Automática:</strong> Bloqueios expiram automaticamente após o tempo limite</li>";
    echo "<li><strong>IP Tracking:</strong> Rastreia IP do cliente para auditoria</li>";
    echo "<li><strong>Índices Otimizados:</strong> Para consultas rápidas por profissional/data</li>";
    echo "</ul>";
    
    echo "<h3>Próximos Passos:</h3>";
    echo "<ol>";
    echo "<li>Implementar métodos de bloqueio/desbloqueio na classe Agendamento</li>";
    echo "<li>Atualizar API de horários para considerar bloqueios temporários</li>";
    echo "<li>Implementar limpeza automática de bloqueios expirados</li>";
    echo "<li>Atualizar frontend para mostrar horários bloqueados</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}
?>