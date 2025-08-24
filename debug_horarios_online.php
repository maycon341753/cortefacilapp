<?php
require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';
require_once 'models/agendamento.php';

echo "<h2>Debug - Horários Online</h2>";

// Verificar conexão com banco
try {
    $pdo = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na conexão: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar se existem salões
$salaoModel = new Salao();
$saloes = $salaoModel->listarTodos();
echo "<h3>Salões encontrados: " . count($saloes) . "</h3>";
foreach ($saloes as $salao) {
    echo "<p>ID: {$salao['id']} - Nome: {$salao['nome']} - Status: {$salao['status']}</p>";
    
    // Verificar horários de funcionamento
    echo "<p>Horário funcionamento: {$salao['horario_funcionamento']}</p>";
    
    // Verificar profissionais do salão
    $profissionalModel = new Profissional();
    $profissionais = $profissionalModel->listarPorSalao($salao['id']);
    echo "<p>Profissionais: " . count($profissionais) . "</p>";
    
    foreach ($profissionais as $prof) {
        echo "<p>&nbsp;&nbsp;- {$prof['nome']} (ID: {$prof['id']}) - Status: {$prof['status']}</p>";
    }
    
    echo "<hr>";
}

// Testar API de horários
echo "<h3>Teste da API de Horários</h3>";
if (!empty($saloes) && !empty($profissionais)) {
    $salao_id = $saloes[0]['id'];
    $profissional_id = $profissionais[0]['id'];
    $data = date('Y-m-d');
    
    echo "<p>Testando para Salão ID: {$salao_id}, Profissional ID: {$profissional_id}, Data: {$data}</p>";
    
    // Simular chamada da API
    $_GET['salao_id'] = $salao_id;
    $_GET['profissional_id'] = $profissional_id;
    $_GET['data'] = $data;
    
    ob_start();
    include 'api/horarios.php';
    $api_response = ob_get_clean();
    
    echo "<h4>Resposta da API:</h4>";
    echo "<pre>" . htmlspecialchars($api_response) . "</pre>";
    
    // Verificar agendamentos existentes
    $agendamentoModel = new Agendamento();
    $horariosOcupados = $agendamentoModel->listarHorariosOcupados($profissional_id, $data);
    
    echo "<h4>Horários ocupados:</h4>";
    echo "<pre>" . print_r($horariosOcupados, true) . "</pre>";
    
    // Verificar todos os agendamentos
    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id_profissional = ? AND data = ?");
    $stmt->execute([$profissional_id, $data]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Agendamentos na data:</h4>";
    echo "<pre>" . print_r($agendamentos, true) . "</pre>";
}

// Verificar estrutura da tabela agendamentos
echo "<h3>Estrutura da tabela agendamentos</h3>";
try {
    $stmt = $pdo->query("DESCRIBE agendamentos");
    $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($estrutura, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao verificar estrutura: " . $e->getMessage() . "</p>";
}

// Verificar configuração de horários
echo "<h3>Configuração de Horários</h3>";
if (!empty($saloes)) {
    $salao = $saloes[0];
    echo "<p>Horário de funcionamento do salão: {$salao['horario_funcionamento']}</p>";
    
    // Tentar decodificar JSON se for o caso
    $horarios = json_decode($salao['horario_funcionamento'], true);
    if ($horarios) {
        echo "<p>Horários decodificados:</p>";
        echo "<pre>" . print_r($horarios, true) . "</pre>";
    }
}

echo "<p><strong>Debug concluído!</strong></p>";
?>