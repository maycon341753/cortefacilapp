<?php
/**
 * CRIAR PARCEIRO E SALÃO - SOLUÇÃO DEFINITIVA
 * Este arquivo cria um parceiro de teste e seu salão para resolver o problema
 */

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'models/salao.php';

$log = [];
$erro = '';
$sucesso = '';
$problema_resolvido = false;
$parceiro_criado = null;
$salao_criado = null;

function adicionarLog($mensagem, $tipo = 'info') {
    global $log;
    $log[] = [
        'timestamp' => date('H:i:s'),
        'tipo' => $tipo,
        'mensagem' => $mensagem
    ];
}

// Processar criação automática
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar_tudo') {
    try {
        $conn = getConnection();
        
        // ETAPA 1: Verificar se já existe parceiro
        adicionarLog('🔍 ETAPA 1: Verificando parceiros existentes...', 'info');
        
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 1");
        $stmt->execute();
        $parceiro_existente = $stmt->fetch();
        
        if (!$parceiro_existente) {
            // ETAPA 2: Criar parceiro de teste
            adicionarLog('👤 ETAPA 2: Criando parceiro de teste...', 'info');
            
            $stmt = $conn->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) 
                VALUES (?, ?, ?, 'parceiro', ?, NOW())
            ");
            
            $senha_hash = password_hash('123456', PASSWORD_DEFAULT);
            $email_teste = 'parceiro.teste.' . time() . '@cortefacil.com';
            
            $resultado = $stmt->execute([
                'Parceiro Teste CorteFácil',
                $email_teste,
                $senha_hash,
                '(11) 99999-8888'
            ]);
            
            if ($resultado) {
                $id_parceiro = $conn->lastInsertId();
                adicionarLog("✅ Parceiro criado com ID: $id_parceiro", 'success');
                
                // Buscar o parceiro criado
                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$id_parceiro]);
                $parceiro_criado = $stmt->fetch();
                
            } else {
                throw new Exception('Falha ao criar parceiro');
            }
        } else {
            adicionarLog('✅ Parceiro já existe: ' . $parceiro_existente['nome'], 'success');
            $parceiro_criado = $parceiro_existente;
        }
        
        // ETAPA 3: Verificar se o parceiro já tem salão
        adicionarLog('🏢 ETAPA 3: Verificando salão do parceiro...', 'info');
        
        $salao = new Salao();
        $salao_existente = $salao->buscarPorDono($parceiro_criado['id']);
        
        if (!$salao_existente) {
            // ETAPA 4: Criar salão para o parceiro
            adicionarLog('🏗️ ETAPA 4: Criando salão para o parceiro...', 'info');
            
            $dados_salao = [
                'nome' => 'Salão ' . $parceiro_criado['nome'],
                'endereco' => 'Rua das Flores, 123 - Centro - São Paulo/SP - CEP: 01000-000',
                'telefone' => $parceiro_criado['telefone'] ?? '(11) 99999-9999',
                'descricao' => 'Salão criado automaticamente para resolver problema de acesso à página profissionais.php',
                'usuario_id' => $parceiro_criado['id'] // Tabela usa usuario_id
            ];
            
            $id_salao = $salao->cadastrar($dados_salao);
            
            if ($id_salao) {
                adicionarLog("✅ Salão criado com sucesso! ID: $id_salao", 'success');
                $salao_criado = $salao->buscarPorId($id_salao);
            } else {
                throw new Exception('Falha ao criar salão');
            }
        } else {
            adicionarLog('✅ Parceiro já possui salão: ' . $salao_existente['nome'], 'success');
            $salao_criado = $salao_existente;
        }
        
        // ETAPA 5: Fazer login do parceiro
        adicionarLog('🔑 ETAPA 5: Fazendo login do parceiro...', 'info');
        login($parceiro_criado);
        adicionarLog('✅ Login realizado com sucesso!', 'success');
        
        $problema_resolvido = true;
        $sucesso = 'Parceiro e salão criados com sucesso! Problema resolvido.';
        
    } catch (Exception $e) {
        adicionarLog('❌ Erro: ' . $e->getMessage(), 'error');
        $erro = $e->getMessage();
    }
}

// Verificar situação atual
$status_atual = [];
try {
    $conn = getConnection();
    
    // Contar usuários por tipo
    $stmt = $conn->prepare("SELECT tipo_usuario, COUNT(*) as total FROM usuarios GROUP BY tipo_usuario");
    $stmt->execute();
    $usuarios_por_tipo = $stmt->fetchAll();
    
    foreach ($usuarios_por_tipo as $tipo) {
        $status_atual['usuarios_' . $tipo['tipo_usuario']] = $tipo['total'];
    }
    
    // Contar salões
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM saloes");
    $stmt->execute();
    $status_atual['total_saloes'] = $stmt->fetch()['total'];
    
    // Parceiros sem salão
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM usuarios u 
        LEFT JOIN saloes s ON u.id = s.usuario_id
        WHERE u.tipo_usuario = 'parceiro' AND s.id IS NULL
    ");
    $stmt->execute();
    $status_atual['parceiros_sem_salao'] = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    $status_atual['erro'] = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Parceiro e Salão - CorteFácil</title>
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
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
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
        .alert-info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, #FF6B6B, #FF8E53);
            color: white;
        }
        .btn-success {
            background: linear-gradient(135deg, #4ECDC4, #44A08D);
            color: white;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .status-card.success {
            border-left-color: #28a745;
        }
        .status-card.warning {
            border-left-color: #ffc107;
        }
        .status-card.danger {
            border-left-color: #dc3545;
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
        .actions {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .big-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .created-info {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Criar Parceiro e Salão</h1>
            <p>Solução Definitiva para o Problema de Acesso</p>
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
        
        <!-- Status Atual do Banco -->
        <div class="alert alert-info">
            <h3>📊 Status Atual do Banco de Dados</h3>
            <div class="status-grid">
                <div class="status-card <?php echo ($status_atual['usuarios_admin'] ?? 0) > 0 ? 'success' : 'warning'; ?>">
                    <div class="big-number"><?php echo $status_atual['usuarios_admin'] ?? 0; ?></div>
                    <div>Administradores</div>
                </div>
                <div class="status-card <?php echo ($status_atual['usuarios_cliente'] ?? 0) > 0 ? 'success' : 'warning'; ?>">
                    <div class="big-number"><?php echo $status_atual['usuarios_cliente'] ?? 0; ?></div>
                    <div>Clientes</div>
                </div>
                <div class="status-card <?php echo ($status_atual['usuarios_parceiro'] ?? 0) > 0 ? 'success' : 'danger'; ?>">
                    <div class="big-number"><?php echo $status_atual['usuarios_parceiro'] ?? 0; ?></div>
                    <div>Parceiros</div>
                </div>
                <div class="status-card <?php echo ($status_atual['total_saloes'] ?? 0) > 0 ? 'success' : 'danger'; ?>">
                    <div class="big-number"><?php echo $status_atual['total_saloes'] ?? 0; ?></div>
                    <div>Salões</div>
                </div>
                <div class="status-card <?php echo ($status_atual['parceiros_sem_salao'] ?? 0) == 0 ? 'success' : 'warning'; ?>">
                    <div class="big-number"><?php echo $status_atual['parceiros_sem_salao'] ?? 0; ?></div>
                    <div>Parceiros sem Salão</div>
                </div>
            </div>
        </div>
        
        <?php if ($parceiro_criado): ?>
            <div class="created-info">
                <h3>👤 Parceiro Criado/Utilizado</h3>
                <p><strong>ID:</strong> <?php echo $parceiro_criado['id']; ?></p>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($parceiro_criado['nome']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($parceiro_criado['email']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($parceiro_criado['telefone'] ?? 'N/A'); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($salao_criado): ?>
            <div class="created-info">
                <h3>🏢 Salão Criado/Utilizado</h3>
                <p><strong>ID:</strong> <?php echo $salao_criado['id']; ?></p>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($salao_criado['nome']); ?></p>
                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($salao_criado['endereco']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($salao_criado['telefone']); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($log)): ?>
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
        <?php endif; ?>
        
        <!-- Ações -->
        <div class="actions">
            <h3>🎯 Ações Disponíveis</h3>
            
            <?php if (!$problema_resolvido): ?>
                <?php if (($status_atual['usuarios_parceiro'] ?? 0) == 0 || ($status_atual['parceiros_sem_salao'] ?? 0) > 0): ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ AÇÃO NECESSÁRIA!</strong><br>
                        <?php if (($status_atual['usuarios_parceiro'] ?? 0) == 0): ?>
                            Não há parceiros cadastrados no banco.<br>
                        <?php endif; ?>
                        <?php if (($status_atual['parceiros_sem_salao'] ?? 0) > 0): ?>
                            Há <?php echo $status_atual['parceiros_sem_salao']; ?> parceiro(s) sem salão.<br>
                        <?php endif; ?>
                        Clique no botão abaixo para resolver automaticamente.
                    </div>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="acao" value="criar_tudo">
                        <button type="submit" class="btn btn-primary">🚀 RESOLVER PROBLEMA AUTOMATICAMENTE</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-success">
                        <strong>✅ TUDO PARECE OK!</strong><br>
                        Há parceiros com salões cadastrados. O problema pode ser outro.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong>🎉 PROBLEMA RESOLVIDO!</strong><br>
                    Parceiro criado e logado com salão cadastrado. Agora você pode acessar a página de profissionais.
                </div>
                
                <a href="parceiro/profissionais.php" class="btn btn-success">✅ TESTAR PÁGINA PROFISSIONAIS</a>
                <a href="parceiro/dashboard.php" class="btn btn-secondary">📊 IR PARA DASHBOARD</a>
            <?php endif; ?>
            
            <a href="test_login_parceiro.php" class="btn btn-secondary">🔍 Debug Detalhado</a>
            <a href="logout.php" class="btn btn-secondary">🚪 Logout</a>
        </div>
        
        <!-- Informações de Login -->
        <?php if ($parceiro_criado && $problema_resolvido): ?>
            <div class="alert alert-info">
                <h3>🔑 Informações de Login</h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($parceiro_criado['email']); ?></p>
                <p><strong>Senha:</strong> 123456</p>
                <p><em>Use essas credenciais para fazer login manualmente se necessário.</em></p>
            </div>
        <?php endif; ?>
        
        <!-- Status da Sessão -->
        <div class="alert alert-info">
            <h3>📊 Status da Sessão Atual</h3>
            <p><strong>Logado:</strong> <?php echo isLoggedIn() ? '✅ Sim' : '❌ Não'; ?></p>
            <?php if (isLoggedIn()): ?>
                <p><strong>Nome:</strong> <?php echo $_SESSION['usuario_nome']; ?></p>
                <p><strong>Tipo:</strong> <?php echo $_SESSION['tipo_usuario']; ?></p>
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
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <p><strong>CorteFácil</strong> - Sistema de Resolução Automática de Problemas</p>
            <p><small>Arquivo: criar_parceiro_e_salao.php - Remover após debug</small></p>
        </div>
    </div>
</body>
</html>