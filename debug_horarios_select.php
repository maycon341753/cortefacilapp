<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Verificar se está logado
if (!isLoggedIn()) {
    echo "<h2>❌ Usuário não está logado</h2>";
    exit;
}

echo "<h1>🔍 Debug - Problema do Select de Horários</h1>";
echo "<hr>";

// 1. Verificar dados básicos
echo "<h2>1. Verificação de Dados Básicos</h2>";

try {
    $pdo = getConnection();
    
    // Verificar profissionais ativos
    $stmt = $pdo->query("SELECT id, nome, ativo FROM profissionais WHERE ativo = 1");
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Profissionais ativos encontrados:</strong> " . count($profissionais) . "</p>";
    foreach ($profissionais as $prof) {
        echo "<li>ID: {$prof['id']} - Nome: {$prof['nome']}</li>";
    }
    
    // Verificar horários de funcionamento
    $stmt = $pdo->query("SELECT * FROM horarios_funcionamento LIMIT 5");
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Horários de funcionamento cadastrados:</strong> " . count($horarios) . "</p>";
    foreach ($horarios as $horario) {
        echo "<li>Salão: {$horario['salao_id']} - Dia: {$horario['dia_semana']} - {$horario['hora_inicio']} às {$horario['hora_fim']}</li>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro na verificação básica: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 2. Testar API de horários diretamente
echo "<h2>2. Teste Direto da API de Horários</h2>";

$profissional_id = 1; // ID do primeiro profissional
$data = date('Y-m-d'); // Data de hoje

echo "<p><strong>Testando com:</strong></p>";
echo "<li>profissional_id: $profissional_id</li>";
echo "<li>data: $data</li>";

// Simular chamada da API
$_GET['profissional_id'] = $profissional_id;
$_GET['data'] = $data;

ob_start();
include 'api/horarios.php';
$api_response = ob_get_clean();

echo "<p><strong>Resposta da API:</strong></p>";
echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($api_response);
echo "</pre>";

// Verificar se é JSON válido
$json_data = json_decode($api_response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<p>✅ Resposta é JSON válido</p>";
    echo "<p><strong>Dados decodificados:</strong></p>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<p>❌ Resposta não é JSON válido. Erro: " . json_last_error_msg() . "</p>";
}

echo "<hr>";

// 3. Testar JavaScript
echo "<h2>3. Teste JavaScript - Função carregarHorarios</h2>";
?>

<script>
// Função de debug para testar carregamento de horários
function debugCarregarHorarios() {
    console.log('🔍 Iniciando debug da função carregarHorarios');
    
    const profissionalSelect = document.getElementById('profissional');
    const dataInput = document.getElementById('data');
    const horaSelect = document.getElementById('hora');
    
    console.log('Elementos encontrados:', {
        profissional: profissionalSelect ? 'OK' : 'NÃO ENCONTRADO',
        data: dataInput ? 'OK' : 'NÃO ENCONTRADO', 
        hora: horaSelect ? 'OK' : 'NÃO ENCONTRADO'
    });
    
    if (!profissionalSelect || !dataInput || !horaSelect) {
        console.error('❌ Elementos necessários não encontrados');
        return;
    }
    
    const profissionalId = profissionalSelect.value;
    const data = dataInput.value;
    
    console.log('Valores obtidos:', {
        profissionalId: profissionalId,
        data: data
    });
    
    if (!profissionalId || !data) {
        console.log('⚠️ Profissional ou data não selecionados');
        return;
    }
    
    // Fazer requisição para API
    const url = `api/horarios.php?profissional_id=${profissionalId}&data=${data}`;
    console.log('🌐 Fazendo requisição para:', url);
    
    fetch(url)
        .then(response => {
            console.log('📡 Resposta recebida:', response.status, response.statusText);
            return response.text();
        })
        .then(text => {
            console.log('📄 Texto da resposta:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('✅ JSON parseado com sucesso:', data);
                
                // Verificar estrutura da resposta
                let horarios;
                if (data.success && data.data) {
                    horarios = data.data;
                    console.log('📋 Horários extraídos (formato success/data):', horarios);
                } else if (Array.isArray(data)) {
                    horarios = data;
                    console.log('📋 Horários extraídos (formato array direto):', horarios);
                } else {
                    console.error('❌ Formato de resposta não reconhecido:', data);
                    return;
                }
                
                // Limpar select
                horaSelect.innerHTML = '';
                
                if (horarios.length === 0) {
                    console.log('⚠️ Nenhum horário disponível');
                    horaSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
                } else {
                    console.log('✅ Populando select com', horarios.length, 'horários');
                    horarios.forEach(horario => {
                        const option = document.createElement('option');
                        option.value = horario;
                        option.textContent = horario;
                        horaSelect.appendChild(option);
                        console.log('➕ Adicionado horário:', horario);
                    });
                }
                
            } catch (error) {
                console.error('❌ Erro ao fazer parse do JSON:', error);
                console.log('📄 Texto original:', text);
            }
        })
        .catch(error => {
            console.error('❌ Erro na requisição:', error);
        });
}

// Executar debug quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Página carregada, executando debug em 2 segundos...');
    setTimeout(debugCarregarHorarios, 2000);
});
</script>

<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 5px;">
    <h3>🔧 Instruções de Debug</h3>
    <p>1. Abra o Console do Navegador (F12 → Console)</p>
    <p>2. Recarregue esta página</p>
    <p>3. Observe as mensagens de debug no console</p>
    <p>4. Verifique se há erros ou problemas na requisição</p>
</div>

<div style="margin-top: 20px;">
    <button onclick="debugCarregarHorarios()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
        🔄 Executar Debug Novamente
    </button>
</div>