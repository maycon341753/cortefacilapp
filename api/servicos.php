<?php
/**
 * API para Gerenciamento de Serviços
 * Endpoints para operações CRUD de serviços dos salões
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configurações de erro
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../models/servico.php';
    require_once __DIR__ . '/../includes/auth.php';
    
    // Forçar conexão online se necessário
    $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($serverName, 'cortefacil.app') !== false || file_exists(__DIR__ . '/../.env.online')) {
        $db = Database::getInstance();
        $db->forceOnlineConfig();
    }
    
    $servico = new Servico();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verificar autenticação para operações que modificam dados
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
        session_start();
        if (!isLoggedIn() || !isParceiro()) {
            http_response_code(401);
            echo json_encode(['error' => 'Acesso não autorizado']);
            exit;
        }
    }
    
    switch ($method) {
        case 'GET':
            handleGet($servico);
            break;
            
        case 'POST':
            handlePost($servico, $input);
            break;
            
        case 'PUT':
            handlePut($servico, $input);
            break;
            
        case 'DELETE':
            handleDelete($servico, $input);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Erro na API de serviços: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}

/**
 * Listar serviços
 */
function handleGet($servico) {
    $salao_id = $_GET['salao_id'] ?? null;
    $servico_id = $_GET['id'] ?? null;
    
    if ($servico_id) {
        // Buscar serviço específico
        $resultado = $servico->buscarPorId($servico_id);
        if ($resultado) {
            echo json_encode($resultado);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Serviço não encontrado']);
        }
    } elseif ($salao_id) {
        // Listar serviços do salão
        $servicos = $servico->listarPorSalao($salao_id);
        echo json_encode($servicos);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID do salão é obrigatório']);
    }
}

/**
 * Criar novo serviço
 */
function handlePost($servico, $input) {
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados não fornecidos']);
        return;
    }
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['id_salao', 'nome', 'preco', 'duracao_minutos'];
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($input[$campo]) || empty($input[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo '$campo' é obrigatório"]);
            return;
        }
    }
    
    // Validar tipos de dados
    if (!is_numeric($input['preco']) || $input['preco'] <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Preço deve ser um número maior que zero']);
        return;
    }
    
    if (!is_numeric($input['duracao_minutos']) || $input['duracao_minutos'] <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Duração deve ser um número maior que zero']);
        return;
    }
    
    $dados = [
        'id_salao' => intval($input['id_salao']),
        'nome' => trim($input['nome']),
        'descricao' => trim($input['descricao'] ?? ''),
        'preco' => floatval($input['preco']),
        'duracao_minutos' => intval($input['duracao_minutos']),
        'ativo' => isset($input['ativo']) ? (bool)$input['ativo'] : true
    ];
    
    if ($servico->criar($dados)) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Serviço criado com sucesso']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao criar serviço']);
    }
}

/**
 * Atualizar serviço
 */
function handlePut($servico, $input) {
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do serviço é obrigatório']);
        return;
    }
    
    $id = intval($input['id']);
    
    // Verificar se o serviço existe
    $servico_existente = $servico->buscarPorId($id);
    if (!$servico_existente) {
        http_response_code(404);
        echo json_encode(['error' => 'Serviço não encontrado']);
        return;
    }
    
    // Validar campos obrigatórios
    $campos_obrigatorios = ['nome', 'preco', 'duracao_minutos'];
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($input[$campo]) || empty($input[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo '$campo' é obrigatório"]);
            return;
        }
    }
    
    // Validar tipos de dados
    if (!is_numeric($input['preco']) || $input['preco'] <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Preço deve ser um número maior que zero']);
        return;
    }
    
    if (!is_numeric($input['duracao_minutos']) || $input['duracao_minutos'] <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Duração deve ser um número maior que zero']);
        return;
    }
    
    $dados = [
        'nome' => trim($input['nome']),
        'descricao' => trim($input['descricao'] ?? ''),
        'preco' => floatval($input['preco']),
        'duracao_minutos' => intval($input['duracao_minutos']),
        'ativo' => isset($input['ativo']) ? (bool)$input['ativo'] : true
    ];
    
    if ($servico->atualizar($id, $dados)) {
        echo json_encode(['success' => true, 'message' => 'Serviço atualizado com sucesso']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar serviço']);
    }
}

/**
 * Excluir serviço
 */
function handleDelete($servico, $input) {
    $id = $input['id'] ?? $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do serviço é obrigatório']);
        return;
    }
    
    $id = intval($id);
    
    // Verificar se o serviço existe
    $servico_existente = $servico->buscarPorId($id);
    if (!$servico_existente) {
        http_response_code(404);
        echo json_encode(['error' => 'Serviço não encontrado']);
        return;
    }
    
    if ($servico->excluir($id)) {
        echo json_encode(['success' => true, 'message' => 'Serviço excluído com sucesso']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao excluir serviço']);
    }
}
?>