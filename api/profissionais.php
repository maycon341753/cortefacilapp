<?php
/**
 * API para buscar profissionais por salão
 * Retorna lista de profissionais em formato JSON
 */

// Iniciar sessão se não estiver ativa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/profissional.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    // Verificar se usuário está logado
    if (!isLoggedIn()) {
        throw new Exception('Usuário não autenticado.');
    }
    
    // Verificar se foi passado o ID do salão
    $salao_param = $_GET['salao_id'] ?? $_GET['salao'] ?? '';
    if (empty($salao_param)) {
        throw new Exception('ID do salão é obrigatório.');
    }
    
    $id_salao = (int)$salao_param;
    
    if ($id_salao <= 0) {
        throw new Exception('ID do salão inválido.');
    }
    
    // Buscar profissionais do salão
    $profissional = new Profissional();
    $profissionais = $profissional->listarPorSalao($id_salao);
    
    // Filtrar apenas profissionais ativos
    $profissionais_ativos = array_filter($profissionais, function($prof) {
        return $prof['status'] === 'ativo';
    });
    
    // Reindexar array
    $profissionais_ativos = array_values($profissionais_ativos);
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'profissionais' => $profissionais_ativos,
        'total' => count($profissionais_ativos)
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