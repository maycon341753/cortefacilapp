<?php
/**
 * Script para criar a tabela 'horarios' completa e inserir dados padrão
 * Estrutura: horarios(id, hora_inicio, hora_fim, profissional_id, salao_id)
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

echo "<h2>🔧 Criação Completa da Tabela 'horarios'</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";

try {
    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
    // 1. Verificar se tabela já existe
    echo "<h3>1. Verificando Tabela Existente</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'horarios'");
    $tabela_existe = $stmt->rowCount() > 0;
    
    if ($tabela_existe) {
        echo "<p class='warning'>⚠️ Tabela 'horarios' já existe</p>";
        
        // Verificar se tem dados
        $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios");
        $total_existente = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='info'>📊 Registros existentes: {$total_existente}</p>";
        
        if ($total_existente > 0) {
            echo "<p class='info'>🔄 Limpando dados existentes...</p>";
            $conn->exec("DELETE FROM horarios");
            echo "<p class='success'>✅ Dados limpos</p>";
        }
    } else {
        echo "<p class='info'>🔧 Criando tabela 'horarios'...</p>";
        
        // Criar a tabela
        $sql_create = "
            CREATE TABLE horarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hora_inicio TIME NOT NULL,
                hora_fim TIME NOT NULL,
                profissional_id INT NOT NULL,
                salao_id INT NOT NULL,
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_profissional_salao (profissional_id, salao_id),
                INDEX idx_horarios (hora_inicio, hora_fim),
                INDEX idx_ativo (ativo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $conn->exec($sql_create);
        echo "<p class='success'>✅ Tabela 'horarios' criada com sucesso!</p>";
    }
    
    // 2. Buscar profissionais e salões
    echo "<h3>2. Buscando Profissionais e Salões</h3>";
    
    $salaoModel = new Salao();
    $profissionalModel = new Profissional();
    
    $saloes = $salaoModel->listarAtivos();
    echo "<p class='info'>📍 Salões ativos: " . count($saloes) . "</p>";
    
    if (empty($saloes)) {
        throw new Exception('Nenhum salão ativo encontrado!');
    }
    
    $total_profissionais = 0;
    $total_horarios_inseridos = 0;
    
    // 3. Inserir horários para cada profissional
    echo "<h3>3. Inserindo Horários Padrão</h3>";
    
    // Horários padrão (intervalos de 30 minutos)
    $horarios_padrao = [
        // Manhã
        ['08:00:00', '08:30:00'],
        ['08:30:00', '09:00:00'],
        ['09:00:00', '09:30:00'],
        ['09:30:00', '10:00:00'],
        ['10:00:00', '10:30:00'],
        ['10:30:00', '11:00:00'],
        ['11:00:00', '11:30:00'],
        ['11:30:00', '12:00:00'],
        // Tarde (pausa para almoço 12:00-13:00)
        ['13:00:00', '13:30:00'],
        ['13:30:00', '14:00:00'],
        ['14:00:00', '14:30:00'],
        ['14:30:00', '15:00:00'],
        ['15:00:00', '15:30:00'],
        ['15:30:00', '16:00:00'],
        ['16:00:00', '16:30:00'],
        ['16:30:00', '17:00:00'],
        ['17:00:00', '17:30:00'],
        ['17:30:00', '18:00:00']
    ];
    
    $sql_insert = "INSERT INTO horarios (hora_inicio, hora_fim, profissional_id, salao_id, ativo) VALUES (?, ?, ?, ?, 1)";
    $stmt_insert = $conn->prepare($sql_insert);
    
    foreach ($saloes as $salao) {
        echo "<h4>📍 Salão: {$salao['nome']} (ID: {$salao['id']})</h4>";
        
        $profissionais = $profissionalModel->listarPorSalao($salao['id']);
        echo "<p class='info'>👨‍💼 Profissionais encontrados: " . count($profissionais) . "</p>";
        
        if (empty($profissionais)) {
            echo "<p class='warning'>⚠️ Nenhum profissional ativo neste salão</p>";
            continue;
        }
        
        foreach ($profissionais as $profissional) {
            echo "<p class='info'>➤ Inserindo horários para: {$profissional['nome']} (ID: {$profissional['id']})</p>";
            
            $horarios_inseridos_prof = 0;
            
            foreach ($horarios_padrao as $horario) {
                try {
                    $stmt_insert->execute([
                        $horario[0], // hora_inicio
                        $horario[1], // hora_fim
                        $profissional['id'], // profissional_id
                        $salao['id'] // salao_id
                    ]);
                    $horarios_inseridos_prof++;
                    $total_horarios_inseridos++;
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Erro ao inserir horário {$horario[0]}-{$horario[1]}: " . $e->getMessage() . "</p>";
                }
            }
            
            echo "<p class='success'>✅ Horários inseridos: {$horarios_inseridos_prof}</p>";
            $total_profissionais++;
        }
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 20px 0;'>";
    echo "<h4>🎉 Inserção Concluída!</h4>";
    echo "<p><strong>Profissionais processados:</strong> {$total_profissionais}</p>";
    echo "<p><strong>Total de horários inseridos:</strong> {$total_horarios_inseridos}</p>";
    echo "<p><strong>Horários por profissional:</strong> " . count($horarios_padrao) . "</p>";
    echo "</div>";
    
    // 4. Verificar inserção
    echo "<h3>4. Verificando Inserção</h3>";
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios WHERE ativo = 1");
    $total_final = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p class='success'>✅ Total de horários ativos na tabela: {$total_final}</p>";
    
    // Mostrar alguns exemplos
    $stmt = $conn->query("
        SELECT h.hora_inicio, h.hora_fim, p.nome as profissional_nome, s.nome as salao_nome
        FROM horarios h
        JOIN profissionais p ON h.profissional_id = p.id
        JOIN saloes s ON h.salao_id = s.id
        WHERE h.ativo = 1
        ORDER BY s.nome, p.nome, h.hora_inicio
        LIMIT 10
    ");
    $exemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($exemplos)) {
        echo "<h4>📋 Exemplos de horários inseridos:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Horário</th><th>Profissional</th><th>Salão</th></tr>";
        foreach ($exemplos as $ex) {
            echo "<tr>";
            echo "<td>{$ex['hora_inicio']} - {$ex['hora_fim']}</td>";
            echo "<td>{$ex['profissional_nome']}</td>";
            echo "<td>{$ex['salao_nome']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Próximos passos
    echo "<h3>5. Próximos Passos</h3>";
    echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>";
    echo "<h4>🔄 Atualizar API de Horários</h4>";
    echo "<p>Agora é necessário atualizar a API para buscar horários da tabela 'horarios' ao invés de gerar dinamicamente.</p>";
    echo "<p><strong>Arquivo a modificar:</strong> <code>api/horarios.php</code></p>";
    echo "<p><strong>Mudança:</strong> Substituir <code>gerarHorariosDisponiveisComBloqueios()</code> por consulta na tabela 'horarios'</p>";
    echo "</div>";
    
    // 6. Links para teste
    echo "<h3>6. Testar Sistema</h3>";
    echo "<p><a href='atualizar_api_horarios.php' style='background:#dc3545;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔄 Atualizar API</a></p>";
    echo "<p><a href='cliente/agendar.php' target='_blank' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🎯 Testar Agendamento</a></p>";
    echo "<p><a href='verificar_horarios_online.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔍 Verificar Status</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p class='error'>📋 Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "<hr>";
echo "<p><strong>🎯 Criação da tabela concluída!</strong></p>";
?>