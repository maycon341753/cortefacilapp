<?php
/**
 * Página de Agenda do Parceiro
 * Visualização da agenda completa do salão com todos os agendamentos
 */

// ===== FORÇAR CONEXÃO ONLINE PARA PRODUÇÃO =====
try {
    // Verificar se estamos em produção e forçar conexão online
    $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($serverName, 'cortefacil.app') !== false || file_exists(__DIR__ . '/../.env.online')) {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance();
        $db->forceOnlineConfig();
        $conn = $db->connect();
        if (!$conn) {
            throw new Exception('Falha na conexão online forçada');
        }
        error_log('Agenda: Conexão online forçada com sucesso');
    }
} catch (Exception $e) {
    error_log('Agenda: Erro ao forçar conexão online: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/salao.php';
require_once __DIR__ . '/../models/agendamento.php';
require_once __DIR__ . '/../models/profissional.php';

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();
$agendamento = new Agendamento();
$profissional = new Profissional();

// Verificar se tem salão cadastrado
$meu_salao = $salao->buscarPorDono($usuario['id']);
if (!$meu_salao) {
    header('Location: salao.php');
    exit;
}

// Parâmetros de filtro
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d');
$data_fim = $_GET['data_fim'] ?? date('Y-m-d', strtotime('+7 days'));
$profissional_id = $_GET['profissional_id'] ?? '';
$status = $_GET['status'] ?? '';

// Buscar profissionais do salão
$profissionais = $profissional->listarPorSalao($meu_salao['id']);

// Buscar agendamentos
$filtros = [
    'data_inicio' => $data_inicio,
    'data_fim' => $data_fim
];

if ($profissional_id) {
    $filtros['profissional_id'] = $profissional_id;
}

if ($status) {
    $filtros['status'] = $status;
}

$agendamentos = $agendamento->listarPorSalao($meu_salao['id'], $filtros);

// Organizar agendamentos por data e hora
$agenda = [];
foreach ($agendamentos as $ag) {
    $data = $ag['data'];
    $hora = $ag['hora'];
    
    if (!isset($agenda[$data])) {
        $agenda[$data] = [];
    }
    
    if (!isset($agenda[$data][$hora])) {
        $agenda[$data][$hora] = [];
    }
    
    $agenda[$data][$hora][] = $ag;
}

// Ordenar por data
ksort($agenda);

// Estatísticas
$total_agendamentos = count($agendamentos);
$agendamentos_hoje = count(array_filter($agendamentos, fn($a) => $a['data'] === date('Y-m-d')));
$agendamentos_pendentes = count(array_filter($agendamentos, fn($a) => $a['status'] === 'pendente'));
$agendamentos_confirmados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'confirmado'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - CorteFácil Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../includes/parceiro_navigation.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Agenda do Salão
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total</h6>
                                        <h3 class="mb-0"><?php echo $total_agendamentos; ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Hoje</h6>
                                        <h3 class="mb-0"><?php echo $agendamentos_hoje; ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Pendentes</h6>
                                        <h3 class="mb-0"><?php echo $agendamentos_pendentes; ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Confirmados</h6>
                                        <h3 class="mb-0"><?php echo $agendamentos_confirmados; ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Filtros
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo htmlspecialchars($data_inicio); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?php echo htmlspecialchars($data_fim); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="profissional_id" class="form-label">Profissional</label>
                                <select class="form-select" id="profissional_id" name="profissional_id">
                                    <option value="">Todos os profissionais</option>
                                    <?php foreach ($profissionais as $prof): ?>
                                        <option value="<?php echo $prof['id']; ?>" 
                                                <?php echo $profissional_id == $prof['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prof['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos os status</option>
                                    <option value="pendente" <?php echo $status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="confirmado" <?php echo $status === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    <option value="concluido" <?php echo $status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>
                                    Filtrar
                                </button>
                                <a href="agenda.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Limpar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Agenda -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-week me-2"></i>
                            Agenda - <?php echo formatarData($data_inicio); ?> a <?php echo formatarData($data_fim); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($agenda)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum agendamento encontrado</h5>
                                <p class="text-muted">Não há agendamentos para o período selecionado.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($agenda as $data => $horarios): ?>
                                <div class="agenda-day mb-4">
                                    <div class="day-header bg-light p-3 rounded-top">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calendar-day me-2 text-primary"></i>
                                            <?php 
                                                $data_obj = new DateTime($data);
                                                echo $data_obj->format('d/m/Y') . ' - ' . 
                                                     ucfirst(strftime('%A', $data_obj->getTimestamp()));
                                            ?>
                                            <span class="badge bg-primary ms-2">
                                                <?php echo count(array_merge(...array_values($horarios))); ?> agendamentos
                                            </span>
                                        </h5>
                                    </div>
                                    
                                    <div class="day-content border border-top-0 rounded-bottom p-3">
                                        <?php 
                                        // Ordenar horários
                                        ksort($horarios);
                                        foreach ($horarios as $hora => $agendamentos_hora): 
                                        ?>
                                            <div class="time-slot mb-3">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="time-label bg-primary text-white text-center py-2 rounded">
                                                            <strong><?php echo substr($hora, 0, 5); ?></strong>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="row">
                                                            <?php foreach ($agendamentos_hora as $ag): ?>
                                                                <div class="col-md-6 mb-2">
                                                                    <div class="appointment-card border rounded p-3 h-100 
                                                                         <?php 
                                                                         switch($ag['status']) {
                                                                             case 'confirmado': echo 'border-success bg-light-success'; break;
                                                                             case 'pendente': echo 'border-warning bg-light-warning'; break;
                                                                             case 'cancelado': echo 'border-danger bg-light-danger'; break;
                                                                             case 'concluido': echo 'border-info bg-light-info'; break;
                                                                             default: echo 'border-secondary';
                                                                         }
                                                                         ?>">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="mb-0">
                                                                                <i class="fas fa-user me-2"></i>
                                                                                <?php echo htmlspecialchars($ag['cliente_nome']); ?>
                                                                            </h6>
                                                                            <span class="badge 
                                                                                <?php 
                                                                                switch($ag['status']) {
                                                                                    case 'confirmado': echo 'bg-success'; break;
                                                                                    case 'pendente': echo 'bg-warning'; break;
                                                                                    case 'cancelado': echo 'bg-danger'; break;
                                                                                    case 'concluido': echo 'bg-info'; break;
                                                                                    default: echo 'bg-secondary';
                                                                                }
                                                                                ?>">
                                                                                <?php echo ucfirst($ag['status']); ?>
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <div class="mb-2">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-cut me-1"></i>
                                                                                <?php echo htmlspecialchars($ag['profissional_nome']); ?>
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="mb-2">
                                                                            <small class="text-muted">
                                                                                <i class="fas fa-phone me-1"></i>
                                                                                <?php echo htmlspecialchars($ag['cliente_telefone'] ?? 'Não informado'); ?>
                                                                            </small>
                                                                        </div>
                                                                        
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <small class="text-muted">
                                                                                #<?php echo str_pad($ag['id'], 6, '0', STR_PAD_LEFT); ?>
                                                                            </small>
                                                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                                    onclick="verDetalhes(<?php echo htmlspecialchars(json_encode($ag)); ?>)">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalhes do Agendamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhesContent">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Função para ver detalhes do agendamento
        function verDetalhes(agendamento) {
            const statusClass = {
                'confirmado': 'success',
                'pendente': 'warning',
                'cancelado': 'danger',
                'concluido': 'info'
            };
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informações do Cliente</h6>
                        <p><strong>Nome:</strong> ${agendamento.cliente_nome}</p>
                        <p><strong>Email:</strong> ${agendamento.cliente_email || 'Não informado'}</p>
                        <p><strong>Telefone:</strong> ${agendamento.cliente_telefone || 'Não informado'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Informações do Agendamento</h6>
                        <p><strong>ID:</strong> #${String(agendamento.id).padStart(6, '0')}</p>
                        <p><strong>Data:</strong> ${new Date(agendamento.data + 'T00:00:00').toLocaleDateString('pt-BR')}</p>
                        <p><strong>Horário:</strong> ${agendamento.hora.substring(0, 5)}</p>
                        <p><strong>Profissional:</strong> ${agendamento.profissional_nome}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${statusClass[agendamento.status] || 'secondary'}">${agendamento.status.charAt(0).toUpperCase() + agendamento.status.slice(1)}</span></p>
                        <p><strong>Taxa:</strong> R$ ${parseFloat(agendamento.valor_taxa || 0).toFixed(2).replace('.', ',')}</p>
                    </div>
                </div>
                ${agendamento.observacoes ? `
                <hr>
                <h6>Observações</h6>
                <p>${agendamento.observacoes}</p>
                ` : ''}
            `;
            
            document.getElementById('detalhesContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('modalDetalhes')).show();
        }
        
        // Atualizar automaticamente a cada 5 minutos
        setInterval(function() {
            if (!document.hidden) {
                location.reload();
            }
        }, 300000); // 5 minutos
        
        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            // Ctrl + P para imprimir
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            
            // F5 para atualizar
            if (e.key === 'F5') {
                location.reload();
            }
        });
    </script>
    
    <style>
        .bg-light-success { background-color: #d1e7dd !important; }
        .bg-light-warning { background-color: #fff3cd !important; }
        .bg-light-danger { background-color: #f8d7da !important; }
        .bg-light-info { background-color: #d1ecf1 !important; }
        
        .appointment-card {
            transition: all 0.3s ease;
        }
        
        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .time-label {
            font-size: 14px;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media print {
            .sidebar, .btn-toolbar, .card-header .btn {
                display: none !important;
            }
            
            .col-md-9 {
                width: 100% !important;
                margin: 0 !important;
            }
            
            .appointment-card {
                break-inside: avoid;
            }
        }
    </style>
</body>
</html>