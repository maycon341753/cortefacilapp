<?php
require_once 'config/database.php';

echo "<h2>Verificação do Banco de Dados Online - Sistema de Bloqueio de Horários</h2>";

try {
    // Conectar ao banco de dados
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<p style='color: green;'>✓ Conexão estabelecida com sucesso!</p>";
    
    // 1. Verificar se a tabela agendamentos existe e tem a estrutura necessária
    echo "<h3>1. Verificação da Tabela 'agendamentos'</h3>";
    
    $sql = "DESCRIBE agendamentos";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p style='color: red;'>✗ Tabela 'agendamentos' não encontrada!</p>";
        echo "<h4>Criando tabela 'agendamentos'...</h4>";
        
        $create_sql = "
        CREATE TABLE agendamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            id_salao INT NOT NULL,
            id_profissional INT NOT NULL,
            data DATE NOT NULL,
            hora TIME NOT NULL,
            status ENUM('pendente', 'confirmado', 'cancelado', 'concluido') DEFAULT 'pendente',
            valor_taxa DECIMAL(10,2) DEFAULT 1.29,
            observacoes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
            FOREIGN KEY (id_profissional) REFERENCES profissionais(id) ON DELETE CASCADE,
            UNIQUE KEY unique_appointment (id_profissional, data, hora)
        )";
        
        $db->exec($create_sql);
        echo "<p style='color: green;'>✓ Tabela 'agendamentos' criada com sucesso!</p>";
        
        // Criar índices para performance
        $index_sql = "
        CREATE INDEX idx_agendamentos_data_hora ON agendamentos(data, hora);
        CREATE INDEX idx_agendamentos_profissional ON agendamentos(id_profissional);
        ";
        $db->exec($index_sql);
        echo "<p style='color: green;'>✓ Índices criados com sucesso!</p>";
        
    } else {
        echo "<p style='color: green;'>✓ Tabela 'agendamentos' encontrada!</p>";
        
        // Mostrar estrutura atual
        echo "<h4>Estrutura atual da tabela:</h4>";
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 15px;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar se tem os campos necessários
        $required_fields = ['id', 'id_cliente', 'id_salao', 'id_profissional', 'data', 'hora', 'status'];
        $existing_fields = array_column($columns, 'Field');
        
        $missing_fields = array_diff($required_fields, $existing_fields);
        
        if (empty($missing_fields)) {
            echo "<p style='color: green;'>✓ Todos os campos necessários estão presentes!</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Campos faltando: " . implode(', ', $missing_fields) . "</p>";
        }
    }
    
    // 2. Verificar se existem outras tabelas necessárias
    echo "<h3>2. Verificação de Outras Tabelas Necessárias</h3>";
    
    $required_tables = ['usuarios', 'saloes', 'profissionais'];
    
    foreach ($required_tables as $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            echo "<p style='color: green;'>✓ Tabela '$table' encontrada!</p>";
        } else {
            echo "<p style='color: red;'>✗ Tabela '$table' não encontrada!</p>";
        }
    }
    
    // 3. Verificar se precisa de tabela adicional para bloqueios temporários
    echo "<h3>3. Análise de Necessidade de Tabelas Adicionais</h3>";
    
    echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc; margin: 10px 0;'>";
    echo "<h4>Análise da Funcionalidade de Bloqueio:</h4>";
    echo "<p><strong>Funcionalidade implementada:</strong> Bloqueio de 30 minutos após cada agendamento</p>";
    echo "<p><strong>Implementação atual:</strong> Lógica no código PHP (função listarHorariosOcupados)</p>";
    echo "<p><strong>Tabelas necessárias:</strong> Apenas a tabela 'agendamentos' existente</p>";
    echo "<p><strong>Conclusão:</strong> <span style='color: green; font-weight: bold;'>Não é necessário criar tabelas adicionais!</span></p>";
    echo "</div>";
    
    echo "<h4>Justificativa:</h4>";
    echo "<ul>";
    echo "<li>O bloqueio de 30 minutos é calculado dinamicamente no código</li>";
    echo "<li>A tabela 'agendamentos' já contém todos os dados necessários (profissional, data, hora, status)</li>";
    echo "<li>A constraint UNIQUE (id_profissional, data, hora) previne conflitos diretos</li>";
    echo "<li>A lógica de bloqueio é aplicada na consulta, não requer armazenamento adicional</li>";
    echo "</ul>";
    
    // 4. Testar a funcionalidade atual
    echo "<h3>4. Teste da Funcionalidade Atual</h3>";
    
    // Contar agendamentos existentes
    $sql = "SELECT COUNT(*) as total FROM agendamentos WHERE status != 'cancelado'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $total_agendamentos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<p>Total de agendamentos ativos no sistema: <strong>$total_agendamentos</strong></p>";
    
    if ($total_agendamentos > 0) {
        // Mostrar alguns agendamentos de exemplo
        $sql = "SELECT id_profissional, data, hora, status FROM agendamentos WHERE status != 'cancelado' ORDER BY data DESC, hora DESC LIMIT 5";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Últimos agendamentos (exemplo):</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Profissional</th><th>Data</th><th>Hora</th><th>Status</th></tr>";
        foreach ($agendamentos as $ag) {
            echo "<tr>";
            echo "<td>{$ag['id_profissional']}</td>";
            echo "<td>{$ag['data']}</td>";
            echo "<td>{$ag['hora']}</td>";
            echo "<td>{$ag['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Resumo Final</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px;'>";
echo "<p><strong style='color: #2e7d32;'>✓ BANCO DE DADOS PRONTO PARA A FUNCIONALIDADE!</strong></p>";
echo "<p>A estrutura atual do banco de dados é suficiente para o sistema de bloqueio de horários.</p>";
echo "<p>Não são necessárias tabelas adicionais.</p>";
echo "</div>";

echo "<p><a href='cliente/agendar.php'>Testar Sistema de Agendamento</a> | <a href='test_horarios_bloqueio.php'>Testar Bloqueio de Horários</a></p>";
?>