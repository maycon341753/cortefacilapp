<?php
echo "=== TESTE DA API DE HORÁRIOS ===\n";

// Simular parâmetros GET
$_GET['salao_id'] = '11';
$_GET['profissional_id'] = '21';
$_GET['data'] = date('Y-m-d', strtotime('+1 day')); // Amanhã

echo "Testando com:\n";
echo "- Salão ID: {$_GET['salao_id']}\n";
echo "- Profissional ID: {$_GET['profissional_id']}\n";
echo "- Data: {$_GET['data']}\n\n";

// Capturar a saída da API
ob_start();
include 'api/horarios.php';
$output = ob_get_clean();

echo "Resposta da API:\n";
echo $output . "\n";

// Tentar decodificar JSON
$response = json_decode($output, true);
if ($response) {
    echo "\n=== ANÁLISE DA RESPOSTA ===\n";
    echo "Success: " . ($response['success'] ? 'true' : 'false') . "\n";
    
    if (isset($response['data'])) {
        echo "Total de horários: " . count($response['data']) . "\n";
        echo "Horários disponíveis:\n";
        foreach ($response['data'] as $horario) {
            echo "- $horario\n";
        }
    }
    
    if (isset($response['debug'])) {
        echo "\nDebug info:\n";
        echo "- Horários cadastrados: " . $response['debug']['horarios_cadastrados'] . "\n";
        echo "- Horários ocupados: " . $response['debug']['horarios_ocupados'] . "\n";
        echo "- Bloqueios temporários: " . $response['debug']['bloqueios_temporarios'] . "\n";
        
        if (isset($response['debug']['funcionamento_salao'])) {
            $func = $response['debug']['funcionamento_salao'];
            echo "- Funcionamento: Dia {$func['dia_semana']}, {$func['hora_abertura']} às {$func['hora_fechamento']}\n";
        }
    }
    
    if (isset($response['error'])) {
        echo "\nERRO: " . $response['error'] . "\n";
    }
} else {
    echo "\nERRO: Não foi possível decodificar a resposta JSON\n";
}
?>