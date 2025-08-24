<?php
/**
 * Teste do modelo Profissional
 */

header('Content-Type: application/json');

require_once 'config/database.php';
require_once 'models/profissional.php';

try {
    if (!isset($_GET['salao']) || empty($_GET['salao'])) {
        throw new Exception('ID do salão é obrigatório.');
    }
    
    $id_salao = (int)$_GET['salao'];
    
    if ($id_salao <= 0) {
        throw new Exception('ID do salão inválido.');
    }
    
    // Testar modelo Profissional
    $profissional = new Profissional();
    $profissionais = $profissional->listarPorSalao($id_salao);
    
    echo json_encode([
        'success' => true,
        'data' => $profissionais,
        'total' => count($profissionais),
        'debug' => [
            'modelo_usado' => 'Profissional::listarPorSalao()',
            'id_salao' => $id_salao
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'get_params' => $_GET
        ]
    ]);
}
?>