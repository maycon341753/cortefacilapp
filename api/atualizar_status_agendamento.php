<?php
/**
 * API para Atualizar Status de Agendamento
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar autenticação
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$user = $auth->getCurrentUser();

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Validar dados obrigatórios
$agendamentoId = isset($input['agendamento_id']) ? (int) $input['agendamento_id'] : 0;
$novoStatus = isset($input['status']) ? trim($input['status']) : '';

if (!$agendamentoId || !$novoStatus) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do agendamento e status são obrigatórios']);
    exit;
}

// Validar status permitidos
$statusPermitidos = ['pendente', 'confirmado', 'concluido', 'cancelado'];
if (!in_array($novoStatus, $statusPermitidos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit;
}

try {
    // Verificar se o agendamento existe e se o usuário tem permissão para alterá-lo
    if ($user['tipo_usuario'] === 'cliente') {
        // Cliente só pode cancelar seus próprios agendamentos
        if ($novoStatus !== 'cancelado') {
            throw new Exception('Clientes só podem cancelar agendamentos');
        }
        
        $sql = "SELECT a.*, s.nome as salao_nome 
                FROM agendamentos a 
                JOIN saloes s ON a.id_salao = s.id 
                WHERE a.id = ? AND a.id_cliente = ?";
        $params = [$agendamentoId, $user['id']];
        
    } elseif ($user['tipo_usuario'] === 'parceiro') {
        // Parceiro pode alterar agendamentos do seu salão
        $sql = "SELECT a.*, s.nome as salao_nome 
                FROM agendamentos a 
                JOIN saloes s ON a.id_salao = s.id 
                WHERE a.id = ? AND s.id_dono = ?";
        $params = [$agendamentoId, $user['id']];
        
    } elseif ($user['tipo_usuario'] === 'admin') {
        // Admin pode alterar qualquer agendamento
        $sql = "SELECT a.*, s.nome as salao_nome 
                FROM agendamentos a 
                JOIN saloes s ON a.id_salao = s.id 
                WHERE a.id = ?";
        $params = [$agendamentoId];
        
    } else {
        throw new Exception('Tipo de usuário inválido');
    }
    
    $stmt = $database->query($sql, $params);
    $agendamento = $stmt->fetch();
    
    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado ou sem permissão');
    }
    
    // Verificar se a mudança de status é válida
    $statusAtual = $agendamento['status'];
    
    // Regras de transição de status
    $transicoesPermitidas = [
        'pendente' => ['confirmado', 'cancelado'],
        'confirmado' => ['concluido', 'cancelado'],
        'concluido' => [], // Status final
        'cancelado' => [] // Status final
    ];
    
    if (!in_array($novoStatus, $transicoesPermitidas[$statusAtual])) {
        throw new Exception("Não é possível alterar status de '{$statusAtual}' para '{$novoStatus}'");
    }
    
    // Atualizar status do agendamento
    $sqlUpdate = "UPDATE agendamentos SET status = ?, data_atualizacao = NOW() WHERE id = ?";
    $database->query($sqlUpdate, [$novoStatus, $agendamentoId]);
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso!',
        'data' => [
            'agendamento_id' => $agendamentoId,
            'status_anterior' => $statusAtual,
            'status_atual' => $novoStatus,
            'salao' => $agendamento['salao_nome']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>