<?php
/**
 * Página de Agendamento do Cliente
 * Permite ao cliente escolher salão, profissional, data e horário
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';
require_once '../models/salao.php';
require_once '../models/profissional.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$agendamento = new Agendamento();
$salao = new Salao();
$profissional = new Profissional();

$erro = '';
$sucesso = '';

// Processar formulário de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        $id_salao = (int)($_POST['id_salao'] ?? 0);
        $id_profissional = (int)($_POST['id_profissional'] ?? 0);
        $data = $_POST['data'] ?? '';
        $hora = $_POST['hora'] ?? '';
        
        // Validações
        if (!$id_salao || !$id_profissional || !$data || !$hora) {
            throw new Exception('Todos os campos são obrigatórios.');
        }
        
        // Validar data (não pode ser no passado)
        if (!isDataFutura($data)) {
            throw new Exception('A data deve ser futura.');
        }
        
        // Validar horário
        if (!validarHorario($hora)) {
            throw new Exception('Horário inválido.');
        }
        
        // Verificar disponibilidade
        if (!$agendamento->verificarDisponibilidade($id_profissional, $data, $hora)) {
            throw new Exception('Este horário não está mais disponível.');
        }
        
        // Criar agendamento
        $dados_agendamento = [
            'id_cliente' => $usuario['id'],
            'id_salao' => $id_salao,
            'id_profissional' => $id_profissional,
            'data' => $data,
            'hora' => $hora,
            'status' => 'pendente',
            'valor_taxa' => 1.29
        ];
        
        $id_novo_agendamento = $agendamento->criar($dados_agendamento);
        
        if ($id_novo_agendamento) {
            // Redirecionar para página de pagamento
            header('Location: pagamento.php?agendamento=' . $id_novo_agendamento);
            exit;
        } else {
            throw new Exception('Erro ao criar agendamento.');
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar salões disponíveis
$saloes_disponiveis = $salao->listarAtivos();

// Se foi passado um salão específico na URL
$salao_selecionado = isset($_GET['salao']) ? (int)$_GET['salao'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Agendamento - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- QR Code será gerado com implementação própria -->
    <style>
        /* Ocultar campos hidden do CSRF token */
        input[type="hidden"][name="csrf_token"] {
            display: none !important;
            visibility: hidden !important;
            position: absolute !important;
            left: -9999px !important;
        }

        /* Estilos do Modal de Agendamento */
        .modal-step {
            min-height: 300px;
        }

        .profissional-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .profissional-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .profissional-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        /* Calendário */
        .calendar-widget {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .calendar-day:hover {
            background-color: #e9ecef;
        }

        .calendar-day.disabled {
            color: #6c757d;
            cursor: not-allowed;
            background-color: #f8f9fa;
        }

        .calendar-day.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .calendar-day.today {
            background-color: #ffc107;
            color: #000;
            font-weight: bold;
        }

        .calendar-weekday {
            text-align: center;
            font-weight: bold;
            padding: 0.5rem;
            background-color: #f8f9fa;
            font-size: 0.75rem;
        }

        /* Grid de Horários */
        .horarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 0.5rem;
        }

        .horario-slot {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: white;
        }

        .horario-slot:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .horario-slot.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .horario-slot.disabled {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
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
                            <a class="nav-link active" href="agendar.php">
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
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-primary d-md-none me-3 sidebar-toggle" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="h2 mb-0">
                            <i class="fas fa-calendar-plus me-2 text-primary"></i>
                            Novo Agendamento
                        </h1>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="dashboard.php" class="btn btn-outline-secondary">
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
                
                <?php if ($sucesso): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($sucesso); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário oculto para submissão -->
                <form id="formAgendamento" method="POST" style="display: none;">
                    <input type="hidden" id="id_salao" name="id_salao">
                    <input type="hidden" id="id_profissional" name="id_profissional">
                    <input type="hidden" id="data" name="data">
                    <input type="hidden" id="hora" name="hora">
                </form>
                
                <!-- Área de Seleção de Salões -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-store me-2"></i>
                                    Escolha o Salão para Agendar
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3" id="saloesGrid">
                                    <?php foreach ($saloes_disponiveis as $s): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card salon-card h-100" 
                                                 data-salon-id="<?php echo $s['id']; ?>"
                                                 data-endereco="<?php echo htmlspecialchars($s['endereco']); ?>"
                                                 data-telefone="<?php echo htmlspecialchars($s['telefone']); ?>"
                                                 style="cursor: pointer; transition: all 0.3s ease;">
                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex align-items-start mb-2">
                                                        <div class="salon-icon me-3">
                                                            <i class="fas fa-cut text-primary" style="font-size: 2rem;"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="card-title mb-1 text-truncate"><?php echo htmlspecialchars($s['nome']); ?></h5>
                                                            <p class="text-muted mb-1 small">
                                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                                <?php echo htmlspecialchars($s['cidade'] ?? 'Cidade não informada'); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <p class="text-muted small mb-1">
                                                            <i class="fas fa-calendar-check me-1"></i>
                                                            <?php echo ($s['total_agendamentos'] ?? 0); ?> agendamentos realizados
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="mb-3 flex-grow-1">
                                                        <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.2;">
                                                            <i class="fas fa-location-dot me-1"></i>
                                                            <?php 
                                                                $endereco_completo = trim($s['endereco']);
                                                                if (!empty($s['bairro'])) {
                                                                    $endereco_completo .= ', ' . trim($s['bairro']);
                                                                }
                                                                if (!empty($s['cidade'])) {
                                                                    $endereco_completo .= ', ' . trim($s['cidade']);
                                                                }
                                                                if (!empty($s['cep'])) {
                                                                    $endereco_completo .= ' - ' . trim($s['cep']);
                                                                }
                                                                echo htmlspecialchars($endereco_completo);
                                                            ?>
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="mt-auto">
                                                        <button type="button" class="btn btn-primary btn-sm w-100 agendar-btn" data-salao-id="<?php echo $s['id']; ?>">
                                                            <i class="fas fa-calendar-plus me-1"></i>
                                                            Agendar Aqui
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if (empty($saloes_disponiveis)): ?>
                                    <div class="alert alert-warning text-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Nenhum salão disponível no momento.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar com Informações -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações Importantes
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-primary">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        Taxa da Plataforma
                                    </h6>
                                    <p class="small mb-0">
                                        Cobramos apenas <strong>R$ 1,29</strong> por agendamento para manter a plataforma funcionando.
                                        O pagamento do serviço é feito diretamente no salão.
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-primary">
                                        <i class="fas fa-calendar-times me-2"></i>
                                        Cancelamento
                                    </h6>
                                    <p class="small mb-0">
                                        Você pode cancelar seu agendamento até 2 horas antes do horário marcado.
                                    </p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-primary">
                                        <i class="fas fa-clock me-2"></i>
                                        Pontualidade
                                    </h6>
                                    <p class="small mb-0">
                                        Chegue com 10 minutos de antecedência para não atrasar outros clientes.
                                    </p>
                                </div>
                                
                                <div>
                                    <h6 class="text-primary">
                                        <i class="fas fa-phone me-2"></i>
                                        Dúvidas?
                                    </h6>
                                    <p class="small mb-0">
                                        Entre em contato diretamente com o salão para esclarecer dúvidas sobre serviços e preços.
                                    </p>
                                </div>
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
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mb-0">Carregando...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Agendamento -->
    <div class="modal fade" id="agendamentoModal" tabindex="-1" aria-labelledby="agendamentoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agendamentoModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i>
                        Agendar Serviço
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 33%" id="modalProgressBar"></div>
                    </div>

                    <!-- Etapa 1: Seleção de Profissional -->
                    <div class="modal-step" id="modalStep1">
                        <h6 class="mb-3">
                            <span class="badge bg-primary me-2">1</span>
                            Escolha o Profissional
                        </h6>
                        <div class="row g-3" id="profissionaisGrid">
                            <!-- Profissionais serão carregados aqui -->
                        </div>
                        <div class="text-center mt-3" id="loadingProfissionais" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <span class="ms-2">Carregando profissionais...</span>
                        </div>
                    </div>

                    <!-- Etapa 2: Seleção de Data -->
                    <div class="modal-step" id="modalStep2" style="display: none;">
                        <h6 class="mb-3">
                            <span class="badge bg-primary me-2">2</span>
                            Escolha a Data
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="calendar-widget" id="calendarWidget">
                                    <!-- Calendário será gerado aqui -->
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="selected-date-info">
                                    <h6>Data Selecionada:</h6>
                                    <div class="alert alert-light" id="selectedDateDisplay">
                                        <i class="fas fa-calendar me-2"></i>
                                        Selecione uma data
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Etapa 3: Seleção de Horário -->
                    <div class="modal-step" id="modalStep3" style="display: none;">
                        <h6 class="mb-3">
                            <span class="badge bg-primary me-2">3</span>
                            Escolha o Horário
                        </h6>
                        <div class="row">
                            <div class="col-12">
                                <div class="horarios-grid" id="modalHorariosGrid">
                                    <!-- Horários serão carregados aqui -->
                                </div>
                                <div class="text-center mt-3" id="loadingHorarios" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <span class="ms-2">Carregando horários...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo Final -->
                    <div class="modal-step" id="modalStep4" style="display: none;">
                        <h6 class="mb-3">
                            <span class="badge bg-success me-2">4</span>
                            Confirmar Agendamento
                        </h6>
                        <div class="alert alert-light border">
                            <h6><i class="fas fa-clipboard-check me-2"></i>Resumo do Agendamento</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Salão:</strong> <span id="modalResumoSalao">-</span></p>
                                    <p class="mb-1"><strong>Profissional:</strong> <span id="modalResumoProfissional">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Data:</strong> <span id="modalResumoData">-</span></p>
                                    <p class="mb-1"><strong>Horário:</strong> <span id="modalResumoHora">-</span></p>
                                </div>
                            </div>
                            <hr>
                            <p class="mb-2">
                                <strong>Taxa da Plataforma:</strong> 
                                <span class="text-success fw-bold">R$ 1,29</span>
                            </p>
                            <div class="alert alert-warning border-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Importante:</strong> Não devolvemos o valor de R$ 1,29 caso não compareça no horário marcado.
                            </div>
                        </div>
                        
                        <!-- Área do QR Code PIX -->
                        <div id="pixPaymentArea" style="display: none;">
                            <div class="alert alert-info border-info">
                                <h6><i class="fas fa-qrcode me-2"></i>Pagamento PIX</h6>
                                <p class="mb-3">Escaneie o QR Code abaixo ou copie o código PIX para finalizar seu agendamento:</p>
                                
                                <div class="text-center mb-3">
                                    <div id="qrCodeContainer" class="d-inline-block p-3 bg-white border rounded">
                                        <!-- QR Code será gerado aqui -->
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label"><strong>Código PIX Copia e Cola:</strong></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="pixCode" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="copyPixCode">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <p class="small text-muted mb-2">Após o pagamento, seu agendamento será confirmado automaticamente.</p>
                                    <button type="button" class="btn btn-success" id="confirmPaymentBtn">
                                        <i class="fas fa-check me-2"></i>
                                        Já Paguei - Confirmar Agendamento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="modalBtnVoltar" style="display: none;">
                        <i class="fas fa-arrow-left me-2"></i>
                        Voltar
                    </button>
                    <button type="button" class="btn btn-primary" id="modalBtnProximo">
                        Próximo
                        <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="modalBtnConfirmar" style="display: none;">
                        <i class="fas fa-qrcode me-2"></i>
                        Gerar QR Code PIX
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // ===== MODAL DE AGENDAMENTO =====
        let modalCurrentStep = 1;
        let modalSelectedSalon = null;
        let modalSelectedProfessional = null;
        let modalSelectedDate = null;
        let modalSelectedTime = null;

        // Elementos do modal
        let agendamentoModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar modal
            agendamentoModal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
            
            // Inicializar botões do modal
            const modalBtnProximo = document.getElementById('modalBtnProximo');
            const modalBtnVoltar = document.getElementById('modalBtnVoltar');
            const modalBtnConfirmar = document.getElementById('modalBtnConfirmar');
            
            // Event listeners para botões do modal
            modalBtnProximo.addEventListener('click', nextModalStep);
            modalBtnVoltar.addEventListener('click', prevModalStep);
            modalBtnConfirmar.addEventListener('click', confirmarAgendamentoModal);
            
            // Event listeners para botões "Agendar Aqui"
            document.querySelectorAll('.agendar-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const salaoId = this.dataset.salaoId;
                    const salaoCard = this.closest('.salon-card');
                    
                    modalSelectedSalon = {
                        id: salaoId,
                        nome: salaoCard.querySelector('.card-title').textContent,
                        endereco: salaoCard.dataset.endereco
                    };
                    
                    resetModal();
                    carregarProfissionaisModal(salaoId);
                    agendamentoModal.show();
                });
            });
            
            // Event listeners dos botões do modal
            document.getElementById('modalBtnVoltar').addEventListener('click', function() {
                if (modalCurrentStep > 1) {
                    modalCurrentStep--;
                    mostrarEtapaModal(modalCurrentStep);
                    atualizarProgressoModal();
                }
            });
            
            document.getElementById('modalBtnProximo').addEventListener('click', function() {
                if (modalCurrentStep < 4) {
                    modalCurrentStep++;
                    mostrarEtapaModal(modalCurrentStep);
                    atualizarProgressoModal();
                    
                    if (modalCurrentStep === 2) {
                        gerarCalendario();
                    } else if (modalCurrentStep === 3 && modalSelectedDate) {
                        carregarHorariosModal();
                    } else if (modalCurrentStep === 4) {
                        atualizarResumoModal();
                    }
                }
            });
            
            document.getElementById('modalBtnConfirmar').addEventListener('click', function() {
                confirmarAgendamentoModal();
            });
        });
        
        function resetModal() {
            modalCurrentStep = 1;
            modalSelectedProfessional = null;
            modalSelectedDate = null;
            modalSelectedTime = null;
            
            mostrarEtapaModal(1);
            atualizarProgressoModal();
        }
        
        function atualizarProgressoModal() {
            const progress = (modalCurrentStep / 4) * 100;
            document.getElementById('modalProgressBar').style.width = progress + '%';
        }
        
        function mostrarEtapaModal(step) {
            // Esconder todas as etapas
            document.querySelectorAll('.modal-step').forEach(s => s.style.display = 'none');
            
            // Mostrar etapa atual
            const currentStepElement = document.getElementById(`modalStep${step}`);
            if (currentStepElement) {
                currentStepElement.style.display = 'block';
            }
            
            // Atualizar botões
            const btnVoltar = document.getElementById('modalBtnVoltar');
            const btnProximo = document.getElementById('modalBtnProximo');
            const btnConfirmar = document.getElementById('modalBtnConfirmar');
            
            if (btnVoltar) btnVoltar.style.display = step > 1 ? 'inline-block' : 'none';
            if (btnProximo) btnProximo.style.display = step < 4 ? 'inline-block' : 'none';
            if (btnConfirmar) btnConfirmar.style.display = step === 4 ? 'inline-block' : 'none';
        }
        
        function carregarProfissionaisModal(salaoId) {
            const container = document.getElementById('profissionaisGrid');
            container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
            
            fetch(`../api/profissionais.php?salao_id=${salaoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.profissionais.length > 0) {
                        container.innerHTML = '';
                        data.profissionais.forEach(prof => {
                            const card = criarCardProfissional(prof);
                            container.appendChild(card);
                        });
                    } else {
                        container.innerHTML = '<div class="alert alert-warning">Nenhum profissional disponível.</div>';
                    }
                })
                .catch(error => {
                    container.innerHTML = '<div class="alert alert-danger">Erro ao carregar profissionais.</div>';
                });
        }
        
        function criarCardProfissional(prof) {
            const card = document.createElement('div');
            card.className = 'col-md-6 mb-3';
            card.innerHTML = `
                <div class="card profissional-card h-100" data-prof-id="${prof.id}" style="cursor: pointer;">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie text-primary mb-2" style="font-size: 2rem;"></i>
                        <h6 class="card-title">${prof.nome}</h6>
                        <p class="card-text small text-muted">${prof.especialidade || 'Profissional'}</p>
                    </div>
                </div>
            `;
            
            card.addEventListener('click', function() {
                // Remover seleção anterior
                document.querySelectorAll('.profissional-card').forEach(c => c.classList.remove('selected'));
                
                // Selecionar atual
                card.querySelector('.profissional-card').classList.add('selected');
                modalSelectedProfessional = {
                    id: prof.id,
                    name: prof.nome
                };
            });
            
            return card;
        }
        
        function gerarCalendario() {
            const calendarWidget = document.getElementById('calendarWidget');
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            const monthNames = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];
            
            const weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            
            let html = `
                <div class="calendar-header">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="prevMonth">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h6 class="mb-0" id="monthYear">${monthNames[currentMonth]} ${currentYear}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="nextMonth">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-grid">
            `;
            
            // Cabeçalho dos dias da semana
            weekdays.forEach(day => {
                html += `<div class="calendar-weekday">${day}</div>`;
            });
            
            // Gerar dias do mês
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            
            // Dias vazios do início
            for (let i = 0; i < firstDay; i++) {
                html += '<div class="calendar-day disabled"></div>';
            }
            
            // Dias do mês
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth, day);
                const dateStr = date.toISOString().split('T')[0];
                const isToday = date.toDateString() === today.toDateString();
                const isPast = date < today;
                
                let classes = 'calendar-day';
                if (isPast) classes += ' disabled';
                if (isToday) classes += ' today';
                
                html += `<div class="${classes}" data-date="${dateStr}">${day}</div>`;
            }
            
            html += '</div>';
            calendarWidget.innerHTML = html;
            
            // Event listeners para os dias
            calendarWidget.addEventListener('click', function(e) {
                if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('disabled')) {
                    // Remover seleção anterior
                    calendarWidget.querySelectorAll('.calendar-day').forEach(day => {
                        day.classList.remove('selected');
                    });
                    
                    // Selecionar dia atual
                    e.target.classList.add('selected');
                    modalSelectedDate = e.target.dataset.date;
                    
                    // Atualizar display da data selecionada
                    const selectedDateDisplay = document.getElementById('selectedDateDisplay');
                    if (selectedDateDisplay) {
                        selectedDateDisplay.innerHTML = `
                            <i class="fas fa-calendar me-2"></i>
                            ${formatDateBR(modalSelectedDate)}
                        `;
                    }
                }
            });
        }

        function carregarHorariosModal() {
            console.log('carregarHorariosModal() chamada');
            const grid = document.getElementById('modalHorariosGrid');
            const loading = document.getElementById('loadingHorarios');
            
            console.log('Elementos encontrados:', { grid, loading });
            console.log('Dados selecionados:', { 
                profissional: modalSelectedProfessional, 
                salao: modalSelectedSalon, 
                data: modalSelectedDate 
            });
            
            if (!modalSelectedProfessional || !modalSelectedDate) {
                console.log('Dados incompletos - mostrando aviso');
                grid.innerHTML = '<div class="alert alert-warning">Selecione um profissional e uma data primeiro.</div>';
                return;
            }
            
            loading.style.display = 'block';
            grid.innerHTML = '';
            
            const url = `../api/horarios.php?profissional_id=${modalSelectedProfessional.id}&salao_id=${modalSelectedSalon.id}&data=${modalSelectedDate}`;
            console.log('Fazendo requisição para:', url);
            
            fetch(url)
                .then(response => {
                    console.log('Resposta recebida:', response.status, response.statusText);
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data);
                    loading.style.display = 'none';
                    
                    if (data.success && data.data && data.data.length > 0) {
                        console.log('Criando', data.data.length, 'slots de horário');
                        data.data.forEach(horario => {
                            const slot = document.createElement('div');
                            slot.className = 'horario-slot';
                            slot.textContent = horario;
                            slot.dataset.hora = horario;
                            
                            slot.addEventListener('click', function() {
                                // Remover seleção anterior
                                grid.querySelectorAll('.horario-slot').forEach(s => {
                                    s.classList.remove('selected');
                                });
                                
                                // Selecionar atual
                                slot.classList.add('selected');
                                modalSelectedTime = horario;
                                console.log('Horário selecionado:', horario);
                            });
                            
                            grid.appendChild(slot);
                        });
                        console.log('Slots criados com sucesso');
                    } else {
                        console.log('Nenhum horário disponível');
                        grid.innerHTML = '<div class="alert alert-warning">Nenhum horário disponível para esta data.</div>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar horários:', error);
                    loading.style.display = 'none';
                    grid.innerHTML = '<div class="alert alert-danger">Erro ao carregar horários.</div>';
                });
        }
        
        function atualizarResumoModal() {
            const resumoSalao = document.getElementById('modalResumoSalao');
            const resumoProfissional = document.getElementById('modalResumoProfissional');
            const resumoData = document.getElementById('modalResumoData');
            const resumoHora = document.getElementById('modalResumoHora');
            
            if (resumoSalao && modalSelectedSalon) {
                resumoSalao.textContent = modalSelectedSalon.name || '-';
            }
            
            if (resumoProfissional && modalSelectedProfessional) {
                resumoProfissional.textContent = modalSelectedProfessional.name || '-';
            }
            
            if (resumoData && modalSelectedDate) {
                resumoData.textContent = formatDateBR(modalSelectedDate);
            }
            
            if (resumoHora && modalSelectedTime) {
                resumoHora.textContent = modalSelectedTime;
            }
        }



        // Conectar botões "Agendar Aqui" ao modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.salon-select-btn')) {
                e.preventDefault();
                const salonCard = e.target.closest('.salon-card');
                const salonId = salonCard.dataset.salonId;
                const salonName = salonCard.querySelector('h5').textContent;
                
                modalSelectedSalon = {
                    id: salonId,
                    name: salonName
                };
                
                // Resetar modal
                resetModal();
                
                // Carregar profissionais
                carregarProfissionaisModal(salonId);
                
                // Abrir modal
                agendamentoModal.show();
            }
        });

        function resetModal() {
            modalCurrentStep = 1;
            modalSelectedProfessional = null;
            modalSelectedDate = null;
            modalSelectedTime = null;
            
            // Mostrar apenas primeira etapa
            document.querySelectorAll('.modal-step').forEach(step => step.style.display = 'none');
            document.getElementById('modalStep1').style.display = 'block';
            
            // Resetar botões
            modalBtnVoltar.style.display = 'none';
            modalBtnProximo.style.display = 'inline-block';
            modalBtnConfirmar.style.display = 'none';
            
            // Resetar progress bar
            updateModalProgress();
        }

        function updateModalProgress() {
            const progress = (modalCurrentStep / 4) * 100;
            modalProgressBar.style.width = progress + '%';
        }

        function nextModalStep() {
            if (modalCurrentStep === 1 && !modalSelectedProfessional) {
                alert('Por favor, selecione um profissional.');
                return;
            }
            
            if (modalCurrentStep === 2 && !modalSelectedDate) {
                alert('Por favor, selecione uma data.');
                return;
            }
            
            if (modalCurrentStep === 3 && !modalSelectedTime) {
                alert('Por favor, selecione um horário.');
                return;
            }
            
            modalCurrentStep++;
            
            if (modalCurrentStep === 2) {
                showModalStep2();
            } else if (modalCurrentStep === 3) {
                showModalStep3();
            } else if (modalCurrentStep === 4) {
                showModalStep4();
            }
            
            updateModalButtons();
            updateModalProgress();
        }

        function prevModalStep() {
            modalCurrentStep--;
            
            document.querySelectorAll('.modal-step').forEach(step => step.style.display = 'none');
            document.getElementById('modalStep' + modalCurrentStep).style.display = 'block';
            
            updateModalButtons();
            updateModalProgress();
        }

        function updateModalButtons() {
            modalBtnVoltar.style.display = modalCurrentStep > 1 ? 'inline-block' : 'none';
            modalBtnProximo.style.display = modalCurrentStep < 4 ? 'inline-block' : 'none';
            modalBtnConfirmar.style.display = modalCurrentStep === 4 ? 'inline-block' : 'none';
        }



        function showModalStep2() {
            document.querySelectorAll('.modal-step').forEach(step => step.style.display = 'none');
            document.getElementById('modalStep2').style.display = 'block';
            
            generateCalendar();
        }

        function showModalStep3() {
            document.querySelectorAll('.modal-step').forEach(step => step.style.display = 'none');
            document.getElementById('modalStep3').style.display = 'block';
            
            carregarHorariosModal();
        }

        function showModalStep4() {
            document.querySelectorAll('.modal-step').forEach(step => step.style.display = 'none');
            document.getElementById('modalStep4').style.display = 'block';
            
            // Atualizar resumo
            atualizarResumoModal();
        }

        function generateCalendar() {
            const calendarWidget = document.getElementById('calendarWidget');
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            const monthNames = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];
            
            const weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            
            let html = `
                <div class="calendar-header">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="prevMonth">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h6 class="mb-0" id="monthYear">${monthNames[currentMonth]} ${currentYear}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="nextMonth">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-grid">
            `;
            
            // Cabeçalho dos dias da semana
            weekdays.forEach(day => {
                html += `<div class="calendar-weekday">${day}</div>`;
            });
            
            // Gerar dias do mês
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            
            // Dias vazios do início
            for (let i = 0; i < firstDay; i++) {
                html += '<div class="calendar-day disabled"></div>';
            }
            
            // Dias do mês
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentYear, currentMonth, day);
                const dateStr = date.toISOString().split('T')[0];
                const isToday = date.toDateString() === today.toDateString();
                const isPast = date < today;
                
                let classes = 'calendar-day';
                if (isPast) classes += ' disabled';
                if (isToday) classes += ' today';
                
                html += `<div class="${classes}" data-date="${dateStr}">${day}</div>`;
            }
            
            html += '</div>';
            calendarWidget.innerHTML = html;
            
            // Event listeners para os dias
            calendarWidget.addEventListener('click', function(e) {
                if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('disabled')) {
                    // Remover seleção anterior
                    calendarWidget.querySelectorAll('.calendar-day').forEach(day => {
                        day.classList.remove('selected');
                    });
                    
                    // Selecionar dia atual
                    e.target.classList.add('selected');
                    modalSelectedDate = e.target.dataset.date;
                    
                    // Atualizar display da data selecionada
                    const selectedDateDisplay = document.getElementById('selectedDateDisplay');
                    if (selectedDateDisplay) {
                        selectedDateDisplay.innerHTML = `
                            <i class="fas fa-calendar me-2"></i>
                            ${formatDateBR(modalSelectedDate)}
                        `;
                    }
                }
            });
        }



        function confirmarAgendamentoModal() {
            // Esconder o botão de gerar QR Code e mostrar a área de pagamento PIX
            document.getElementById('modalBtnConfirmar').style.display = 'none';
            document.getElementById('pixPaymentArea').style.display = 'block';
            
            // Gerar QR Code PIX
            gerarQRCodePIX();
        }
        
        function gerarQRCodePIX() {
            // Dados do agendamento para o PIX
            const dadosAgendamento = {
                salao: modalSelectedSalon.name,
                profissional: modalSelectedProfessional.name,
                data: formatDateBR(modalSelectedDate),
                hora: modalSelectedTime,
                valor: 1.29
            };
            
            // Simular geração de código PIX (em produção, isso seria feito no backend)
            const pixCode = gerarCodigoPIX(dadosAgendamento);
            
            // Exibir código PIX
            const pixCodeElement = document.getElementById('pixCode');
            if (pixCodeElement) {
                pixCodeElement.value = pixCode;
            }
            
            // Gerar representação visual do QR Code
             const qrContainer = document.getElementById('qrCodeContainer');
             if (qrContainer) {
                 qrContainer.innerHTML = '';
                 
                 // Criar representação visual simples do QR Code
                 const qrDisplay = document.createElement('div');
                 qrDisplay.style.cssText = `
                     width: 200px;
                     height: 200px;
                     background: #000;
                     color: #fff;
                     display: flex;
                     align-items: center;
                     justify-content: center;
                     text-align: center;
                     font-size: 12px;
                     padding: 10px;
                     box-sizing: border-box;
                     border: 2px solid #000;
                     position: relative;
                 `;
                 
                 // Adicionar padrão visual de QR Code
                 qrDisplay.innerHTML = `
                     <div style="position: absolute; top: 5px; left: 5px; width: 30px; height: 30px; background: #fff;"></div>
                     <div style="position: absolute; top: 5px; right: 5px; width: 30px; height: 30px; background: #fff;"></div>
                     <div style="position: absolute; bottom: 5px; left: 5px; width: 30px; height: 30px; background: #fff;"></div>
                     <div style="color: #fff; font-weight: bold; z-index: 1;">QR CODE PIX<br><small>R$ 1,29</small></div>
                 `;
                 
                 qrContainer.appendChild(qrDisplay);
             }
            
            // Configurar botão de copiar
             const copyBtn = document.getElementById('copyPixCode');
             if (copyBtn) {
                 copyBtn.addEventListener('click', function() {
                     navigator.clipboard.writeText(pixCode).then(function() {
                         // Feedback visual
                         const btn = document.getElementById('copyPixCode');
                         if (btn) {
                             const originalText = btn.innerHTML;
                             btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
                             btn.classList.add('btn-success');
                             btn.classList.remove('btn-outline-secondary');
                             
                             setTimeout(function() {
                                 btn.innerHTML = originalText;
                                 btn.classList.remove('btn-success');
                                 btn.classList.add('btn-outline-secondary');
                             }, 2000);
                         }
                     }).catch(function(err) {
                         console.error('Erro ao copiar:', err);
                         alert('Erro ao copiar código PIX');
                     });
                 });
             }
             
             // Configurar botão de confirmação de pagamento
             const confirmBtn = document.getElementById('confirmPaymentBtn');
             if (confirmBtn) {
                 confirmBtn.addEventListener('click', function() {
                     finalizarAgendamento();
                 });
             }
        }
        
        function gerarCodigoPIX(dados) {
            // Em produção, isso seria feito no backend com uma API de pagamento real
            // Por enquanto, vamos simular um código PIX
            const timestamp = Date.now();
            const hash = btoa(JSON.stringify(dados) + timestamp).substring(0, 20);
            return `00020126580014BR.GOV.BCB.PIX0136${hash}@cortefacil.app5204000053039865802BR5913CorteFacil App6009SAO PAULO62070503***6304${hash.substring(0, 4).toUpperCase()}`;
        }
        
        function finalizarAgendamento() {
            // Mostrar loading
            const btn = document.getElementById('confirmPaymentBtn');
            btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Processando...';
            btn.disabled = true;
            
            // Preencher formulário principal com os dados selecionados
            document.getElementById('id_salao').value = modalSelectedSalon.id;
            document.getElementById('id_profissional').value = modalSelectedProfessional.id;
            document.getElementById('data').value = modalSelectedDate;
            document.getElementById('hora').value = modalSelectedTime;
            
            // Simular processamento do pagamento
            setTimeout(function() {
                // Fechar modal
                agendamentoModal.hide();
                
                // Submeter formulário
                document.getElementById('formAgendamento').submit();
            }, 2000);
        }

        function formatDateBR(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('pt-BR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    </script>
</body>
</html>