<?php
/**
 * Teste direto no banco de dados para profissionais
 */

header('Content-Type: application/json');

require_once 'config/database.php';

try {
    if (!isset($_GET['salao']) || empty($_GET['salao'])) {
        throw new Exception('ID do salão é obrigatório.');
    }
    
    $id_salao = (int)$_GET['salao'];
    
    if ($id_salao <= 0) {
        throw new Exception('ID do salão inválido.');
    }
    
    $conn = connectWithFallback();
    
    // Verificar se o salão existe
    $stmt = $conn->prepare("SELECT id, nome FROM saloes WHERE id = ?");
    $stmt->execute([$id_salao]);
    $salao = $stmt->fetch();
    
    if (!$salao) {
        throw new Exception("Salão com ID {$id_salao} não encontrado.");
    }
    
    // Buscar profissionais
    $sql = "SELECT p.*, s.nome as nome_salao 
            FROM profissionais p 
            INNER JOIN saloes s ON p.id_salao = s.id 
            WHERE p.id_salao = ? 
            ORDER BY p.nome";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_salao]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Informações adicionais para debug
    $debug_info = [
        'salao' => $salao,
        'total_profissionais' => count($profissionais),
        'sql_executado' => $sql,
        'parametros' => [$id_salao]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $profissionais,
        'total' => count($profissionais),
        'debug' => $debug_info
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