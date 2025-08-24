<?php
/**
 * Debug para identificar problema com horários não aparecendo
 */

echo "<h2>🔍 Debug - Problema com Horários</h2>";
echo "<hr>";

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h3>1. Verificar Sessão e Autenticação</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Dados da Sessão:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// Verificar se usuário está logado
require_once 'includes/auth.php';
$logado = isLoggedIn();
echo "<p><strong>Usuário logado:</strong> " . ($logado ? '✅ SIM' : '❌ NÃO') . "</p>";

if (!$logado) {
    echo "<div style='background: #ffe6e6; padding: 15px; border-left: 4px solid #ff0000; margin: 10px 0;'>";
    echo "<strong>❌ PROBLEMA ENCONTRADO:</strong> Usuário não está logado!<br>";
    echo "A API de horários requer autenticação. Faça login primeiro.";
    echo "</div>";
    echo "<p><a href='login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fazer Login</a></p>";
}

echo "<hr>";

echo "<h3>2. Testar Conexão com Banco de Dados</h3>";
try {
    require_once 'config/database.php';
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        echo "<p>✅ <strong>Conexão com banco:</strong> OK</p>";
        
        // Verificar tabelas necessárias
        $tabelas = ['profissionais', 'saloes', 'horarios_funcionamento', 'agendamentos', 'bloqueios_temporarios'];
        
        foreach ($tabelas as $tabela) {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tabela]);
            
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ <strong>Tabela '{$tabela}':</strong> Existe</p>";
            } else {
                echo "<p>❌ <strong>Tabela '{$tabela}':</strong> NÃO EXISTE</p>";
            }
        }
        
    } else {
        echo "<p>❌ <strong>Conexão com banco:</strong> FALHOU</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Erro na conexão:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

echo "<h3>3. Verificar Dados de Teste</h3>";

if ($conn) {
    // Verificar se existem salões
    $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes WHERE ativo = 1");
    $saloes_count = $stmt->fetch()['total'];
    echo "<p><strong>Salões ativos:</strong> {$saloes_count}</p>";
    
    // Verificar se existem profissionais
    $stmt = $conn->query("SELECT COUNT(*) as total FROM profissionais WHERE ativo = 1");
    $profissionais_count = $stmt->fetch()['total'];
    echo "<p><strong>Profissionais ativos:</strong> {$profissionais_count}</p>";
    
    // Verificar horários de funcionamento
    $stmt = $conn->query("SELECT COUNT(*) as total FROM horarios_funcionamento");
    $horarios_count = $stmt->fetch()['total'];
    echo "<p><strong>Horários de funcionamento cadastrados:</strong> {$horarios_count}</p>";
    
    if ($saloes_count == 0 || $profissionais_count == 0 || $horarios_count == 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
        echo "<strong>⚠️ ATENÇÃO:</strong> Dados básicos faltando!<br>";
        if ($saloes_count == 0) echo "- Nenhum salão ativo encontrado<br>";
        if ($profissionais_count == 0) echo "- Nenhum profissional ativo encontrado<br>";
        if ($horarios_count == 0) echo "- Nenhum horário de funcionamento cadastrado<br>";
        echo "</div>";
    }
    
    // Buscar um profissional para teste
    $stmt = $conn->query("SELECT p.id, p.nome, s.nome as salao_nome FROM profissionais p JOIN saloes s ON p.id_salao = s.id WHERE p.ativo = 1 LIMIT 1");
    $profissional_teste = $stmt->fetch();
    
    if ($profissional_teste) {
        echo "<p><strong>Profissional para teste:</strong> {$profissional_teste['nome']} (ID: {$profissional_teste['id']}) - Salão: {$profissional_teste['salao_nome']}</p>";
        
        echo "<hr>";
        echo "<h3>4. Testar API de Horários</h3>";
        
        $data_teste = date('Y-m-d', strtotime('+1 day')); // Amanhã
        $profissional_id = $profissional_teste['id'];
        
        echo "<p><strong>Testando para:</strong> Profissional ID {$profissional_id}, Data: {$data_teste}</p>";
        
        // Simular chamada da API
        $_GET['profissional_id'] = $profissional_id;
        $_GET['data'] = $data_teste;
        
        echo "<h4>📡 Resposta da API:</h4>";
        echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px;'>";
        
        ob_start();
        try {
            include 'api/horarios.php';
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        $api_response = ob_get_clean();
        
        echo "<pre>" . htmlspecialchars($api_response) . "</pre>";
        echo "</div>";
        
        // Verificar se é JSON válido
        $json_data = json_decode($api_response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>✅ <strong>JSON válido</strong></p>";
            
            if (isset($json_data['success']) && $json_data['success']) {
                echo "<p>✅ <strong>API retornou sucesso</strong></p>";
                
                if (isset($json_data['data']) && is_array($json_data['data'])) {
                    $horarios_count = count($json_data['data']);
                    echo "<p>📊 <strong>Horários retornados:</strong> {$horarios_count}</p>";
                    
                    if ($horarios_count > 0) {
                        echo "<p>✅ <strong>Horários encontrados!</strong> Primeiros 5:</p>";
                        echo "<ul>";
                        foreach (array_slice($json_data['data'], 0, 5) as $hora) {
                            echo "<li>{$hora}</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>⚠️ <strong>Nenhum horário disponível</strong></p>";
                    }
                } else {
                    echo "<p>❌ <strong>Campo 'data' não encontrado ou inválido</strong></p>";
                }
            } else {
                echo "<p>❌ <strong>API retornou erro:</strong> " . ($json_data['error'] ?? 'Erro desconhecido') . "</p>";
            }
        } else {
            echo "<p>❌ <strong>Resposta não é JSON válido</strong></p>";
        }
        
        echo "<hr>";
        echo "<h3>5. Testar Método Direto da Classe</h3>";
        
        try {
            require_once 'models/agendamento.php';
            $agendamento = new Agendamento($conn);
            
            $horarios_direto = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data_teste, session_id());
            
            echo "<p><strong>Horários via método direto:</strong> " . count($horarios_direto) . "</p>";
            
            if (!empty($horarios_direto)) {
                echo "<p>✅ <strong>Método direto funcionando!</strong> Primeiros 5:</p>";
                echo "<ul>";
                foreach (array_slice($horarios_direto, 0, 5) as $hora) {
                    echo "<li>{$hora}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>⚠️ <strong>Método direto não retornou horários</strong></p>";
                
                // Testar método sem bloqueios
                $horarios_sem_bloqueio = $agendamento->gerarHorariosDisponiveis($profissional_id, $data_teste);
                echo "<p><strong>Horários sem bloqueio:</strong> " . count($horarios_sem_bloqueio) . "</p>";
                
                if (!empty($horarios_sem_bloqueio)) {
                    echo "<p>✅ <strong>Problema está no sistema de bloqueios</strong></p>";
                } else {
                    echo "<p>❌ <strong>Problema na geração básica de horários</strong></p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p>❌ <strong>Erro no método direto:</strong> " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>❌ <strong>Nenhum profissional encontrado para teste</strong></p>";
    }
}

echo "<hr>";
echo "<h3>📋 Resumo e Soluções</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #007cba; margin: 10px 0;'>";
echo "<p><strong>Se os horários não aparecem, verifique:</strong></p>";
echo "<ol>";
echo "<li>✅ Usuário está logado</li>";
echo "<li>✅ Banco de dados conectado</li>";
echo "<li>✅ Tabelas existem</li>";
echo "<li>✅ Dados básicos cadastrados (salões, profissionais, horários)</li>";
echo "<li>✅ API retorna JSON válido</li>";
echo "<li>✅ JavaScript processa corretamente</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='cliente/agendar.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔗 Testar Página de Agendamento</a>";
echo "<a href='teste_sistema_agendamento.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Executar Testes Completos</a></p>";
?>