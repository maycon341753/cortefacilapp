<?php
/**
 * API para buscar horários disponíveis
 * Retorna lista de horários livres para um profissional em uma data específica
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/agendamento.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    // Verificar se usuário está logado
    if (!isLoggedIn()) {
        throw new Exception('Usuário não autenticado.');
    }
    
    // Verificar parâmetros obrigatórios
    if (!isset($_GET['profissional']) || empty($_GET['profissional'])) {
        throw new Exception('ID do profissional é obrigatório.');
    }
    
    if (!isset($_GET['data']) || empty($_GET['data'])) {
        throw new Exception('Data é obrigatória.');
    }
    
    $id_profissional = (int)$_GET['profissional'];
    $data = $_GET['data'];
    
    // Validar ID do profissional
    if ($id_profissional <= 0) {
        throw new Exception('ID do profissional inválido.');
    }
    
    // Validar formato da data
    if (!validarData($data)) {
        throw new Exception('Formato de data inválido.');
    }
    
    // Verificar se a data não é no passado
    if (!isDataFutura($data) && !isDataHoje($data)) {
        throw new Exception('Não é possível agendar para datas passadas.');
    }
    
    // Buscar horários disponíveis
    $agendamento = new Agendamento();
    $horarios_disponiveis = $agendamento->gerarHorariosDisponiveis($id_profissional, $data);
    
    // Se for hoje, filtrar horários que já passaram
    if (isDataHoje($data)) {
        $hora_atual = date('H:i');
        $horarios_disponiveis = array_filter($horarios_disponiveis, function($hora) use ($hora_atual) {
            return $hora > $hora_atual;
        });
        // Reindexar array
        $horarios_disponiveis = array_values($horarios_disponiveis);
    }
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'data' => $horarios_disponiveis,
        'total' => count($horarios_disponiveis),
        'data_solicitada' => $data,
        'profissional_id' => $id_profissional
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor.'
    ]);
}
?>