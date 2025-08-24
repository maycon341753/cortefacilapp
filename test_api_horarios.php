<?php
/**
 * Teste específico da API de horários
 * Verifica se a API está retornando horários disponíveis corretamente
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';
require_once 'models/agendamento.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h2>Teste da API de Horários</h2>";

// Simular login de cliente para teste
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'cliente';
$_SESSION['user_name'] = 'Teste Cliente';

// Buscar dados para teste
$salaoModel = new Salao();
$profissionalModel = new Profissional();
$agendamentoModel = new Agendamento();

$saloes = $salaoModel->listarAtivos();
echo "<h3>Salões ativos encontrados: " . count($saloes) . "</h3>";

if (empty($saloes)) {
    echo "<p style='color: red;'>❌ Nenhum salão ativo encontrado!</p>";
    exit;
}

$salao = $saloes[0];
echo "<p>✓ Usando salão: {$salao['nome']} (ID: {$salao['id']})</p>";

$profissionais = $profissionalModel->listarPorSalao($salao['id']);
echo "<h3>Profissionais do salão: " . count($profissionais) . "</h3>";

if (empty($profissionais)) {
    echo "<p style='color: red;'>❌ Nenhum profissional encontrado para este salão!</p>";
    exit;
}

$profissional = $profissionais[0];
echo "<p>✓ Usando profissional: {$profissional['nome']} (ID: {$profissional['id']})</p>";

$data_teste = date('Y-m-d', strtotime('+1 day')); // Amanhã
echo "<p>✓ Data de teste: {$data_teste}</p>";

echo "<hr>";

// Teste 1: Verificar horários ocupados
echo "<h3>1. Horários ocupados</h3>";
$horariosOcupados = $agendamentoModel->listarHorariosOcupados($profissional['id'], $data_teste);
echo "<p>Horários ocupados encontrados: " . count($horariosOcupados) . "</p>";
if (!empty($horariosOcupados)) {
    echo "<ul>";
    foreach ($horariosOcupados as $hora) {
        echo "<li>{$hora}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>✓ Nenhum horário ocupado para esta data</p>";
}

// Teste 2: Gerar horários disponíveis
echo "<h3>2. Horários disponíveis (método direto)</h3>";
$horariosDisponiveis = $agendamentoModel->gerarHorariosDisponiveis($profissional['id'], $data_teste);
echo "<p>Horários disponíveis: " . count($horariosDisponiveis) . "</p>";
if (!empty($horariosDisponiveis)) {
    echo "<ul>";
    foreach ($horariosDisponiveis as $hora) {
        echo "<li>{$hora}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Nenhum horário disponível!</p>";
}

// Teste 3: Simular chamada da API
echo "<h3>3. Teste da API (simulação)</h3>";
$_GET['profissional'] = $profissional['id'];
$_GET['data'] = $data_teste;

echo "<p>Parâmetros da API:</p>";
echo "<ul>";
echo "<li>profissional: {$_GET['profissional']}</li>";
echo "<li>data: {$_GET['data']}</li>";
echo "</ul>";

ob_start();
include 'api/horarios.php';
$api_response = ob_get_clean();

echo "<h4>Resposta da API:</h4>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($api_response) . "</pre>";

// Teste 4: Verificar se há problemas de autenticação
echo "<h3>4. Status da sessão</h3>";
echo "<p>Usuário logado: " . (isLoggedIn() ? '✓ Sim' : '❌ Não') . "</p>";
echo "<p>Tipo de usuário: " . ($_SESSION['user_type'] ?? 'não definido') . "</p>";
echo "<p>ID do usuário: " . ($_SESSION['user_id'] ?? 'não definido') . "</p>";

// Teste 5: Verificar estrutura do banco
echo "<h3>5. Verificação do banco de dados</h3>";
try {
    $pdo = Database::getInstance()->getConnection();
    echo "<p>✓ Conexão com banco estabelecida</p>";
    
    // Verificar se existem agendamentos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
    $total_agendamentos = $stmt->fetch()['total'];
    echo "<p>Total de agendamentos no banco: {$total_agendamentos}</p>";
    
    // Verificar agendamentos para o profissional de teste
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos WHERE id_profissional = ? AND data = ?");
    $stmt->execute([$profissional['id'], $data_teste]);
    $agendamentos_profissional = $stmt->fetch()['total'];
    echo "<p>Agendamentos para o profissional na data de teste: {$agendamentos_profissional}</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Teste concluído!</strong></p>";
?>