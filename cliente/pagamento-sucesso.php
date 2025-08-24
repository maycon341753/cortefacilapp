<?php
/**
 * Página de Sucesso do Pagamento
 * Confirma que o pagamento foi processado e o agendamento foi confirmado
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$agendamento = new Agendamento();

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

// Verificar se o agendamento foi confirmado
if ($dados_agendamento['status'] !== 'confirmado') {
    header('Location: pagamento.php?agendamento=' . $id_agendamento);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado - CorteFácil</title>
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
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Sucesso -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="success-icon">
                                    <i class="fas fa-check-circle fa-5x text-success"></i>
                                </div>
                            </div>
                            
                            <h1 class="h2 text-success mb-3">
                                <i class="fas fa-party-horn me-2"></i>
                                Pagamento Confirmado!
                            </h1>
                            
                            <p class="lead mb-4">
                                Seu agendamento foi confirmado com sucesso. 
                                Você receberá uma confirmação por email em breve.
                            </p>
                        </div>
                        
                        <!-- Detalhes do Agendamento -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Detalhes do Agendamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Número do Agendamento:</label>
                                            <p class="mb-0 h6">#<?php echo str_pad($dados_agendamento['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Salão:</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($dados_agendamento['nome_salao']); ?></p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Profissional:</label>
                                            <p class="mb-0"><?php echo htmlspecialchars($dados_agendamento['nome_profissional']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Data:</label>
                                            <p class="mb-0"><?php echo formatarData($dados_agendamento['data']); ?></p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Horário:</label>
                                            <p class="mb-0"><?php echo formatarHora($dados_agendamento['hora']); ?></p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-muted">Status:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-success fs-6">Confirmado</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-0">
                                            <label class="form-label fw-bold text-muted">Data do Pagamento:</label>
                                            <p class="mb-0"><?php echo formatarData(date('Y-m-d')); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <div class="mb-0">
                                            <label class="form-label fw-bold text-muted">Valor Pago:</label>
                                            <p class="mb-0 h5 text-success">R$ <?php echo number_format($dados_agendamento['valor_taxa'], 2, ',', '.'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Próximos Passos -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-list-check me-2"></i>
                                    Próximos Passos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <span class="fw-bold">1</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Confirmação por Email</h6>
                                                <p class="mb-0 small text-muted">
                                                    Você receberá um email com todos os detalhes do seu agendamento.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <span class="fw-bold">2</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Lembrete</h6>
                                                <p class="mb-0 small text-muted">
                                                    Você receberá um lembrete 1 dia antes do seu agendamento.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <span class="fw-bold">3</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Compareça no Horário</h6>
                                                <p class="mb-0 small text-muted">
                                                    Chegue com 10 minutos de antecedência no salão.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <span class="fw-bold">4</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Pagamento do Serviço</h6>
                                                <p class="mb-0 small text-muted">
                                                    O pagamento do serviço é feito diretamente no salão.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações de Contato -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-phone me-2"></i>
                                    Informações de Contato
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Salão</h6>
                                        <p class="mb-1"><?php echo htmlspecialchars($dados_agendamento['nome_salao']); ?></p>
                                        <p class="mb-1 small text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($dados_agendamento['endereco_salao'] ?? 'Endereço não disponível'); ?>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            <?php echo formatTelefone($dados_agendamento['telefone_salao'] ?? ''); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Suporte CorteFácil</h6>
                                        <p class="mb-1 small text-muted">
                                            <i class="fas fa-envelope me-1"></i>
                                            suporte@cortefacil.com
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            <i class="fas fa-phone me-1"></i>
                                            (11) 9999-9999
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ações -->
                        <div class="text-center mb-5">
                            <div class="btn-group" role="group">
                                <a href="dashboard.php" class="btn btn-primary">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Ir para Dashboard
                                </a>
                                
                                <a href="agendamentos.php" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Meus Agendamentos
                                </a>
                                
                                <a href="agendar.php" class="btn btn-outline-success">
                                    <i class="fas fa-plus me-2"></i>
                                    Novo Agendamento
                                </a>
                            </div>
                        </div>
                        
                        <!-- Botão de Impressão -->
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir Comprovante
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Animação de sucesso
        document.addEventListener('DOMContentLoaded', function() {
            // Animar ícone de sucesso
            const successIcon = document.querySelector('.success-icon i');
            if (successIcon) {
                successIcon.style.transform = 'scale(0)';
                successIcon.style.transition = 'transform 0.5s ease-in-out';
                
                setTimeout(() => {
                    successIcon.style.transform = 'scale(1)';
                }, 200);
            }
            
            // Mostrar notificação de sucesso
            setTimeout(() => {
                CorteFacil.showNotification('Agendamento confirmado com sucesso!', 'success');
            }, 1000);
        });
    </script>
    
    <style>
        @media print {
            .sidebar {
                display: none !important;
            }
            
            .col-md-9 {
                width: 100% !important;
                margin: 0 !important;
            }
            
            .btn {
                display: none !important;
            }
            
            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
        
        .success-icon {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
    </style>
</body>
</html>