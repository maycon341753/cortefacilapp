<?php
/**
 * Debug final da API de profissionais
 * Testa todos os cenários possíveis
 */

// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'models/profissional.php';

// Simular login se necessário
if (!isset($_SESSION['usuario_id'])) {
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
    <title>Debug Final - API Profissionais</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; margin: 15px 0; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { border-left: 4px solid #28a745; }
        .error { border-left: 4px solid #dc3545; }
        .warning { border-left: 4px solid #ffc107; }
        .info { border-left: 4px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        select { padding: 8px; margin: 5px; min-width: 200px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Final - API de Profissionais</h1>
        
        <!-- Status da Sessão -->
        <div class="card <?php echo isLoggedIn() ? 'success' : 'error'; ?>">
            <h3>📋 Status da Sessão</h3>
            <p><strong>Logado:</strong> <?php echo isLoggedIn() ? '✅ SIM' : '❌ NÃO'; ?></p>
            <p><strong>É Cliente:</strong> <?php echo isCliente() ? '✅ SIM' : '❌ NÃO'; ?></p>
            <p><strong>Usuário ID:</strong> <?php echo $_SESSION['usuario_id'] ?? 'NÃO DEFINIDO'; ?></p>
            <p><strong>Nome:</strong> <?php echo $_SESSION['usuario_nome'] ?? 'NÃO DEFINIDO'; ?></p>
            <details>
                <summary>Ver sessão completa</summary>
                <pre><?php echo htmlspecialchars(print_r($_SESSION, true)); ?></pre>
            </details>
        </div>
        
        <div class="grid">
            <!-- Teste de Salões -->
            <div class="card info">
                <h3>🏢 Salões Disponíveis</h3>
                <?php
                try {
                    $conn = connectWithFallback();
                    $stmt = $conn->prepare("SELECT id, nome FROM saloes ORDER BY nome");
                    $stmt->execute();
                    $saloes = $stmt->fetchAll();
                    
                    if ($saloes) {
                        echo "<select id='salaoSelect' onchange='selecionarSalao(this.value)'>";
                        echo "<option value=''>Selecione um salão</option>";
                        foreach ($saloes as $salao) {
                            echo "<option value='{$salao['id']}'>{$salao['nome']} (ID: {$salao['id']})</option>";
                        }
                        echo "</select>";
                        echo "<p>Total: " . count($saloes) . " salões</p>";
                    } else {
                        echo "<p class='error'>❌ Nenhum salão encontrado!</p>";
                    }
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
            
            <!-- Controles de Teste -->
            <div class="card">
                <h3>🧪 Testes Disponíveis</h3>
                <button class="btn-primary" onclick="testarAPIOriginal()">Testar API Original</button>
                <button class="btn-success" onclick="testarAPICorrigida()">Testar API Corrigida</button>
                <button class="btn-warning" onclick="testarBancoDireto()">Testar Banco Direto</button>
                <button class="btn-danger" onclick="testarModeloProfissional()">Testar Modelo</button>
                <br><br>
                <button class="btn-primary" onclick="testarTodosOsSaloes()">🚀 Testar Todos os Salões</button>
                <button onclick="limparResultados()">🗑️ Limpar</button>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="card">
            <h3>📊 Resultados dos Testes</h3>
            <div id="resultados"></div>
        </div>
    </div>
    
    <script>
        let salaoSelecionado = null;
        
        function log(message, type = 'info') {
            const resultados = document.getElementById('resultados');
            const timestamp = new Date().toLocaleTimeString();
            const icon = {
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'info': 'ℹ️'
            }[type] || 'ℹ️';
            
            const div = document.createElement('div');
            div.className = `card ${type}`;
            div.innerHTML = `<strong>[${timestamp}] ${icon}</strong> ${message}`;
            resultados.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth' });
        }
        
        function limparResultados() {
            document.getElementById('resultados').innerHTML = '';
        }
        
        function selecionarSalao(id) {
            salaoSelecionado = id;
            if (id) {
                log(`Salão selecionado: ID ${id}`, 'info');
            }
        }
        
        function verificarSalao() {
            if (!salaoSelecionado) {
                log('❌ Selecione um salão primeiro!', 'error');
                return false;
            }
            return true;
        }
        
        async function testarAPIOriginal() {
            if (!verificarSalao()) return;
            
            log(`🔍 Testando API original para salão ${salaoSelecionado}...`);
            
            try {
                const response = await fetch(`api/profissionais.php?salao=${salaoSelecionado}`);
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ API Original: ${data.total} profissionais encontrados`, 'success');
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(prof => {
                            log(`   👤 ${prof.nome} - ${prof.especialidade || 'Sem especialidade'}`);
                        });
                    }
                } else {
                    log(`❌ API Original falhou: ${data.error}`, 'error');
                }
                
                log(`📋 Resposta completa: <pre>${JSON.stringify(data, null, 2)}</pre>`);
                
            } catch (error) {
                log(`❌ Erro na API Original: ${error.message}`, 'error');
            }
        }
        
        async function testarAPICorrigida() {
            if (!verificarSalao()) return;
            
            log(`🔍 Testando API corrigida para salão ${salaoSelecionado}...`);
            
            try {
                const response = await fetch(`api_profissionais_corrigida.php?salao=${salaoSelecionado}&debug=true`);
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ API Corrigida: ${data.total} profissionais encontrados`, 'success');
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(prof => {
                            log(`   👤 ${prof.nome} - ${prof.especialidade || 'Sem especialidade'}`);
                        });
                    }
                    if (data.debug) {
                        log(`🔧 Debug info: Método auth: ${data.debug.auth_method}, Total encontrado: ${data.debug.total_found}`);
                    }
                } else {
                    log(`❌ API Corrigida falhou: ${data.error}`, 'error');
                    if (data.debug) {
                        log(`🔧 Debug: ${JSON.stringify(data.debug, null, 2)}`);
                    }
                }
                
            } catch (error) {
                log(`❌ Erro na API Corrigida: ${error.message}`, 'error');
            }
        }
        
        async function testarBancoDireto() {
            if (!verificarSalao()) return;
            
            log(`🔍 Testando consulta direta no banco para salão ${salaoSelecionado}...`);
            
            try {
                const response = await fetch(`teste_banco_direto.php?salao=${salaoSelecionado}`);
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Banco Direto: ${data.total} profissionais encontrados`, 'success');
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(prof => {
                            log(`   👤 ${prof.nome} - Status: ${prof.status || 'N/A'}`);
                        });
                    }
                } else {
                    log(`❌ Banco Direto falhou: ${data.error}`, 'error');
                }
                
            } catch (error) {
                log(`❌ Erro no Banco Direto: ${error.message}`, 'error');
            }
        }
        
        async function testarModeloProfissional() {
            if (!verificarSalao()) return;
            
            log(`🔍 Testando modelo Profissional para salão ${salaoSelecionado}...`);
            
            try {
                const response = await fetch(`teste_modelo_profissional.php?salao=${salaoSelecionado}`);
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Modelo: ${data.total} profissionais encontrados`, 'success');
                    if (data.data && data.data.length > 0) {
                        data.data.forEach(prof => {
                            log(`   👤 ${prof.nome} - ${prof.especialidade || 'Sem especialidade'}`);
                        });
                    }
                } else {
                    log(`❌ Modelo falhou: ${data.error}`, 'error');
                }
                
            } catch (error) {
                log(`❌ Erro no Modelo: ${error.message}`, 'error');
            }
        }
        
        async function testarTodosOsSaloes() {
            log('🚀 Iniciando teste completo em todos os salões...', 'info');
            
            const select = document.getElementById('salaoSelect');
            const saloes = Array.from(select.options).slice(1); // Remove primeira opção
            
            for (const option of saloes) {
                salaoSelecionado = option.value;
                log(`\n🏢 Testando salão: ${option.text}`, 'info');
                
                await testarAPIOriginal();
                await new Promise(resolve => setTimeout(resolve, 500)); // Pausa entre testes
            }
            
            log('🎉 Teste completo finalizado!', 'success');
        }
        
        // Log inicial
        log('🔧 Sistema de debug carregado. Selecione um salão e execute os testes.', 'info');
    </script>
</body>
</html>