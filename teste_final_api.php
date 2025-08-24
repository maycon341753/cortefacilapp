<?php
/**
 * Teste final da API de horários após correções
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste Final - API de Horários Corrigida</h1>";
echo "<hr>";

// Simular sessão de cliente logado
session_start();
$_SESSION['cliente_id'] = 1;
$_SESSION['cliente_nome'] = 'Cliente Teste';
$_SESSION['cliente_logado'] = true;

echo "<h2>1. Simulando Sessão de Cliente</h2>";
echo "<p>✅ Cliente logado: ID = " . $_SESSION['cliente_id'] . ", Nome = " . $_SESSION['cliente_nome'] . "</p>";

echo "<h2>2. Testando API com Diferentes Parâmetros</h2>";

$testes = [
    ['profissional' => 1, 'data' => '2024-01-20'],
    ['profissional' => 1, 'data' => '2024-01-21'],
    ['profissional' => 2, 'data' => '2024-01-20']
];

foreach ($testes as $i => $teste) {
    echo "<h3>Teste " . ($i + 1) . ": Profissional {$teste['profissional']}, Data {$teste['data']}</h3>";
    
    // Simular parâmetros GET
    $_GET['profissional'] = $teste['profissional'];
    $_GET['data'] = $teste['data'];
    
    ob_start();
    try {
        include 'api/horarios.php';
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = json_encode(['error' => $e->getMessage()]);
    }
    ob_end_clean();
    
    echo "<div style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; margin: 10px 0;'>";
    echo "<strong>Resposta:</strong><br>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Tentar fazer parse JSON
    $json = json_decode($output, true);
    if ($json !== null) {
        echo "<p style='color: green;'>✅ JSON válido</p>";
        if (isset($json['success']) && $json['success']) {
            echo "<p style='color: green;'>✅ Sucesso: " . count($json['data']) . " horários encontrados</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ API retornou erro: " . ($json['error'] ?? 'Erro desconhecido') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ JSON inválido</p>";
    }
    echo "</div>";
}

echo "<h2>3. Teste JavaScript (Simulação Frontend)</h2>";
?>

<div id="teste-js"></div>

<script>
function testarAPIJavaScript() {
    const resultado = document.getElementById('teste-js');
    resultado.innerHTML = '<p>Testando API via JavaScript...</p>';
    
    // Simular exatamente como o frontend faz
    const CorteFacilTest = {
        ajax: {
            get: function(url, callback) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (typeof callback === 'function') {
                            callback(null, data);
                        }
                    })
                    .catch(error => {
                        if (typeof callback === 'function') {
                            callback(error, null);
                        }
                    });
            }
        }
    };
    
    CorteFacilTest.ajax.get('api/horarios.php?profissional=1&data=2024-01-20', function(error, horarios) {
        if (error) {
            console.error('Erro ao carregar horários:', error);
            resultado.innerHTML = `
                <div style="background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; margin: 10px 0;">
                    <h4>❌ Erro na simulação JavaScript:</h4>
                    <pre>${error.message}</pre>
                </div>
            `;
            return;
        }
        
        console.log('Horários recebidos via JS:', horarios);
        resultado.innerHTML = `
            <div style="background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; margin: 10px 0;">
                <h4>✅ Simulação JavaScript - Sucesso:</h4>
                <pre>${JSON.stringify(horarios, null, 2)}</pre>
                <p><strong>Quantidade de horários:</strong> ${horarios && horarios.data ? horarios.data.length : 0}</p>
            </div>
        `;
    });
}

// Executar teste automático
window.onload = function() {
    setTimeout(testarAPIJavaScript, 1000);
};
</script>

<?php
echo "<h2>4. Links para Teste Manual</h2>";
echo "<p><a href='api/horarios.php?profissional=1&data=2024-01-20' target='_blank'>Testar API diretamente</a></p>";
echo "<p><a href='cliente/agendar.php' target='_blank'>Testar página de agendamento</a></p>";

echo "<hr>";
echo "<p><em>Teste concluído em " . date('Y-m-d H:i:s') . "</em></p>";
?>