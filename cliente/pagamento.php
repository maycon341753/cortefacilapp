<?php
/**
 * Página de Pagamento Simulado
 * Simula o pagamento da taxa de R$ 1,29 do agendamento
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$agendamento = new Agendamento();

$erro = '';
$sucesso = '';

// Verificar se foi passado o ID do agendamento
if (!isset($_GET['agendamento']) || empty($_GET['agendamento'])) {
    header('Location: dashboard.php');
    exit;
}

$id_agendamento = (int)$_GET['agendamento'];

// Buscar dados do agendamento
$dados_agendamento = $agendamento->buscarPorId($id_agendamento);

if (!$dados_agendamento || $dados_agendamento['id_cliente'] != $usuario['id']) {
    header('Location: dashboard.php');
    exit;
}

// Verificar se o agendamento ainda está pendente
if ($dados_agendamento['status'] !== 'pendente') {
    header('Location: agendamentos.php');
    exit;
}

// Processar pagamento simulado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        $metodo_pagamento = $_POST['metodo_pagamento'] ?? '';
        
        if (empty($metodo_pagamento)) {
            throw new Exception('Selecione um método de pagamento.');
        }
        
        // Simular processamento do pagamento
        sleep(2); // Simular delay do processamento
        
        // Atualizar status do agendamento para confirmado
        $resultado = $agendamento->atualizarStatus($id_agendamento, 'confirmado');
        
        if ($resultado) {
            // Log da atividade
            logActivity($usuario['id'], 'pagamento_realizado', "Pagamento de R$ 1,29 realizado para agendamento #{$id_agendamento}");
            
            // Redirecionar para página de sucesso
            header('Location: pagamento-sucesso.php?agendamento=' . $id_agendamento);
            exit;
        } else {
            throw new Exception('Erro ao processar pagamento. Tente novamente.');
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            <i class="fas fa-cut me-2"></i>
                            CorteFácil
                        </h5>
                        <small class="text-white-50">Cliente</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendar.php">
                                <i class="fas fa-calendar-plus"></i>
                                Novo Agendamento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendamentos.php">
                                <i class="fas fa-calendar-alt"></i>
                                Meus Agendamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="saloes.php">
                                <i class="fas fa-store"></i>
                                Salões Parceiros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="perfil.php">
                                <i class="fas fa-user"></i>
                                Meu Perfil
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white-50">
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-credit-card me-2 text-primary"></i>
                        Pagamento
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="agendar.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Voltar
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Alertas -->
                <?php if ($erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($erro); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Resumo do Agendamento -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-clipboard-check me-2"></i>
                                    Resumo do Agendamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Salão:</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($dados_agendamento['nome_salao']); ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Profissional:</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($dados_agendamento['nome_profissional']); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Data:</label>
                                            <p class="mb-0"><?php echo formatarData($dados_agendamento['data']); ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Horário:</label>
                                            <p class="mb-0"><?php echo formatarHora($dados_agendamento['hora']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-0">
                                            <label class="form-label fw-bold">Status:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-warning">Aguardando Pagamento</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="mb-0">
                                            <label class="form-label fw-bold">Taxa da Plataforma:</label>
                                            <p class="mb-0 h5 text-success">R$ <?php echo number_format($dados_agendamento['valor_taxa'], 2, ',', '.'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulário de Pagamento -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Método de Pagamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formPagamento">
                                    <?php echo generateCsrfToken(); ?>
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Simulação de Pagamento:</strong> Este é um ambiente de demonstração. 
                                        Nenhum pagamento real será processado.
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Escolha o método de pagamento:</label>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="card payment-method" data-method="cartao">
                                                    <div class="card-body text-center">
                                                        <input type="radio" name="metodo_pagamento" value="cartao" id="cartao" class="d-none">
                                                        <label for="cartao" class="w-100 cursor-pointer">
                                                            <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                                                            <h6>Cartão de Crédito</h6>
                                                            <p class="small text-muted mb-0">Visa, Mastercard, Elo</p>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <div class="card payment-method" data-method="pix">
                                                    <div class="card-body text-center">
                                                        <input type="radio" name="metodo_pagamento" value="pix" id="pix" class="d-none">
                                                        <label for="pix" class="w-100 cursor-pointer">
                                                            <i class="fas fa-qrcode fa-3x text-success mb-3"></i>
                                                            <h6>PIX</h6>
                                                            <p class="small text-muted mb-0">Pagamento instantâneo</p>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Detalhes do Pagamento -->
                                    <div id="detalhesCartao" class="payment-details" style="display: none;">
                                        <h6 class="mb-3">Dados do Cartão</h6>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label for="numeroCartao" class="form-label">Número do Cartão</label>
                                                <input type="text" class="form-control" id="numeroCartao" 
                                                       placeholder="1234 5678 9012 3456" maxlength="19">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="cvv" class="form-label">CVV</label>
                                                <input type="text" class="form-control" id="cvv" 
                                                       placeholder="123" maxlength="4">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="mesVencimento" class="form-label">Mês</label>
                                                <select class="form-select" id="mesVencimento">
                                                    <option value="">Mês</option>
                                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                                        <option value="<?php echo sprintf('%02d', $i); ?>">
                                                            <?php echo sprintf('%02d', $i); ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="anoVencimento" class="form-label">Ano</label>
                                                <select class="form-select" id="anoVencimento">
                                                    <option value="">Ano</option>
                                                    <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nomeCartao" class="form-label">Nome no Cartão</label>
                                            <input type="text" class="form-control" id="nomeCartao" 
                                                   placeholder="Nome como está no cartão">
                                        </div>
                                    </div>
                                    
                                    <div id="detalhesPix" class="payment-details" style="display: none;">
                                        <div class="text-center">
                                            <h6 class="mb-3">Pagamento via PIX</h6>
                                            <div class="bg-light p-4 rounded mb-3">
                                                <i class="fas fa-qrcode fa-5x text-success mb-3"></i>
                                                <p class="mb-2">Escaneie o QR Code com seu banco</p>
                                                <p class="small text-muted">Ou copie e cole a chave PIX</p>
                                            </div>
                                            <div class="input-group">
                                                <input type="text" class="form-control" 
                                                       value="00020126580014BR.GOV.BCB.PIX0136123e4567-e12b-12d1-a456-426614174000" 
                                                       readonly id="chavePix">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="copiarChavePix()">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Total -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="mb-0">Total a Pagar:</h5>
                                        <h4 class="mb-0 text-success">R$ <?php echo number_format($dados_agendamento['valor_taxa'], 2, ',', '.'); ?></h4>
                                    </div>
                                    
                                    <!-- Botões -->
                                    <div class="d-flex justify-content-between">
                                        <a href="agendar.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Cancelar
                                        </a>
                                        
                                        <button type="submit" class="btn btn-success btn-lg" id="btnPagar">
                                            <i class="fas fa-credit-card me-2"></i>
                                            Confirmar Pagamento
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Processando...</span>
                    </div>
                    <p class="mb-0">Processando pagamento...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Event listeners para métodos de pagamento
            const paymentMethods = document.querySelectorAll('.payment-method');
            const paymentDetails = document.querySelectorAll('.payment-details');
            
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    const methodType = this.dataset.method;
                    const radio = this.querySelector('input[type="radio"]');
                    
                    // Marcar radio button
                    radio.checked = true;
                    
                    // Remover seleção anterior
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    paymentDetails.forEach(d => d.style.display = 'none');
                    
                    // Adicionar seleção atual
                    this.classList.add('selected');
                    
                    // Mostrar detalhes do método selecionado
                    const details = document.getElementById('detalhes' + methodType.charAt(0).toUpperCase() + methodType.slice(1));
                    if (details) {
                        details.style.display = 'block';
                    }
                });
            });
            
            // Máscara para número do cartão
            const numeroCartao = document.getElementById('numeroCartao');
            if (numeroCartao) {
                numeroCartao.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                    this.value = value;
                });
            }
            
            // Máscara para CVV
            const cvv = document.getElementById('cvv');
            if (cvv) {
                cvv.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                });
            }
            
            // Submissão do formulário
            const form = document.getElementById('formPagamento');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const metodo = document.querySelector('input[name="metodo_pagamento"]:checked');
                if (!metodo) {
                    CorteFacil.showNotification('Selecione um método de pagamento.', 'warning');
                    return;
                }
                
                // Mostrar loading
                const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
                loadingModal.show();
                
                // Simular delay e submeter
                setTimeout(() => {
                    form.submit();
                }, 2000);
            });
        });
        
        function copiarChavePix() {
            const chavePix = document.getElementById('chavePix');
            chavePix.select();
            document.execCommand('copy');
            CorteFacil.showNotification('Chave PIX copiada!', 'success');
        }
    </script>
    
    <style>
        .payment-method {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .payment-method:hover {
            border-color: var(--bs-primary);
            transform: translateY(-2px);
        }
        
        .payment-method.selected {
            border-color: var(--bs-primary);
            background-color: rgba(var(--bs-primary-rgb), 0.1);
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
</body>
</html>