<?php
/**
 * API para bloquear temporariamente um horário
 * Usado quando cliente seleciona um horário durante o processo de agendamento
 */

// Iniciar sessão se não estiver ativa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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
    
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido.');
    }
    
    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Fallback para dados via $_POST
        $input = $_POST;
    }
    
    // Verificar parâmetros obrigatórios
    if (!isset($input['profissional_id']) || empty($input['profissional_id'])) {
        throw new Exception('ID do profissional é obrigatório.');
    }
    
    if (!isset($input['data']) || empty($input['data'])) {
        throw new Exception('Data é obrigatória.');
    }
    
    if (!isset($input['hora']) || empty($input['hora'])) {
        throw new Exception('Hora é obrigatória.');
    }
    
    $id_profissional = (int)$input['profissional_id'];
    $data = $input['data'];
    $hora = $input['hora'];
    $acao = $input['acao'] ?? 'bloquear'; // 'bloquear' ou 'desbloquear'
    
    // Validar parâmetros
    if ($id_profissional <= 0) {
        throw new Exception('ID do profissional inválido.');
    }
    
    if (!validarData($data)) {
        throw new Exception('Formato de data inválido.');
    }
    
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        throw new Exception('Formato de hora inválido.');
    }
    
    // Verificar se a data não é no passado
    if (!isDataFutura($data) && !isDataHoje($data)) {
        throw new Exception('Não é possível bloquear horários em datas passadas.');
    }
    
    // Conectar ao banco de dados
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    $agendamento = new Agendamento($conn);
    $session_id = session_id();
    $ip_cliente = $_SERVER['REMOTE_ADDR'] ?? null;
    
    if ($acao === 'bloquear') {
        // Bloquear horário temporariamente
        $resultado = $agendamento->bloquearHorarioTemporariamente(
            $id_profissional, 
            $data, 
            $hora, 
            $session_id, 
            $ip_cliente,
            10 // 10 minutos de bloqueio
        );
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Horário bloqueado temporariamente',
                'data' => [
                    'profissional_id' => $id_profissional,
                    'data' => $data,
                    'hora' => $hora,
                    'session_id' => $session_id,
                    'expires_in_minutes' => 10
                ]
            ]);
        } else {
            throw new Exception('Horário não disponível ou já bloqueado por outro cliente.');
        }
        
    } elseif ($acao === 'desbloquear') {
        // Desbloquear horário
        $resultado = $agendamento->desbloquearHorario(
            $id_profissional, 
            $data, 
            $hora, 
            $session_id
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Horário desbloqueado',
            'data' => [
                'profissional_id' => $id_profissional,
                'data' => $data,
                'hora' => $hora,
                'session_id' => $session_id
            ]
        ]);
        
    } else {
        throw new Exception('Ação inválida. Use "bloquear" ou "desbloquear".');
    }
    
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