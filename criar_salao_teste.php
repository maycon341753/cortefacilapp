<?php
/**
 * Criar Salão de Teste para Parceiro
 * Resolve o problema de parceiro sem salão cadastrado
 */

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'models/salao.php';

$erro = '';
$sucesso = '';
$salao_criado = null;

// Processar criação do salão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar_salao') {
    try {
        $conn = getConnection();
        
        // Buscar um parceiro que não tenha salão
        $stmt = $conn->prepare("
            SELECT u.* FROM usuarios u 
            LEFT JOIN saloes s ON u.id = s.id_dono 
            WHERE u.tipo_usuario = 'parceiro' AND s.id IS NULL 
            LIMIT 1
        ");
        $stmt->execute();
        $parceiro = $stmt->fetch();
        
        if (!$parceiro) {
            // Se não encontrou parceiro sem salão, pegar o primeiro parceiro
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE tipo_usuario = 'parceiro' LIMIT 1");
            $stmt->execute();
            $parceiro = $stmt->fetch();
        }
        
        if ($parceiro) {
            // Criar salão de teste
            $dados_salao = [
                'nome' => 'Salão Teste - ' . $parceiro['nome'],
                'endereco' => 'Rua Teste, 123',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01000-000',
                'telefone' => $parceiro['telefone'] ?? '(11) 99999-9999',
                'email' => $parceiro['email'],
                'descricao' => 'Salão de teste criado automaticamente para resolver problema de acesso.',
                'horario_funcionamento' => 'Segunda a Sexta: 8h às 18h, Sábado: 8h às 16h',
                'id_dono' => $parceiro['id']
            ];
            
            $salao = new Salao();
            $id_salao = $salao->cadastrar($dados_salao);
            
            if ($id_salao) {
                $sucesso = "Salão criado com sucesso! ID: $id_salao";
                
                // Buscar o salão criado
                $salao_criado = $salao->buscarPorId($id_salao);
                
                // Fazer login automático do parceiro
                login($parceiro);
            } else {
                $erro = "Erro ao criar salão";
            }
        } else {
            $erro = "Nenhum parceiro encontrado no banco de dados";
        }
        
    } catch (Exception $e) {
        $erro = "Erro ao criar salão: " . $e->getMessage();
    }
}

// Verificar situação atual
$parceiros_sem_salao = [];
$parceiros_com_salao = [];

try {
    $conn = getConnection();
    
    // Parceiros sem salão
    $stmt = $conn->prepare("
        SELECT u.id, u.nome, u.email FROM usuarios u 
        LEFT JOIN saloes s ON u.id = s.id_dono 
        WHERE u.tipo_usuario = 'parceiro' AND s.id IS NULL
    ");
    $stmt->execute();
    $parceiros_sem_salao = $stmt->fetchAll();
    
    // Parceiros com salão
    $stmt = $conn->prepare("
        SELECT u.id, u.nome, u.email, s.id as salao_id, s.nome as salao_nome 
        FROM usuarios u 
        INNER JOIN saloes s ON u.id = s.id_dono 
        WHERE u.tipo_usuario = 'parceiro'
    ");
    $stmt->execute();
    $parceiros_com_salao = $stmt->fetchAll();
    
} catch (Exception $e) {
    $erro = "Erro ao verificar parceiros: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Salão de Teste - CorteFácil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
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
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
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
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏢 Criar Salão de Teste</h1>
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
        
        <!-- Diagnóstico -->
        <div class="info-box">
            <h2>🎯 Diagnóstico do Problema</h2>
            <div class="alert alert-warning">
                <strong>🔍 PROBLEMA IDENTIFICADO:</strong><br>
                O erro na página <code>profissionais.php</code> acontece porque o parceiro não possui um salão cadastrado.<br><br>
                <strong>Código em profissionais.php:</strong>
                <pre style="background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 4px;">$meu_salao = $salao->buscarPorDono($_SESSION['usuario_id']);
if (!$meu_salao) {
    header('Location: salao.php');
    exit;
}</pre>
                <strong>Solução:</strong> Criar um salão para o parceiro.
            </div>
        </div>
        
        <div class="grid">
            <!-- Parceiros SEM Salão -->
            <div class="info-box">
                <h2>❌ Parceiros SEM Salão (<?php echo count($parceiros_sem_salao); ?>)</h2>
                <?php if ($parceiros_sem_salao): ?>
                    <table>
                        <tr><th>ID</th><th>Nome</th><th>Email</th></tr>
                        <?php foreach ($parceiros_sem_salao as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['nome']); ?></td>
                                <td><?php echo htmlspecialchars($p['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    
                    <div style="text-align: center; margin: 20px 0;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="acao" value="criar_salao">
                            <button type="submit" class="btn btn-primary">🏢 Criar Salão para Primeiro Parceiro</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">✅ Todos os parceiros já possuem salão!</div>
                <?php endif; ?>
            </div>
            
            <!-- Parceiros COM Salão -->
            <div class="info-box">
                <h2>✅ Parceiros COM Salão (<?php echo count($parceiros_com_salao); ?>)</h2>
                <?php if ($parceiros_com_salao): ?>
                    <table>
                        <tr><th>ID</th><th>Parceiro</th><th>Salão</th></tr>
                        <?php foreach ($parceiros_com_salao as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['nome']); ?></td>
                                <td><?php echo htmlspecialchars($p['salao_nome']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <div class="alert alert-danger">❌ Nenhum parceiro possui salão cadastrado!</div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($salao_criado): ?>
            <!-- Salão Criado -->
            <div class="info-box">
                <h2>🎉 Salão Criado com Sucesso!</h2>
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <?php foreach ($salao_criado as $campo => $valor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($campo); ?></td>
                            <td><?php echo htmlspecialchars($valor ?? 'NULL'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- Status da Sessão -->
        <div class="info-box">
            <h2>📊 Status da Sessão</h2>
            <table>
                <tr>
                    <th>Verificação</th>
                    <th>Status</th>
                    <th>Detalhes</th>
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
                <?php if (isLoggedIn() && isParceiro()): ?>
                    <?php
                    $salao = new Salao();
                    $meu_salao = $salao->buscarPorDono($_SESSION['usuario_id']);
                    ?>
                    <tr>
                        <td>Possui Salão</td>
                        <td><?php echo $meu_salao ? '✅ Sim' : '❌ Não'; ?></td>
                        <td><?php echo $meu_salao ? $meu_salao['nome'] : 'Nenhum salão'; ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Ações -->
        <div class="info-box">
            <h2>🎯 Ações</h2>
            
            <?php if (isLoggedIn() && isParceiro()): ?>
                <?php
                $salao = new Salao();
                $meu_salao = $salao->buscarPorDono($_SESSION['usuario_id']);
                ?>
                
                <?php if ($meu_salao): ?>
                    <div class="alert alert-success">
                        <strong>🎉 PROBLEMA RESOLVIDO!</strong><br>
                        O parceiro agora possui um salão cadastrado e pode acessar a página de profissionais.
                    </div>
                    <a href="parceiro/profissionais.php" class="btn btn-success">✅ Testar Página Profissionais</a>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <strong>⚠️ AINDA SEM SALÃO!</strong><br>
                        O parceiro logado ainda não possui salão. Use o botão acima para criar.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <a href="test_login_parceiro.php" class="btn btn-primary">🔑 Fazer Login como Parceiro</a>
            <?php endif; ?>
            
            <a href="debug_parceiro_info.php" class="btn btn-secondary">🔍 Debug Completo</a>
            <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>
        
        <!-- Próximos Passos -->
        <div class="info-box">
            <h2>📋 Próximos Passos</h2>
            <ol>
                <li><strong>Criar Salão:</strong> Use o botão acima se houver parceiros sem salão</li>
                <li><strong>Fazer Login:</strong> Acesse <code>test_login_parceiro.php</code></li>
                <li><strong>Testar Profissionais:</strong> Acesse <code>parceiro/profissionais.php</code></li>
                <li><strong>Verificar CSRF:</strong> Se ainda houver erro, pode ser problema de token</li>
                <li><strong>Limpar Arquivos:</strong> Remover arquivos de teste após resolver</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <p><strong>CorteFácil</strong> - Resolver Problema do Parceiro</p>
            <p><small>Arquivo: criar_salao_teste.php - Remover após debug</small></p>
        </div>
    </div>
</body>
</html>