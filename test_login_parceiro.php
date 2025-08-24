<?php
/**
 * Teste de Login do Parceiro
 * Verifica se conseguimos fazer login e acessar a área do parceiro
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

$erro = '';
$sucesso = '';
$usuario_logado = null;

// Processar login de teste
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'login_teste') {
    try {
        $conn = getConnection();
        
        // Buscar um parceiro no banco para teste
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 1");
        $stmt->execute();
        $parceiro = $stmt->fetch();
        
        if ($parceiro) {
            // Fazer login automático
            login($parceiro);
            $sucesso = "Login realizado com sucesso como: " . $parceiro['nome'];
            $usuario_logado = getLoggedUser();
        } else {
            $erro = "Nenhum parceiro encontrado no banco de dados";
        }
        
    } catch (Exception $e) {
        $erro = "Erro ao fazer login: " . $e->getMessage();
    }
}

// Verificar se já está logado
if (isLoggedIn()) {
    $usuario_logado = getLoggedUser();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Login Parceiro - CorteFácil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Teste de Login do Parceiro</h1>
        <p><strong>Data:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        
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
        
        <!-- Status Atual -->
        <div class="info-box">
            <h2>📊 Status Atual</h2>
            <table>
                <tr>
                    <th>Verificação</th>
                    <th>Status</th>
                    <th>Detalhes</th>
                </tr>
                <tr>
                    <td>Sessão Ativa</td>
                    <td><?php echo session_status() === PHP_SESSION_ACTIVE ? '✅ Sim' : '❌ Não'; ?></td>
                    <td>ID: <?php echo session_id(); ?></td>
                </tr>
                <tr>
                    <td>Usuário Logado</td>
                    <td><?php echo isLoggedIn() ? '✅ Sim' : '❌ Não'; ?></td>
                    <td><?php echo isLoggedIn() ? $_SESSION['usuario_nome'] : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td>É Parceiro</td>
                    <td><?php echo isParceiro() ? '✅ Sim' : '❌ Não'; ?></td>
                    <td><?php echo isLoggedIn() ? $_SESSION['tipo_usuario'] : 'N/A'; ?></td>
                </tr>
            </table>
        </div>
        
        <?php if ($usuario_logado): ?>
            <!-- Informações do Usuário Logado -->
            <div class="info-box">
                <h2>👤 Usuário Logado</h2>
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <tr><td>ID</td><td><?php echo $usuario_logado['id']; ?></td></tr>
                    <tr><td>Nome</td><td><?php echo $usuario_logado['nome']; ?></td></tr>
                    <tr><td>Email</td><td><?php echo $usuario_logado['email']; ?></td></tr>
                    <tr><td>Tipo</td><td><?php echo $usuario_logado['tipo_usuario']; ?></td></tr>
                    <tr><td>Telefone</td><td><?php echo $usuario_logado['telefone'] ?? 'N/A'; ?></td></tr>
                </table>
            </div>
            
            <!-- Verificar Salão -->
            <div class="info-box">
                <h2>🏢 Verificação do Salão</h2>
                <?php
                try {
                    require_once 'models/salao.php';
                    $salao = new Salao();
                    $meu_salao = $salao->buscarPorDono($usuario_logado['id']);
                    
                    if ($meu_salao) {
                        echo '<div class="alert alert-success">✅ Salão encontrado!</div>';
                        echo '<table>';
                        echo '<tr><th>Campo</th><th>Valor</th></tr>';
                        foreach ($meu_salao as $campo => $valor) {
                            echo '<tr><td>' . htmlspecialchars($campo) . '</td><td>' . htmlspecialchars($valor ?? 'NULL') . '</td></tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<div class="alert alert-danger">❌ Nenhum salão encontrado para este parceiro!</div>';
                        echo '<p><strong>Isso explica o erro!</strong> O arquivo profissionais.php redireciona para salao.php quando o parceiro não tem salão cadastrado.</p>';
                    }
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">❌ Erro ao verificar salão: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Ações -->
        <div class="info-box">
            <h2>🎯 Ações</h2>
            
            <?php if (!isLoggedIn()): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="acao" value="login_teste">
                    <button type="submit" class="btn btn-primary">🔑 Fazer Login Automático (Primeiro Parceiro)</button>
                </form>
            <?php else: ?>
                <p><strong>Usuário já está logado!</strong></p>
                
                <?php if (isParceiro()): ?>
                    <?php
                    // Verificar se tem salão
                    require_once 'models/salao.php';
                    $salao = new Salao();
                    $meu_salao = $salao->buscarPorDono($usuario_logado['id']);
                    ?>
                    
                    <?php if ($meu_salao): ?>
                        <a href="parceiro/profissionais.php" class="btn btn-success">✅ Acessar Profissionais</a>
                    <?php else: ?>
                        <a href="parceiro/salao.php" class="btn btn-primary">🏢 Cadastrar Salão Primeiro</a>
                        <p><em>Você precisa cadastrar um salão antes de gerenciar profissionais.</em></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
            <?php endif; ?>
            
            <a href="debug_parceiro_info.php" class="btn btn-secondary">🔍 Debug Completo</a>
        </div>
        
        <!-- Lista de Parceiros no Banco -->
        <div class="info-box">
            <h2>👥 Parceiros no Banco de Dados</h2>
            <?php
            try {
                $conn = getConnection();
                $stmt = $conn->prepare("SELECT id, nome, email, telefone, data_cadastro FROM usuarios WHERE tipo_usuario = 'parceiro' ORDER BY id");
                $stmt->execute();
                $parceiros = $stmt->fetchAll();
                
                if ($parceiros) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Nome</th><th>Email</th><th>Telefone</th><th>Data Cadastro</th></tr>';
                    foreach ($parceiros as $p) {
                        echo '<tr>';
                        echo '<td>' . $p['id'] . '</td>';
                        echo '<td>' . htmlspecialchars($p['nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($p['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($p['telefone'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($p['data_cadastro'] ?? 'N/A') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="alert alert-danger">❌ Nenhum parceiro encontrado no banco!</div>';
                }
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">❌ Erro ao buscar parceiros: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
        
        <!-- Diagnóstico -->
        <div class="info-box">
            <h2>🎯 Diagnóstico do Problema</h2>
            <?php if (isLoggedIn() && isParceiro()): ?>
                <?php
                require_once 'models/salao.php';
                $salao = new Salao();
                $meu_salao = $salao->buscarPorDono($usuario_logado['id']);
                ?>
                
                <?php if (!$meu_salao): ?>
                    <div class="alert alert-danger">
                        <strong>🎯 PROBLEMA IDENTIFICADO!</strong><br>
                        O parceiro está logado corretamente, mas não possui um salão cadastrado.<br>
                        O arquivo <code>profissionais.php</code> tem esta verificação:
                        <pre style="background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 4px;">if (!$meu_salao) {
    header('Location: salao.php');
    exit;
}</pre>
                        <strong>Solução:</strong> Cadastrar um salão primeiro em <a href="parceiro/salao.php">salao.php</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <strong>✅ TUDO OK!</strong><br>
                        O parceiro tem salão cadastrado. O problema pode ser outro (CSRF, sessão, etc.)
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>❌ PROBLEMA:</strong> Usuário não está logado como parceiro.<br>
                    <strong>Solução:</strong> Fazer login primeiro.
                </div>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <p><strong>CorteFácil</strong> - Teste de Login do Parceiro</p>
            <p><small>Arquivo: test_login_parceiro.php - Remover após debug</small></p>
        </div>
    </div>
</body>
</html>