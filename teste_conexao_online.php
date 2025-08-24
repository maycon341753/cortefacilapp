<?php
/**
 * Teste de conexão com banco de dados online
 * Verifica se a API consegue conectar e buscar dados
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão - Banco Online</h1>";
echo "<hr>";

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'models/agendamento.php';

echo "<h2>1. Testando Detecção de Ambiente</h2>";
echo "<p><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</p>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";
echo "<p><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</p>";
echo "<p><strong>Arquivo .env.online existe:</strong> " . (file_exists('.env.online') ? 'SIM' : 'NÃO') . "</p>";

echo "<h2>2. Testando Conexão com Banco</h2>";
try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
        
        // Testar uma query simples
        $stmt = $conn->prepare("SELECT DATABASE() as db_name");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Banco conectado:</strong> " . $result['db_name'] . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Testando Tabelas</h2>";
try {
    // Verificar se as tabelas existem
    $tabelas = ['saloes', 'profissionais', 'agendamentos', 'usuarios'];
    
    foreach ($tabelas as $tabela) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM $tabela");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Tabela $tabela:</strong> " . $result['total'] . " registros</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar tabelas: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Testando Modelo Agendamento</h2>";
try {
    $agendamento = new Agendamento($conn);
    
    // Testar geração de horários
    $horarios = $agendamento->gerarHorariosDisponiveis(1, '2024-01-20');
    echo "<p><strong>Horários gerados:</strong> " . count($horarios) . "</p>";
    echo "<pre>" . print_r($horarios, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no modelo: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Testando API Diretamente</h2>";
echo "<p>Testando chamada direta para a API...</p>";

// Simular chamada da API
$_GET['profissional'] = '1';
$_GET['data'] = '2024-01-20';

ob_start();
try {
    include 'api/horarios.php';
    $output = ob_get_contents();
} catch (Exception $e) {
    $output = "Erro: " . $e->getMessage();
}
ob_end_clean();

echo "<h3>Resposta da API:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($output);
echo "</pre>";

// Tentar fazer parse JSON
echo "<h3>Parse JSON:</h3>";
$json = json_decode($output, true);
if ($json !== null) {
    echo "<p style='color: green;'>✅ JSON válido</p>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ JSON inválido ou resposta não é JSON</p>";
    echo "<p><strong>Erro JSON:</strong> " . json_last_error_msg() . "</p>";
}

echo "<hr>";
echo "<p><em>Teste concluído em " . date('Y-m-d H:i:s') . "</em></p>";
?>