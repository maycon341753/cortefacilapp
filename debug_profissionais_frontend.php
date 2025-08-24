<?php
/**
 * Debug do Frontend - Profissionais
 * Verifica se há problemas no carregamento de profissionais no frontend
 */

require_once 'includes/auth.php';
require_once 'models/salao.php';

// Simular sessão de cliente logado
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'cliente';
$_SESSION['user_name'] = 'Cliente Teste';

$salao = new Salao();
$saloes = $salao->listarAtivos();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Carregamento de Profissionais</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        select, button { padding: 10px; margin: 5px; }
        #log { background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>🐛 Debug - Carregamento de Profissionais</h1>
    
    <div class="debug-section">
        <h3>1. Teste de Seleção de Salão</h3>
        <select id="id_salao" onchange="carregarProfissionais(this.value)">
            <option value="">Selecione um salão...</option>
            <?php foreach ($saloes as $s): ?>
                <option value="<?= $s['id'] ?>" 
                        data-endereco="<?= htmlspecialchars($s['endereco'] ?? '') ?>"
                        data-telefone="<?= htmlspecialchars($s['telefone'] ?? '') ?>">
                    <?= htmlspecialchars($s['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <select id="id_profissional">
            <option value="">Escolha um salão primeiro...</option>
        </select>
    </div>
    
    <div class="debug-section">
        <h3>2. Teste Manual da API</h3>
        <button onclick="testarAPI()">Testar API Diretamente</button>
        <button onclick="limparLog()">Limpar Log</button>
    </div>
    
    <div class="debug-section">
        <h3>3. Log de Debug</h3>
        <div id="log"></div>
    </div>

    <script>
        // Função de log
        function log(message, type = 'info') {
            const logDiv = document.getElementById('log');
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'error' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : '';
            logDiv.innerHTML += `<div class="${className}">[${timestamp}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        function limparLog() {
            document.getElementById('log').innerHTML = '';
        }
        
        // Implementação simples do CorteFacil.ajax se não existir
        if (typeof CorteFacil === 'undefined') {
            window.CorteFacil = {
                ajax: {
                    get: function(url) {
                        log(`Fazendo requisição GET para: ${url}`);
                        return fetch(url)
                            .then(response => {
                                log(`Status da resposta: ${response.status}`);
                                if (!response.ok) {
                                    throw new Error(`HTTP ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                log(`Dados recebidos: ${JSON.stringify(data)}`, 'success');
                                return data;
                            })
                            .catch(error => {
                                log(`Erro na requisição: ${error.message}`, 'error');
                                throw error;
                            });
                    }
                }
            };
        }
        
        function carregarProfissionais(idSalao) {
            log(`Carregando profissionais para salão ID: ${idSalao}`);
            
            const select = document.getElementById('id_profissional');
            select.innerHTML = '<option value="">Carregando...</option>';
            
            if (!idSalao) {
                select.innerHTML = '<option value="">Escolha um salão primeiro...</option>';
                return;
            }
            
            CorteFacil.ajax.get(`api/profissionais.php?salao=${idSalao}`)
                .then(response => {
                    log(`Resposta da API recebida`);
                    select.innerHTML = '<option value="">Selecione um profissional...</option>';
                    
                    // Verificar se a resposta tem o formato esperado da API
                    let profissionais = [];
                    if (response && response.success && response.data) {
                        profissionais = response.data;
                        log(`Formato de resposta: API padrão com ${profissionais.length} profissionais`, 'success');
                    } else if (Array.isArray(response)) {
                        // Compatibilidade com resposta direta em array
                        profissionais = response;
                        log(`Formato de resposta: Array direto com ${profissionais.length} profissionais`, 'warning');
                    } else {
                        log(`Formato de resposta inesperado: ${JSON.stringify(response)}`, 'error');
                    }
                    
                    if (profissionais.length === 0) {
                        select.innerHTML = '<option value="">Nenhum profissional disponível</option>';
                        log('Nenhum profissional encontrado', 'warning');
                        return;
                    }
                    
                    profissionais.forEach(prof => {
                        const option = document.createElement('option');
                        option.value = prof.id;
                        option.textContent = `${prof.nome} - ${prof.especialidade}`;
                        select.appendChild(option);
                        log(`Profissional adicionado: ${prof.nome} - ${prof.especialidade}`);
                    });
                    
                    log(`Total de ${profissionais.length} profissionais carregados com sucesso`, 'success');
                })
                .catch(error => {
                    log(`Erro ao carregar profissionais: ${error.message}`, 'error');
                    select.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
                });
        }
        
        function testarAPI() {
            const salaoId = document.getElementById('id_salao').value;
            if (!salaoId) {
                log('Selecione um salão primeiro!', 'warning');
                return;
            }
            
            log(`Testando API manualmente para salão ${salaoId}`);
            
            fetch(`api/profissionais.php?salao=${salaoId}`)
                .then(response => {
                    log(`Status HTTP: ${response.status}`);
                    log(`Headers: ${JSON.stringify([...response.headers.entries()])}`);
                    return response.text();
                })
                .then(text => {
                    log(`Resposta bruta: ${text}`);
                    try {
                        const json = JSON.parse(text);
                        log(`JSON parseado: ${JSON.stringify(json, null, 2)}`, 'success');
                    } catch (e) {
                        log(`Erro ao parsear JSON: ${e.message}`, 'error');
                    }
                })
                .catch(error => {
                    log(`Erro na requisição: ${error.message}`, 'error');
                });
        }
        
        // Log inicial
        log('Debug iniciado - Pronto para testar!');
    </script>
</body>
</html>