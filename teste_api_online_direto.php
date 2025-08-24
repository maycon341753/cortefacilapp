<?php
/**
 * Teste direto da API de horários no servidor online
 * Simula uma chamada real da página de agendamento
 */

// Simular parâmetros GET que a página de agendamento enviaria
$_GET['salao_id'] = '10';  // ID do salão que sabemos que existe
$_GET['profissional_id'] = '20';  // ID do profissional que sabemos que existe
$_GET['data'] = '2025-01-23';  // Data de teste
$_SERVER['REQUEST_METHOD'] = 'GET';

// Configurar headers para evitar warnings
ob_start();

echo "=== TESTE DA API DE HORÁRIOS ONLINE ===\n";
echo "Parâmetros: salao_id={$_GET['salao_id']}, profissional_id={$_GET['profissional_id']}, data={$_GET['data']}\n\n";

// Incluir e executar a API
try {
    include 'api/horarios.php';
    $output = ob_get_clean();
    
    echo "Resposta da API:\n";
    echo $output . "\n";
    
    // Verificar se é JSON válido
    $json_data = json_decode($output, true);
    if ($json_data) {
        echo "\n✓ JSON válido retornado\n";
        
        if (isset($json_data['success']) && $json_data['success']) {
            echo "✓ API funcionando corretamente\n";
            echo "✓ Total de horários: " . (isset($json_data['total']) ? $json_data['total'] : 'N/A') . "\n";
            
            if (isset($json_data['horarios']) && count($json_data['horarios']) > 0) {
                echo "✓ Horários disponíveis encontrados\n";
                echo "\nPrimeiros 3 horários:\n";
                for ($i = 0; $i < min(3, count($json_data['horarios'])); $i++) {
                    $h = $json_data['horarios'][$i];
                    echo "- {$h['display']}\n";
                }
            } else {
                echo "✗ Nenhum horário retornado\n";
            }
        } else {
            echo "✗ API retornou erro: " . ($json_data['message'] ?? 'Erro desconhecido') . "\n";
        }
    } else {
        echo "✗ Resposta não é JSON válido\n";
        echo "Conteúdo retornado: " . substr($output, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "✗ Erro ao executar API: " . $e->getMessage() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>