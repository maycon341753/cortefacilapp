<?php
// Teste da API de horários no ambiente online
require_once 'config/database.php';
require_once 'models/agendamento.php';
require_once 'models/profissional.php';
require_once 'models/salao.php';

echo "<h2>Teste da API de Horários - Ambiente Online</h2>";

// 1. Verificar conexão com banco
echo "<h3>1. Testando Conexão com Banco</h3>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Conexão com banco estabelecida<br>";
    
    // Verificar se estamos no ambiente online
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    echo "🌐 Host atual: " . $host . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Verificar tabelas necessárias
echo "<h3>2. Verificando Tabelas</h3>";
$tabelas = ['saloes', 'profissionais', 'horarios_funcionamento', 'agendamentos'];
foreach ($tabelas as $tabela) {
    $stmt = $db->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tabela]);
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela '$tabela' existe<br>";
    } else {
        echo "❌ Tabela '$tabela' não encontrada<br>";
    }
}

// 3. Verificar dados básicos
echo "<h3>3. Verificando Dados</h3>";

// Salões ativos
$stmt = $db->prepare("SELECT COUNT(*) as total FROM saloes WHERE ativo = 1");
$stmt->execute();
$saloes = $stmt->fetch(PDO::FETCH_ASSOC);
echo "📍 Salões ativos: " . $saloes['total'] . "<br>";

// Profissionais ativos
$stmt = $db->prepare("SELECT COUNT(*) as total FROM profissionais WHERE ativo = 1");
$stmt->execute();
$profissionais = $stmt->fetch(PDO::FETCH_ASSOC);
echo "👨‍💼 Profissionais ativos: " . $profissionais['total'] . "<br>";

// Horários de funcionamento
$stmt = $db->prepare("SELECT COUNT(*) as total FROM horarios_funcionamento");
$stmt->execute();
$horarios = $stmt->fetch(PDO::FETCH_ASSOC);
echo "⏰ Horários de funcionamento: " . $horarios['total'] . "<br>";

// 4. Testar um profissional específico
echo "<h3>4. Testando Profissional Específico</h3>";
$stmt = $db->prepare("SELECT id, nome, id_salao FROM profissionais WHERE ativo = 1 LIMIT 1");
$stmt->execute();
$profissional = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profissional) {
    echo "👨‍💼 Profissional teste: " . $profissional['nome'] . " (ID: " . $profissional['id'] . ")<br>";
    echo "🏢 Salão ID: " . $profissional['id_salao'] . "<br>";
    
    // Verificar horários do salão
    $stmt = $db->prepare("SELECT * FROM horarios_funcionamento WHERE id_salao = ?");
    $stmt->execute([$profissional['id_salao']]);
    $horarios_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📅 Horários do salão:<br>";
    foreach ($horarios_salao as $horario) {
        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        echo "&nbsp;&nbsp;" . $dias[$horario['dia_semana']] . ": " . $horario['hora_abertura'] . " - " . $horario['hora_fechamento'] . "<br>";
    }
    
    // 5. Testar geração de horários
    echo "<h3>5. Testando Geração de Horários</h3>";
    $data_teste = date('Y-m-d', strtotime('+1 day'));
    echo "📅 Data teste: " . $data_teste . "<br>";
    
    try {
        $agendamento = new Agendamento($db);
        $horarios_disponiveis = $agendamento->gerarHorariosDisponiveis($profissional['id'], $data_teste);
        
        echo "⏰ Horários disponíveis: " . count($horarios_disponiveis) . "<br>";
        if (count($horarios_disponiveis) > 0) {
            echo "📋 Primeiros 5 horários:<br>";
            for ($i = 0; $i < min(5, count($horarios_disponiveis)); $i++) {
                echo "&nbsp;&nbsp;" . $horarios_disponiveis[$i] . "<br>";
            }
        } else {
            echo "❌ Nenhum horário disponível encontrado<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao gerar horários: " . $e->getMessage() . "<br>";
        echo "📋 Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    echo "❌ Nenhum profissional ativo encontrado<br>";
}

// 6. Testar API diretamente
echo "<h3>6. Testando API Diretamente</h3>";
if ($profissional) {
    $api_url = "api/horarios.php?profissional_id=" . $profissional['id'] . "&data=" . $data_teste;
    echo "🔗 URL da API: " . $api_url . "<br>";
    
    // Simular chamada da API
    $_GET['profissional_id'] = $profissional['id'];
    $_GET['data'] = $data_teste;
    
    ob_start();
    try {
        include 'api/horarios.php';
        $api_response = ob_get_contents();
    } catch (Exception $e) {
        $api_response = "Erro: " . $e->getMessage();
    }
    ob_end_clean();
    
    echo "📋 Resposta da API:<br>";
    echo "<pre>" . htmlspecialchars($api_response) . "</pre>";
}

echo "<h3>✅ Teste Concluído</h3>";
?>