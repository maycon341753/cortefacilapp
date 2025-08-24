<?php
/**
 * Teste da API de Horários Online
 * Verifica se os horários estão sendo retornados corretamente
 */

// Forçar ambiente online
$_ENV['ENVIRONMENT'] = 'online';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';

require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'includes/functions.php';

// Simular sessão de usuário logado
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'cliente';
$_SESSION['user_name'] = 'Teste Cliente';

echo "<h2>🔍 Teste da API de Horários Online</h2>";
echo "<style>
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    // Conectar ao banco
    echo "<h3>1. Testando Conexão com Banco Online</h3>";
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        echo "<p class='success'>✅ Conexão com banco online estabelecida</p>";
        
        // Verificar ambiente
        $stmt = $conn->query("SELECT DATABASE() as db_name");
        $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p class='info'>📊 Banco conectado: {$db_info['db_name']}</p>";
    } else {
        throw new Exception('Falha na conexão com o banco');
    }
    
    // Buscar profissionais ativos
    echo "<h3>2. Buscando Profissionais Ativos</h3>";
    $stmt = $conn->query("SELECT p.id, p.nome, p.especialidade, s.nome as salao_nome 
                         FROM profissionais p 
                         JOIN saloes s ON p.id_salao = s.id 
                         WHERE p.ativo = 1 AND s.ativo = 1 
                         LIMIT 5");
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($profissionais)) {
        echo "<p class='success'>✅ Encontrados " . count($profissionais) . " profissionais ativos</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Especialidade</th><th>Salão</th></tr>";
        foreach ($profissionais as $prof) {
            echo "<tr><td>{$prof['id']}</td><td>{$prof['nome']}</td><td>{$prof['especialidade']}</td><td>{$prof['salao_nome']}</td></tr>";
        }
        echo "</table>";
    } else {
        throw new Exception('Nenhum profissional ativo encontrado');
    }
    
    // Testar com o primeiro profissional
    $profissional_teste = $profissionais[0];
    $id_profissional = $profissional_teste['id'];
    $data_teste = date('Y-m-d', strtotime('+1 day')); // Amanhã
    
    echo "<h3>3. Testando Horários para Profissional: {$profissional_teste['nome']}</h3>";
    echo "<p class='info'>📅 Data de teste: $data_teste</p>";
    
    // Verificar horários de funcionamento
    echo "<h4>3.1. Verificando Horários de Funcionamento</h4>";
    $dia_semana = date('w', strtotime($data_teste)); // 0=domingo, 1=segunda, etc.
    
    $stmt = $conn->prepare("SELECT * FROM horarios_funcionamento 
                           WHERE id_salao = (SELECT id_salao FROM profissionais WHERE id = ?) 
                           AND dia_semana = ?");
    $stmt->execute([$id_profissional, $dia_semana]);
    $horario_funcionamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($horario_funcionamento) {
        echo "<p class='success'>✅ Horário de funcionamento encontrado</p>";
        echo "<p class='info'>🕐 Funcionamento: {$horario_funcionamento['hora_abertura']} às {$horario_funcionamento['hora_fechamento']}</p>";
    } else {
        echo "<p class='error'>❌ Nenhum horário de funcionamento cadastrado para este dia</p>";
        echo "<p class='info'>💡 Dia da semana: $dia_semana (0=domingo, 1=segunda, etc.)</p>";
        
        // Mostrar todos os horários cadastrados para este salão
        $stmt = $conn->prepare("SELECT * FROM horarios_funcionamento 
                               WHERE id_salao = (SELECT id_salao FROM profissionais WHERE id = ?)");
        $stmt->execute([$id_profissional]);
        $todos_horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($todos_horarios)) {
            echo "<p class='info'>📋 Horários cadastrados para este salão:</p>";
            echo "<table>";
            echo "<tr><th>Dia da Semana</th><th>Abertura</th><th>Fechamento</th></tr>";
            foreach ($todos_horarios as $h) {
                $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                echo "<tr><td>{$dias[$h['dia_semana']]}</td><td>{$h['hora_abertura']}</td><td>{$h['hora_fechamento']}</td></tr>";
            }
            echo "</table>";
        }
    }
    
    // Testar classe Agendamento
    echo "<h4>3.2. Testando Classe Agendamento</h4>";
    $agendamento = new Agendamento($conn);
    
    // Testar método básico
    echo "<p><strong>Testando gerarHorariosDisponiveis...</strong></p>";
    $horarios_basicos = $agendamento->gerarHorariosDisponiveis($id_profissional, $data_teste);
    
    if (!empty($horarios_basicos)) {
        echo "<p class='success'>✅ Método básico funcionando - " . count($horarios_basicos) . " horários</p>";
        echo "<p class='info'>🕐 Primeiros horários: " . implode(', ', array_slice($horarios_basicos, 0, 5)) . "...</p>";
    } else {
        echo "<p class='error'>❌ Método básico não retornou horários</p>";
    }
    
    // Testar método com bloqueios
    echo "<p><strong>Testando gerarHorariosDisponiveisComBloqueios...</strong></p>";
    $session_id = session_id();
    $horarios_com_bloqueios = $agendamento->gerarHorariosDisponiveisComBloqueios($id_profissional, $data_teste, $session_id);
    
    if (!empty($horarios_com_bloqueios)) {
        echo "<p class='success'>✅ Método com bloqueios funcionando - " . count($horarios_com_bloqueios) . " horários</p>";
        echo "<p class='info'>🕐 Primeiros horários: " . implode(', ', array_slice($horarios_com_bloqueios, 0, 5)) . "...</p>";
    } else {
        echo "<p class='error'>❌ Método com bloqueios não retornou horários</p>";
    }
    
    // Simular chamada da API
    echo "<h4>3.3. Simulando Chamada da API</h4>";
    
    // Definir parâmetros GET
    $_GET['profissional_id'] = $id_profissional;
    $_GET['data'] = $data_teste;
    
    // Capturar saída da API
    ob_start();
    
    try {
        // Incluir o código da API (sem os headers)
        $profissional_param = $_GET['profissional_id'];
        $data_api = $_GET['data'];
        $id_profissional_api = (int)$profissional_param;
        
        if ($id_profissional_api <= 0) {
            throw new Exception('ID do profissional inválido.');
        }
        
        if (!validarData($data_api)) {
            throw new Exception('Formato de data inválido.');
        }
        
        $agendamento_api = new Agendamento($conn);
        $session_id_api = session_id();
        
        $horarios_api = $agendamento_api->gerarHorariosDisponiveisComBloqueios($id_profissional_api, $data_api, $session_id_api);
        
        // Se for hoje, filtrar horários que já passaram
        if (isDataHoje($data_api)) {
            $hora_atual = date('H:i');
            $horarios_api = array_filter($horarios_api, function($hora) use ($hora_atual) {
                return $hora > $hora_atual;
            });
            $horarios_api = array_values($horarios_api);
        }
        
        $resposta_api = [
            'success' => true,
            'data' => $horarios_api,
            'total' => count($horarios_api),
            'data_solicitada' => $data_api,
            'profissional_id' => $id_profissional_api
        ];
        
        echo json_encode($resposta_api);
        
    } catch (Exception $e) {
        $resposta_api = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        echo json_encode($resposta_api);
    }
    
    $saida_api = ob_get_clean();
    
    echo "<p class='info'>📡 Resposta da API:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>$saida_api</pre>";
    
    // Tentar fazer parse da resposta
    $resposta_json = json_decode($saida_api, true);
    if ($resposta_json) {
        if ($resposta_json['success']) {
            echo "<p class='success'>✅ API retornou sucesso com " . $resposta_json['total'] . " horários</p>";
        } else {
            echo "<p class='error'>❌ API retornou erro: " . $resposta_json['error'] . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Resposta da API não é um JSON válido</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔗 Links para Teste</h3>";
echo "<a href='api/horarios.php?profissional_id=1&data=" . date('Y-m-d', strtotime('+1 day')) . "' target='_blank' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔗 Testar API Diretamente</a>";
echo "<a href='cliente/agendar.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🌐 Página de Agendamento</a>";
?>