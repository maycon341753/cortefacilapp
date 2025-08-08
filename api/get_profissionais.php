<?php
/**
 * API para buscar profissionais de um salão
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

try {
    // Verifica se o ID do salão foi fornecido
    if (!isset($_GET['salao_id']) || empty($_GET['salao_id'])) {
        throw new Exception('ID do salão é obrigatório');
    }
    
    $salaoId = (int) $_GET['salao_id'];
    
    // Busca profissionais do salão
    $sql = "SELECT id, nome, especialidade, telefone, horario_trabalho 
            FROM profissionais 
            WHERE id_salao = ? AND ativo = 1 
            ORDER BY nome";
    
    $stmt = $database->query($sql, [$salaoId]);
    $profissionais = $stmt->fetchAll();
    
    // Retorna os profissionais
    echo json_encode($profissionais);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>