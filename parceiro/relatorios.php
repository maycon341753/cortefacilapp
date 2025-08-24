<?php
/**
 * Página de Relatórios do Parceiro
 * Exibe estatísticas e relatórios do salão
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
        error_log('Relatorios: Conexão online forçada com sucesso');
    }
} catch (Exception $e) {
    error_log('Relatorios: Erro ao forçar conexão online: ' . $e->getMessage());
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

// Parâmetros de período
$periodo = $_GET['periodo'] ?? 'mes_atual';
$data_inicio = '';
$data_fim = '';

switch ($periodo) {
    case 'hoje':
        $data_inicio = $data_fim = date('Y-m-d');
        break;
    case 'semana_atual':
        $data_inicio = date('Y-m-d', strtotime('monday this week'));
        $data_fim = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'mes_atual':
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-t');
        break;
    case 'mes_passado':
        $data_inicio = date('Y-m-01', strtotime('first day of last month'));
        $data_fim = date('Y-m-t', strtotime('last day of last month'));
        break;
    case 'ano_atual':
        $data_inicio = date('Y-01-01');
        $data_fim = date('Y-12-31');
        break;
    case 'personalizado':
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-t');
        break;
}

// Buscar dados para relatórios
$filtros = [
    'data_inicio' => $data_inicio,
    'data_fim' => $data_fim
];

$agendamentos = $agendamento->listarPorSalao($meu_salao['id'], $filtros);
$profissionais = $profissional->listarPorSalao($meu_salao['id']);

// Calcular estatísticas
$total_agendamentos = count($agendamentos);
$agendamentos_confirmados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'confirmado'));
$agendamentos_concluidos = count(array_filter($agendamentos, fn($a) => $a['status'] === 'concluido'));
$agendamentos_cancelados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'cancelado'));

// Receita total (taxa de agendamentos)
$receita_total = array_sum(array_map(fn($a) => floatval($a['valor_taxa']), $agendamentos));
$receita_confirmada = array_sum(array_map(fn($a) => floatval($a['valor_taxa']), 
    array_filter($agendamentos, fn($a) => in_array($a['status'], ['confirmado', 'concluido']))));

// Agendamentos por profissional
$agendamentos_por_profissional = [];
foreach ($agendamentos as $ag) {
    $prof_id = $ag['id_profissional'];
    if (!isset($agendamentos_por_profissional[$prof_id])) {
        $agendamentos_por_profissional[$prof_id] = [
            'nome' => $ag['profissional_nome'],
            'total' => 0,
            'confirmados' => 0,
            'concluidos' => 0,
            'cancelados' => 0
        ];
    }
    
    $agendamentos_por_profissional[$prof_id]['total']++;
    
    switch ($ag['status']) {
        case 'confirmado':
            $agendamentos_por_profissional[$prof_id]['confirmados']++;
            break;
        case 'concluido':
            $agendamentos_por_profissional[$prof_id]['concluidos']++;
            break;
        case 'cancelado':
            $agendamentos_por_profissional[$prof_id]['cancelados']++;
            break;
    }
}

// Agendamentos por dia da semana
$agendamentos_por_dia = [
    'Segunda' => 0, 'Terça' => 0, 'Quarta' => 0, 'Quinta' => 0, 
    'Sexta' => 0, 'Sábado' => 0, 'Domingo' => 0
];

foreach ($agendamentos as $ag) {
    $dia_semana = date('w', strtotime($ag['data']));
    $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    $agendamentos_por_dia[$dias[$dia_semana]]++;
}

// Horários mais procurados
$horarios_populares = [];
foreach ($agendamentos as $ag) {
    $hora = substr($ag['hora'], 0, 5);
    if (!isset($horarios_populares[$hora])) {
        $horarios_populares[$hora] = 0;
    }
    $horarios_populares[$hora]++;
}
arsort($horarios_populares);
$horarios_populares = array_slice($horarios_populares, 0, 5, true);

// Taxa de conversão
$taxa_conversao = $total_agendamentos > 0 ? 
    round(($agendamentos_confirmados + $agendamentos_concluidos) / $total_agendamentos * 100, 1) : 0;

// Período para exibição
$periodo_texto = [
    'hoje' => 'Hoje',
    'semana_atual' => 'Esta Semana',
    'mes_atual' => 'Este Mês',
    'mes_passado' => 'Mês Passado',
    'ano_atual' => 'Este Ano',
    'personalizado' => 'Período Personalizado'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - CorteFácil Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Relatórios
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
                
                <!-- Filtro de Período -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            Período: <?php echo $periodo_texto[$periodo] ?? 'Personalizado'; ?>
                            <?php if ($data_inicio && $data_fim): ?>
                                (<?php echo formatarData($data_inicio); ?> a <?php echo formatarData($data_fim); ?>)
                            <?php endif; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="periodo" class="form-label">Período</label>
                                <select class="form-select" id="periodo" name="periodo" onchange="toggleCustomDates()">
                                    <option value="hoje" <?php echo $periodo === 'hoje' ? 'selected' : ''; ?>>Hoje</option>
                                    <option value="semana_atual" <?php echo $periodo === 'semana_atual' ? 'selected' : ''; ?>>Esta Semana</option>
                                    <option value="mes_atual" <?php echo $periodo === 'mes_atual' ? 'selected' : ''; ?>>Este Mês</option>
                                    <option value="mes_passado" <?php echo $periodo === 'mes_passado' ? 'selected' : ''; ?>>Mês Passado</option>
                                    <option value="ano_atual" <?php echo $periodo === 'ano_atual' ? 'selected' : ''; ?>>Este Ano</option>
                                    <option value="personalizado" <?php echo $periodo === 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                                </select>
                            </div>
                            <div class="col-md-3" id="dataInicio" style="display: <?php echo $periodo === 'personalizado' ? 'block' : 'none'; ?>">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo htmlspecialchars($data_inicio); ?>">
                            </div>
                            <div class="col-md-3" id="dataFim" style="display: <?php echo $periodo === 'personalizado' ? 'block' : 'none'; ?>">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?php echo htmlspecialchars($data_fim); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>
                                        Gerar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Estatísticas Principais -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total de Agendamentos</h6>
                                        <h2 class="mb-0"><?php echo $total_agendamentos; ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Taxa de Conversão</h6>
                                        <h2 class="mb-0"><?php echo $taxa_conversao; ?>%</h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-percentage fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Receita Total</h6>
                                        <h2 class="mb-0">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Receita Confirmada</h6>
                                        <h2 class="mb-0">R$ <?php echo number_format($receita_confirmada, 2, ',', '.'); ?></h2>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
                    <!-- Status dos Agendamentos -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Status dos Agendamentos
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartStatus" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Agendamentos por Dia da Semana -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Agendamentos por Dia da Semana
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDias" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabelas de Dados -->
                <div class="row mb-4">
                    <!-- Performance por Profissional -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-users me-2"></i>
                                    Performance por Profissional
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($agendamentos_por_profissional)): ?>
                                    <p class="text-muted text-center py-3">Nenhum dado disponível para o período selecionado.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Profissional</th>
                                                    <th>Total</th>
                                                    <th>Confirmados</th>
                                                    <th>Concluídos</th>
                                                    <th>Cancelados</th>
                                                    <th>Taxa de Sucesso</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($agendamentos_por_profissional as $prof): ?>
                                                    <?php 
                                                    $taxa_sucesso = $prof['total'] > 0 ? 
                                                        round(($prof['confirmados'] + $prof['concluidos']) / $prof['total'] * 100, 1) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($prof['nome']); ?></strong></td>
                                                        <td><?php echo $prof['total']; ?></td>
                                                        <td><span class="badge bg-success"><?php echo $prof['confirmados']; ?></span></td>
                                                        <td><span class="badge bg-info"><?php echo $prof['concluidos']; ?></span></td>
                                                        <td><span class="badge bg-danger"><?php echo $prof['cancelados']; ?></span></td>
                                                        <td>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                     style="width: <?php echo $taxa_sucesso; ?>%" 
                                                                     aria-valuenow="<?php echo $taxa_sucesso; ?>" 
                                                                     aria-valuemin="0" aria-valuemax="100">
                                                                    <?php echo $taxa_sucesso; ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Horários Mais Procurados -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Horários Mais Procurados
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($horarios_populares)): ?>
                                    <p class="text-muted text-center py-3">Nenhum dado disponível.</p>
                                <?php else: ?>
                                    <?php foreach ($horarios_populares as $hora => $quantidade): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><strong><?php echo $hora; ?></strong></span>
                                            <div class="d-flex align-items-center">
                                                <div class="progress me-2" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" 
                                                         style="width: <?php echo ($quantidade / max($horarios_populares)) * 100; ?>%"></div>
                                                </div>
                                                <span class="badge bg-primary"><?php echo $quantidade; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Função para mostrar/ocultar campos de data personalizada
        function toggleCustomDates() {
            const periodo = document.getElementById('periodo').value;
            const dataInicio = document.getElementById('dataInicio');
            const dataFim = document.getElementById('dataFim');
            
            if (periodo === 'personalizado') {
                dataInicio.style.display = 'block';
                dataFim.style.display = 'block';
            } else {
                dataInicio.style.display = 'none';
                dataFim.style.display = 'none';
            }
        }
        
        // Gráfico de Status dos Agendamentos
        const ctxStatus = document.getElementById('chartStatus').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Confirmados', 'Concluídos', 'Cancelados', 'Pendentes'],
                datasets: [{
                    data: [
                        <?php echo $agendamentos_confirmados; ?>,
                        <?php echo $agendamentos_concluidos; ?>,
                        <?php echo $agendamentos_cancelados; ?>,
                        <?php echo $total_agendamentos - $agendamentos_confirmados - $agendamentos_concluidos - $agendamentos_cancelados; ?>
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#dc3545',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Gráfico de Agendamentos por Dia da Semana
        const ctxDias = document.getElementById('chartDias').getContext('2d');
        new Chart(ctxDias, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($agendamentos_por_dia)); ?>,
                datasets: [{
                    label: 'Agendamentos',
                    data: <?php echo json_encode(array_values($agendamentos_por_dia)); ?>,
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    
    <style>
        @media print {
            .sidebar, .btn-toolbar {
                display: none !important;
            }
            
            .col-md-9 {
                width: 100% !important;
                margin: 0 !important;
            }
            
            .card {
                break-inside: avoid;
                margin-bottom: 20px;
            }
        }
    </style>
</body>
</html>