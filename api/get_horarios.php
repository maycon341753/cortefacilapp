<?php
/**
 * API para buscar horários disponíveis de um profissional
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

try {
    // Verifica se os parâmetros foram fornecidos
    if (!isset($_GET['profissional_id']) || !isset($_GET['data'])) {
        throw new Exception('ID do profissional e data são obrigatórios');
    }
    
    $profissionalId = (int) $_GET['profissional_id'];
    $data = $_GET['data'];
    
    // Valida formato da data
    if (!DateTime::createFromFormat('Y-m-d', $data)) {
        throw new Exception('Formato de data inválido');
    }
    
    // Verifica se a data não é no passado
    if ($data < date('Y-m-d')) {
        throw new Exception('Não é possível agendar para datas passadas');
    }
    
    // Busca agendamentos já existentes para este profissional na data
    $sql = "SELECT hora_agendamento 
            FROM agendamentos 
            WHERE id_profissional = ? 
            AND data_agendamento = ? 
            AND status IN ('pendente', 'confirmado')";
    
    $stmt = $database->query($sql, [$profissionalId, $data]);
    $agendamentosExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Gera horários disponíveis (8h às 18h, de hora em hora)
    $horariosDisponiveis = [];
    $horaInicio = 8;
    $horaFim = 18;
    
    for ($hora = $horaInicio; $hora < $horaFim; $hora++) {
        $horarioFormatado = sprintf('%02d:00', $hora);
        
        // Verifica se o horário não está ocupado
        $disponivel = !in_array($horarioFormatado, $agendamentosExistentes);
        
        // Se for hoje, verifica se o horário já passou
        if ($data === date('Y-m-d')) {
            $horaAtual = (int) date('H');
            if ($hora <= $horaAtual) {
                $disponivel = false;
            }
        }
        
        $horariosDisponiveis[] = [
            'hora' => $horarioFormatado,
            'disponivel' => $disponivel
        ];
    }
    
    // Retorna os horários
    echo json_encode($horariosDisponiveis);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>