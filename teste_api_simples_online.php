<?php
require_once 'includes/functions.php';

echo "<h2>Teste Simples da API de Horários Online</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Forçar ambiente online
$_ENV['ENVIRONMENT'] = 'online';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';

// Simular sessão
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'cliente';

// Parâmetros de teste
$profissional_id = 1;
$data = date('Y-m-d');

echo "<p class='info'>Testando API com:</p>";
echo "<ul>";
echo "<li>Profissional ID: $profissional_id</li>";
echo "<li>Data: $data</li>";
echo "</ul>";

// Simular chamada GET
$_GET['profissional_id'] = $profissional_id;
$_GET['data'] = $data;

echo "<h3>Resposta da API:</h3>";
echo "<div style='background:#f8f9fa;padding:15px;border:1px solid #dee2e6;border-radius:5px;'>";

// Capturar output da API
ob_start();
try {
    include 'api/horarios.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
$api_output = ob_get_clean();

echo "<pre>" . htmlspecialchars($api_output) . "</pre>";
echo "</div>";

// Tentar decodificar JSON
$response = json_decode($api_output, true);
if ($response) {
    echo "<h3>Análise da Resposta:</h3>";
    if (isset($response['success']) && $response['success']) {
        echo "<p class='success'>✅ API funcionou com sucesso!</p>";
        if (isset($response['data'])) {
            echo "<p class='info'>📊 Horários encontrados: " . count($response['data']) . "</p>";
            if (!empty($response['data'])) {
                echo "<p class='info'>🕐 Horários: " . implode(', ', $response['data']) . "</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ API retornou erro</p>";
        if (isset($response['error'])) {
            echo "<p class='error'>Erro: " . htmlspecialchars($response['error']) . "</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Resposta não é um JSON válido</p>";
}

echo "<hr>";
echo "<p><a href='cliente/agendar.php' target='_blank'>🎯 Testar na página de agendamento</a></p>";
?>