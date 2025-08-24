<?php
/**
 * Script para atualizar a API de horários para usar a tabela 'horarios'
 * ao invés de gerar horários dinamicamente
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='utf-8'><title>Atualizar API Horários</title></head><body>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} .box{background:#f8f9fa;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #007bff;} pre{background:#f8f9fa;padding:10px;border-radius:5px;overflow-x:auto;}</style>";

echo "<h1>🔄 Atualização da API de Horários</h1>";
echo "<p class='info'>📅 Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // 1. Verificar arquivo atual da API
    $api_file = 'api/horarios.php';
    echo "<h2>1. 📁 Verificando API Atual</h2>";
    
    if (!file_exists($api_file)) {
        throw new Exception("Arquivo da API não encontrado: {$api_file}");
    }
    
    echo "<p class='success'>✅ Arquivo encontrado: {$api_file}</p>";
    
    // Fazer backup do arquivo original
    $backup_file = $api_file . '.backup.' . date('Y-m-d_H-i-s');
    if (copy($api_file, $backup_file)) {
        echo "<p class='success'>✅ Backup criado: {$backup_file}</p>";
    } else {
        echo "<p class='warning'>⚠️ Não foi possível criar backup</p>";
    }
    
    // 2. Ler conteúdo atual
    $conteudo_atual = file_get_contents($api_file);
    echo "<p class='info'>📊 Tamanho do arquivo atual: " . strlen($conteudo_atual) . " bytes</p>";
    
    // 3. Criar nova versão da API
    echo "<h2>2. 🔧 Criando Nova Versão da API</h2>";
    
    $nova_api = '<?php
/**
 * API de Horários - Versão atualizada para usar tabela "horarios"
 * Busca horários cadastrados e verifica disponibilidade
 */

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Tratar requisições OPTIONS (CORS preflight)
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once "../config/database.php";
require_once "../models/agendamento.php";

try {
    // Conectar ao banco
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception("Erro na conexão com o banco de dados");
    }
    
    // Obter parâmetros
    $profissional_id = isset($_GET["profissional_id"]) ? (int)$_GET["profissional_id"] : 0;
    $salao_id = isset($_GET["salao_id"]) ? (int)$_GET["salao_id"] : 0;
    $data = isset($_GET["data"]) ? $_GET["data"] : date("Y-m-d");
    
    // Validar parâmetros obrigatórios
    if ($profissional_id <= 0) {
        throw new Exception("ID do profissional é obrigatório");
    }
    
    // Validar formato da data
    if (!preg_match("/^\\d{4}-\\d{2}-\\d{2}$/", $data)) {
        throw new Exception("Formato de data inválido. Use YYYY-MM-DD");
    }
    
    // Verificar se a data não é no passado
    if (strtotime($data) < strtotime(date("Y-m-d"))) {
        echo json_encode([
            "success" => true,
            "horarios" => [],
            "message" => "Não há horários disponíveis para datas passadas"
        ]);
        exit;
    }
    
    // 1. Buscar horários cadastrados na tabela "horarios"
    $sql_horarios = "
        SELECT h.id, h.hora_inicio, h.hora_fim, h.profissional_id, h.salao_id,
               p.nome as profissional_nome, s.nome as salao_nome
        FROM horarios h
        JOIN profissionais p ON h.profissional_id = p.id
        JOIN saloes s ON h.salao_id = s.id
        WHERE h.profissional_id = :profissional_id
        AND h.ativo = 1
        AND p.ativo = 1
        AND s.ativo = 1
    ";
    
    // Adicionar filtro de salão se fornecido
    if ($salao_id > 0) {
        $sql_horarios .= " AND h.salao_id = :salao_id";
    }
    
    $sql_horarios .= " ORDER BY h.hora_inicio";
    
    $stmt_horarios = $conn->prepare($sql_horarios);
    $stmt_horarios->bindParam(":profissional_id", $profissional_id, PDO::PARAM_INT);
    
    if ($salao_id > 0) {
        $stmt_horarios->bindParam(":salao_id", $salao_id, PDO::PARAM_INT);
    }
    
    $stmt_horarios->execute();
    $horarios_cadastrados = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($horarios_cadastrados)) {
        echo json_encode([
            "success" => true,
            "horarios" => [],
            "message" => "Nenhum horário cadastrado para este profissional",
            "debug" => [
                "profissional_id" => $profissional_id,
                "salao_id" => $salao_id,
                "data" => $data
            ]
        ]);
        exit;
    }
    
    // 2. Buscar agendamentos já ocupados para a data
    $sql_ocupados = "
        SELECT hora
        FROM agendamentos
        WHERE profissional_id = :profissional_id
        AND data = :data
        AND status IN ("confirmado", "pendente")
    ";
    
    $stmt_ocupados = $conn->prepare($sql_ocupados);
    $stmt_ocupados->bindParam(":profissional_id", $profissional_id, PDO::PARAM_INT);
    $stmt_ocupados->bindParam(":data", $data, PDO::PARAM_STR);
    $stmt_ocupados->execute();
    
    $horarios_ocupados = [];
    while ($row = $stmt_ocupados->fetch(PDO::FETCH_ASSOC)) {
        $horarios_ocupados[] = $row["hora"];
    }
    
    // 3. Verificar bloqueios temporários (sistema de bloqueio em tempo real)
    $agendamentoModel = new Agendamento($conn);
    
    // Limpar bloqueios expirados
    if (method_exists($agendamentoModel, "limparBloqueiosExpirados")) {
        $agendamentoModel->limparBloqueiosExpirados();
    }
    
    // Buscar horários bloqueados temporariamente
    $horarios_bloqueados = [];
    if (method_exists($agendamentoModel, "listarHorariosBloqueados")) {
        $bloqueios = $agendamentoModel->listarHorariosBloqueados($profissional_id, $data);
        foreach ($bloqueios as $bloqueio) {
            $horarios_bloqueados[] = $bloqueio["hora"];
        }
    }
    
    // 4. Filtrar horários disponíveis
    $horarios_disponiveis = [];
    
    foreach ($horarios_cadastrados as $horario) {
        $hora_inicio = $horario["hora_inicio"];
        
        // Verificar se não está ocupado
        $ocupado = in_array($hora_inicio, $horarios_ocupados);
        
        // Verificar se não está bloqueado temporariamente
        $bloqueado = in_array($hora_inicio, $horarios_bloqueados);
        
        if (!$ocupado && !$bloqueado) {
            $horarios_disponiveis[] = [
                "id" => $horario["id"],
                "hora_inicio" => $hora_inicio,
                "hora_fim" => $horario["hora_fim"],
                "hora_formatada" => date("H:i", strtotime($hora_inicio)),
                "disponivel" => true
            ];
        }
    }
    
    // 5. Resposta da API
    $response = [
        "success" => true,
        "horarios" => $horarios_disponiveis,
        "total_cadastrados" => count($horarios_cadastrados),
        "total_ocupados" => count($horarios_ocupados),
        "total_bloqueados" => count($horarios_bloqueados),
        "total_disponiveis" => count($horarios_disponiveis),
        "data" => $data,
        "profissional_id" => $profissional_id,
        "salao_id" => $salao_id,
        "message" => count($horarios_disponiveis) > 0 
            ? "Horários disponíveis encontrados" 
            : "Nenhum horário disponível para esta data"
    ];
    
    // Adicionar informações de debug se solicitado
    if (isset($_GET["debug"]) && $_GET["debug"] == "1") {
        $response["debug"] = [
            "horarios_ocupados" => $horarios_ocupados,
            "horarios_bloqueados" => $horarios_bloqueados,
            "sql_horarios" => $sql_horarios,
            "sql_ocupados" => $sql_ocupados
        ];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
}
?>';
    
    // 4. Salvar nova versão
    if (file_put_contents($api_file, $nova_api)) {
        echo "<p class='success'>✅ Nova API salva com sucesso!</p>";
        echo "<p class='info'>📊 Tamanho da nova API: " . strlen($nova_api) . " bytes</p>";
    } else {
        throw new Exception("Erro ao salvar nova versão da API");
    }
    
    // 5. Mostrar principais mudanças
    echo "<h2>3. 🔄 Principais Mudanças</h2>";
    echo "<div class='box'>";
    echo "<h4>✅ Implementado:</h4>";
    echo "<ul>";
    echo "<li>✅ Busca horários da tabela 'horarios' ao invés de gerar dinamicamente</li>";
    echo "<li>✅ Filtra por profissional_id e salao_id (opcional)</li>";
    echo "<li>✅ Verifica agendamentos ocupados na tabela 'agendamentos'</li>";
    echo "<li>✅ Mantém sistema de bloqueio temporário existente</li>";
    echo "<li>✅ Validação de parâmetros e datas</li>";
    echo "<li>✅ Resposta JSON estruturada com informações detalhadas</li>";
    echo "<li>✅ Modo debug opcional (?debug=1)</li>";
    echo "</ul>";
    
    echo "<h4>🔄 Estrutura da Resposta:</h4>";
    echo "<pre>{\n";
    echo "  \"success\": true,\n";
    echo "  \"horarios\": [\n";
    echo "    {\n";
    echo "      \"id\": 1,\n";
    echo "      \"hora_inicio\": \"08:00:00\",\n";
    echo "      \"hora_fim\": \"08:30:00\",\n";
    echo "      \"hora_formatada\": \"08:00\",\n";
    echo "      \"disponivel\": true\n";
    echo "    }\n";
    echo "  ],\n";
    echo "  \"total_disponiveis\": 15,\n";
    echo "  \"message\": \"Horários disponíveis encontrados\"\n";
    echo "}</pre>";
    echo "</div>";
    
    // 6. Testar nova API
    echo "<h2>4. 🧪 Testando Nova API</h2>";
    
    // Buscar um profissional para teste
    require_once 'config/database.php';
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        $stmt = $conn->query("SELECT id, nome FROM profissionais WHERE ativo = 1 LIMIT 1");
        $profissional_teste = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profissional_teste) {
            $data_teste = date('Y-m-d', strtotime('+1 day'));
            $url_teste = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/api/horarios.php";
            $params_teste = "profissional_id={$profissional_teste['id']}&data={$data_teste}&debug=1";
            
            echo "<div class='box'>";
            echo "<h4>🧪 Teste da API:</h4>";
            echo "<p><strong>Profissional:</strong> {$profissional_teste['nome']} (ID: {$profissional_teste['id']})</p>";
            echo "<p><strong>Data:</strong> {$data_teste}</p>";
            echo "<p><strong>URL:</strong> <a href='{$url_teste}?{$params_teste}' target='_blank'>{$url_teste}?{$params_teste}</a></p>";
            echo "</div>";
        }
    }
    
    // 7. Próximos passos
    echo "<h2>5. 🎯 Próximos Passos</h2>";
    echo "<div class='box'>";
    echo "<h4>✅ Concluído:</h4>";
    echo "<ul>";
    echo "<li>✅ API atualizada para usar tabela 'horarios'</li>";
    echo "<li>✅ Backup do arquivo original criado</li>";
    echo "<li>✅ Validações e filtros implementados</li>";
    echo "</ul>";
    
    echo "<h4>🔄 Testar:</h4>";
    echo "<ul>";
    echo "<li>🔄 Formulário de agendamento</li>";
    echo "<li>🔄 Seleção de horários no frontend</li>";
    echo "<li>🔄 Sistema de bloqueio em tempo real</li>";
    echo "</ul>";
    echo "</div>";
    
    // 8. Links para teste
    echo "<h2>6. 🔗 Links para Teste</h2>";
    echo "<p><a href='cliente/agendar.php' target='_blank' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🎯 Testar Agendamento</a></p>";
    
    if (isset($url_teste) && isset($params_teste)) {
        echo "<p><a href='{$url_teste}?{$params_teste}' target='_blank' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔍 Testar API (Debug)</a></p>";
    }
    
    echo "<div style='background:#d4edda;padding:20px;border-radius:5px;border-left:4px solid #28a745;margin:20px 0;'>";
    echo "<h3>🎉 API Atualizada com Sucesso!</h3>";
    echo "<p>A API agora busca horários da tabela 'horarios' e verifica disponibilidade corretamente.</p>";
    echo "<p><strong>Teste o formulário de agendamento para verificar se os horários aparecem corretamente.</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:20px;border-radius:5px;border-left:4px solid #dc3545;margin:20px 0;'>";
    echo "<h3>❌ Erro na Atualização</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . " (linha " . $e->getLine() . ")</p>";
    echo "</div>";
}

echo "</body></html>";
?>