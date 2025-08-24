<?php
/**
 * Teste direto da API original de horários
 */

echo "<h2>🧪 Teste da API Original de Horários</h2>";
echo "<hr>";

// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simular login se não estiver logado
if (!isset($_SESSION['user_id'])) {
    echo "<h3>🔐 Simulando Login</h3>";
    
    // Conectar ao banco para pegar um usuário de teste
    require_once 'config/database.php';
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        $stmt = $conn->query("SELECT id, nome, email FROM usuarios WHERE tipo = 'cliente' LIMIT 1");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_type'] = 'cliente';
            
            echo "<p>✅ <strong>Login simulado:</strong> {$usuario['nome']} (ID: {$usuario['id']})</p>";
        } else {
            echo "<p>❌ <strong>Nenhum usuário cliente encontrado</strong></p>";
        }
    }
}

echo "<h3>📊 Status da Sessão</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Usuário logado:</strong> " . (isset($_SESSION['user_id']) ? '✅ SIM' : '❌ NÃO') . "</p>";
if (isset($_SESSION['user_name'])) {
    echo "<p><strong>Nome:</strong> {$_SESSION['user_name']}</p>";
}

echo "<hr>";

// Buscar profissional para teste
if ($conn) {
    $stmt = $conn->query("SELECT p.id, p.nome, s.nome as salao_nome FROM profissionais p JOIN saloes s ON p.id_salao = s.id WHERE p.ativo = 1 LIMIT 1");
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profissional) {
        $profissional_id = $profissional['id'];
        $data_teste = date('Y-m-d', strtotime('+1 day')); // Amanhã
        
        echo "<h3>🎯 Teste da API Original</h3>";
        echo "<p><strong>Profissional:</strong> {$profissional['nome']} (ID: {$profissional_id})</p>";
        echo "<p><strong>Salão:</strong> {$profissional['salao_nome']}</p>";
        echo "<p><strong>Data:</strong> {$data_teste}</p>";
        
        // Simular parâmetros GET
        $_GET['profissional_id'] = $profissional_id;
        $_GET['data'] = $data_teste;
        
        echo "<h4>📡 Resposta da API Original:</h4>";
        echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px 0;'>";
        
        // Capturar output da API
        ob_start();
        
        try {
            // Incluir a API original
            include 'api/horarios.php';
        } catch (Exception $e) {
            echo "ERRO: " . $e->getMessage();
        } catch (Error $e) {
            echo "ERRO FATAL: " . $e->getMessage();
        }
        
        $response = ob_get_clean();
        
        echo "<pre style='white-space: pre-wrap; word-wrap: break-word;'>" . htmlspecialchars($response) . "</pre>";
        echo "</div>";
        
        // Verificar se é JSON válido
        $json_data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<h4>✅ Análise da Resposta JSON:</h4>";
            
            if (isset($json_data['success']) && $json_data['success']) {
                echo "<p>✅ <strong>Status:</strong> Sucesso</p>";
                
                if (isset($json_data['data'])) {
                    $horarios_count = count($json_data['data']);
                    echo "<p>📊 <strong>Horários encontrados:</strong> {$horarios_count}</p>";
                    
                    if ($horarios_count > 0) {
                        echo "<p>🕐 <strong>Primeiros horários:</strong></p>";
                        echo "<ul>";
                        foreach (array_slice($json_data['data'], 0, 10) as $hora) {
                            echo "<li>{$hora}</li>";
                        }
                        echo "</ul>";
                        
                        if ($horarios_count > 10) {
                            echo "<p>... e mais " . ($horarios_count - 10) . " horários</p>";
                        }
                    } else {
                        echo "<p>⚠️ <strong>Nenhum horário disponível</strong></p>";
                    }
                }
                
            } else {
                echo "<p>❌ <strong>Status:</strong> Erro</p>";
                echo "<p><strong>Mensagem:</strong> " . ($json_data['error'] ?? 'Erro desconhecido') . "</p>";
            }
        } else {
            echo "<p>❌ <strong>Resposta não é JSON válido</strong></p>";
            echo "<p><strong>Erro JSON:</strong> " . json_last_error_msg() . "</p>";
        }
        
        echo "<hr>";
        
        // Teste direto da classe Agendamento
        echo "<h3>🔧 Teste Direto da Classe Agendamento</h3>";
        
        try {
            require_once 'models/agendamento.php';
            
            $agendamento = new Agendamento($conn);
            $session_id = session_id();
            
            echo "<p><strong>Testando método gerarHorariosDisponiveisComBloqueios...</strong></p>";
            
            $horarios_direto = $agendamento->gerarHorariosDisponiveisComBloqueios($profissional_id, $data_teste, $session_id);
            
            echo "<p>📊 <strong>Horários retornados diretamente:</strong> " . count($horarios_direto) . "</p>";
            
            if (!empty($horarios_direto)) {
                echo "<p>🕐 <strong>Primeiros horários:</strong></p>";
                echo "<ul>";
                foreach (array_slice($horarios_direto, 0, 10) as $hora) {
                    echo "<li>{$hora}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>⚠️ <strong>Nenhum horário retornado pelo método direto</strong></p>";
                
                // Testar método sem bloqueios
                echo "<p><strong>Testando método gerarHorariosDisponiveis (sem bloqueios)...</strong></p>";
                $horarios_sem_bloqueio = $agendamento->gerarHorariosDisponiveis($profissional_id, $data_teste);
                echo "<p>📊 <strong>Horários sem bloqueio:</strong> " . count($horarios_sem_bloqueio) . "</p>";
                
                if (!empty($horarios_sem_bloqueio)) {
                    echo "<p>✅ <strong>Problema está no sistema de bloqueios</strong></p>";
                    echo "<p>🕐 <strong>Horários básicos:</strong></p>";
                    echo "<ul>";
                    foreach (array_slice($horarios_sem_bloqueio, 0, 10) as $hora) {
                        echo "<li>{$hora}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>❌ <strong>Problema na geração básica de horários</strong></p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p>❌ <strong>Erro na classe Agendamento:</strong> " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>❌ <strong>Nenhum profissional encontrado para teste</strong></p>";
    }
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='cliente/agendar.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📅 Página de Agendamento</a>";
echo "<a href='teste_api_debug.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 API Debug</a></p>";
?>