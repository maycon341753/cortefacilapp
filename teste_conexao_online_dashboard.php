<?php
/**
 * Script para testar especificamente a conexão online e o método buscarPorDono
 * que está causando erro no dashboard do parceiro
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/salao.php';

echo "<h2>Teste de Conexão Online - Dashboard Parceiro</h2>";

try {
    // 1. Testar detecção de ambiente
    echo "<h3>1. Detecção de Ambiente:</h3>";
    echo "<p>SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'não definido') . "</p>";
    echo "<p>HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "</p>";
    echo "<p>DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não definido') . "</p>";
    
    $db = Database::getInstance();
    $debugInfo = $db->getDebugInfo();
    echo "<p>Ambiente detectado: " . $debugInfo['environment'] . "</p>";
    
    // 2. Forçar conexão online
    echo "<h3>2. Forçando Conexão Online:</h3>";
    $db->forceOnlineConfig();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Falha na conexão online');
    }
    
    echo "<p style='color: green;'>✅ Conexão online estabelecida com sucesso</p>";
    
    // 3. Testar consulta direta na tabela saloes
    echo "<h3>3. Teste de Consulta Direta:</h3>";
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM saloes WHERE id_dono IS NOT NULL");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p>Total de salões com id_dono: {$result['total']}</p>";
    
    // 4. Testar método buscarPorDono com usuário específico
    echo "<h3>4. Teste do Método buscarPorDono:</h3>";
    
    // Primeiro, vamos ver quais id_dono existem
    $stmt = $conn->prepare("SELECT DISTINCT id_dono FROM saloes WHERE id_dono IS NOT NULL LIMIT 5");
    $stmt->execute();
    $donos = $stmt->fetchAll();
    
    echo "<p>IDs de donos encontrados na tabela:</p>";
    echo "<ul>";
    foreach ($donos as $dono) {
        echo "<li>ID: {$dono['id_dono']}</li>";
    }
    echo "</ul>";
    
    if (!empty($donos)) {
        $salao = new Salao();
        $primeiro_dono_id = $donos[0]['id_dono'];
        
        echo "<p>Testando buscarPorDono com ID: {$primeiro_dono_id}</p>";
        
        $resultado = $salao->buscarPorDono($primeiro_dono_id);
        
        if ($resultado) {
            echo "<p style='color: green;'>✅ Método buscarPorDono funcionou!</p>";
            echo "<p>Salão encontrado: {$resultado['nome']}</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Método retornou false, mas sem erro</p>";
        }
    }
    
    // 5. Simular o que acontece no dashboard
    echo "<h3>5. Simulação do Dashboard:</h3>";
    
    // Simular usuário logado (você pode ajustar este ID)
    $usuario_teste = ['id' => 1, 'nome' => 'Teste', 'tipo' => 'parceiro'];
    
    echo "<p>Simulando usuário logado com ID: {$usuario_teste['id']}</p>";
    
    $salao = new Salao();
    $meu_salao = $salao->buscarPorDono($usuario_teste['id']);
    
    if ($meu_salao) {
        echo "<p style='color: green;'>✅ Dashboard funcionaria! Salão encontrado: {$meu_salao['nome']}</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Dashboard redirecionaria para cadastro de salão (usuário não tem salão)</p>";
    }
    
    // 6. Verificar logs de erro
    echo "<h3>6. Verificação de Logs:</h3>";
    $error_log_path = __DIR__ . '/parceiro/error.log';
    if (file_exists($error_log_path)) {
        $recent_errors = array_slice(file($error_log_path), -5);
        echo "<p>Últimos 5 erros do dashboard:</p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
        foreach ($recent_errors as $error) {
            echo htmlspecialchars($error);
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>Conclusão:</strong> Se este script funcionar sem erros, o problema pode estar na detecção de ambiente ou na sessão do usuário no dashboard real.</p>";
?>