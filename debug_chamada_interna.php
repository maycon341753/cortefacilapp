<?php
/**
 * Debug específico para a chamada interna do listarHorariosOcupados
 */

require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'models/profissional.php';

echo "<h2>🔍 Debug Chamada Interna</h2>";

// Parâmetros de teste
$id_profissional = 1;
$data = '2025-08-23';

echo "<p class='info'>Testando com profissional ID: {$id_profissional}, data: {$data}</p>";

// 1. Instanciar Agendamento
echo "<h3>1. Instanciando Agendamento</h3>";
$agendamento = new Agendamento();
echo "<p class='success'>✅ Agendamento instanciado</p>";

// 2. Testar listarHorariosOcupados diretamente
echo "<h3>2. Testando listarHorariosOcupados Diretamente</h3>";
try {
    $horarios_ocupados = $agendamento->listarHorariosOcupados($id_profissional, $data);
    echo "<p class='success'>✅ Método direto funcionou: " . count($horarios_ocupados) . " horários</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro no método direto: {$e->getMessage()}</p>";
}

// 3. Simular o início do gerarHorariosDisponiveis
echo "<h3>3. Simulando Início do gerarHorariosDisponiveis</h3>";

try {
    // Buscar profissional (copiado do método original)
    $profissional_model = new Profissional();
    $profissional = $profissional_model->buscarPorId($id_profissional);
    
    if (!$profissional) {
        echo "<p class='error'>❌ Profissional não encontrado</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Profissional encontrado: {$profissional['nome']}</p>";
    
    $id_salao = $profissional['id_salao'];
    echo "<p class='info'>ID do salão: {$id_salao}</p>";
    
    // Determinar dia da semana
    $dia_semana = date('w', strtotime($data));
    echo "<p class='info'>Dia da semana: {$dia_semana}</p>";
    
    // Buscar horários de funcionamento (usando a mesma conexão)
    $reflection = new ReflectionClass($agendamento);
    $connProperty = $reflection->getProperty('conn');
    $connProperty->setAccessible(true);
    $conn = $connProperty->getValue($agendamento);
    
    echo "<p class='info'>Conexão ID: " . spl_object_id($conn) . "</p>";
    
    $sql_horarios = "SELECT hora_abertura, hora_fechamento 
                   FROM horarios_funcionamento 
                   WHERE id_salao = :id_salao AND dia_semana = :dia_semana AND ativo = 1";
    
    $stmt_horarios = $conn->prepare($sql_horarios);
    $stmt_horarios->bindParam(':id_salao', $id_salao);
    $stmt_horarios->bindParam(':dia_semana', $dia_semana);
    $stmt_horarios->execute();
    
    $horario_funcionamento = $stmt_horarios->fetch(PDO::FETCH_ASSOC);
    
    if (!$horario_funcionamento) {
        echo "<p class='error'>❌ Nenhum horário de funcionamento encontrado</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Horário de funcionamento: {$horario_funcionamento['hora_abertura']} - {$horario_funcionamento['hora_fechamento']}</p>";
    
    // 4. Agora testar listarHorariosOcupados no contexto do método
    echo "<h3>4. Testando listarHorariosOcupados no Contexto</h3>";
    
    // Verificar se a conexão mudou
    $connProperty2 = $reflection->getProperty('conn');
    $connProperty2->setAccessible(true);
    $conn2 = $connProperty2->getValue($agendamento);
    
    echo "<p class='info'>Conexão ID após busca de horários: " . spl_object_id($conn2) . "</p>";
    echo "<p class='" . ($conn === $conn2 ? 'success' : 'error') . "'>" . ($conn === $conn2 ? '✅' : '❌') . " Mesma conexão: " . ($conn === $conn2 ? 'SIM' : 'NÃO') . "</p>";
    
    // Testar novamente listarHorariosOcupados
    $horarios_ocupados2 = $agendamento->listarHorariosOcupados($id_profissional, $data);
    echo "<p class='success'>✅ Método no contexto funcionou: " . count($horarios_ocupados2) . " horários</p>";
    
    // 5. Verificar se há diferença na estrutura da tabela
    echo "<h3>5. Verificando Estrutura da Tabela no Contexto</h3>";
    
    $stmt_desc = $conn2->query("DESCRIBE agendamentos");
    $columns = $stmt_desc->fetchAll();
    $statusCol = null;
    foreach ($columns as $col) {
        if ($col['Field'] === 'status') {
            $statusCol = $col;
            break;
        }
    }
    
    echo "<p class='" . ($statusCol ? 'success' : 'error') . "'>Status no contexto: " . ($statusCol ? $statusCol['Type'] : 'NÃO ENCONTRADA') . "</p>";
    
    // 6. Testar query manual com status
    echo "<h3>6. Testando Query Manual com Status</h3>";
    
    $sql_manual = "SELECT COUNT(*) as total FROM agendamentos WHERE profissional_id = :profissional_id AND data = :data AND status != 'cancelado'";
    $stmt_manual = $conn2->prepare($sql_manual);
    $stmt_manual->bindParam(':profissional_id', $id_profissional, PDO::PARAM_INT);
    $stmt_manual->bindParam(':data', $data, PDO::PARAM_STR);
    $stmt_manual->execute();
    $result_manual = $stmt_manual->fetch();
    
    echo "<p class='success'>✅ Query manual funcionou: {$result_manual['total']} registros</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na simulação: {$e->getMessage()}</p>";
    echo "<p class='error'>Arquivo: {$e->getFile()}, Linha: {$e->getLine()}</p>";
}

echo "<hr>";
echo "<p><strong>Debug de chamada interna concluído!</strong></p>";

?>

<style>
.success { color: green; }
.error { color: red; }
.info { color: blue; }
</style>