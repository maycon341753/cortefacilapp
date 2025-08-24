<?php
/**
 * Teste completo do carregamento de profissionais
 * Simula exatamente o que acontece na página de agendamento
 */

// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

// Simular login de cliente (como na página real)
if (!isset($_SESSION['usuario_id'])) {
    // Buscar um cliente para simular login
    $conn = connectWithFallback();
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE tipo_usuario = 'cliente' LIMIT 1");
    $stmt->execute();
    $cliente = $stmt->fetch();
    
    if ($cliente) {
        $_SESSION['usuario_id'] = $cliente['id'];
        $_SESSION['usuario_nome'] = $cliente['nome'];
        $_SESSION['usuario_email'] = $cliente['email'];
        $_SESSION['tipo_usuario'] = $cliente['tipo_usuario'];
        $_SESSION['usuario_telefone'] = $cliente['telefone'] ?? '';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Completo - Carregamento de Profissionais</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        select, button { padding: 8px; margin: 5px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .log { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Teste Completo - Carregamento de Profissionais</h1>
        
        <!-- Informações da Sessão -->
        <div class="section info">
            <h3>Informações da Sessão</h3>
            <pre><?php echo htmlspecialchars(print_r($_SESSION, true)); ?></pre>
            
            <p><strong>isLoggedIn():</strong> <?php echo isLoggedIn() ? 'SIM' : 'NÃO'; ?></p>
            <p><strong>isCliente():</strong> <?php echo isCliente() ? 'SIM' : 'NÃO'; ?></p>
            
            <?php $user = getLoggedUser(); ?>
            <p><strong>getLoggedUser():</strong></p>
            <pre><?php echo $user ? htmlspecialchars(print_r($user, true)) : 'NULL'; ?></pre>
        </div>
        
        <!-- Teste de Salões -->
        <div class="section">
            <h3>Salões Disponíveis</h3>
            <?php
            try {
                $conn = connectWithFallback();
                $stmt = $conn->prepare("SELECT * FROM saloes ORDER BY nome");
                $stmt->execute();
                $saloes = $stmt->fetchAll();
                
                if ($saloes) {
                    echo "<select id='salaos' onchange='carregarProfissionais(this.value)'>";
                    echo "<option value=''>Selecione um salão</option>";
                    foreach ($saloes as $salao) {
                        echo "<option value='{$salao['id']}'>{$salao['nome']}</option>";
                    }
                    echo "</select>";
                } else {
                    echo "<p class='error'>Nenhum salão encontrado!</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>Erro ao buscar salões: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
        
        <!-- Área de Profissionais -->
        <div class="section">
            <h3>Profissionais</h3>
            <select id='profissionais'>
                <option value=''>Carregando...</option>
            </select>
        </div>
        
        <!-- Testes Diretos -->
        <div class="section">
            <h3>Testes Diretos</h3>
            <button onclick="testarAPIDireta()">Testar API Original</button>
            <button onclick="testarAPICorrigida()">Testar API Corrigida</button>
            <button onclick="testarBancoDireto()">Testar Banco Direto</button>
        </div>
        
        <!-- Log de Resultados -->
        <div class="section">
            <h3>Log de Resultados</h3>
            <div id="log" class="log"></div>
            <button onclick="limparLog()">Limpar Log</button>
        </div>
    </div>
    
    <script>
        function log(message, type = 'info') {
            const logDiv = document.getElementById('log');
            const timestamp = new Date().toLocaleTimeString();
            const className = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';
            logDiv.innerHTML += `<div class="${className}">[${timestamp}] ${message}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }
        
        function limparLog() {
            document.getElementById('log').innerHTML = '';
        }
        
        // Função original do sistema
        function carregarProfissionais(idSalao) {
            const selectProfissionais = document.getElementById('profissionais');
            
            if (!idSalao) {
                selectProfissionais.innerHTML = '<option value="">Selecione um salão primeiro</option>';
                return;
            }
            
            selectProfissionais.innerHTML = '<option value="">Carregando...</option>';
            log(`Carregando profissionais para salão ID: ${idSalao}`);
            
            // Simular a chamada AJAX original
            fetch(`api/profissionais.php?salao=${idSalao}`)
                .then(response => {
                    log(`Resposta recebida: Status ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    log(`Dados recebidos: ${JSON.stringify(data)}`, 'success');
                    
                    if (data.success && data.data && data.data.length > 0) {
                        let options = '<option value="">Selecione um profissional</option>';
                        data.data.forEach(profissional => {
                            options += `<option value="${profissional.id}">${profissional.nome}</option>`;
                        });
                        selectProfissionais.innerHTML = options;
                        log(`${data.data.length} profissionais carregados com sucesso!`, 'success');
                    } else {
                        selectProfissionais.innerHTML = '<option value="">Nenhum profissional disponível</option>';
                        log('Nenhum profissional encontrado para este salão', 'error');
                    }
                })
                .catch(error => {
                    log(`Erro na requisição: ${error.message}`, 'error');
                    selectProfissionais.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
                });
        }
        
        function testarAPIDireta() {
            const salaoId = document.getElementById('salaos').value;
            if (!salaoId) {
                log('Selecione um salão primeiro!', 'error');
                return;
            }
            
            log(`Testando API original para salão ${salaoId}`);
            fetch(`api/profissionais.php?salao=${salaoId}`)
                .then(response => response.json())
                .then(data => {
                    log(`API Original: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');
                })
                .catch(error => {
                    log(`Erro API Original: ${error.message}`, 'error');
                });
        }
        
        function testarAPICorrigida() {
            const salaoId = document.getElementById('salaos').value;
            if (!salaoId) {
                log('Selecione um salão primeiro!', 'error');
                return;
            }
            
            log(`Testando API corrigida para salão ${salaoId}`);
            fetch(`api_profissionais_corrigida.php?salao=${salaoId}&debug=true`)
                .then(response => response.json())
                .then(data => {
                    log(`API Corrigida: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');
                })
                .catch(error => {
                    log(`Erro API Corrigida: ${error.message}`, 'error');
                });
        }
        
        function testarBancoDireto() {
            const salaoId = document.getElementById('salaos').value;
            if (!salaoId) {
                log('Selecione um salão primeiro!', 'error');
                return;
            }
            
            log(`Testando consulta direta no banco para salão ${salaoId}`);
            fetch(`teste_banco_direto.php?salao=${salaoId}`)
                .then(response => response.json())
                .then(data => {
                    log(`Banco Direto: ${JSON.stringify(data, null, 2)}`, data.success ? 'success' : 'error');
                })
                .catch(error => {
                    log(`Erro Banco Direto: ${error.message}`, 'error');
                });
        }
        
        // Log inicial
        log('Página carregada. Selecione um salão para testar.');
    </script>
</body>
</html>