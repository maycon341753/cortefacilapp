<?php
// Teste final das correções de horários
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Teste Final - Correções de Horários</h2>";
echo "<style>";
echo ".success { color: green; font-weight: bold; }";
echo ".error { color: red; font-weight: bold; }";
echo ".warning { color: orange; font-weight: bold; }";
echo ".test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo "</style>";

// 1. Teste de conexão e dados básicos
echo "<div class='test-section'>";
echo "<h3>1. Verificação de Dados Básicos</h3>";

try {
    require_once 'config/database.php';
    require_once 'models/agendamento.php';
    require_once 'models/profissional.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar um profissional ativo
    $stmt = $db->prepare("SELECT p.id, p.nome, p.id_salao, s.nome as salao_nome FROM profissionais p JOIN saloes s ON p.id_salao = s.id WHERE p.ativo = 1 LIMIT 1");
    $stmt->execute();
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profissional) {
        echo "<p class='success'>✅ Profissional encontrado: " . $profissional['nome'] . " (ID: " . $profissional['id'] . ")</p>";
        echo "<p>Salão: " . $profissional['salao_nome'] . " (ID: " . $profissional['id_salao'] . ")</p>";
        
        // Verificar horários do salão
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM horarios_funcionamento WHERE id_salao = ?");
        $stmt->execute([$profissional['id_salao']]);
        $horarios_count = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($horarios_count['total'] > 0) {
            echo "<p class='success'>✅ Horários de funcionamento configurados: " . $horarios_count['total'] . " registros</p>";
        } else {
            echo "<p class='error'>❌ Nenhum horário de funcionamento configurado</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Nenhum profissional ativo encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 2. Teste da API de horários
if (isset($profissional)) {
    echo "<div class='test-section'>";
    echo "<h3>2. Teste da API de Horários</h3>";
    
    $data_teste = date('Y-m-d', strtotime('+1 day'));
    echo "<p>Testando para: Profissional ID " . $profissional['id'] . ", Data: " . $data_teste . "</p>";
    
    // Simular chamada da API
    $_GET['profissional_id'] = $profissional['id'];
    $_GET['data'] = $data_teste;
    
    // Simular sessão de usuário logado
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['user_type'] = 'cliente';
    
    ob_start();
    try {
        include 'api/horarios.php';
        $api_response = ob_get_contents();
    } catch (Exception $e) {
        $api_response = json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    ob_end_clean();
    
    echo "<h4>Resposta da API:</h4>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($api_response) . "</pre>";
    
    // Tentar decodificar JSON
    $json_data = json_decode($api_response, true);
    if ($json_data !== null) {
        if (isset($json_data['success']) && $json_data['success']) {
            $horarios_count = isset($json_data['data']) ? count($json_data['data']) : 0;
            echo "<p class='success'>✅ API funcionando corretamente - " . $horarios_count . " horários disponíveis</p>";
            
            if ($horarios_count > 0) {
                echo "<p>Primeiros horários: " . implode(', ', array_slice($json_data['data'], 0, 5)) . "</p>";
            }
        } else {
            echo "<p class='error'>❌ API retornou erro: " . ($json_data['error'] ?? 'Erro desconhecido') . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Resposta da API não é um JSON válido</p>";
    }
    
    echo "</div>";
}

// 3. Teste JavaScript
echo "<div class='test-section'>";
echo "<h3>3. Teste JavaScript</h3>";
echo "<button onclick='testarJavaScript()' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Testar Chamada JavaScript</button>";
echo "<div id='resultado-js' style='margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; min-height: 50px;'></div>";
echo "</div>";

// 4. Resumo e próximos passos
echo "<div class='test-section'>";
echo "<h3>4. Resumo das Correções Aplicadas</h3>";
echo "<ul>";
echo "<li>✅ Corrigido parâmetro da API de 'profissional' para 'profissional_id'</li>";
echo "<li>✅ Atualizado JavaScript para tratar resposta da API corretamente</li>";
echo "<li>✅ API agora aceita ambos os parâmetros para compatibilidade</li>";
echo "<li>✅ Melhorado tratamento de erros no frontend</li>";
echo "</ul>";
echo "</div>";

if (isset($profissional)) {
    echo "<script>";
    echo "function testarJavaScript() {";
    echo "    const resultDiv = document.getElementById('resultado-js');";
    echo "    resultDiv.innerHTML = 'Testando...';";
    echo "    ";
    echo "    const url = 'api/horarios.php?profissional_id=" . $profissional['id'] . "&data=" . $data_teste . "';";
    echo "    console.log('Testando URL:', url);";
    echo "    ";
    echo "    fetch(url)";
    echo "        .then(response => {";
    echo "            console.log('Status:', response.status);";
    echo "            return response.json();";
    echo "        })";
    echo "        .then(data => {";
    echo "            console.log('Dados recebidos:', data);";
    echo "            ";
    echo "            if (data.success) {";
    echo "                const count = data.data ? data.data.length : 0;";
    echo "                resultDiv.innerHTML = '<p class=\"success\">✅ Sucesso! ' + count + ' horários disponíveis</p>';";
    echo "                if (count > 0) {";
    echo "                    resultDiv.innerHTML += '<p>Horários: ' + data.data.slice(0, 5).join(', ') + '</p>';";
    echo "                }";
    echo "            } else {";
    echo "                resultDiv.innerHTML = '<p class=\"error\">❌ Erro: ' + (data.error || 'Erro desconhecido') + '</p>';";
    echo "            }";
    echo "        })";
    echo "        .catch(error => {";
    echo "            console.error('Erro:', error);";
    echo "            resultDiv.innerHTML = '<p class=\"error\">❌ Erro na requisição: ' + error.message + '</p>';";
    echo "        });";
    echo "}";
    echo "</script>";
}

echo "<p style='margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 5px;'>";
echo "<strong>Próximo passo:</strong> Teste a página de agendamento em <a href='cliente/agendar.php' target='_blank'>cliente/agendar.php</a> para verificar se os horários estão carregando corretamente.";
echo "</p>";
?>