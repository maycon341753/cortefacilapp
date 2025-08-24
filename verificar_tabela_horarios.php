<?php
/**
 * Script para verificar e criar a tabela horarios_funcionamento no banco online
 * e ajustar o método de geração de horários para respeitar os horários do salão
 */

require_once 'config/database.php';

echo "<h2>Verificação da Tabela de Horários de Funcionamento</h2>";

try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela horarios_funcionamento existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'horarios_funcionamento'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠ Tabela 'horarios_funcionamento' não encontrada. Criando...</p>";
        
        // Criar tabela horarios_funcionamento
        $sql_create = "
            CREATE TABLE horarios_funcionamento (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_salao INT NOT NULL,
                dia_semana INT NOT NULL COMMENT '1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado, 0=Domingo',
                hora_abertura TIME NOT NULL,
                hora_fechamento TIME NOT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
                UNIQUE KEY unique_salao_dia (id_salao, dia_semana)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $conn->exec($sql_create);
        echo "<p style='color: green;'>✓ Tabela 'horarios_funcionamento' criada com sucesso!</p>";
        
        // Inserir horários padrão para todos os salões existentes
        $stmt_saloes = $conn->query("SELECT id FROM saloes WHERE ativo = 1");
        $saloes = $stmt_saloes->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Inserindo horários padrão para " . count($saloes) . " salões...</p>";
        
        $sql_insert = "INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento, ativo) VALUES (?, ?, ?, ?, 1)";
        $stmt_insert = $conn->prepare($sql_insert);
        
        foreach ($saloes as $salao) {
            // Horários padrão: Segunda a Sexta 8h-18h, Sábado 8h-16h
            $horarios_padrao = [
                1 => ['08:00:00', '18:00:00'], // Segunda
                2 => ['08:00:00', '18:00:00'], // Terça
                3 => ['08:00:00', '18:00:00'], // Quarta
                4 => ['08:00:00', '18:00:00'], // Quinta
                5 => ['08:00:00', '18:00:00'], // Sexta
                6 => ['08:00:00', '16:00:00'], // Sábado
            ];
            
            foreach ($horarios_padrao as $dia => $horario) {
                $stmt_insert->execute([
                    $salao['id'],
                    $dia,
                    $horario[0],
                    $horario[1]
                ]);
            }
        }
        
        echo "<p style='color: green;'>✓ Horários padrão inseridos para todos os salões!</p>";
        
    } else {
        echo "<p style='color: green;'>✓ Tabela 'horarios_funcionamento' já existe</p>";
    }
    
    // Verificar dados existentes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios_funcionamento");
    $total_horarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>📊 Total de registros de horários: {$total_horarios}</p>";
    
    // Mostrar alguns exemplos
    $stmt = $conn->query("
        SELECT hf.*, s.nome as nome_salao 
        FROM horarios_funcionamento hf 
        INNER JOIN saloes s ON hf.id_salao = s.id 
        ORDER BY s.nome, hf.dia_semana 
        LIMIT 10
    ");
    $exemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($exemplos)) {
        echo "<h3>Exemplos de Horários Cadastrados:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Salão</th><th>Dia da Semana</th><th>Abertura</th><th>Fechamento</th><th>Ativo</th></tr>";
        
        $dias_semana = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado'
        ];
        
        foreach ($exemplos as $horario) {
            echo "<tr>";
            echo "<td>{$horario['nome_salao']}</td>";
            echo "<td>{$dias_semana[$horario['dia_semana']]}</td>";
            echo "<td>{$horario['hora_abertura']}</td>";
            echo "<td>{$horario['hora_fechamento']}</td>";
            echo "<td>" . ($horario['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3 style='color: green;'>✅ Verificação concluída com sucesso!</h3>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ul>";
    echo "<li>✓ Tabela horarios_funcionamento está disponível</li>";
    echo "<li>⏳ Modificar método gerarHorariosDisponiveis para usar horários do salão</li>";
    echo "<li>⏳ Testar geração de horários com base nos horários do salão</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}
?>