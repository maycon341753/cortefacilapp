<?php
/**
 * API para criar novo agendamento
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../config/database.php';

try {
    // Verifica se o usuário está logado
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_type'] !== 'cliente') {
        throw new Exception('Usuário não autorizado');
    }
    
    $clienteId = $_SESSION['user_id'];
    
    // Valida dados recebidos
    $salaoId = (int) ($_POST['salao'] ?? 0);
    $profissionalId = (int) ($_POST['profissional'] ?? 0);
    $data = $_POST['data'] ?? '';
    $horario = $_POST['horario'] ?? '';
    $servico = trim($_POST['servico'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    // Validações
    if (!$salaoId || !$profissionalId || !$data || !$horario) {
        throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
    }
    
    // Valida formato da data
    if (!DateTime::createFromFormat('Y-m-d', $data)) {
        throw new Exception('Formato de data inválido');
    }
    
    // Valida formato do horário
    if (!DateTime::createFromFormat('H:i', $horario)) {
        throw new Exception('Formato de horário inválido');
    }
    
    // Verifica se a data não é no passado
    if ($data < date('Y-m-d')) {
        throw new Exception('Não é possível agendar para datas passadas');
    }
    
    // Se for hoje, verifica se o horário já passou
    if ($data === date('Y-m-d') && $horario <= date('H:i')) {
        throw new Exception('Não é possível agendar para horários que já passaram');
    }
    
    // Inicia transação
    $database->beginTransaction();
    
    try {
        // Verifica se o horário ainda está disponível (double-check)
        $sqlCheck = "SELECT id FROM agendamentos 
                     WHERE id_profissional = ? 
                     AND data_agendamento = ? 
                     AND hora_agendamento = ? 
                     AND status IN ('pendente', 'confirmado')";
        
        $stmtCheck = $database->query($sqlCheck, [$profissionalId, $data, $horario]);
        
        if ($stmtCheck->fetch()) {
            throw new Exception('Este horário já foi agendado por outro cliente');
        }
        
        // Cria o agendamento
        $sqlInsert = "INSERT INTO agendamentos 
                      (id_cliente, id_salao, id_profissional, data_agendamento, hora_agendamento, servico, observacoes, status, valor_taxa) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente', 1.29)";
        
        $stmtInsert = $database->query($sqlInsert, [
            $clienteId, 
            $salaoId, 
            $profissionalId, 
            $data, 
            $horario, 
            $servico, 
            $observacoes
        ]);
        
        $agendamentoId = $database->lastInsertId();
        
        // Confirma a transação
        $database->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Agendamento criado com sucesso',
            'agendamento_id' => $agendamentoId
        ]);
        
    } catch (Exception $e) {
        $database->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>