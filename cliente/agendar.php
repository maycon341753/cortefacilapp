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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <h1 class="h2">
                        <i class="fas fa-calendar-plus me-2 text-primary"></i>
                        Novo Agendamento
                    </h1>
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
                
                <!-- Formulário de Agendamento -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Agendar Serviço
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formAgendamento">
                                    <?php echo generateCsrfToken(); ?>
                                    
                                    <!-- Etapa 1: Escolher Salão -->
                                    <div class="step-section" id="step1">
                                        <h6 class="mb-3">
                                            <span class="badge bg-primary me-2">1</span>
                                            Escolha o Salão
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label for="id_salao" class="form-label">Salão *</label>
                                            <select class="form-select" id="id_salao" name="id_salao" required>
                                                <option value="">Selecione um salão...</option>
                                                <?php foreach ($saloes_disponiveis as $s): ?>
                                                    <option value="<?php echo $s['id']; ?>" 
                                                            <?php echo ($salao_selecionado == $s['id']) ? 'selected' : ''; ?>
                                                            data-endereco="<?php echo htmlspecialchars($s['endereco']); ?>"
                                                            data-telefone="<?php echo htmlspecialchars($s['telefone']); ?>">
                                                        <?php echo htmlspecialchars($s['nome']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Escolha o salão onde deseja ser atendido.</div>
                                        </div>
                                        
                                        <!-- Informações do Salão Selecionado -->
                                        <div id="infoSalao" class="alert alert-info" style="display: none;">
                                            <h6><i class="fas fa-info-circle me-2"></i>Informações do Salão</h6>
                                            <p class="mb-1"><strong>Endereço:</strong> <span id="salaoEndereco"></span></p>
                                            <p class="mb-0"><strong>Telefone:</strong> <span id="salaoTelefone"></span></p>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Etapa 2: Escolher Profissional -->
                                    <div class="step-section" id="step2" style="display: none;">
                                        <h6 class="mb-3">
                                            <span class="badge bg-primary me-2">2</span>
                                            Escolha o Profissional
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label for="id_profissional" class="form-label">Profissional *</label>
                                            <select class="form-select" id="id_profissional" name="id_profissional" required>
                                                <option value="">Primeiro selecione um salão...</option>
                                            </select>
                                            <div class="form-text">Escolha o profissional que irá atendê-lo.</div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Etapa 3: Escolher Data e Horário -->
                                    <div class="step-section" id="step3" style="display: none;">
                                        <h6 class="mb-3">
                                            <span class="badge bg-primary me-2">3</span>
                                            Escolha Data e Horário
                                        </h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="data" class="form-label">Data *</label>
                                                    <input type="date" class="form-control" id="data" name="data" 
                                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                                    <div class="form-text">Selecione a data desejada.</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="hora" class="form-label">Horário *</label>
                                                    <select class="form-select" id="hora" name="hora" required>
                                                        <option value="">Primeiro selecione uma data...</option>
                                                    </select>
                                                    <div class="form-text">Escolha um horário disponível.</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Horários Disponíveis -->
                                        <div id="horariosDisponiveis" class="mb-3" style="display: none;">
                                            <label class="form-label">Horários Disponíveis</label>
                                            <div id="gridHorarios" class="time-slots-grid">
                                                <!-- Horários serão carregados via JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Resumo do Agendamento -->
                                    <div class="step-section" id="resumo" style="display: none;">
                                        <h6 class="mb-3">
                                            <span class="badge bg-success me-2">4</span>
                                            Confirmar Agendamento
                                        </h6>
                                        
                                        <div class="alert alert-light border">
                                            <h6><i class="fas fa-clipboard-check me-2"></i>Resumo do Agendamento</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Salão:</strong> <span id="resumoSalao">-</span></p>
                                                    <p class="mb-1"><strong>Profissional:</strong> <span id="resumoProfissional">-</span></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Data:</strong> <span id="resumoData">-</span></p>
                                                    <p class="mb-1"><strong>Horário:</strong> <span id="resumoHora">-</span></p>
                                                </div>
                                            </div>
                                            <hr>
                                            <p class="mb-0">
                                                <strong>Taxa da Plataforma:</strong> 
                                                <span class="text-success fw-bold">R$ 1,29</span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Botões -->
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-secondary" id="btnVoltar" style="display: none;">
                                            <i class="fas fa-arrow-left me-2"></i>
                                            Voltar
                                        </button>
                                        
                                        <div class="ms-auto">
                                            <button type="button" class="btn btn-primary" id="btnProximo">
                                                Próximo
                                                <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                            
                                            <button type="submit" class="btn btn-success" id="btnConfirmar" style="display: none;">
                                                <i class="fas fa-check me-2"></i>
                                                Confirmar Agendamento
                                            </button>
                                        </div>
                                    </div>
                                </form>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Controle de etapas do formulário
        let currentStep = 1;
        const totalSteps = 4;
        
        // Elementos
        const btnProximo = document.getElementById('btnProximo');
        const btnVoltar = document.getElementById('btnVoltar');
        const btnConfirmar = document.getElementById('btnConfirmar');
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Se já tem salão selecionado, carregar profissionais
            const salaoSelect = document.getElementById('id_salao');
            if (salaoSelect.value) {
                carregarProfissionais(salaoSelect.value);
                mostrarInfoSalao();
                showStep(2);
                currentStep = 2;
            }
            
            // Event listeners
            salaoSelect.addEventListener('change', function() {
                if (this.value) {
                    carregarProfissionais(this.value);
                    mostrarInfoSalao();
                    if (currentStep === 1) {
                        showStep(2);
                        currentStep = 2;
                    }
                } else {
                    document.getElementById('id_profissional').innerHTML = '<option value="">Primeiro selecione um salão...</option>';
                    document.getElementById('infoSalao').style.display = 'none';
                    hideStepsAfter(1);
                    currentStep = 1;
                }
                updateButtons();
            });
            
            document.getElementById('id_profissional').addEventListener('change', function() {
                if (this.value && currentStep === 2) {
                    showStep(3);
                    currentStep = 3;
                } else if (!this.value) {
                    hideStepsAfter(2);
                    currentStep = 2;
                }
                updateButtons();
            });
            
            document.getElementById('data').addEventListener('change', function() {
                if (this.value) {
                    carregarHorarios();
                }
            });
            
            document.getElementById('hora').addEventListener('change', function() {
                if (this.value && currentStep === 3) {
                    atualizarResumo();
                    showStep(4);
                    currentStep = 4;
                } else if (!this.value) {
                    hideStepsAfter(3);
                    currentStep = 3;
                }
                updateButtons();
            });
            
            btnProximo.addEventListener('click', nextStep);
            btnVoltar.addEventListener('click', prevStep);
        });
        
        function showStep(step) {
            document.getElementById('step' + step).style.display = 'block';
            if (step === 4) {
                document.getElementById('resumo').style.display = 'block';
            }
        }
        
        function hideStepsAfter(step) {
            for (let i = step + 1; i <= totalSteps; i++) {
                if (i === 4) {
                    document.getElementById('resumo').style.display = 'none';
                } else {
                    document.getElementById('step' + i).style.display = 'none';
                }
            }
        }
        
        function updateButtons() {
            btnVoltar.style.display = currentStep > 1 ? 'inline-block' : 'none';
            btnProximo.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
            btnConfirmar.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
        }
        
        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                    if (currentStep === 4) {
                        atualizarResumo();
                    }
                    updateButtons();
                }
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                hideStepsAfter(currentStep);
                updateButtons();
            }
        }
        
        function validateCurrentStep() {
            switch (currentStep) {
                case 1:
                    return document.getElementById('id_salao').value !== '';
                case 2:
                    return document.getElementById('id_profissional').value !== '';
                case 3:
                    return document.getElementById('data').value !== '' && document.getElementById('hora').value !== '';
                default:
                    return true;
            }
        }
        
        function mostrarInfoSalao() {
            const select = document.getElementById('id_salao');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('salaoEndereco').textContent = option.dataset.endereco;
                document.getElementById('salaoTelefone').textContent = option.dataset.telefone;
                document.getElementById('infoSalao').style.display = 'block';
            }
        }
        
        function carregarProfissionais(idSalao) {
            const select = document.getElementById('id_profissional');
            select.innerHTML = '<option value="">Carregando...</option>';
            
            CorteFacil.ajax.get(`../api/profissionais.php?salao=${idSalao}`)
                .then(data => {
                    select.innerHTML = '<option value="">Selecione um profissional...</option>';
                    data.forEach(prof => {
                        const option = document.createElement('option');
                        option.value = prof.id;
                        option.textContent = `${prof.nome} - ${prof.especialidade}`;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar profissionais:', error);
                    select.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
                });
        }
        
        function carregarHorarios() {
            const idProfissional = document.getElementById('id_profissional').value;
            const data = document.getElementById('data').value;
            
            if (!idProfissional || !data) return;
            
            const select = document.getElementById('hora');
            select.innerHTML = '<option value="">Carregando...</option>';
            
            CorteFacil.ajax.get(`../api/horarios.php?profissional=${idProfissional}&data=${data}`)
                .then(horarios => {
                    select.innerHTML = '<option value="">Selecione um horário...</option>';
                    horarios.forEach(hora => {
                        const option = document.createElement('option');
                        option.value = hora;
                        option.textContent = hora;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar horários:', error);
                    select.innerHTML = '<option value="">Erro ao carregar horários</option>';
                });
        }
        
        function atualizarResumo() {
            const salaoSelect = document.getElementById('id_salao');
            const profissionalSelect = document.getElementById('id_profissional');
            const data = document.getElementById('data').value;
            const hora = document.getElementById('hora').value;
            
            document.getElementById('resumoSalao').textContent = salaoSelect.options[salaoSelect.selectedIndex].text;
            document.getElementById('resumoProfissional').textContent = profissionalSelect.options[profissionalSelect.selectedIndex].text;
            document.getElementById('resumoData').textContent = CorteFacil.formatDate(data);
            document.getElementById('resumoHora').textContent = hora;
        }
    </script>
</body>
</html>