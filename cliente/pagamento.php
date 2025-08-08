<?php
/**
 * Página de Pagamento do Cliente
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once '../includes/auth.php';
$auth->requireAuth('cliente');

$user = $auth->getCurrentUser();
$agendamento = null;
$error = '';

// Buscar dados do agendamento
if (isset($_GET['agendamento_id'])) {
    $agendamentoId = (int) $_GET['agendamento_id'];
    
    try {
        $sql = "SELECT a.*, s.nome as salao_nome, s.endereco, s.telefone, 
                       p.nome as profissional_nome, p.especialidade,
                       u.nome as cliente_nome
                FROM agendamentos a 
                JOIN saloes s ON a.id_salao = s.id 
                JOIN profissionais p ON a.id_profissional = p.id 
                JOIN usuarios u ON a.id_cliente = u.id
                WHERE a.id = ? AND a.id_cliente = ? AND a.status = 'pendente'";
        
        $stmt = $database->query($sql, [$agendamentoId, $user['id']]);
        $agendamento = $stmt->fetch();
        
        if (!$agendamento) {
            $error = 'Agendamento não encontrado ou já foi processado';
        }
        
    } catch (Exception $e) {
        $error = 'Erro ao carregar agendamento: ' . $e->getMessage();
    }
} else {
    $error = 'ID do agendamento não fornecido';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - CorteFácil</title>
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
                        <li><a href="dashboard.php">Painel</a></li>
                        <li><a href="agendar.php">Novo Agendamento</a></li>
                        <li><a href="historico.php">Histórico</a></li>
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
                <?php if ($error): ?>
                    <div class="card">
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <div class="text-center">
                            <a href="dashboard.php" class="btn btn-primary">Voltar ao Painel</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header text-center">
                            <h2 class="card-title">Confirmar Pagamento</h2>
                            <p>Finalize seu agendamento pagando a taxa de R$ 1,29</p>
                        </div>
                        
                        <!-- Resumo do Agendamento -->
                        <div class="booking-summary">
                            <h3>Resumo do Agendamento</h3>
                            
                            <div class="summary-card">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="summary-section">
                                            <h4>📍 Local</h4>
                                            <p><strong><?php echo htmlspecialchars($agendamento['salao_nome']); ?></strong></p>
                                            <p><?php echo htmlspecialchars($agendamento['endereco']); ?></p>
                                            <p>📞 <?php echo htmlspecialchars($agendamento['telefone']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="summary-section">
                                            <h4>👤 Profissional</h4>
                                            <p><strong><?php echo htmlspecialchars($agendamento['profissional_nome']); ?></strong></p>
                                            <p><?php echo htmlspecialchars($agendamento['especialidade']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="summary-section">
                                            <h4>📅 Data e Horário</h4>
                                            <p><strong><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></strong></p>
                                            <p><?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="summary-section">
                                            <h4>✂️ Serviço</h4>
                                            <p><?php echo htmlspecialchars($agendamento['servico'] ?: 'Não especificado'); ?></p>
                                            <?php if ($agendamento['observacoes']): ?>
                                                <p><small><?php echo htmlspecialchars($agendamento['observacoes']); ?></small></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detalhes do Pagamento -->
                        <div class="payment-details">
                            <h3>Detalhes do Pagamento</h3>
                            
                            <div class="payment-card">
                                <div class="payment-item">
                                    <span>Taxa de Agendamento</span>
                                    <span class="price">R$ 1,29</span>
                                </div>
                                <div class="payment-item total">
                                    <span><strong>Total a Pagar</strong></span>
                                    <span class="price"><strong>R$ 1,29</strong></span>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Importante:</strong> Esta taxa garante seu agendamento na plataforma. 
                                O valor do serviço será pago diretamente no salão.
                            </div>
                        </div>
                        
                        <!-- Simulação de Pagamento -->
                        <div class="payment-form">
                            <h3>Dados do Pagamento</h3>
                            <p class="text-center"><em>Esta é uma simulação de pagamento para demonstração</em></p>
                            
                            <form id="form_pagamento">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="numero_cartao" class="form-label">Número do Cartão</label>
                                            <input 
                                                type="text" 
                                                id="numero_cartao" 
                                                class="form-control" 
                                                placeholder="1234 5678 9012 3456"
                                                value="1234 5678 9012 3456"
                                                readonly
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cvv" class="form-label">CVV</label>
                                            <input 
                                                type="text" 
                                                id="cvv" 
                                                class="form-control" 
                                                placeholder="123"
                                                value="123"
                                                readonly
                                            >
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nome_titular" class="form-label">Nome do Titular</label>
                                            <input 
                                                type="text" 
                                                id="nome_titular" 
                                                class="form-control" 
                                                value="<?php echo htmlspecialchars($user['nome']); ?>"
                                                readonly
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="validade" class="form-label">Validade</label>
                                            <input 
                                                type="text" 
                                                id="validade" 
                                                class="form-control" 
                                                placeholder="MM/AA"
                                                value="12/25"
                                                readonly
                                            >
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Botão de Pagamento -->
                        <div class="text-center mt-4">
                            <button 
                                type="button" 
                                id="btn_pagar" 
                                class="btn btn-success btn-lg"
                                onclick="simularPagamento(<?php echo $agendamento['id']; ?>)"
                            >
                                💳 Pagar R$ 1,29
                            </button>
                            
                            <div class="mt-3">
                                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                        
                        <!-- Informações de Segurança -->
                        <div class="security-info mt-4">
                            <div class="alert alert-success">
                                <h5>🔒 Pagamento Seguro</h5>
                                <ul class="mb-0">
                                    <li>Seus dados estão protegidos com criptografia SSL</li>
                                    <li>Não armazenamos dados do cartão de crédito</li>
                                    <li>Transação processada por gateway seguro</li>
                                    <li>Você receberá confirmação por email</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
        .booking-summary {
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background-color: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .summary-section {
            margin-bottom: 1.5rem;
        }
        
        .summary-section h4 {
            color: #667eea;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .summary-section p {
            margin-bottom: 0.25rem;
        }
        
        .payment-card {
            background-color: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .payment-item:last-child {
            border-bottom: none;
        }
        
        .payment-item.total {
            border-top: 2px solid #667eea;
            margin-top: 0.5rem;
            padding-top: 1rem;
            font-size: 1.1rem;
        }
        
        .price {
            color: #28a745;
            font-weight: bold;
        }
        
        .payment-form {
            background-color: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .security-info .alert {
            border-left: 4px solid #28a745;
        }
        
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            min-width: 200px;
        }
    </style>
</body>
</html>