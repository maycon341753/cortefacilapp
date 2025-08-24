<?php
require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

echo "<h2>Teste de Horários para o Próximo Dia</h2>";

try {
    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p>✅ Conexão com banco estabelecida</p>";
    
    // Buscar dados de teste
    $salaoModel = new Salao();
    $profissionalModel = new Profissional();
    $agendamentoModel = new Agendamento($conn);
    
    $saloes = $salaoModel->listarAtivos();
    if (empty($saloes)) {
        throw new Exception('Nenhum salão ativo encontrado');
    }
    
    $salao = $saloes[0];
    echo "<p>🏪 Salão: <strong>{$salao['nome']}</strong> (ID: {$salao['id']})</p>";
    
    $profissionais = $profissionalModel->listarPorSalao($salao['id']);
    if (empty($profissionais)) {
        throw new Exception('Nenhum profissional encontrado para este salão');
    }
    
    $profissional = $profissionais[0];
    echo "<p>👨‍💼 Profissional: <strong>{$profissional['nome']}</strong> (ID: {$profissional['id']})</p>";
    
    // Testar diferentes datas
    $datas_teste = [
        'Hoje' => date('Y-m-d'),
        'Amanhã' => date('Y-m-d', strtotime('+1 day')),
        'Depois de amanhã' => date('Y-m-d', strtotime('+2 days')),
        'Próxima semana' => date('Y-m-d', strtotime('+7 days'))
    ];
    
    foreach ($datas_teste as $label => $data) {
        echo "<h3>📅 {$label} ({$data})</h3>";
        
        // Testar geração de horários
        $horarios = $agendamentoModel->gerarHorariosDisponiveis($profissional['id'], $data);
        
        echo "<p>Horários encontrados: <strong>" . count($horarios) . "</strong></p>";
        
        if (!empty($horarios)) {
            echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>✅ Horários disponíveis:</strong><br>";
            foreach ($horarios as $hora) {
                echo "<span style='background: #fff; padding: 3px 8px; margin: 2px; border-radius: 3px; display: inline-block;'>{$hora}</span>";
            }
            echo "</div>";
        } else {
            echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>❌ Nenhum horário disponível</strong>";
            echo "</div>";
        }
        
        // Testar API diretamente
        echo "<h4>🔗 Teste da API</h4>";
        $api_url = "api/horarios.php?profissional={$profissional['id']}&data={$data}";
        echo "<p>URL da API: <a href='{$api_url}' target='_blank'>{$api_url}</a></p>";
        
        // Simular chamada da API
        $_GET['profissional'] = $profissional['id'];
        $_GET['data'] = $data;
        
        ob_start();
        try {
            include 'api/horarios.php';
            $api_response = ob_get_clean();
            $api_data = json_decode($api_response, true);
            
            if ($api_data && isset($api_data['success']) && $api_data['success']) {
                echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>";
                echo "<strong>✅ API funcionando:</strong> " . count($api_data['data']) . " horários retornados";
                echo "</div>";
            } else {
                echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px;'>";
                echo "<strong>❌ Erro na API:</strong> " . ($api_data['error'] ?? 'Resposta inválida');
                echo "</div>";
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px;'>";
            echo "<strong>❌ Erro na API:</strong> " . $e->getMessage();
            echo "</div>";
        }
        
        echo "<hr>";
    }
    
    echo "<h3>🔧 Links de Teste</h3>";
    echo "<p><a href='cliente/agendar.php' target='_blank'>🎯 Testar página de agendamento</a></p>";
    echo "<p><a href='api/horarios.php?profissional={$profissional['id']}&data=" . date('Y-m-d', strtotime('+1 day')) . "' target='_blank'>🔗 Testar API diretamente</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe8e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Erro:</strong> " . $e->getMessage();
    echo "</div>";
}
?>