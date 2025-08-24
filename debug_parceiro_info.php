<?php
/**
 * Debug das Informações do Parceiro
 * Verifica se o parceiro tem todas as informações necessárias
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'models/salao.php';

// Verificar se é parceiro
if (!isParceiro()) {
    die('Acesso negado. Faça login como parceiro.');
}

$usuario = getLoggedUser();
$salao = new Salao();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Parceiro - CorteFácil</title>
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
        .info-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
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
            font-weight: bold;
        }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug das Informações do Parceiro</h1>
        <p><strong>Data:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        
        <!-- Informações da Sessão -->
        <div class="info-section">
            <h2>📋 Informações da Sessão</h2>
            <table>
                <tr><th>Campo</th><th>Valor</th><th>Status</th></tr>
                <tr>
                    <td>Session ID</td>
                    <td><?php echo session_id(); ?></td>
                    <td><?php echo session_id() ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Usuario ID</td>
                    <td><?php echo $_SESSION['usuario_id'] ?? 'N/A'; ?></td>
                    <td><?php echo isset($_SESSION['usuario_id']) ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Nome</td>
                    <td><?php echo $_SESSION['usuario_nome'] ?? 'N/A'; ?></td>
                    <td><?php echo isset($_SESSION['usuario_nome']) ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo $_SESSION['usuario_email'] ?? 'N/A'; ?></td>
                    <td><?php echo isset($_SESSION['usuario_email']) ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Tipo Usuario</td>
                    <td><?php echo $_SESSION['tipo_usuario'] ?? 'N/A'; ?></td>
                    <td><?php echo ($_SESSION['tipo_usuario'] ?? '') === 'parceiro' ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Telefone</td>
                    <td><?php echo $_SESSION['usuario_telefone'] ?? 'N/A'; ?></td>
                    <td><?php echo isset($_SESSION['usuario_telefone']) ? '✅' : '❌'; ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Informações do Usuário (getLoggedUser) -->
        <div class="info-section">
            <h2>👤 Dados do Usuário (getLoggedUser)</h2>
            <?php if ($usuario): ?>
                <table>
                    <tr><th>Campo</th><th>Valor</th></tr>
                    <tr><td>ID</td><td><?php echo $usuario['id']; ?></td></tr>
                    <tr><td>Nome</td><td><?php echo $usuario['nome']; ?></td></tr>
                    <tr><td>Email</td><td><?php echo $usuario['email']; ?></td></tr>
                    <tr><td>Tipo</td><td><?php echo $usuario['tipo_usuario']; ?></td></tr>
                    <tr><td>Telefone</td><td><?php echo $usuario['telefone'] ?? 'N/A'; ?></td></tr>
                </table>
            <?php else: ?>
                <div class="error">❌ Erro: getLoggedUser() retornou null</div>
            <?php endif; ?>
        </div>
        
        <!-- Verificação do Salão -->
        <div class="info-section">
            <h2>🏢 Informações do Salão</h2>
            <?php
            try {
                $meu_salao = $salao->buscarPorDono($usuario['id']);
                
                if ($meu_salao) {
                    echo '<div class="success">✅ Salão encontrado!</div>';
                    echo '<table>';
                    echo '<tr><th>Campo</th><th>Valor</th></tr>';
                    foreach ($meu_salao as $campo => $valor) {
                        echo '<tr><td>' . htmlspecialchars($campo) . '</td><td>' . htmlspecialchars($valor ?? 'NULL') . '</td></tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="error">❌ Nenhum salão encontrado para este parceiro!</div>';
                    echo '<p><strong>Possíveis causas:</strong></p>';
                    echo '<ul>';
                    echo '<li>Parceiro não cadastrou um salão ainda</li>';
                    echo '<li>Problema na consulta ao banco de dados</li>';
                    echo '<li>ID do usuário incorreto</li>';
                    echo '</ul>';
                }
            } catch (Exception $e) {
                echo '<div class="error">❌ Erro ao buscar salão: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
        
        <!-- Teste de Conexão com Banco -->
        <div class="info-section">
            <h2>🗄️ Teste de Conexão com Banco</h2>
            <?php
            try {
                require_once 'config/database.php';
                $conn = getConnection();
                
                if ($conn) {
                    echo '<div class="success">✅ Conexão com banco estabelecida</div>';
                    
                    // Testar se a tabela saloes existe
                    $stmt = $conn->query("SHOW TABLES LIKE 'saloes'");
                    if ($stmt->rowCount() > 0) {
                        echo '<div class="success">✅ Tabela saloes existe</div>';
                        
                        // Verificar estrutura da tabela
                        $stmt = $conn->query("DESCRIBE saloes");
                        $colunas = $stmt->fetchAll();
                        
                        echo '<h4>Estrutura da tabela saloes:</h4>';
                        echo '<table>';
                        echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>';
                        foreach ($colunas as $coluna) {
                            echo '<tr>';
                            echo '<td>' . $coluna['Field'] . '</td>';
                            echo '<td>' . $coluna['Type'] . '</td>';
                            echo '<td>' . $coluna['Null'] . '</td>';
                            echo '<td>' . $coluna['Key'] . '</td>';
                            echo '<td>' . ($coluna['Default'] ?? 'NULL') . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                        // Contar salões do parceiro
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM saloes WHERE id_dono = ?");
                        $stmt->execute([$usuario['id']]);
                        $count = $stmt->fetch();
                        
                        echo '<p><strong>Total de salões do parceiro:</strong> ' . $count['total'] . '</p>';
                        
                        if ($count['total'] == 0) {
                            echo '<div class="warning">⚠️ Este parceiro não possui salões cadastrados</div>';
                            echo '<p><strong>Solução:</strong> O parceiro precisa cadastrar um salão primeiro em <a href="salao.php">salao.php</a></p>';
                        }
                        
                    } else {
                        echo '<div class="error">❌ Tabela saloes não existe</div>';
                    }
                    
                } else {
                    echo '<div class="error">❌ Falha na conexão com banco</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
        
        <!-- Informações de CSRF -->
        <div class="info-section">
            <h2>🔐 Informações de CSRF</h2>
            <table>
                <tr><th>Campo</th><th>Valor</th><th>Status</th></tr>
                <tr>
                    <td>Token CSRF</td>
                    <td><?php echo isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 20) . '...' : 'N/A'; ?></td>
                    <td><?php echo isset($_SESSION['csrf_token']) ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Token Time</td>
                    <td><?php echo isset($_SESSION['csrf_token_time']) ? date('d/m/Y H:i:s', $_SESSION['csrf_token_time']) : 'N/A'; ?></td>
                    <td><?php echo isset($_SESSION['csrf_token_time']) ? '✅' : '❌'; ?></td>
                </tr>
                <tr>
                    <td>Token Age</td>
                    <td><?php 
                        if (isset($_SESSION['csrf_token_time'])) {
                            $age = time() - $_SESSION['csrf_token_time'];
                            echo $age . ' segundos';
                        } else {
                            echo 'N/A';
                        }
                    ?></td>
                    <td><?php 
                        if (isset($_SESSION['csrf_token_time'])) {
                            $age = time() - $_SESSION['csrf_token_time'];
                            echo $age < 7200 ? '✅' : '❌ (Expirado)';
                        } else {
                            echo '❌';
                        }
                    ?></td>
                </tr>
            </table>
            
            <?php
            // Gerar novo token para teste
            $novo_token = generateCSRFToken();
            echo '<p><strong>Novo token gerado:</strong></p>';
            echo '<div class="code">' . htmlspecialchars($novo_token) . '</div>';
            ?>
        </div>
        
        <!-- Diagnóstico Final -->
        <div class="info-section">
            <h2>🎯 Diagnóstico Final</h2>
            <?php
            $problemas = [];
            $solucoes = [];
            
            // Verificar problemas
            if (!isset($_SESSION['usuario_id'])) {
                $problemas[] = "Usuário não está logado";
                $solucoes[] = "Fazer login novamente";
            }
            
            if (($_SESSION['tipo_usuario'] ?? '') !== 'parceiro') {
                $problemas[] = "Usuário não é parceiro";
                $solucoes[] = "Verificar tipo de usuário no banco";
            }
            
            if (!$meu_salao) {
                $problemas[] = "Parceiro não possui salão cadastrado";
                $solucoes[] = "Cadastrar salão em salao.php";
            }
            
            if (!isset($_SESSION['csrf_token'])) {
                $problemas[] = "Token CSRF não existe";
                $solucoes[] = "Token será gerado automaticamente";
            } elseif (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > 7200)) {
                $problemas[] = "Token CSRF expirado";
                $solucoes[] = "Token será regenerado automaticamente";
            }
            
            if (empty($problemas)) {
                echo '<div class="success">✅ <strong>Tudo OK!</strong> O parceiro tem todas as informações necessárias.</div>';
            } else {
                echo '<div class="error"><strong>❌ Problemas encontrados:</strong></div>';
                echo '<ul>';
                foreach ($problemas as $problema) {
                    echo '<li>' . htmlspecialchars($problema) . '</li>';
                }
                echo '</ul>';
                
                echo '<div class="warning"><strong>🔧 Soluções sugeridas:</strong></div>';
                echo '<ul>';
                foreach ($solucoes as $solucao) {
                    echo '<li>' . htmlspecialchars($solucao) . '</li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <p><strong>CorteFácil</strong> - Debug do Parceiro</p>
            <p><small>Arquivo: debug_parceiro_info.php - Remover após debug</small></p>
            <p>
                <a href="parceiro/profissionais.php" style="margin: 0 10px;">← Voltar para Profissionais</a>
                <a href="parceiro/salao.php" style="margin: 0 10px;">Cadastrar Salão</a>
                <a href="logout.php" style="margin: 0 10px;">Logout</a>
            </p>
        </div>
    </div>
</body>
</html>