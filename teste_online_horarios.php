<?php
// Teste direto no servidor online para verificar horários

// Configuração do banco online (credenciais corretas)
$host = 'srv486.hstgr.io';
$dbname = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'Maycon341753';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conexão com banco online estabelecida\n";
    
    // Verificar se tabela horarios existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'horarios'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabela 'horarios' existe\n";
        
        // Verificar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE horarios");
        echo "\nEstrutura da tabela 'horarios':\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
        
        // Verificar dados na tabela
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM horarios");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "\n✓ Total de horários cadastrados: $total\n";
        
        if ($total > 0) {
            // Mostrar alguns horários de exemplo
            $stmt = $pdo->query("SELECT * FROM horarios LIMIT 5");
            echo "\nExemplos de horários:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "- ID: {$row['id']}, Horário: {$row['hora_inicio']}-{$row['hora_fim']}, Profissional: {$row['profissional_id']}, Salão: {$row['salao_id']}\n";
            }
        }
    } else {
        echo "✗ Tabela 'horarios' NÃO existe\n";
    }
    
    // Verificar tabela bloqueios_temporarios
    $stmt = $pdo->query("SHOW TABLES LIKE 'bloqueios_temporarios'");
    if ($stmt->rowCount() > 0) {
        echo "\n✓ Tabela 'bloqueios_temporarios' existe\n";
    } else {
        echo "\n✗ Tabela 'bloqueios_temporarios' NÃO existe\n";
    }
    
    // Testar API de horários simulando parâmetros
    echo "\n=== TESTE DA API DE HORÁRIOS ===\n";
    
    // Simular parâmetros GET
    $_GET['salao_id'] = '1';
    $_GET['profissional_id'] = '1';
    $_GET['data'] = '2025-01-23';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    // Capturar output da API
    ob_start();
    include 'api/horarios.php';
    $api_output = ob_get_clean();
    
    echo "Resposta da API:\n";
    echo $api_output . "\n";
    
    // Verificar se é JSON válido
    $json_data = json_decode($api_output, true);
    if ($json_data) {
        echo "\n✓ API retornou JSON válido\n";
        if (isset($json_data['success']) && $json_data['success']) {
            echo "✓ API funcionando corretamente\n";
            if (isset($json_data['horarios']) && count($json_data['horarios']) > 0) {
                echo "✓ Horários encontrados: " . count($json_data['horarios']) . "\n";
            } else {
                echo "✗ Nenhum horário retornado pela API\n";
            }
        } else {
            echo "✗ API retornou erro: " . ($json_data['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "✗ API não retornou JSON válido\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>