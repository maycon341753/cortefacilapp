<?php
require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>Debug do Método gerarHorariosDisponiveis</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Forçar ambiente online
$_ENV['ENVIRONMENT'] = 'online';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';

try {
    // Conectar ao banco online
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<p class='success'>✅ Conectado ao banco online</p>";
    
    // Buscar um profissional ativo
    $stmt = $conn->query("SELECT id, nome, id_salao FROM profissionais WHERE ativo = 1 LIMIT 1");
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profissional) {
        throw new Exception('Nenhum profissional ativo encontrado');
    }
    
    $profissional_id = $profissional['id'];
    $data = '2025-08-23';
    
    echo "<p class='info'>👨‍💼 Profissional: {$profissional['nome']} (ID: {$profissional_id}, Salão: {$profissional['id_salao']})</p>";
    echo "<p class='info'>📅 Data: {$data}</p>";
    
    // Criar instância da classe
    $agendamento = new Agendamento($conn);
    
    echo "<h3>Passo a passo do método gerarHorariosDisponiveis</h3>";
    
    // Passo 1: Verificar se profissional existe
    echo "<h4>1. Verificando profissional</h4>";
    $stmt_prof = $conn->prepare("SELECT id_salao FROM profissionais WHERE id = ? AND ativo = 1");
    $stmt_prof->execute([$profissional_id]);
    $prof_data = $stmt_prof->fetch(PDO::FETCH_ASSOC);
    
    if ($prof_data) {
        echo "<p class='success'>✅ Profissional encontrado. Salão ID: {$prof_data['id_salao']}</p>";
    } else {
        echo "<p class='error'>❌ Profissional não encontrado ou inativo</p>";
        exit;
    }
    
    // Passo 2: Verificar horários de funcionamento
    echo "<h4>2. Verificando horários de funcionamento</h4>";
    $dia_semana = date('w', strtotime($data));
    echo "<p class='info'>📅 Dia da semana: {$dia_semana}</p>";
    
    $stmt_horarios = $conn->prepare("SELECT hora_abertura, hora_fechamento FROM horarios_funcionamento WHERE id_salao = ? AND dia_semana = ? AND ativo = 1");
    $stmt_horarios->execute([$prof_data['id_salao'], $dia_semana]);
    $horario_funcionamento = $stmt_horarios->fetch(PDO::FETCH_ASSOC);
    
    if ($horario_funcionamento) {
        echo "<p class='success'>✅ Horário de funcionamento encontrado: {$horario_funcionamento['hora_abertura']} às {$horario_funcionamento['hora_fechamento']}</p>";
    } else {
        echo "<p class='error'>❌ Nenhum horário de funcionamento encontrado para este dia</p>";
        exit;
    }
    
    // Passo 3: Testar listarHorariosOcupados diretamente
    echo "<h4>3. Testando listarHorariosOcupados</h4>";
    try {
        $horarios_ocupados = $agendamento->listarHorariosOcupados($profissional_id, $data);
        echo "<p class='success'>✅ listarHorariosOcupados funcionou. Horários ocupados: " . count($horarios_ocupados) . "</p>";
        if (!empty($horarios_ocupados)) {
            echo "<p class='info'>🕐 Horários: " . implode(', ', $horarios_ocupados) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro em listarHorariosOcupados: " . $e->getMessage() . "</p>";
        
        // Testar query direta
        echo "<h5>3.1. Testando query direta</h5>";
        try {
            $stmt_direct = $conn->prepare("SELECT hora FROM agendamentos WHERE id_profissional = ? AND data = ? AND status != 'cancelado' ORDER BY hora");
            $stmt_direct->execute([$profissional_id, $data]);
            $horarios_direct = $stmt_direct->fetchAll(PDO::FETCH_COLUMN);
            echo "<p class='success'>✅ Query direta funcionou. Horários: " . count($horarios_direct) . "</p>";
        } catch (Exception $e2) {
            echo "<p class='error'>❌ Erro na query direta: " . $e2->getMessage() . "</p>";
        }
        
        exit;
    }
    
    // Passo 4: Testar gerarHorariosPorIntervalo
    echo "<h4>4. Testando gerarHorariosPorIntervalo</h4>";
    try {
        // Usar reflexão para acessar método privado
        $reflection = new ReflectionClass($agendamento);
        $method = $reflection->getMethod('gerarHorariosPorIntervalo');
        $method->setAccessible(true);
        
        $horarios_funcionamento_array = $method->invoke($agendamento, $horario_funcionamento['hora_abertura'], $horario_funcionamento['hora_fechamento'], 30);
        echo "<p class='success'>✅ gerarHorariosPorIntervalo funcionou. Horários gerados: " . count($horarios_funcionamento_array) . "</p>";
        if (!empty($horarios_funcionamento_array)) {
            echo "<p class='info'>🕐 Primeiros 10 horários: " . implode(', ', array_slice($horarios_funcionamento_array, 0, 10)) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro em gerarHorariosPorIntervalo: " . $e->getMessage() . "</p>";
    }
    
    // Passo 5: Testar método completo
    echo "<h4>5. Testando método gerarHorariosDisponiveis completo</h4>";
    try {
        $horarios_disponiveis = $agendamento->gerarHorariosDisponiveis($profissional_id, $data);
        echo "<p class='success'>✅ gerarHorariosDisponiveis funcionou! Horários disponíveis: " . count($horarios_disponiveis) . "</p>";
        if (!empty($horarios_disponiveis)) {
            echo "<p class='info'>🕐 Primeiros 10 horários: " . implode(', ', array_slice($horarios_disponiveis, 0, 10)) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro em gerarHorariosDisponiveis: " . $e->getMessage() . "</p>";
        echo "<p class='info'>📋 Stack trace:</p>";
        echo "<pre style='background:#f8f9fa;padding:10px;border:1px solid #ddd;font-size:12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Debug concluído!</strong></p>";
?>