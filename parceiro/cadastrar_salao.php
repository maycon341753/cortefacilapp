<?php
/**
 * Página de Cadastro de Salão
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once '../includes/auth.php';
$auth->requireAuth('parceiro');

$user = $auth->getCurrentUser();
$error = '';
$success = '';

// Verificar se já possui salão cadastrado
try {
    $sqlVerificar = "SELECT id FROM saloes WHERE id_dono = ? AND ativo = 1";
    $stmtVerificar = $database->query($sqlVerificar, [$user['id']]);
    $salaoExistente = $stmtVerificar->fetch();
    
    if ($salaoExistente) {
        header('Location: dashboard.php');
        exit;
    }
} catch (Exception $e) {
    $error = 'Erro ao verificar salão existente: ' . $e->getMessage();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $horario_funcionamento = trim($_POST['horario_funcionamento'] ?? '');
    
    // Validações
    if (empty($nome)) {
        $error = 'Nome do salão é obrigatório';
    } elseif (empty($endereco)) {
        $error = 'Endereço é obrigatório';
    } elseif (empty($telefone)) {
        $error = 'Telefone é obrigatório';
    } else {
        try {
            // Inserir salão
            $sql = "INSERT INTO saloes (id_dono, nome, endereco, telefone, descricao, horario_funcionamento, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)";
            
            $database->query($sql, [
                $user['id'],
                $nome,
                $endereco,
                $telefone,
                $descricao,
                $horario_funcionamento
            ]);
            
            $success = 'Salão cadastrado com sucesso!';
            
            // Redirecionar após 2 segundos
            header('refresh:2;url=dashboard.php');
            
        } catch (Exception $e) {
            $error = 'Erro ao cadastrar salão: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Salão - CorteFácil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">CorteFácil</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="../logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2 class="card-title">🏪 Cadastrar Meu Salão</h2>
                        <p>Complete as informações do seu salão para começar a receber agendamentos</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br><small>Redirecionando para o painel...</small>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="form_cadastro_salao">
                        <div class="form-group">
                            <label for="nome" class="form-label">Nome do Salão *</label>
                            <input 
                                type="text" 
                                id="nome" 
                                name="nome" 
                                class="form-control" 
                                placeholder="Ex: Salão Beleza & Estilo"
                                value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="endereco" class="form-label">Endereço Completo *</label>
                            <textarea 
                                id="endereco" 
                                name="endereco" 
                                class="form-control" 
                                rows="3"
                                placeholder="Rua, número, bairro, cidade - CEP"
                                required
                            ><?php echo htmlspecialchars($_POST['endereco'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone" class="form-label">Telefone/WhatsApp *</label>
                                    <input 
                                        type="tel" 
                                        id="telefone" 
                                        name="telefone" 
                                        class="form-control" 
                                        placeholder="(11) 99999-9999"
                                        value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"
                                        required
                                    >
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="horario_funcionamento" class="form-label">Horário de Funcionamento</label>
                                    <input 
                                        type="text" 
                                        id="horario_funcionamento" 
                                        name="horario_funcionamento" 
                                        class="form-control" 
                                        placeholder="Ex: Seg-Sex 8h-18h, Sáb 8h-16h"
                                        value="<?php echo htmlspecialchars($_POST['horario_funcionamento'] ?? ''); ?>"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao" class="form-label">Descrição do Salão</label>
                            <textarea 
                                id="descricao" 
                                name="descricao" 
                                class="form-control" 
                                rows="4"
                                placeholder="Descreva seu salão, especialidades, diferenciais..."
                            ><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="info-box">
                            <h4>📋 Informações Importantes</h4>
                            <ul>
                                <li><strong>Gratuito:</strong> Não cobramos mensalidade dos parceiros</li>
                                <li><strong>Taxa por agendamento:</strong> Clientes pagam R$ 1,29 por agendamento</li>
                                <li><strong>Pagamento no salão:</strong> Valores dos serviços são pagos diretamente no seu salão</li>
                                <li><strong>Gestão completa:</strong> Controle total da sua agenda e profissionais</li>
                            </ul>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input 
                                    type="checkbox" 
                                    id="aceito_termos" 
                                    class="form-check-input" 
                                    required
                                >
                                <label for="aceito_termos" class="form-check-label">
                                    Aceito os <a href="#" target="_blank">termos de uso</a> e 
                                    <a href="#" target="_blank">política de privacidade</a> *
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                🏪 Cadastrar Salão
                            </button>
                        </div>
                    </form>
                    
                    <div class="next-steps mt-4">
                        <h4>🚀 Próximos Passos</h4>
                        <div class="steps-list">
                            <div class="step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h5>Cadastrar Profissionais</h5>
                                    <p>Adicione os profissionais que trabalham no seu salão</p>
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h5>Configurar Agenda</h5>
                                    <p>Defina horários de funcionamento e disponibilidade</p>
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h5>Receber Agendamentos</h5>
                                    <p>Clientes poderão agendar serviços no seu salão</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="header mt-5">
        <div class="container">
            <div class="text-center">
                <p>&copy; 2024 CorteFácil - Sistema de Agendamentos</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    
    <style>
        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
        }
        
        .info-box h4 {
            margin-bottom: 1rem;
        }
        
        .info-box ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }
        
        .info-box li {
            margin-bottom: 0.5rem;
        }
        
        .next-steps {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .next-steps h4 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .step-number {
            background: #667eea;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .step-content h5 {
            margin-bottom: 0.25rem;
            color: #333;
        }
        
        .step-content p {
            margin-bottom: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .steps-list {
                gap: 1.5rem;
            }
            
            .step {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>