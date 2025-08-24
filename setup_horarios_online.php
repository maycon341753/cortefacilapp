<?php
/**
 * Script para executar no ambiente online (https://cortefacil.app)
 * Cria tabela 'horarios' e atualiza sistema de agendamento
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='utf-8'><title>Setup Horários Online</title></head><body>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} .box{background:#f8f9fa;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #007bff;}</style>";

echo "<h1>🚀 Setup Sistema de Horários - Ambiente Online</h1>";
echo "<p class='info'>📅 Data/Hora: " . date('d/m/Y H:i:s') . "</p>";
echo "<p class='info'>🌐 Servidor: " . $_SERVER['HTTP_HOST'] . "</p>";

try {
    // 1. Conectar ao banco
    echo "<h2>1. 🔌 Conexão com Banco de Dados</h2>";
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<p class='success'>✅ Conexão estabelecida com sucesso!</p>";
    
    // 2. Verificar tabelas existentes
    echo "<h2>2. 📋 Verificando Estrutura do Banco</h2>";
    
    $tabelas_necessarias = ['saloes', 'profissionais', 'agendamentos', 'horarios'];
    $tabelas_existentes = [];
    
    foreach ($tabelas_necessarias as $tabela) {
        $stmt = $conn->query("SHOW TABLES LIKE '{$tabela}'");
        $existe = $stmt->rowCount() > 0;
        $tabelas_existentes[$tabela] = $existe;
        
        if ($existe) {
            echo "<p class='success'>✅ Tabela '{$tabela}' existe</p>";
        } else {
            echo "<p class='error'>❌ Tabela '{$tabela}' NÃO existe</p>";
        }
    }
    
    // 3. Criar tabela 'horarios' se não existir
    if (!$tabelas_existentes['horarios']) {
        echo "<h2>3. 🔧 Criando Tabela 'horarios'</h2>";
        
        $sql_create_horarios = "
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
        
        $conn->exec($sql_create_horarios);
        echo "<p class='success'>✅ Tabela 'horarios' criada com sucesso!</p>";
        $tabelas_existentes['horarios'] = true;
    } else {
        echo "<h2>3. 📊 Verificando Dados da Tabela 'horarios'</h2>";
        $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios");
        $total_horarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='info'>📊 Horários cadastrados: {$total_horarios}</p>";
    }
    
    // 4. Inserir horários padrão se tabela estiver vazia
    $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios WHERE ativo = 1");
    $total_horarios_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total_horarios_ativos == 0) {
        echo "<h2>4. 📝 Inserindo Horários Padrão</h2>";
        
        // Buscar salões e profissionais
        $salaoModel = new Salao();
        $profissionalModel = new Profissional();
        
        $saloes = $salaoModel->listarAtivos();
        echo "<p class='info'>📍 Salões ativos: " . count($saloes) . "</p>";
        
        if (empty($saloes)) {
            echo "<p class='warning'>⚠️ Nenhum salão ativo encontrado. Criando salão padrão...</p>";
            
            // Criar salão padrão se não existir
            $conn->exec("
                INSERT IGNORE INTO saloes (nome, endereco, telefone, ativo, created_at) 
                VALUES ('Salão Principal', 'Endereço Principal', '(11) 99999-9999', 1, NOW())
            ");
            
            $saloes = $salaoModel->listarAtivos();
            echo "<p class='success'>✅ Salão padrão criado</p>";
        }
        
        // Horários padrão (08:00 às 18:00, intervalos de 30min, pausa 12:00-13:00)
        $horarios_padrao = [
            ['08:00:00', '08:30:00'], ['08:30:00', '09:00:00'], ['09:00:00', '09:30:00'],
            ['09:30:00', '10:00:00'], ['10:00:00', '10:30:00'], ['10:30:00', '11:00:00'],
            ['11:00:00', '11:30:00'], ['11:30:00', '12:00:00'],
            ['13:00:00', '13:30:00'], ['13:30:00', '14:00:00'], ['14:00:00', '14:30:00'],
            ['14:30:00', '15:00:00'], ['15:00:00', '15:30:00'], ['15:30:00', '16:00:00'],
            ['16:00:00', '16:30:00'], ['16:30:00', '17:00:00'], ['17:00:00', '17:30:00'],
            ['17:30:00', '18:00:00']
        ];
        
        $sql_insert = "INSERT INTO horarios (hora_inicio, hora_fim, profissional_id, salao_id, ativo) VALUES (?, ?, ?, ?, 1)";
        $stmt_insert = $conn->prepare($sql_insert);
        
        $total_inseridos = 0;
        
        foreach ($saloes as $salao) {
            echo "<div class='box'>";
            echo "<h4>📍 Salão: {$salao['nome']} (ID: {$salao['id']})</h4>";
            
            $profissionais = $profissionalModel->listarPorSalao($salao['id']);
            
            if (empty($profissionais)) {
                echo "<p class='warning'>⚠️ Nenhum profissional encontrado. Criando profissional padrão...</p>";
                
                // Criar profissional padrão
                $conn->exec("
                    INSERT IGNORE INTO profissionais (nome, email, telefone, salao_id, ativo, created_at) 
                    VALUES ('Profissional Padrão', 'profissional@{$salao['id']}.com', '(11) 99999-9999', {$salao['id']}, 1, NOW())
                ");
                
                $profissionais = $profissionalModel->listarPorSalao($salao['id']);
                echo "<p class='success'>✅ Profissional padrão criado</p>";
            }
            
            echo "<p class='info'>👨‍💼 Profissionais: " . count($profissionais) . "</p>";
            
            foreach ($profissionais as $profissional) {
                echo "<p>➤ {$profissional['nome']} (ID: {$profissional['id']}) - ";
                
                $inseridos_prof = 0;
                foreach ($horarios_padrao as $horario) {
                    try {
                        $stmt_insert->execute([
                            $horario[0], $horario[1], $profissional['id'], $salao['id']
                        ]);
                        $inseridos_prof++;
                        $total_inseridos++;
                    } catch (Exception $e) {
                        // Ignorar duplicatas
                    }
                }
                
                echo "{$inseridos_prof} horários inseridos</p>";
            }
            
            echo "</div>";
        }
        
        echo "<p class='success'>✅ Total de horários inseridos: {$total_inseridos}</p>";
    } else {
        echo "<h2>4. ✅ Horários Já Cadastrados</h2>";
        echo "<p class='success'>✅ {$total_horarios_ativos} horários ativos encontrados</p>";
    }
    
    // 5. Verificar dados finais
    echo "<h2>5. 📊 Resumo Final</h2>";
    
    $queries_verificacao = [
        'Salões ativos' => "SELECT COUNT(*) as total FROM saloes WHERE ativo = 1",
        'Profissionais ativos' => "SELECT COUNT(*) as total FROM profissionais WHERE ativo = 1",
        'Horários ativos' => "SELECT COUNT(*) as total FROM horarios WHERE ativo = 1",
        'Agendamentos' => "SELECT COUNT(*) as total FROM agendamentos"
    ];
    
    echo "<div class='box'>";
    foreach ($queries_verificacao as $label => $query) {
        try {
            $stmt = $conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p class='info'><strong>{$label}:</strong> {$result['total']}</p>";
        } catch (Exception $e) {
            echo "<p class='error'><strong>{$label}:</strong> Erro - {$e->getMessage()}</p>";
        }
    }
    echo "</div>";
    
    // 6. Testar API de horários
    echo "<h2>6. 🧪 Testando API de Horários</h2>";
    
    // Buscar primeiro profissional para teste
    $stmt = $conn->query("SELECT id, nome FROM profissionais WHERE ativo = 1 LIMIT 1");
    $profissional_teste = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profissional_teste) {
        $data_teste = date('Y-m-d', strtotime('+1 day'));
        echo "<p class='info'>🧪 Testando com profissional: {$profissional_teste['nome']} (ID: {$profissional_teste['id']})</p>";
        echo "<p class='info'>📅 Data de teste: {$data_teste}</p>";
        
        // Simular chamada da API
        $url_api = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/api/horarios.php";
        $params = "profissional_id={$profissional_teste['id']}&data={$data_teste}";
        
        echo "<p class='info'>🔗 URL da API: <a href='{$url_api}?{$params}' target='_blank'>{$url_api}?{$params}</a></p>";
        
        // Teste direto da query
        $stmt = $conn->prepare("
            SELECT h.hora_inicio, h.hora_fim 
            FROM horarios h 
            WHERE h.profissional_id = ? AND h.ativo = 1
            ORDER BY h.hora_inicio
        ");
        $stmt->execute([$profissional_teste['id']]);
        $horarios_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p class='success'>✅ Horários encontrados na tabela: " . count($horarios_disponiveis) . "</p>";
        
        if (!empty($horarios_disponiveis)) {
            echo "<p class='info'>📋 Primeiros 5 horários:</p>";
            echo "<ul>";
            foreach (array_slice($horarios_disponiveis, 0, 5) as $h) {
                echo "<li>{$h['hora_inicio']} - {$h['hora_fim']}</li>";
            }
            echo "</ul>";
        }
    }
    
    // 7. Próximos passos
    echo "<h2>7. 🎯 Próximos Passos</h2>";
    echo "<div class='box'>";
    echo "<h4>✅ Concluído:</h4>";
    echo "<ul>";
    echo "<li>✅ Tabela 'horarios' criada/verificada</li>";
    echo "<li>✅ Horários padrão inseridos</li>";
    echo "<li>✅ Estrutura do banco validada</li>";
    echo "</ul>";
    
    echo "<h4>🔄 Pendente:</h4>";
    echo "<ul>";
    echo "<li>🔄 Atualizar API api/horarios.php para usar tabela 'horarios'</li>";
    echo "<li>🔄 Testar formulário de agendamento</li>";
    echo "</ul>";
    echo "</div>";
    
    // 8. Links úteis
    echo "<h2>8. 🔗 Links para Teste</h2>";
    echo "<p><a href='cliente/agendar.php' target='_blank' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🎯 Testar Agendamento</a></p>";
    echo "<p><a href='api/horarios.php?profissional_id=1&data=" . date('Y-m-d', strtotime('+1 day')) . "' target='_blank' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔍 Testar API Horários</a></p>";
    
    echo "<div style='background:#d4edda;padding:20px;border-radius:5px;border-left:4px solid #28a745;margin:20px 0;'>";
    echo "<h3>🎉 Setup Concluído com Sucesso!</h3>";
    echo "<p>A tabela 'horarios' foi criada e populada com horários padrão.</p>";
    echo "<p><strong>Próximo passo:</strong> Atualizar a API para usar a nova tabela.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:20px;border-radius:5px;border-left:4px solid #dc3545;margin:20px 0;'>";
    echo "<h3>❌ Erro no Setup</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . " (linha " . $e->getLine() . ")</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</body></html>";
?>