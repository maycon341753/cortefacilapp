<?php
/**
 * Página de Agendamento do Cliente
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once '../includes/auth.php';
$auth->requireAuth('cliente');

$user = $auth->getCurrentUser();

// Buscar salões disponíveis
try {
    $sql = "SELECT s.*, u.nome as dono_nome 
            FROM saloes s 
            JOIN usuarios u ON s.id_dono = u.id 
            WHERE s.ativo = 1 
            ORDER BY s.nome";
    $stmt = $database->query($sql);
    $saloes = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Erro ao carregar salões: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Agendamento - CorteFácil</title>
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
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card booking-system">
                    <div class="card-header">
                        <h2 class="card-title">Fazer Novo Agendamento</h2>
                        <p>Escolha o salão, profissional e horário desejado</p>
                    </div>
                    
                    <form id="form_agendamento" data-validate>
                        <!-- Etapa 1: Escolher Salão -->
                        <div class="booking-step" id="step-1">
                            <h3>1. Escolha o Salão</h3>
                            
                            <?php if (empty($saloes)): ?>
                                <div class="alert alert-warning">
                                    Nenhum salão disponível no momento.
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label for="salao" class="form-label">Salão *</label>
                                    <select id="salao" name="salao" class="form-control form-select" required>
                                        <option value="">Selecione um salão</option>
                                        <?php foreach ($saloes as $salao): ?>
                                            <option value="<?php echo $salao['id']; ?>" 
                                                    data-endereco="<?php echo htmlspecialchars($salao['endereco']); ?>"
                                                    data-telefone="<?php echo htmlspecialchars($salao['telefone']); ?>">
                                                <?php echo htmlspecialchars($salao['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div id="salao-info" class="salao-info" style="display: none;">
                                    <div class="card">
                                        <h4 id="salao-nome"></h4>
                                        <p><strong>Endereço:</strong> <span id="salao-endereco"></span></p>
                                        <p><strong>Telefone:</strong> <span id="salao-telefone"></span></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Etapa 2: Escolher Profissional -->
                        <div class="booking-step" id="step-2" style="display: none;">
                            <h3>2. Escolha o Profissional</h3>
                            
                            <div class="form-group">
                                <label for="profissional" class="form-label">Profissional *</label>
                                <select id="profissional" name="profissional" class="form-control form-select" required>
                                    <option value="">Selecione um profissional</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Etapa 3: Escolher Data -->
                        <div class="booking-step" id="step-3" style="display: none;">
                            <h3>3. Escolha a Data</h3>
                            
                            <div class="form-group">
                                <label for="data" class="form-label">Data do Agendamento *</label>
                                <input 
                                    type="date" 
                                    id="data" 
                                    name="data" 
                                    class="form-control" 
                                    required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                                >
                            </div>
                        </div>
                        
                        <!-- Etapa 4: Escolher Horário -->
                        <div class="booking-step" id="step-4" style="display: none;">
                            <h3>4. Escolha o Horário</h3>
                            
                            <div id="horarios" class="time-slots">
                                <!-- Horários serão carregados via JavaScript -->
                            </div>
                            
                            <input type="hidden" id="horario_selecionado" name="horario" required>
                        </div>
                        
                        <!-- Etapa 5: Detalhes do Serviço -->
                        <div class="booking-step" id="step-5" style="display: none;">
                            <h3>5. Detalhes do Serviço</h3>
                            
                            <div class="form-group">
                                <label for="servico" class="form-label">Tipo de Serviço</label>
                                <input 
                                    type="text" 
                                    id="servico" 
                                    name="servico" 
                                    class="form-control"
                                    placeholder="Ex: Corte, Escova, Manicure..."
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="observacoes" class="form-label">Observações</label>
                                <textarea 
                                    id="observacoes" 
                                    name="observacoes" 
                                    class="form-control" 
                                    rows="3"
                                    placeholder="Alguma observação especial para o profissional..."
                                ></textarea>
                            </div>
                        </div>
                        
                        <!-- Resumo e Confirmação -->
                        <div class="booking-step" id="step-6" style="display: none;">
                            <h3>6. Confirmar Agendamento</h3>
                            
                            <div class="booking-summary">
                                <div class="card">
                                    <h4>Resumo do Agendamento</h4>
                                    <div class="summary-item">
                                        <strong>Salão:</strong> <span id="summary-salao"></span>
                                    </div>
                                    <div class="summary-item">
                                        <strong>Profissional:</strong> <span id="summary-profissional"></span>
                                    </div>
                                    <div class="summary-item">
                                        <strong>Data:</strong> <span id="summary-data"></span>
                                    </div>
                                    <div class="summary-item">
                                        <strong>Horário:</strong> <span id="summary-horario"></span>
                                    </div>
                                    <div class="summary-item">
                                        <strong>Serviço:</strong> <span id="summary-servico"></span>
                                    </div>
                                    <div class="summary-item">
                                        <strong>Taxa de Agendamento:</strong> <span class="price">R$ 1,29</span>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <strong>Importante:</strong> Você pagará R$ 1,29 para confirmar este agendamento. 
                                    O valor do serviço será pago diretamente no salão.
                                </div>
                            </div>
                            
                            <button type="button" id="confirmar_agendamento" class="btn btn-success w-100" disabled>
                                Confirmar e Ir para Pagamento
                            </button>
                        </div>
                        
                        <!-- Navegação entre etapas -->
                        <div class="booking-navigation mt-4">
                            <button type="button" id="btn-anterior" class="btn btn-secondary" style="display: none;">
                                Anterior
                            </button>
                            <button type="button" id="btn-proximo" class="btn btn-primary" style="display: none;">
                                Próximo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Sidebar com informações -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Como Funciona</h3>
                    </div>
                    
                    <div class="step-info">
                        <div class="step-item">
                            <span class="step-number">1</span>
                            <div>
                                <h5>Escolha o Salão</h5>
                                <p>Selecione o salão de sua preferência</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <span class="step-number">2</span>
                            <div>
                                <h5>Selecione o Profissional</h5>
                                <p>Escolha o profissional especializado</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <span class="step-number">3</span>
                            <div>
                                <h5>Defina Data e Horário</h5>
                                <p>Escolha quando quer ser atendido</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <span class="step-number">4</span>
                            <div>
                                <h5>Pague a Taxa</h5>
                                <p>Confirme pagando apenas R$ 1,29</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Dicas Importantes</h3>
                    </div>
                    
                    <ul>
                        <li>Chegue 10 minutos antes do horário</li>
                        <li>O pagamento do serviço é feito no salão</li>
                        <li>Você pode cancelar até 2 horas antes</li>
                        <li>Em caso de dúvidas, entre em contato com o salão</li>
                    </ul>
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
    
    <script>
        let currentStep = 1;
        const totalSteps = 6;
        
        document.addEventListener('DOMContentLoaded', function() {
            initBookingFlow();
        });
        
        function initBookingFlow() {
            // Event listeners para navegação
            document.getElementById('btn-proximo').addEventListener('click', nextStep);
            document.getElementById('btn-anterior').addEventListener('click', prevStep);
            
            // Event listener para seleção de salão
            document.getElementById('salao').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (this.value) {
                    showSalaoInfo(selectedOption);
                    loadProfissionais(this.value);
                    showStep(2);
                } else {
                    hideSalaoInfo();
                    hideStepsFrom(2);
                }
            });
            
            // Event listener para seleção de profissional
            document.getElementById('profissional').addEventListener('change', function() {
                if (this.value) {
                    showStep(3);
                } else {
                    hideStepsFrom(3);
                }
            });
            
            // Event listener para seleção de data
            document.getElementById('data').addEventListener('change', function() {
                if (this.value) {
                    loadHorariosDisponiveis();
                    showStep(4);
                } else {
                    hideStepsFrom(4);
                }
            });
            
            // Event listener para confirmar agendamento
            document.getElementById('confirmar_agendamento').addEventListener('click', confirmarAgendamento);
        }
        
        function showSalaoInfo(option) {
            const info = document.getElementById('salao-info');
            document.getElementById('salao-nome').textContent = option.text;
            document.getElementById('salao-endereco').textContent = option.dataset.endereco;
            document.getElementById('salao-telefone').textContent = option.dataset.telefone;
            info.style.display = 'block';
        }
        
        function hideSalaoInfo() {
            document.getElementById('salao-info').style.display = 'none';
        }
        
        function showStep(stepNumber) {
            document.getElementById(`step-${stepNumber}`).style.display = 'block';
            updateNavigation();
        }
        
        function hideStepsFrom(stepNumber) {
            for (let i = stepNumber; i <= totalSteps; i++) {
                document.getElementById(`step-${i}`).style.display = 'none';
            }
            updateNavigation();
        }
        
        function updateNavigation() {
            // Lógica para mostrar/esconder botões de navegação
            const btnAnterior = document.getElementById('btn-anterior');
            const btnProximo = document.getElementById('btn-proximo');
            
            // Por enquanto, esconde os botões pois o fluxo é automático
            btnAnterior.style.display = 'none';
            btnProximo.style.display = 'none';
        }
        
        function selectHorario(button, hora) {
            // Remove seleção anterior
            document.querySelectorAll('.time-slot.selected').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Adiciona seleção atual
            button.classList.add('selected');
            
            // Define valor no input hidden
            document.getElementById('horario_selecionado').value = hora;
            
            // Mostra próxima etapa
            showStep(5);
            
            // Atualiza resumo quando chegar na etapa 6
            setTimeout(() => {
                showStep(6);
                updateSummary();
            }, 500);
        }
        
        function updateSummary() {
            const salaoSelect = document.getElementById('salao');
            const profissionalSelect = document.getElementById('profissional');
            const data = document.getElementById('data').value;
            const horario = document.getElementById('horario_selecionado').value;
            const servico = document.getElementById('servico').value || 'Não especificado';
            
            document.getElementById('summary-salao').textContent = salaoSelect.options[salaoSelect.selectedIndex].text;
            document.getElementById('summary-profissional').textContent = profissionalSelect.options[profissionalSelect.selectedIndex].text;
            document.getElementById('summary-data').textContent = formatDate(data);
            document.getElementById('summary-horario').textContent = horario;
            document.getElementById('summary-servico').textContent = servico;
            
            // Habilita botão de confirmar
            document.getElementById('confirmar_agendamento').disabled = false;
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR');
        }
    </script>
    
    <style>
        .booking-step {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .booking-step:last-child {
            border-bottom: none;
        }
        
        .booking-step h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .salao-info {
            margin-top: 1rem;
        }
        
        .salao-info .card {
            background-color: #f8f9fa;
            border: 1px solid #e1e5e9;
        }
        
        .booking-summary .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .booking-summary .summary-item:last-child {
            border-bottom: none;
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        .price {
            color: #28a745;
            font-weight: bold;
        }
        
        .step-info .step-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .step-info .step-number {
            width: 30px;
            height: 30px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .step-info h5 {
            margin-bottom: 0.25rem;
            color: #333;
        }
        
        .step-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</body>
</html>