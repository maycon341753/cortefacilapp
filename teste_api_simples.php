<?php
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Teste Simples da API de Horários</h2>";

// Testar para amanhã
$data_teste = date('Y-m-d', strtotime('+1 day'));
$profissional_id = 11; // ID do profissional de teste

echo "<h3>Testando para: Profissional {$profissional_id}, Data: {$data_teste}</h3>";

// Fazer requisição para a API
$url = "http://localhost/cortefacil/cortefacilapp/api/horarios.php?profissional={$profissional_id}&data={$data_teste}";

echo "<p><strong>URL da API:</strong> <a href='{$url}' target='_blank'>{$url}</a></p>";

// Usar cURL para fazer a requisição
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

// Iniciar sessão para simular usuário logado
session_start();
$_SESSION['usuario_logado'] = [
    'id' => 1,
    'nome' => 'Teste',
    'tipo' => 'cliente'
];

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h4>Resposta da API (HTTP {$http_code}):</h4>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($response);
echo "</pre>";

// Tentar decodificar JSON
$data = json_decode($response, true);
if ($data) {
    echo "<h4>Dados Decodificados:</h4>";
    echo "<ul>";
    echo "<li><strong>Sucesso:</strong> " . ($data['success'] ? 'Sim' : 'Não') . "</li>";
    if (isset($data['data'])) {
        echo "<li><strong>Horários encontrados:</strong> " . count($data['data']) . "</li>";
        if (!empty($data['data'])) {
            echo "<li><strong>Horários:</strong> " . implode(', ', $data['data']) . "</li>";
        }
    }
    if (isset($data['error'])) {
        echo "<li><strong>Erro:</strong> " . $data['error'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>Erro:</strong> Não foi possível decodificar a resposta JSON</p>";
}

echo "<hr>";
echo "<h3>Teste Manual</h3>";
echo "<p>Clique no link acima para testar a API diretamente no navegador.</p>";
echo "<p><a href='cliente/agendar.php' target='_blank'>🎯 Ir para página de agendamento</a></p>";
?>