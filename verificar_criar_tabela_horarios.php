<?php
/**
 * Script para verificar e criar a tabela 'horarios' no banco online
 * Estrutura: horarios(id, hora_inicio, hora_fim, profissional_id, salao_id)
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

echo "<h2>🔍 Verificação e Criação da Tabela 'horarios'</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela 'horarios' existe
    echo "<h3>1. Verificando se tabela 'horarios' existe</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'horarios'");
    $tabela_existe = $stmt->rowCount() > 0;
    
    if ($tabela_existe) {
        echo "<p class='success'>✅ Tabela 'horarios' já existe!</p>";
        
        // Mostrar estrutura atual
        echo "<h4>Estrutura atual:</h4>";
        $stmt = $conn->query("DESCRIBE horarios");
        $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
        $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='info'>📊 Total de horários cadastrados: {$total}</p>";
        
        if ($total > 0) {
            echo "<h4>Primeiros 10 registros:</h4>";
            $stmt = $conn->query("SELECT h.*, s.nome as salao_nome, p.nome as profissional_nome 
                                 FROM horarios h 
                                 LEFT JOIN saloes s ON h.salao_id = s.id 
                                 LEFT JOIN profissionais p ON h.profissional_id = p.id 
                                 LIMIT 10");
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Hora Início</th><th>Hora Fim</th><th>Profissional</th><th>Salão</th></tr>";
            foreach ($registros as $reg) {
                echo "<tr>";
                echo "<td>{$reg['id']}</td>";
                echo "<td>{$reg['hora_inicio']}</td>";
                echo "<td>{$reg['hora_fim']}</td>";
                echo "<td>{$reg['profissional_nome']} (ID: {$reg['profissional_id']})</td>";
                echo "<td>{$reg['salao_nome']} (ID: {$reg['salao_id']})</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p class='error'>❌ Tabela 'horarios' NÃO existe!</p>";
        echo "<p class='info'>🔧 Criando tabela 'horarios'...</p>";
        
        // Criar a tabela horarios
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
                FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
                FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $conn->exec($sql_create);
        echo "<p class='success'>✅ Tabela 'horarios' criada com sucesso!</p>";
        
        // Inserir horários padrão para todos os profissionais ativos
        echo "<h3>2. Inserindo horários padrão</h3>";
        
        $salaoModel = new Salao();
        $profissionalModel = new Profissional();
        
        $saloes = $salaoModel->listarAtivos();
        echo "<p class='info'>📍 Salões ativos encontrados: " . count($saloes) . "</p>";
        
        $total_horarios_inseridos = 0;
        
        foreach ($saloes as $salao) {
            $profissionais = $profissionalModel->listarPorSalao($salao['id']);
            echo "<p class='info'>👨‍💼 Profissionais no salão {$salao['nome']}: " . count($profissionais) . "</p>";
            
            foreach ($profissionais as $profissional) {
                // Horários padrão: 08:00 às 18:00 com intervalos de 30 minutos
                $horarios_padrao = [
                    ['08:00:00', '08:30:00'],
                    ['08:30:00', '09:00:00'],
                    ['09:00:00', '09:30:00'],
                    ['09:30:00', '10:00:00'],
                    ['10:00:00', '10:30:00'],
                    ['10:30:00', '11:00:00'],
                    ['11:00:00', '11:30:00'],
                    ['11:30:00', '12:00:00'],
                    ['13:00:00', '13:30:00'], // Pausa para almoço 12:00-13:00
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
                
                $sql_insert = "INSERT INTO horarios (hora_inicio, hora_fim, profissional_id, salao_id) VALUES (?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                
                foreach ($horarios_padrao as $horario) {
                    $stmt_insert->execute([
                        $horario[0],
                        $horario[1],
                        $profissional['id'],
                        $salao['id']
                    ]);
                    $total_horarios_inseridos++;
                }
                
                echo "<p class='success'>✅ Horários inseridos para {$profissional['nome']}: " . count($horarios_padrao) . "</p>";
            }
        }
        
        echo "<p class='success'>🎉 Total de horários inseridos: {$total_horarios_inseridos}</p>";
    }
    
    // Verificar se API precisa ser atualizada
    echo "<h3>3. Verificando API de horários</h3>";
    $api_file = 'api/horarios.php';
    if (file_exists($api_file)) {
        $api_content = file_get_contents($api_file);
        if (strpos($api_content, 'gerarHorariosDisponiveisComBloqueios') !== false) {
            echo "<p class='info'>⚠️ API ainda usa geração dinâmica de horários</p>";
            echo "<p class='info'>💡 Será necessário atualizar para buscar da tabela 'horarios'</p>";
        } else {
            echo "<p class='success'>✅ API já atualizada</p>";
        }
    }
    
    // Teste rápido
    echo "<h3>4. Teste rápido</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios WHERE ativo = 1");
    $total_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p class='success'>✅ Horários ativos na tabela: {$total_ativos}</p>";
    
    // Links para teste
    echo "<h3>5. Links para teste</h3>";
    echo "<p><a href='cliente/agendar.php' target='_blank' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🎯 Testar Agendamento</a></p>";
    echo "<p><a href='api/horarios.php?profissional_id=1&data=" . date('Y-m-d', strtotime('+1 day')) . "' target='_blank' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔗 Testar API</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p class='error'>📋 Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "<hr>";
echo "<p><strong>🎯 Verificação concluída!</strong></p>";
?>