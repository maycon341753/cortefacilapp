<?php
/**
 * API para Processar Pagamento (Simulação)
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../includes/auth.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar autenticação
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['tipo_usuario'] !== 'cliente') {
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

if (!$agendamentoId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do agendamento é obrigatório']);
    exit;
}

try {
    // Iniciar transação
    $database->beginTransaction();
    
    // Verificar se o agendamento existe e pertence ao usuário
    $sql = "SELECT a.*, s.nome as salao_nome, p.nome as profissional_nome 
            FROM agendamentos a 
            JOIN saloes s ON a.id_salao = s.id 
            JOIN profissionais p ON a.id_profissional = p.id 
            WHERE a.id = ? AND a.id_cliente = ? AND a.status = 'pendente'";
    
    $stmt = $database->query($sql, [$agendamentoId, $user['id']]);
    $agendamento = $stmt->fetch();
    
    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado ou já foi processado');
    }
    
    // Simular processamento do pagamento
    // Em um sistema real, aqui seria feita a integração com gateway de pagamento
    $pagamentoAprovado = true; // Simulação: sempre aprovado
    $transacaoId = 'TXN_' . time() . '_' . rand(1000, 9999);
    
    if ($pagamentoAprovado) {
        // Atualizar status do agendamento para confirmado
        $sqlUpdate = "UPDATE agendamentos 
                      SET status = 'confirmado', 
                          transacao_id = ?,
                          data_pagamento = NOW()
                      WHERE id = ?";
        
        $database->query($sqlUpdate, [$transacaoId, $agendamentoId]);
        
        // Commit da transação
        $database->commit();
        
        // Resposta de sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Pagamento processado com sucesso!',
            'data' => [
                'agendamento_id' => $agendamentoId,
                'transacao_id' => $transacaoId,
                'valor' => 1.29,
                'status' => 'confirmado',
                'agendamento' => [
                    'salao' => $agendamento['salao_nome'],
                    'profissional' => $agendamento['profissional_nome'],
                    'data' => date('d/m/Y', strtotime($agendamento['data_agendamento'])),
                    'hora' => date('H:i', strtotime($agendamento['hora_agendamento']))
                ]
            ]
        ]);
        
    } else {
        // Pagamento rejeitado (simulação)
        throw new Exception('Pagamento rejeitado. Tente novamente ou use outro cartão.');
    }
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $database->rollback();
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>