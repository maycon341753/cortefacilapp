<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Verificar se está logado
if (!isLoggedIn()) {
    echo "<h2>❌ Usuário não está logado</h2>";
    echo "<p><a href='../login.php'>Fazer login</a></p>";
    exit;
}

echo "<h1>🔍 Teste da API de Horários - Debug Completo</h1>";
echo "<hr>";

// 1. Verificar dados básicos
echo "<h2>1. Verificação de Dados Básicos</h2>";

try {
    $pdo = getConnection();
    
    // Verificar profissionais ativos
    $stmt = $pdo->query("SELECT id, nome, ativo FROM profissionais WHERE ativo = 1 ORDER BY id LIMIT 5");
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Profissionais ativos encontrados:</strong> " . count($profissionais) . "</p>";
    if (count($profissionais) > 0) {
        echo "<ul>";
        foreach ($profissionais as $prof) {
            echo "<li>ID: {$prof['id']} - Nome: {$prof['nome']} - Ativo: {$prof['ativo']}</li>";
        }
        echo "</ul>";
        $primeiro_profissional = $profissionais[0]['id'];
    } else {
        echo "<p>❌ Nenhum profissional ativo encontrado!</p>";
        exit;
    }
    
    // Verificar horários de funcionamento
    $stmt = $pdo->query("SELECT * FROM horarios_funcionamento ORDER BY salao_id, dia_semana LIMIT 10");
    $horarios_funcionamento = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Horários de funcionamento cadastrados:</strong> " . count($horarios_funcionamento) . "</p>";
    if (count($horarios_funcionamento) > 0) {
        echo "<ul>";
        foreach ($horarios_funcionamento as $hf) {
            echo "<li>Salão: {$hf['salao_id']} - Dia: {$hf['dia_semana']} - {$hf['hora_inicio']} às {$hf['hora_fim']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>⚠️ Nenhum horário de funcionamento cadastrado!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro na verificação básica: " . $e->getMessage() . "</p>";
    exit;
}

echo "<hr>";

// 2. Testar API de horários com diferentes cenários
echo "<h2>2. Teste da API de Horários</h2>";

$data_hoje = date('Y-m-d');
$data_amanha = date('Y-m-d', strtotime('+1 day'));

$testes = [
    [
        'nome' => 'Teste 1: Data de hoje',
        'profissional_id' => $primeiro_profissional,
        'data' => $data_hoje
    ],
    [
        'nome' => 'Teste 2: Data de amanhã',
        'profissional_id' => $primeiro_profissional,
        'data' => $data_amanha
    ]
];

foreach ($testes as $teste) {
    echo "<h3>{$teste['nome']}</h3>";
    echo "<p><strong>Parâmetros:</strong></p>";
    echo "<ul>";
    echo "<li>profissional_id: {$teste['profissional_id']}</li>";
    echo "<li>data: {$teste['data']}</li>";
    echo "</ul>";
    
    // Simular chamada da API
    $_GET = [];
    $_GET['profissional_id'] = $teste['profissional_id'];
    $_GET['data'] = $teste['data'];
    
    echo "<p><strong>Chamando API...</strong></p>";
    
    ob_start();
    try {
        include 'api/horarios.php';
        $api_response = ob_get_clean();
        
        echo "<p><strong>Resposta da API:</strong></p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars($api_response);
        echo "</pre>";
        
        // Verificar se é JSON válido
        $json_data = json_decode($api_response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>✅ Resposta é JSON válido</p>";
            
            if (isset($json_data['success'])) {
                if ($json_data['success']) {
                    echo "<p>✅ API retornou sucesso</p>";
                    if (isset($json_data['data']) && is_array($json_data['data'])) {
                        echo "<p><strong>Horários encontrados:</strong> " . count($json_data['data']) . "</p>";
                        if (count($json_data['data']) > 0) {
                            echo "<ul>";
                            foreach ($json_data['data'] as $horario) {
                                echo "<li>$horario</li>";
                            }
                            echo "</ul>";
                        }
                    } else {
                        echo "<p>⚠️ Campo 'data' não encontrado ou não é array</p>";
                    }
                } else {
                    echo "<p>❌ API retornou erro: " . ($json_data['message'] ?? 'Erro desconhecido') . "</p>";
                }
            } else if (is_array($json_data)) {
                echo "<p>✅ Resposta é array direto com " . count($json_data) . " horários</p>";
                if (count($json_data) > 0) {
                    echo "<ul>";
                    foreach ($json_data as $horario) {
                        echo "<li>$horario</li>";
                    }
                    echo "</ul>";
                }
            }
        } else {
            echo "<p>❌ Resposta não é JSON válido. Erro: " . json_last_error_msg() . "</p>";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p>❌ Erro ao executar API: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>3. Teste JavaScript Simulado</h2>";
?>

<div id="teste-select">
    <label>Profissional:</label>
    <select id="profissional">
        <option value="">Selecione...</option>
        <?php foreach ($profissionais as $prof): ?>
            <option value="<?php echo $prof['id']; ?>"><?php echo htmlspecialchars($prof['nome']); ?></option>
        <?php endforeach; ?>
    </select>
    
    <label>Data:</label>
    <input type="date" id="data" value="<?php echo $data_hoje; ?>" min="<?php echo $data_hoje; ?>">
    
    <label>Horário:</label>
    <select id="hora">
        <option value="">Primeiro selecione profissional e data...</option>
    </select>
    
    <button onclick="testarCarregamentoHorarios()">🔄 Testar Carregamento</button>
</div>

<div id="log-teste" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
    <h4>📋 Log do Teste</h4>
    <div id="log-content"></div>
</div>

<script>
function log(message) {
    const logContent = document.getElementById('log-content');
    const timestamp = new Date().toLocaleTimeString();
    logContent.innerHTML += `<div>[${timestamp}] ${message}</div>`;
    console.log(message);
}

function testarCarregamentoHorarios() {
    log('🚀 Iniciando teste de carregamento de horários');
    
    const profissionalSelect = document.getElementById('profissional');
    const dataInput = document.getElementById('data');
    const horaSelect = document.getElementById('hora');
    
    const profissionalId = profissionalSelect.value;
    const data = dataInput.value;
    
    log(`📋 Parâmetros: profissional_id=${profissionalId}, data=${data}`);
    
    if (!profissionalId || !data) {
        log('❌ Profissional ou data não selecionados');
        return;
    }
    
    horaSelect.innerHTML = '<option value="">Carregando...</option>';
    
    const url = `api/horarios.php?profissional_id=${profissionalId}&data=${data}`;
    log(`🌐 Fazendo requisição para: ${url}`);
    
    fetch(url)
        .then(response => {
            log(`📡 Status da resposta: ${response.status} ${response.statusText}`);
            return response.text();
        })
        .then(text => {
            log(`📄 Resposta recebida (${text.length} caracteres)`);
            
            try {
                const response = JSON.parse(text);
                log('✅ JSON parseado com sucesso');
                
                horaSelect.innerHTML = '<option value="">Selecione um horário...</option>';
                
                let horarios = [];
                if (response && response.success && response.data) {
                    horarios = response.data;
                    log(`📋 Horários extraídos (formato success/data): ${horarios.length} itens`);
                } else if (Array.isArray(response)) {
                    horarios = response;
                    log(`📋 Horários extraídos (formato array): ${horarios.length} itens`);
                } else {
                    log('⚠️ Formato de resposta não reconhecido');
                }
                
                if (horarios && horarios.length > 0) {
                    log(`✅ Populando select com ${horarios.length} horários`);
                    horarios.forEach(hora => {
                        const option = document.createElement('option');
                        option.value = hora;
                        option.textContent = hora;
                        horaSelect.appendChild(option);
                    });
                    log('✅ Select populado com sucesso!');
                } else {
                    log('⚠️ Nenhum horário disponível');
                    horaSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
                }
                
            } catch (error) {
                log(`❌ Erro ao fazer parse do JSON: ${error.message}`);
                log(`📄 Texto original: ${text.substring(0, 200)}...`);
                horaSelect.innerHTML = '<option value="">Erro ao processar horários</option>';
            }
        })
        .catch(error => {
            log(`❌ Erro na requisição: ${error.message}`);
            horaSelect.innerHTML = '<option value="">Erro ao carregar horários</option>';
        });
}

// Event listeners
document.getElementById('profissional').addEventListener('change', function() {
    if (this.value && document.getElementById('data').value) {
        testarCarregamentoHorarios();
    }
});

document.getElementById('data').addEventListener('change', function() {
    if (this.value && document.getElementById('profissional').value) {
        testarCarregamentoHorarios();
    }
});

log('🎯 Teste carregado. Selecione um profissional e data para testar automaticamente.');
</script>

<style>
#teste-select {
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}

#teste-select label {
    display: inline-block;
    width: 100px;
    margin-right: 10px;
    font-weight: bold;
}

#teste-select select, #teste-select input {
    margin: 5px 10px 5px 0;
    padding: 5px;
    width: 200px;
}

#teste-select button {
    margin: 10px 0;
    padding: 8px 15px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

#log-content {
    font-family: monospace;
    font-size: 12px;
    max-height: 300px;
    overflow-y: auto;
    background: white;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
}
</style>