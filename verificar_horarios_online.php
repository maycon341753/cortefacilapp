<?php
/**
 * Script para verificar e corrigir o problema de horários no ambiente online
 * Testa tanto a tabela 'horarios' quanto 'horarios_funcionamento'
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';
require_once 'models/agendamento.php';

echo "<h2>🔍 Diagnóstico de Horários - Ambiente Online</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";

try {
    // Detectar ambiente
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    echo "<p class='info'>🌐 Host atual: {$host}</p>";
    
    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
    // 1. Verificar tabelas existentes
    echo "<h3>1. Verificando Tabelas</h3>";
    $tabelas_verificar = ['horarios', 'horarios_funcionamento', 'saloes', 'profissionais', 'agendamentos'];
    $tabelas_existentes = [];
    
    foreach ($tabelas_verificar as $tabela) {
        $stmt = $conn->query("SHOW TABLES LIKE '{$tabela}'");
        $existe = $stmt->rowCount() > 0;
        
        if ($existe) {
            echo "<p class='success'>✅ Tabela '{$tabela}' existe</p>";
            $tabelas_existentes[] = $tabela;
            
            // Contar registros
            $stmt = $conn->query("SELECT COUNT(*) as total FROM {$tabela}");
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "<p class='info'>&nbsp;&nbsp;📊 Registros: {$total}</p>";
        } else {
            echo "<p class='error'>❌ Tabela '{$tabela}' NÃO existe</p>";
        }
    }
    
    // 2. Verificar dados básicos
    echo "<h3>2. Verificando Dados Básicos</h3>";
    
    // Salões ativos
    if (in_array('saloes', $tabelas_existentes)) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes WHERE ativo = 1");
        $saloes_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='info'>📍 Salões ativos: {$saloes_ativos}</p>";
        
        if ($saloes_ativos > 0) {
            $stmt = $conn->query("SELECT id, nome FROM saloes WHERE ativo = 1 LIMIT 3");
            $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($saloes as $salao) {
                echo "<p class='info'>&nbsp;&nbsp;• {$salao['nome']} (ID: {$salao['id']})</p>";
            }
        }
    }
    
    // Profissionais ativos
    if (in_array('profissionais', $tabelas_existentes)) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM profissionais WHERE ativo = 1");
        $profissionais_ativos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='info'>👨‍💼 Profissionais ativos: {$profissionais_ativos}</p>";
        
        if ($profissionais_ativos > 0) {
            $stmt = $conn->query("SELECT p.id, p.nome, s.nome as salao_nome 
                                 FROM profissionais p 
                                 LEFT JOIN saloes s ON p.id_salao = s.id 
                                 WHERE p.ativo = 1 LIMIT 3");
            $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($profissionais as $prof) {
                echo "<p class='info'>&nbsp;&nbsp;• {$prof['nome']} - {$prof['salao_nome']} (ID: {$prof['id']})</p>";
            }
        }
    }
    
    // 3. Verificar qual sistema de horários está sendo usado
    echo "<h3>3. Sistema de Horários Atual</h3>";
    
    if (in_array('horarios', $tabelas_existentes)) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios");
        $horarios_cadastrados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='success'>✅ Tabela 'horarios' existe com {$horarios_cadastrados} registros</p>";
        
        if ($horarios_cadastrados > 0) {
            echo "<p class='info'>📋 Sistema usando tabela 'horarios' (conforme solicitado)</p>";
            
            // Mostrar alguns horários
            $stmt = $conn->query("SELECT h.*, p.nome as profissional_nome, s.nome as salao_nome 
                                 FROM horarios h 
                                 LEFT JOIN profissionais p ON h.profissional_id = p.id 
                                 LEFT JOIN saloes s ON h.salao_id = s.id 
                                 WHERE h.ativo = 1 
                                 LIMIT 5");
            $horarios_exemplo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($horarios_exemplo)) {
                echo "<h4>Exemplos de horários cadastrados:</h4>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>Horário</th><th>Profissional</th><th>Salão</th></tr>";
                foreach ($horarios_exemplo as $h) {
                    echo "<tr>";
                    echo "<td>{$h['hora_inicio']} - {$h['hora_fim']}</td>";
                    echo "<td>{$h['profissional_nome']}</td>";
                    echo "<td>{$h['salao_nome']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p class='warning'>⚠️ Tabela 'horarios' existe mas está vazia</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ Tabela 'horarios' NÃO existe</p>";
        
        if (in_array('horarios_funcionamento', $tabelas_existentes)) {
            echo "<p class='info'>📋 Sistema usando 'horarios_funcionamento' (geração dinâmica)</p>";
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios_funcionamento");
            $hf_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "<p class='info'>⏰ Horários de funcionamento cadastrados: {$hf_total}</p>";
        } else {
            echo "<p class='error'>❌ Nenhum sistema de horários encontrado!</p>";
        }
    }
    
    // 4. Testar API de horários
    echo "<h3>4. Testando API de Horários</h3>";
    
    if ($profissionais_ativos > 0) {
        // Pegar primeiro profissional para teste
        $stmt = $conn->query("SELECT id FROM profissionais WHERE ativo = 1 LIMIT 1");
        $prof_teste = $stmt->fetch(PDO::FETCH_ASSOC);
        $prof_id = $prof_teste['id'];
        $data_teste = date('Y-m-d', strtotime('+1 day'));
        
        echo "<p class='info'>🧪 Testando com profissional ID: {$prof_id}, Data: {$data_teste}</p>";
        
        // Simular chamada da API
        $_GET['profissional_id'] = $prof_id;
        $_GET['data'] = $data_teste;
        
        ob_start();
        try {
            // Simular autenticação
            session_start();
            $_SESSION['user_id'] = 1;
            $_SESSION['user_type'] = 'cliente';
            
            include 'api/horarios.php';
            $api_response = ob_get_contents();
        } catch (Exception $e) {
            $api_response = json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        ob_end_clean();
        
        echo "<h4>Resposta da API:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($api_response) . "</pre>";
        
        // Analisar resposta
        $json_data = json_decode($api_response, true);
        if ($json_data && isset($json_data['success'])) {
            if ($json_data['success']) {
                $horarios_count = isset($json_data['data']) ? count($json_data['data']) : 0;
                echo "<p class='success'>✅ API funcionando! Horários encontrados: {$horarios_count}</p>";
                
                if ($horarios_count > 0) {
                    echo "<p class='info'>🕐 Primeiros horários: " . implode(', ', array_slice($json_data['data'], 0, 5)) . "</p>";
                } else {
                    echo "<p class='warning'>⚠️ API retornou sucesso mas nenhum horário disponível</p>";
                }
            } else {
                echo "<p class='error'>❌ API retornou erro: " . ($json_data['error'] ?? 'Erro desconhecido') . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Resposta da API não é JSON válido</p>";
        }
    }
    
    // 5. Diagnóstico e recomendações
    echo "<h3>5. Diagnóstico e Recomendações</h3>";
    
    if (!in_array('horarios', $tabelas_existentes)) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
        echo "<h4>🔧 Ação Necessária: Criar Tabela 'horarios'</h4>";
        echo "<p>A tabela 'horarios' não existe. É necessário criá-la com a estrutura:</p>";
        echo "<code>horarios(id, hora_inicio, hora_fim, profissional_id, salao_id)</code>";
        echo "<p><strong>Próximos passos:</strong></p>";
        echo "<ol>";
        echo "<li>Criar a tabela 'horarios'</li>";
        echo "<li>Inserir horários padrão para todos os profissionais</li>";
        echo "<li>Atualizar API para buscar da tabela ao invés de gerar dinamicamente</li>";
        echo "</ol>";
        echo "</div>";
    } elseif (isset($horarios_cadastrados) && $horarios_cadastrados == 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
        echo "<h4>📝 Ação Necessária: Inserir Horários</h4>";
        echo "<p>A tabela 'horarios' existe mas está vazia.</p>";
        echo "<p><strong>Próximo passo:</strong> Inserir horários padrão para todos os profissionais</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h4>✅ Sistema Configurado</h4>";
        echo "<p>A tabela 'horarios' existe e possui dados.</p>";
        echo "<p>Verifique se a API está retornando horários corretamente.</p>";
        echo "</div>";
    }
    
    // 6. Links para ação
    echo "<h3>6. Ações Disponíveis</h3>";
    echo "<p><a href='criar_tabela_horarios_completa.php' style='background:#dc3545;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔧 Criar/Corrigir Tabela Horários</a></p>";
    echo "<p><a href='cliente/agendar.php' target='_blank' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🎯 Testar Agendamento</a></p>";
    echo "<p><a href='api/horarios.php?profissional_id={$prof_id}&data={$data_teste}' target='_blank' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔗 Testar API Diretamente</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p class='error'>📋 Stack trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
}

echo "<hr>";
echo "<p><strong>🎯 Diagnóstico concluído!</strong></p>";
?>