<?php
/**
 * Teste final da funcionalidade de horários
 * Verifica se tudo está funcionando após correções
 */

require_once 'config/database.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';
require_once 'models/agendamento.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "<h2>🧪 Teste Final - Sistema de Horários</h2>";

// Simular login de cliente
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'cliente';
$_SESSION['user_name'] = 'Cliente Teste';

try {
    $pdo = Database::getInstance()->getConnection();
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
    
    // 1. Verificar dados básicos
    echo "<h3>1. Verificação de Dados</h3>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM saloes WHERE status = 'ativo'");
    $saloes_ativos = $stmt->fetch()['total'];
    echo "<p>Salões ativos: <strong>{$saloes_ativos}</strong></p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM profissionais WHERE status = 'ativo'");
    $profissionais_ativos = $stmt->fetch()['total'];
    echo "<p>Profissionais ativos: <strong>{$profissionais_ativos}</strong></p>";
    
    if ($saloes_ativos == 0 || $profissionais_ativos == 0) {
        echo "<p style='color: red;'>❌ Dados insuficientes! <a href='criar_dados_teste.php'>Criar dados de teste</a></p>";
        exit;
    }
    
    // 2. Buscar dados para teste
    echo "<h3>2. Dados para Teste</h3>";
    
    $salaoModel = new Salao();
    $profissionalModel = new Profissional();
    $agendamentoModel = new Agendamento();
    
    $saloes = $salaoModel->listarAtivos();
    $salao = $saloes[0];
    echo "<p>🏪 Salão: <strong>{$salao['nome']}</strong> (ID: {$salao['id']})</p>";
    
    $profissionais = $profissionalModel->listarPorSalao($salao['id']);
    $profissional = $profissionais[0];
    echo "<p>👨‍💼 Profissional: <strong>{$profissional['nome']}</strong> (ID: {$profissional['id']})</p>";
    
    $data_teste = date('Y-m-d', strtotime('+1 day'));
    echo "<p>📅 Data de teste: <strong>{$data_teste}</strong></p>";
    
    // 3. Teste direto do modelo
    echo "<h3>3. Teste Direto do Modelo</h3>";
    
    $horariosDisponiveis = $agendamentoModel->gerarHorariosDisponiveis($profissional['id'], $data_teste);
    echo "<p>Horários disponíveis encontrados: <strong>" . count($horariosDisponiveis) . "</strong></p>";
    
    if (!empty($horariosDisponiveis)) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Horários disponíveis:</strong><br>";
        foreach ($horariosDisponiveis as $hora) {
            echo "<span style='background: #fff; padding: 3px 8px; margin: 2px; border-radius: 3px; display: inline-block;'>{$hora}</span>";
        }
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Nenhum horário disponível encontrado!</p>";
    }
    
    // 4. Teste da API
    echo "<h3>4. Teste da API</h3>";
    
    $_GET['profissional'] = $profissional['id'];
    $_GET['data'] = $data_teste;
    
    ob_start();
    include 'api/horarios.php';
    $api_response = ob_get_clean();
    
    echo "<p><strong>Resposta da API:</strong></p>";
    echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace;'>";
    echo htmlspecialchars($api_response);
    echo "</div>";
    
    $api_data = json_decode($api_response, true);
    if ($api_data && $api_data['success']) {
        echo "<p style='color: green;'>✅ API funcionando corretamente!</p>";
        echo "<p>Horários retornados pela API: <strong>" . count($api_data['data']) . "</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ Erro na API: " . ($api_data['error'] ?? 'Resposta inválida') . "</p>";
    }
    
    // 5. Teste de autenticação
    echo "<h3>5. Teste de Autenticação</h3>";
    echo "<p>Usuário logado: " . (isLoggedIn() ? '✅ Sim' : '❌ Não') . "</p>";
    echo "<p>Tipo de usuário: <strong>" . ($_SESSION['user_type'] ?? 'não definido') . "</strong></p>";
    
    // 6. Links de teste
    echo "<h3>6. Links para Teste Manual</h3>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px;'>";
    
    $api_url = "api/horarios.php?profissional={$profissional['id']}&data={$data_teste}";
    echo "<p>🔗 <a href='{$api_url}' target='_blank'>Testar API diretamente</a></p>";
    
    echo "<p>🔗 <a href='cliente/agendar.php' target='_blank'>Página de agendamento</a></p>";
    
    echo "<p>🔗 <a href='cliente/agendar.php?salao={$salao['id']}' target='_blank'>Agendamento com salão pré-selecionado</a></p>";
    
    echo "</div>";
    
    // 7. Instruções para o usuário
    echo "<h3>7. Instruções</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
    echo "<p><strong>Para testar o sistema:</strong></p>";
    echo "<ol>";
    echo "<li>Acesse a <a href='cliente/agendar.php' target='_blank'>página de agendamento</a></li>";
    echo "<li>Faça login como cliente (cliente@teste.com / 123456)</li>";
    echo "<li>Selecione o salão: <strong>{$salao['nome']}</strong></li>";
    echo "<li>Selecione o profissional: <strong>{$profissional['nome']}</strong></li>";
    echo "<li>Escolha a data: <strong>{$data_teste}</strong> ou posterior</li>";
    echo "<li>Verifique se os horários aparecem no dropdown</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>🎯 Teste final concluído!</strong></p>";
?>