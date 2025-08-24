<?php
/**
 * API de horários simplificada para debug
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Log de debug
error_log("=== DEBUG HORARIOS ===");
error_log("GET params: " . print_r($_GET, true));
error_log("SESSION: " . print_r($_SESSION ?? [], true));

try {
    // Verificar parâmetros
    $profissional_id = $_GET['profissional_id'] ?? $_GET['profissional'] ?? null;
    $data = $_GET['data'] ?? null;
    
    error_log("Profissional ID: " . $profissional_id);
    error_log("Data: " . $data);
    
    if (!$profissional_id) {
        throw new Exception('ID do profissional é obrigatório.');
    }
    
    if (!$data) {
        throw new Exception('Data é obrigatória.');
    }
    
    // Iniciar sessão se necessário
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar autenticação de forma mais simples
    require_once __DIR__ . '/../includes/auth.php';
    
    if (!isLoggedIn()) {
        error_log("Usuário não autenticado");
        throw new Exception('Usuário não autenticado. Faça login primeiro.');
    }
    
    error_log("Usuário autenticado OK");
    
    // Conectar ao banco
    require_once __DIR__ . '/../config/database.php';
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    error_log("Conexão com banco OK");
    
    // Verificar se profissional existe
    $stmt = $conn->prepare("SELECT id, nome, id_salao FROM profissionais WHERE id = ? AND ativo = 1");
    $stmt->execute([$profissional_id]);
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profissional) {
        throw new Exception('Profissional não encontrado ou inativo.');
    }
    
    error_log("Profissional encontrado: " . $profissional['nome']);
    
    // Verificar horários de funcionamento do salão
    $dia_semana = date('w', strtotime($data)); // 0 = domingo, 1 = segunda, etc.
    
    $stmt = $conn->prepare("SELECT * FROM horarios_funcionamento WHERE id_salao = ? AND dia_semana = ?");
    $stmt->execute([$profissional['id_salao'], $dia_semana]);
    $horario_funcionamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario_funcionamento) {
        error_log("Salão fechado no dia da semana: " . $dia_semana);
        // Retornar array vazio mas com sucesso
        echo json_encode([
            'success' => true,
            'data' => [],
            'message' => 'Salão fechado neste dia da semana',
            'dia_semana' => $dia_semana,
            'profissional_id' => $profissional_id
        ]);
        exit;
    }
    
    error_log("Horário funcionamento: " . $horario_funcionamento['hora_abertura'] . " - " . $horario_funcionamento['hora_fechamento']);
    
    // Gerar horários simples (sem usar a classe Agendamento por enquanto)
    $horarios = [];
    $hora_inicio = strtotime($horario_funcionamento['hora_abertura']);
    $hora_fim = strtotime($horario_funcionamento['hora_fechamento']);
    
    // Gerar horários de 30 em 30 minutos
    for ($hora = $hora_inicio; $hora < $hora_fim; $hora += 1800) { // 1800 segundos = 30 minutos
        $horario_str = date('H:i', $hora);
        $horarios[] = $horario_str;
    }
    
    error_log("Horários gerados: " . count($horarios));
    
    // Verificar agendamentos existentes
    $stmt = $conn->prepare("SELECT hora FROM agendamentos WHERE id_profissional = ? AND data = ? AND status != 'cancelado'");
    $stmt->execute([$profissional_id, $data]);
    $horarios_ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    error_log("Horários ocupados: " . print_r($horarios_ocupados, true));
    
    // Remover horários ocupados
    $horarios_disponiveis = array_diff($horarios, $horarios_ocupados);
    
    // Se for hoje, remover horários que já passaram
    if ($data == date('Y-m-d')) {
        $hora_atual = date('H:i');
        $horarios_disponiveis = array_filter($horarios_disponiveis, function($horario) use ($hora_atual) {
            return $horario > $hora_atual;
        });
    }
    
    // Reindexar array
    $horarios_disponiveis = array_values($horarios_disponiveis);
    
    error_log("Horários disponíveis finais: " . count($horarios_disponiveis));
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'data' => $horarios_disponiveis,
        'total' => count($horarios_disponiveis),
        'data_solicitada' => $data,
        'profissional_id' => $profissional_id,
        'profissional_nome' => $profissional['nome'],
        'dia_semana' => $dia_semana,
        'horario_funcionamento' => $horario_funcionamento['hora_abertura'] . ' - ' . $horario_funcionamento['hora_fechamento'],
        'horarios_ocupados' => $horarios_ocupados
    ]);
    
} catch (Exception $e) {
    error_log("Erro na API: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'profissional_id' => $profissional_id ?? null,
            'data' => $data ?? null,
            'session_started' => session_status() === PHP_SESSION_ACTIVE,
            'user_logged' => isset($_SESSION['user_id']) ? 'yes' : 'no'
        ]
    ]);
} catch (Error $e) {
    error_log("Erro fatal na API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor.',
        'debug_error' => $e->getMessage()
    ]);
}
?>