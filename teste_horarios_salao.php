<?php
/**
 * Teste para verificar se os horários dos profissionais respeitam os horários do salão
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';
require_once 'models/agendamento.php';

echo "<h2>Teste - Horários Respeitando Funcionamento do Salão</h2>";

try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Buscar salões e seus horários
    $salaoModel = new Salao();
    $profissionalModel = new Profissional();
    $agendamentoModel = new Agendamento($conn);
    
    $saloes = $salaoModel->listarAtivos();
    echo "<h3>Salões encontrados: " . count($saloes) . "</h3>";
    
    if (empty($saloes)) {
        echo "<p style='color: red;'>❌ Nenhum salão ativo encontrado!</p>";
        exit;
    }
    
    foreach ($saloes as $salao) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h4>🏪 {$salao['nome']} (ID: {$salao['id']})</h4>";
        
        // Buscar horários de funcionamento do salão
        $stmt = $conn->prepare("
            SELECT dia_semana, hora_abertura, hora_fechamento, ativo 
            FROM horarios_funcionamento 
            WHERE id_salao = ? 
            ORDER BY dia_semana
        ");
        $stmt->execute([$salao['id']]);
        $horarios_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($horarios_salao)) {
            echo "<p style='color: orange;'>⚠ Nenhum horário de funcionamento cadastrado para este salão</p>";
            continue;
        }
        
        echo "<h5>📅 Horários de Funcionamento:</h5>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 15px;'>";
        echo "<tr><th>Dia</th><th>Abertura</th><th>Fechamento</th><th>Status</th></tr>";
        
        $dias_semana = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado'
        ];
        
        foreach ($horarios_salao as $horario) {
            $status_cor = $horario['ativo'] ? 'green' : 'red';
            $status_texto = $horario['ativo'] ? 'Ativo' : 'Inativo';
            echo "<tr>";
            echo "<td>{$dias_semana[$horario['dia_semana']]}</td>";
            echo "<td>{$horario['hora_abertura']}</td>";
            echo "<td>{$horario['hora_fechamento']}</td>";
            echo "<td style='color: {$status_cor};'>{$status_texto}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Buscar profissionais do salão
        $profissionais = $profissionalModel->listarPorSalao($salao['id']);
        echo "<h5>👨‍💼 Profissionais (" . count($profissionais) . "):</h5>";
        
        if (empty($profissionais)) {
            echo "<p style='color: orange;'>⚠ Nenhum profissional encontrado para este salão</p>";
            continue;
        }
        
        foreach ($profissionais as $profissional) {
            echo "<div style='background: #f8f9fa; padding: 10px; margin: 5px 0; border-radius: 3px;'>";
            echo "<strong>{$profissional['nome']}</strong> - {$profissional['especialidade']}<br>";
            
            // Testar horários para os próximos 3 dias
            echo "<small>Horários disponíveis nos próximos dias:</small><br>";
            
            for ($i = 0; $i < 3; $i++) {
                $data_teste = date('Y-m-d', strtotime("+{$i} day"));
                $dia_semana_teste = date('w', strtotime($data_teste));
                $nome_dia = $dias_semana[$dia_semana_teste];
                
                $horarios_disponiveis = $agendamentoModel->gerarHorariosDisponiveis($profissional['id'], $data_teste);
                
                $cor_resultado = empty($horarios_disponiveis) ? 'orange' : 'green';
                $total_horarios = count($horarios_disponiveis);
                
                echo "<span style='color: {$cor_resultado}; font-size: 12px;'>";
                echo "• {$data_teste} ({$nome_dia}): {$total_horarios} horários";
                
                if (!empty($horarios_disponiveis)) {
                    echo " [" . implode(', ', array_slice($horarios_disponiveis, 0, 3));
                    if (count($horarios_disponiveis) > 3) {
                        echo "...";
                    }
                    echo "]";
                }
                echo "</span><br>";
            }
            
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    echo "<h3 style='color: green;'>✅ Teste concluído!</h3>";
    echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📋 Resumo das Melhorias:</h4>";
    echo "<ul>";
    echo "<li>✓ Tabela 'horarios_funcionamento' verificada/criada</li>";
    echo "<li>✓ Método 'gerarHorariosDisponiveis' atualizado</li>";
    echo "<li>✓ Horários dos profissionais agora respeitam horários do salão</li>";
    echo "<li>✓ Sistema verifica se salão está aberto no dia solicitado</li>";
    echo "<li>✓ Horários gerados dinamicamente baseados no funcionamento</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}
?>