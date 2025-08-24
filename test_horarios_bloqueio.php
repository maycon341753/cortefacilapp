<?php
require_once 'config/database.php';
require_once 'models/agendamento.php';

// Conectar ao banco de dados
$database = new Database();
$db = $database->getConnection();

// Criar instância do modelo de agendamento
$agendamento = new Agendamento($db);

echo "<h2>Teste do Sistema de Horários Disponíveis (Sem Bloqueio de 30min)</h2>";

// Testar com um profissional e data específicos
$id_profissional = 1; // Assumindo que existe um profissional com ID 1
$data = date('Y-m-d'); // Data de hoje

echo "<h3>Testando para Profissional ID: $id_profissional, Data: $data</h3>";

// 1. Verificar horários ocupados (apenas os exatos)
echo "<h4>1. Horários Ocupados (apenas horários exatos agendados):</h4>";
$horarios_ocupados = $agendamento->listarHorariosOcupados($id_profissional, $data);
if (empty($horarios_ocupados)) {
    echo "<p>Nenhum horário ocupado encontrado.</p>";
} else {
    echo "<ul>";
    foreach ($horarios_ocupados as $hora) {
        echo "<li style='color: red;'>$hora (OCUPADO)</li>";
    }
    echo "</ul>";
}

// 2. Gerar horários disponíveis
echo "<h4>2. Horários Disponíveis:</h4>";
try {
    $horarios_disponiveis = $agendamento->gerarHorariosDisponiveis($id_profissional, $data);
    if (empty($horarios_disponiveis)) {
        echo "<p>Nenhum horário disponível encontrado.</p>";
    } else {
        echo "<ul>";
        foreach ($horarios_disponiveis as $hora) {
            echo "<li style='color: green;'>$hora (DISPONÍVEL)</li>";
        }
        echo "</ul>";
        echo "<p><strong>Total de horários disponíveis: " . count($horarios_disponiveis) . "</strong></p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

// 3. Verificar agendamentos existentes na tabela
echo "<h4>3. Agendamentos Existentes na Tabela:</h4>";
try {
    $sql = "SELECT id, id_cliente, id_profissional, data, hora, status 
            FROM agendamentos 
            WHERE id_profissional = :id_profissional 
            AND data = :data 
            ORDER BY hora";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id_profissional', $id_profissional);
    $stmt->bindParam(':data', $data);
    $stmt->execute();
    
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($agendamentos)) {
        echo "<p>Nenhum agendamento encontrado para hoje.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Cliente</th><th>Profissional</th><th>Data</th><th>Hora</th><th>Status</th></tr>";
        foreach ($agendamentos as $ag) {
            $cor = ($ag['status'] == 'cancelado') ? 'color: gray;' : 'color: red; font-weight: bold;';
            echo "<tr style='$cor'>";
            echo "<td>{$ag['id']}</td>";
            echo "<td>{$ag['id_cliente']}</td>";
            echo "<td>{$ag['id_profissional']}</td>";
            echo "<td>{$ag['data']}</td>";
            echo "<td>{$ag['hora']}</td>";
            echo "<td>{$ag['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao buscar agendamentos: " . $e->getMessage() . "</p>";
}

// 4. Simular horários de funcionamento do salão
echo "<h4>4. Exemplo de Horários de Funcionamento do Salão:</h4>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc; margin: 10px 0;'>";
echo "<p><strong>Horários de funcionamento típicos:</strong></p>";
echo "<ul>";
$horarios_funcionamento = ['08:00:00', '08:30:00', '09:00:00', '09:30:00', '10:00:00', '10:30:00', '11:00:00', '11:30:00', '14:00:00', '14:30:00', '15:00:00', '15:30:00', '16:00:00', '16:30:00', '17:00:00', '17:30:00'];
foreach ($horarios_funcionamento as $hora) {
    $ocupado = in_array($hora, $horarios_ocupados);
    $cor = $ocupado ? 'color: red;' : 'color: green;';
    $status = $ocupado ? 'OCUPADO' : 'DISPONÍVEL';
    echo "<li style='$cor'>$hora - $status</li>";
}
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>Resumo da Nova Funcionalidade</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid #4caf50; border-radius: 5px;'>";
echo "<p><strong style='color: #2e7d32;'>✓ SISTEMA ATUALIZADO!</strong></p>";
echo "<ul>";
echo "<li><strong>Antes:</strong> Horários agendados + 30 minutos ficavam indisponíveis</li>";
echo "<li><strong>Agora:</strong> Apenas os horários exatos agendados ficam indisponíveis</li>";
echo "<li><strong>Resultado:</strong> Mais horários disponíveis para os clientes</li>";
echo "<li><strong>Benefício:</strong> Melhor aproveitamento da agenda do profissional</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='cliente/agendar.php'>Testar Sistema de Agendamento</a></p>";
?>