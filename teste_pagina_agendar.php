<?php
// Teste da página de agendamento online
echo "=== TESTE DA PÁGINA DE AGENDAMENTO ONLINE ===\n";

// Simular uma requisição para a página de agendamento
$url = 'https://cortefacil.app/cliente/agendar.php';

echo "Testando URL: $url\n\n";

// Configurar contexto para requisição HTTP
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        ],
        'timeout' => 30
    ]
]);

try {
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "✗ Erro ao acessar a página\n";
        exit(1);
    }
    
    echo "✓ Página carregada com sucesso\n";
    echo "Tamanho da resposta: " . strlen($response) . " bytes\n\n";
    
    // Verificar se há redirecionamento para login
    if (strpos($response, 'login.php') !== false || strpos($response, 'Location: login.php') !== false) {
        echo "⚠ Página está redirecionando para login\n";
    }
    
    // Verificar se contém elementos esperados da página de agendamento
    $elementos_esperados = [
        'Agendar Serviço' => 'Título da página',
        'Escolha o Salão' => 'Seleção de salão',
        'Escolha o Profissional' => 'Seleção de profissional',
        'Escolha Data e Horário' => 'Seleção de data/horário',
        'api/horarios.php' => 'Chamada para API de horários'
    ];
    
    echo "=== VERIFICAÇÃO DE ELEMENTOS ===\n";
    foreach ($elementos_esperados as $elemento => $descricao) {
        if (strpos($response, $elemento) !== false) {
            echo "✓ $descricao encontrado\n";
        } else {
            echo "✗ $descricao NÃO encontrado\n";
        }
    }
    
    // Verificar se há erros JavaScript ou PHP visíveis
    echo "\n=== VERIFICAÇÃO DE ERROS ===\n";
    $erros = [
        'Fatal error' => 'Erro fatal PHP',
        'Parse error' => 'Erro de sintaxe PHP',
        'Warning:' => 'Warning PHP',
        'Notice:' => 'Notice PHP',
        'Uncaught' => 'Erro JavaScript não capturado'
    ];
    
    $tem_erros = false;
    foreach ($erros as $erro => $descricao) {
        if (strpos($response, $erro) !== false) {
            echo "✗ $descricao detectado\n";
            $tem_erros = true;
        }
    }
    
    if (!$tem_erros) {
        echo "✓ Nenhum erro visível detectado\n";
    }
    
    // Mostrar início da resposta para debug
    echo "\n=== INÍCIO DA RESPOSTA (primeiros 500 caracteres) ===\n";
    echo substr($response, 0, 500) . "...\n";
    
} catch (Exception $e) {
    echo "✗ Erro na requisição: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>