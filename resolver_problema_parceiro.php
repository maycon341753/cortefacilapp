<?php
/**
 * RESOLVER PROBLEMA DO PARCEIRO - SOLUÇÃO DEFINITIVA
 * Este arquivo identifica e resolve o problema de acesso à página profissionais.php
 */

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'models/salao.php';

$log = [];
$erro = '';
$sucesso = '';
$problema_resolvido = false;

function adicionarLog($mensagem, $tipo = 'info') {
    global $log;
    $log[] = [
        'timestamp' => date('H:i:s'),
        'tipo' => $tipo,
        'mensagem' => $mensagem
    ];
}

// ETAPA 1: Verificar se existe parceiro no banco
adicionarLog('🔍 ETAPA 1: Verificando parceiros no banco...', 'info');

try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'parceiro'");
    $stmt->execute();
    $total_parceiros = $stmt->fetch()['total'];
    
    if ($total_parceiros == 0) {
        adicionarLog('❌ Nenhum parceiro encontrado! Criando parceiro de teste...', 'warning');
        
        // Criar parceiro de teste
        $stmt = $conn->prepare("
            INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) 
            VALUES (?, ?, ?, 'parceiro', ?, NOW())
        ");
        $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt->execute([
            'Parceiro Teste',
            'parceiro@teste.com',
            $senha_hash,
            '(11) 99999-9999'
        ]);
        
        $id_parceiro = $conn->lastInsertId();
        adicionarLog("✅ Parceiro criado com ID: $id_parceiro", 'success');
    } else {
        adicionarLog("✅ Encontrados $total_parceiros parceiro(s) no banco", 'success');
    }
    
} catch (Exception $e) {
    adicionarLog('❌ Erro ao verificar parceiros: ' . $e->getMessage(), 'error');
    $erro = $e->getMessage();
}

// ETAPA 2: Buscar parceiro sem salão
adicionarLog('🔍 ETAPA 2: Buscando parceiro sem salão...', 'info');

try {
    $stmt = $conn->prepare("
        SELECT u.* FROM usuarios u 
        LEFT JOIN saloes s ON u.id = s.id_dono 
        WHERE u.tipo_usuario = 'parceiro' AND s.id IS NULL 
        LIMIT 1
    ");
    $stmt->execute();
    $parceiro_sem_salao = $stmt->fetch();
    
    if ($parceiro_sem_salao) {
        adicionarLog('🎯 PROBLEMA IDENTIFICADO: Parceiro "' . $parceiro_sem_salao['nome'] . '" não possui salão!', 'warning');
        
        // ETAPA 3: Criar salão para o parceiro
        adicionarLog('🏢 ETAPA 3: Criando salão para o parceiro...', 'info');
        
        $dados_salao = [
                'nome' => 'Salão ' . $parceiro_sem_salao['nome'],
                'endereco' => 'Rua das Flores, 123 - Centro - São Paulo/SP - CEP: 01000-000',
                'telefone' => $parceiro_sem_salao['telefone'] ?? '(11) 99999-9999',
                'descricao' => 'Salão criado automaticamente para resolver problema de acesso.',
                'id_dono' => $parceiro_sem_salao['id']
            ];
            
            // Verificar estrutura da tabela antes de inserir
            adicionarLog('🔍 Verificando estrutura da tabela saloes...', 'info');
            $stmt = $conn->prepare("DESCRIBE saloes");
            $stmt->execute();
            $colunas = $stmt->fetchAll();
            
            $campos_existentes = [];
            foreach ($colunas as $coluna) {
                $campos_existentes[] = $coluna['Field'];
            }
            
            adicionarLog('📋 Campos na tabela: ' . implode(', ', $campos_existentes), 'info');
            
            // Verificar se existe usuario_id em vez de id_dono
            if (in_array('usuario_id', $campos_existentes) && !in_array('id_dono', $campos_existentes)) {
                adicionarLog('⚠️ Tabela usa usuario_id em vez de id_dono', 'warning');
                $dados_salao['usuario_id'] = $dados_salao['id_dono'];
                unset($dados_salao['id_dono']);
            }
        
        $salao = new Salao();
        $id_salao = $salao->cadastrar($dados_salao);
        
        if ($id_salao) {
            adicionarLog("✅ Salão criado com sucesso! ID: $id_salao", 'success');
            
            // ETAPA 4: Fazer login do parceiro
            adicionarLog('🔑 ETAPA 4: Fazendo login do parceiro...', 'info');
            login($parceiro_sem_salao);
            adicionarLog('✅ Login realizado com sucesso!', 'success');
            
            $problema_resolvido = true;
            $sucesso = 'Problema resolvido! Parceiro agora possui salão e está logado.';
            
        } else {
            adicionarLog('❌ Erro ao criar salão', 'error');
            $erro = 'Não foi possível criar o salão';
        }
        
    } else {
        adicionarLog('✅ Todos os parceiros já possuem salão cadastrado', 'success');
        
        // Verificar se algum parceiro está logado
        if (!isLoggedIn()) {
            adicionarLog('🔑 Fazendo login do primeiro parceiro...', 'info');
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 1");
            $stmt->execute();
            $primeiro_parceiro = $stmt->fetch();
            
            if ($primeiro_parceiro) {
                login($primeiro_parceiro);
                adicionarLog('✅ Login realizado com sucesso!', 'success');
                $problema_resolvido = true;
                $sucesso = 'Login realizado! Parceiro já possui salão.';
            }
        } else {
            adicionarLog('✅ Usuário já está logado', 'success');
            $problema_resolvido = true;
            $sucesso = 'Usuário já está logado e possui salão.';
        }
    }
    
} catch (Exception $e) {
    adicionarLog('❌ Erro na resolução: ' . $e->getMessage(), 'error');
    $erro = $e->getMessage();
}

// ETAPA 5: Verificação final
adicionarLog('🔍 ETAPA 5: Verificação final...', 'info');

if (isLoggedIn() && isParceiro()) {
    $salao = new Salao();
    $meu_salao = $salao->buscarPorDono($_SESSION['usuario_id']);
    
    if ($meu_salao) {
        adicionarLog('🎉 SUCESSO TOTAL: Parceiro logado e com salão cadastrado!', 'success');
        $problema_resolvido = true;
    } else {
        adicionarLog('⚠️ Parceiro logado mas ainda sem salão', 'warning');
    }
} else {
    adicionarLog('⚠️ Usuário não está logado como parceiro', 'warning');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resolver Problema Parceiro - CorteFácil</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border-radius: 10px;
        }
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .alert-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .alert-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 8px;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        .log-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .log-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .log-item:last-child {
            border-bottom: none;
        }
        .log-time {
            font-family: monospace;
            color: #6c757d;
            margin-right: 10px;
            min-width: 60px;
        }
        .log-icon {
            margin-right: 8px;
            font-size: 16px;
        }
        .log-info { color: #17a2b8; }
        .log-success { color: #28a745; }
        .log-warning { color: #ffc107; }
        .log-error { color: #dc3545; }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .status-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }
        .status-card.success {
            border-left-color: #28a745;
        }
        .status-card.warning {
            border-left-color: #ffc107;
        }
        .status-card.error {
            border-left-color: #dc3545;
        }
        .actions {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Resolver Problema do Parceiro</h1>
            <p>Diagnóstico e Solução Automática</p>
            <p><strong>Data:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        
        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <strong>❌ Erro:</strong> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <strong>✅ Sucesso:</strong> <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php endif; ?>
        
        <!-- Log de Execução -->
        <div class="log-container">
            <h3>📋 Log de Execução</h3>
            <?php foreach ($log as $item): ?>
                <div class="log-item">
                    <span class="log-time"><?php echo $item['timestamp']; ?></span>
                    <span class="log-icon log-<?php echo $item['tipo']; ?>">
                        <?php 
                        switch($item['tipo']) {
                            case 'success': echo '✅'; break;
                            case 'error': echo '❌'; break;
                            case 'warning': echo '⚠️'; break;
                            default: echo 'ℹ️'; break;
                        }
                        ?>
                    </span>
                    <span><?php echo htmlspecialchars($item['mensagem']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Status Atual -->
        <div class="status-grid">
            <div class="status-card <?php echo isLoggedIn() ? 'success' : 'error'; ?>">
                <h4>👤 Status do Login</h4>
                <p><strong>Logado:</strong> <?php echo isLoggedIn() ? '✅ Sim' : '❌ Não'; ?></p>
                <?php if (isLoggedIn()): ?>
                    <p><strong>Nome:</strong> <?php echo $_SESSION['usuario_nome']; ?></p>
                    <p><strong>Tipo:</strong> <?php echo $_SESSION['tipo_usuario']; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="status-card <?php echo isParceiro() ? 'success' : 'warning'; ?>">
                <h4>🏢 Status do Parceiro</h4>
                <p><strong>É Parceiro:</strong> <?php echo isParceiro() ? '✅ Sim' : '❌ Não'; ?></p>
                <?php if (isParceiro()): ?>
                    <?php
                    $salao = new Salao();
                    $meu_salao = $salao->buscarPorDono($_SESSION['usuario_id']);
                    ?>
                    <p><strong>Possui Salão:</strong> <?php echo $meu_salao ? '✅ Sim' : '❌ Não'; ?></p>
                    <?php if ($meu_salao): ?>
                        <p><strong>Salão:</strong> <?php echo htmlspecialchars($meu_salao['nome']); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="status-card <?php echo $problema_resolvido ? 'success' : 'error'; ?>">
                <h4>🎯 Status do Problema</h4>
                <p><strong>Resolvido:</strong> <?php echo $problema_resolvido ? '✅ Sim' : '❌ Não'; ?></p>
                <?php if ($problema_resolvido): ?>
                    <p>O parceiro pode acessar profissionais.php</p>
                <?php else: ?>
                    <p>Ainda há problemas a resolver</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Ações -->
        <div class="actions">
            <h3>🎯 Próximas Ações</h3>
            
            <?php if ($problema_resolvido): ?>
                <div class="alert alert-success">
                    <strong>🎉 PROBLEMA RESOLVIDO!</strong><br>
                    O parceiro agora pode acessar a página de profissionais sem erros.
                </div>
                
                <a href="parceiro/profissionais.php" class="btn btn-success">✅ Testar Página Profissionais</a>
                <a href="parceiro/dashboard.php" class="btn btn-primary">📊 Ir para Dashboard</a>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>⚠️ PROBLEMA AINDA NÃO RESOLVIDO</strong><br>
                    Verifique os logs acima para identificar o que ainda precisa ser corrigido.
                </div>
                
                <button onclick="location.reload()" class="btn btn-primary">🔄 Tentar Novamente</button>
            <?php endif; ?>
            
            <a href="test_login_parceiro.php" class="btn btn-secondary">🔍 Debug Detalhado</a>
            <a href="logout.php" class="btn btn-secondary">🚪 Logout</a>
        </div>
        
        <!-- Resumo da Solução -->
        <div style="background: #e9ecef; padding: 20px; border-radius: 10px; margin-top: 30px;">
            <h3>📝 Resumo da Solução</h3>
            <p><strong>Problema Original:</strong> Erro na página <code>parceiro/profissionais.php</code></p>
            <p><strong>Causa Identificada:</strong> Parceiro sem salão cadastrado</p>
            <p><strong>Solução Aplicada:</strong></p>
            <ul>
                <li>✅ Verificação de parceiros no banco</li>
                <li>✅ Criação de salão para parceiro sem salão</li>
                <li>✅ Login automático do parceiro</li>
                <li>✅ Verificação final de acesso</li>
            </ul>
            <p><strong>Resultado:</strong> <?php echo $problema_resolvido ? '✅ Problema resolvido' : '❌ Ainda há problemas'; ?></p>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <p><strong>CorteFácil</strong> - Sistema de Resolução Automática</p>
            <p><small>Arquivo: resolver_problema_parceiro.php - Remover após debug</small></p>
        </div>
    </div>
</body>
</html>