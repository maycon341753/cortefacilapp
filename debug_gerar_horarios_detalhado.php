<?php
/**
 * Debug detalhado do método gerarHorariosDisponiveis
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/agendamento.php';

echo "<h2>🔍 Debug Detalhado - gerarHorariosDisponiveis</h2>";

// Configurar para mostrar todos os erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Verificar conexão
echo "<h3>1. Verificando conexão</h3>";
$conn = getConnection();
if ($conn) {
    echo "<p class='success'>✅ Conexão estabelecida</p>";
    
    $stmt = $conn->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p class='info'>📊 Banco: {$result['db_name']}</p>";
} else {
    echo "<p class='error'>❌ Falha na conexão</p>";
    exit;
}

// 2. Buscar profissional ativo
echo "<h3>2. Buscando profissional ativo</h3>";
try {
    $stmt = $conn->query("SELECT id, nome, id_salao FROM profissionais WHERE ativo = 1 LIMIT 1");
    $profissional = $stmt->fetch();
    
    if ($profissional) {
        echo "<p class='success'>✅ Profissional encontrado: {$profissional['nome']} (ID: {$profissional['id']}, Salão: {$profissional['id_salao']})</p>";
        $id_profissional = $profissional['id'];
        $id_salao = $profissional['id_salao'];
    } else {
        echo "<p class='error'>❌ Nenhum profissional ativo encontrado</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao buscar profissional: {$e->getMessage()}</p>";
    exit;
}

// 3. Verificar horários de funcionamento
echo "<h3>3. Verificando horários de funcionamento</h3>";
$data = '2025-08-23';
$dia_semana = date('w', strtotime($data));
echo "<p class='info'>📅 Data: {$data} (Dia da semana: {$dia_semana})</p>";

try {
    $sql_horarios = "SELECT hora_abertura, hora_fechamento 
                   FROM horarios_funcionamento 
                   WHERE id_salao = :id_salao AND dia_semana = :dia_semana AND ativo = 1";
    
    $stmt_horarios = $conn->prepare($sql_horarios);
    $stmt_horarios->bindParam(':id_salao', $id_salao);
    $stmt_horarios->bindParam(':dia_semana', $dia_semana);
    $stmt_horarios->execute();
    
    $horario_funcionamento = $stmt_horarios->fetch(PDO::FETCH_ASSOC);
    
    if ($horario_funcionamento) {
        echo "<p class='success'>✅ Horário de funcionamento: {$horario_funcionamento['hora_abertura']} às {$horario_funcionamento['hora_fechamento']}</p>";
    } else {
        echo "<p class='error'>❌ Nenhum horário de funcionamento encontrado</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao buscar horários de funcionamento: {$e->getMessage()}</p>";
    exit;
}

// 4. Testar listarHorariosOcupados diretamente
echo "<h3>4. Testando listarHorariosOcupados diretamente</h3>";
try {
    // Criar instância da classe Agendamento
    $agendamento = new Agendamento();
    echo "<p class='success'>✅ Classe Agendamento instanciada</p>";
    
    // Testar método diretamente
    echo "<p class='info'>🔍 Chamando listarHorariosOcupados({$id_profissional}, '{$data}')...</p>";
    $horariosOcupados = $agendamento->listarHorariosOcupados($id_profissional, $data);
    echo "<p class='success'>✅ listarHorariosOcupados funcionou! Horários ocupados: " . count($horariosOcupados) . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro em listarHorariosOcupados: {$e->getMessage()}</p>";
    echo "<p class='error'>Stack trace: {$e->getTraceAsString()}</p>";
}

// 5. Testar gerarHorariosDisponiveis com try-catch interno
echo "<h3>5. Testando gerarHorariosDisponiveis com debug</h3>";
try {
    echo "<p class='info'>🔍 Chamando gerarHorariosDisponiveis({$id_profissional}, '{$data}')...</p>";
    
    // Capturar qualquer output de erro
    ob_start();
    $horariosDisponiveis = $agendamento->gerarHorariosDisponiveis($id_profissional, $data);
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "<p class='error'>❌ Output capturado: {$output}</p>";
    }
    
    echo "<p class='success'>✅ gerarHorariosDisponiveis funcionou! Horários disponíveis: " . count($horariosDisponiveis) . "</p>";
    
    if (count($horariosDisponiveis) > 0) {
        echo "<p class='info'>Primeiros 5 horários: " . implode(', ', array_slice($horariosDisponiveis, 0, 5)) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro em gerarHorariosDisponiveis: {$e->getMessage()}</p>";
    echo "<p class='error'>Arquivo: {$e->getFile()}</p>";
    echo "<p class='error'>Linha: {$e->getLine()}</p>";
    echo "<p class='error'>Stack trace: {$e->getTraceAsString()}</p>";
}

// 6. Verificar logs de erro
echo "<h3>6. Verificando logs de erro</h3>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $log_content = file_get_contents($error_log);
    $recent_errors = array_slice(explode("\n", $log_content), -10);
    echo "<h4>Últimos 10 erros do log:</h4>";
    foreach ($recent_errors as $error) {
        if (!empty(trim($error))) {
            echo "<p class='error'>{$error}</p>";
        }
    }
} else {
    echo "<p class='info'>Log de erro não encontrado ou não configurado</p>";
}

echo "<hr>";
echo "<p><strong>Debug detalhado concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>