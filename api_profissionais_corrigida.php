<?php
/**
 * API Corrigida para buscar profissionais por salão
 * Versão com debug e verificação robusta de autenticação
 */

// Iniciar sessão se não estiver ativa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/profissional.php';
require_once __DIR__ . '/includes/auth.php';

// Debug: Log da requisição
error_log("API Profissionais - Requisição recebida: " . print_r($_GET, true));
error_log("API Profissionais - Sessão: " . print_r($_SESSION, true));

try {
    // Verificação mais robusta de autenticação
    $usuario_logado = false;
    $debug_info = [];
    
    // Verificar diferentes formas de autenticação
    if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
        $usuario_logado = true;
        $debug_info['auth_method'] = 'usuario_id';
        $debug_info['user_id'] = $_SESSION['usuario_id'];
    } elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $usuario_logado = true;
        $debug_info['auth_method'] = 'user_id';
        $debug_info['user_id'] = $_SESSION['user_id'];
    } elseif (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
        $usuario_logado = true;
        $debug_info['auth_method'] = 'id';
        $debug_info['user_id'] = $_SESSION['id'];
    }
    
    // Para desenvolvimento, permitir acesso sem autenticação se especificado
    if (!$usuario_logado && isset($_GET['debug']) && $_GET['debug'] === 'allow') {
        $usuario_logado = true;
        $debug_info['auth_method'] = 'debug_bypass';
        $debug_info['warning'] = 'Autenticação bypassed para debug';
    }
    
    if (!$usuario_logado) {
        throw new Exception('Usuário não autenticado. Sessão: ' . json_encode($_SESSION));
    }
    
    // Verificar se foi passado o ID do salão
    if (!isset($_GET['salao']) || empty($_GET['salao'])) {
        throw new Exception('ID do salão é obrigatório.');
    }
    
    $id_salao = (int)$_GET['salao'];
    
    if ($id_salao <= 0) {
        throw new Exception('ID do salão inválido.');
    }
    
    // Verificar se o salão existe
    $conn = connectWithFallback();
    $stmt = $conn->prepare("SELECT id, nome FROM saloes WHERE id = ?");
    $stmt->execute([$id_salao]);
    $salao = $stmt->fetch();
    
    if (!$salao) {
        throw new Exception("Salão com ID {$id_salao} não encontrado.");
    }
    
    $debug_info['salao'] = $salao;
    
    // Buscar profissionais do salão diretamente do banco
    $sql = "SELECT p.*, s.nome as nome_salao 
            FROM profissionais p 
            INNER JOIN saloes s ON p.id_salao = s.id 
            WHERE p.id_salao = ? 
            ORDER BY p.nome";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_salao]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $debug_info['total_found'] = count($profissionais);
    
    // Filtrar apenas profissionais ativos (se a coluna status existir)
    $profissionais_ativos = [];
    foreach ($profissionais as $prof) {
        // Se não tem coluna status ou status é ativo
        if (!isset($prof['status']) || $prof['status'] === 'ativo') {
            $profissionais_ativos[] = $prof;
        }
    }
    
    $debug_info['total_active'] = count($profissionais_ativos);
    
    // Reindexar array
    $profissionais_ativos = array_values($profissionais_ativos);
    
    // Retornar resposta com debug info se solicitado
    $response = [
        'success' => true,
        'data' => $profissionais_ativos,
        'total' => count($profissionais_ativos)
    ];
    
    // Incluir debug info se solicitado
    if (isset($_GET['debug'])) {
        $response['debug'] = $debug_info;
        $response['session'] = $_SESSION;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    $error_response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    // Incluir debug info em caso de erro se solicitado
    if (isset($_GET['debug'])) {
        $error_response['debug'] = $debug_info ?? [];
        $error_response['session'] = $_SESSION ?? [];
        $error_response['get_params'] = $_GET;
    }
    
    echo json_encode($error_response);
    
} catch (Error $e) {
    http_response_code(500);
    $error_response = [
        'success' => false,
        'error' => 'Erro interno do servidor.'
    ];
    
    if (isset($_GET['debug'])) {
        $error_response['debug'] = [
            'php_error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    
    echo json_encode($error_response);
}
?>