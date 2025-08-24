<?php
/**
 * Relatórios - Administrador
 * Exibe relatórios e estatísticas da plataforma
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';
require_once '../models/usuario.php';
require_once '../models/salao.php';
require_once '../models/profissional.php';

// Verificar se é administrador
requireAdmin();

$usuario_logado = getLoggedUser();
$agendamento = new Agendamento();
$usuario = new Usuario();
$salao = new Salao();
$profissional = new Profissional();

// Período selecionado
$periodo = $_GET['periodo'] ?? 'mes_atual';

// Definir datas baseado no período
switch ($periodo) {
    case 'hoje':
        $data_inicio = date('Y-m-d');
        $data_fim = date('Y-m-d');
        $titulo_periodo = 'Hoje';
        break;
    case 'semana_atual':
        $data_inicio = date('Y-m-d', strtotime('monday this week'));
        $data_fim = date('Y-m-d', strtotime('sunday this week'));
        $titulo_periodo = 'Esta Semana';
        break;
    case 'mes_atual':
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-t');
        $titulo_periodo = 'Este Mês';
        break;
    case 'mes_passado':
        $data_inicio = date('Y-m-01', strtotime('first day of last month'));
        $data_fim = date('Y-m-t', strtotime('last day of last month'));
        $titulo_periodo = 'Mês Passado';
        break;
    case 'ano_atual':
        $data_inicio = date('Y-01-01');
        $data_fim = date('Y-12-31');
        $titulo_periodo = 'Este Ano';
        break;
    case 'personalizado':
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-t');
        $titulo_periodo = 'Período Personalizado';
        break;
    default:
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-t');
        $titulo_periodo = 'Este Mês';
}

// Estatísticas gerais
$total_usuarios = $usuario->contar();
$total_clientes = $usuario->contarPorTipo('cliente');
$total_parceiros = $usuario->contarPorTipo('parceiro');
$total_saloes = $salao->contar();
$total_profissionais = $profissional->contar();
$total_agendamentos = $agendamento->contar();

// Estatísticas do período
$agendamentos_periodo = $agendamento->contarPorPeriodo($data_inicio, $data_fim);
$receita_periodo = $agendamento->calcularReceitaPorPeriodo($data_inicio, $data_fim);
$novos_usuarios_periodo = $usuario->contarNovosPorPeriodo($data_inicio, $data_fim);
$novos_saloes_periodo = $salao->contarNovosPorPeriodo($data_inicio, $data_fim);

// Agendamentos por status no período
$agendamentos_por_status = $agendamento->contarPorStatusPeriodo($data_inicio, $data_fim);

// Taxa de conversão (confirmados / total)
$taxa_conversao = $agendamentos_periodo > 0 ? 
    round(($agendamentos_por_status['confirmado'] ?? 0) / $agendamentos_periodo * 100, 1) : 0;

// Agendamentos por dia da semana
$agendamentos_por_dia = $agendamento->contarPorDiaSemana($data_inicio, $data_fim);

// Top 10 salões por agendamentos
$top_saloes = $agendamento->topSaloesPorAgendamentos($data_inicio, $data_fim, 10);

// Top 10 profissionais por agendamentos
$top_profissionais = $agendamento->topProfissionaisPorAgendamentos($data_inicio, $data_fim, 10);

// Evolução mensal dos últimos 12 meses
$evolucao_mensal = [];
for ($i = 11; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $inicio_mes = $mes . '-01';
    $fim_mes = date('Y-m-t', strtotime($inicio_mes));
    
    $evolucao_mensal[] = [
        'mes' => date('M/Y', strtotime($inicio_mes)),
        'agendamentos' => $agendamento->contarPorPeriodo($inicio_mes, $fim_mes),
        'receita' => $agendamento->calcularReceitaPorPeriodo($inicio_mes, $fim_mes)
    ];
}

// Horários mais populares
$horarios_populares = $agendamento->horariosPopulares($data_inicio, $data_fim);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - CorteFácil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .sidebar, .btn, .no-print { display: none !important; }
            .col-md-9 { width: 100% !important; }
            .card { break-inside: avoid; }
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
                        <small class="text-white-50">Administrador</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="saloes.php">
                                <i class="fas fa-store"></i>
                                Salões
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendamentos.php">
                                <i class="fas fa-calendar-alt"></i>
                                Agendamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="relatorios.php">
                                <i class="fas fa-chart-bar"></i>
                                Relatórios
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
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Relatórios - <?php echo $titulo_periodo; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0 no-print">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="exportarPDF()">
                                <i class="fas fa-file-pdf me-2"></i>
                                Exportar PDF
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Filtro de Período -->
                <div class="card mb-4 no-print">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            Período
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select" name="periodo" id="periodo" onchange="toggleCustomDates()">
                                    <option value="hoje" <?php echo $periodo === 'hoje' ? 'selected' : ''; ?>>Hoje</option>
                                    <option value="semana_atual" <?php echo $periodo === 'semana_atual' ? 'selected' : ''; ?>>Esta Semana</option>
                                    <option value="mes_atual" <?php echo $periodo === 'mes_atual' ? 'selected' : ''; ?>>Este Mês</option>
                                    <option value="mes_passado" <?php echo $periodo === 'mes_passado' ? 'selected' : ''; ?>>Mês Passado</option>
                                    <option value="ano_atual" <?php echo $periodo === 'ano_atual' ? 'selected' : ''; ?>>Este Ano</option>
                                    <option value="personalizado" <?php echo $periodo === 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                                </select>
                            </div>
                            <div class="col-md-2" id="data_inicio_div" style="display: <?php echo $periodo === 'personalizado' ? 'block' : 'none'; ?>">
                                <input type="date" class="form-control" name="data_inicio" value="<?php echo $data_inicio; ?>">
                            </div>
                            <div class="col-md-2" id="data_fim_div" style="display: <?php echo $periodo === 'personalizado' ? 'block' : 'none'; ?>">
                                <input type="date" class="form-control" name="data_fim" value="<?php echo $data_fim; ?>">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Estatísticas Gerais -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="mb-3">
                            <i class="fas fa-chart-pie me-2"></i>
                            Estatísticas Gerais da Plataforma
                        </h5>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_usuarios, 0, ',', '.'); ?></h4>
                                <small>Total Usuários</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_clientes, 0, ',', '.'); ?></h4>
                                <small>Clientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-handshake fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_parceiros, 0, ',', '.'); ?></h4>
                                <small>Parceiros</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-store fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_saloes, 0, ',', '.'); ?></h4>
                                <small>Salões</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-cut fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_profissionais, 0, ',', '.'); ?></h4>
                                <small>Profissionais</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_agendamentos, 0, ',', '.'); ?></h4>
                                <small>Agendamentos</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas do Período -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="mb-3">
                            <i class="fas fa-chart-line me-2"></i>
                            Estatísticas do Período (<?php echo formatarData($data_inicio); ?> a <?php echo formatarData($data_fim); ?>)
                        </h5>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                <h4><?php echo number_format($agendamentos_periodo, 0, ',', '.'); ?></h4>
                                <small>Agendamentos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                <h4>R$ <?php echo number_format($receita_periodo, 2, ',', '.'); ?></h4>
                                <small>Receita</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <h4><?php echo number_format($novos_usuarios_periodo, 0, ',', '.'); ?></h4>
                                <small>Novos Usuários</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage fa-2x mb-2"></i>
                                <h4><?php echo $taxa_conversao; ?>%</h4>
                                <small>Taxa Conversão</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
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
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Agendamentos por Dia da Semana
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDiaSemana" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Evolução Mensal -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Evolução dos Últimos 12 Meses
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartEvolucao" width="400" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rankings -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-trophy me-2"></i>
                                    Top 10 Salões
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_saloes)): ?>
                                    <p class="text-muted">Nenhum dado disponível para o período.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Salão</th>
                                                    <th>Agendamentos</th>
                                                    <th>Receita</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_saloes as $index => $sal): ?>
                                                    <tr>
                                                        <td><strong><?php echo $index + 1; ?>º</strong></td>
                                                        <td><?php echo htmlspecialchars($sal['nome']); ?></td>
                                                        <td><?php echo number_format($sal['total_agendamentos'], 0, ',', '.'); ?></td>
                                                        <td>R$ <?php echo number_format($sal['receita'], 2, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-star me-2"></i>
                                    Top 10 Profissionais
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($top_profissionais)): ?>
                                    <p class="text-muted">Nenhum dado disponível para o período.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Profissional</th>
                                                    <th>Salão</th>
                                                    <th>Agendamentos</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($top_profissionais as $index => $prof): ?>
                                                    <tr>
                                                        <td><strong><?php echo $index + 1; ?>º</strong></td>
                                                        <td><?php echo htmlspecialchars($prof['nome']); ?></td>
                                                        <td><?php echo htmlspecialchars($prof['salao_nome']); ?></td>
                                                        <td><?php echo number_format($prof['total_agendamentos'], 0, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Horários Populares -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Horários Mais Populares
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($horarios_populares)): ?>
                                    <p class="text-muted">Nenhum dado disponível para o período.</p>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($horarios_populares as $horario): ?>
                                            <div class="col-md-2 mb-3">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center">
                                                        <h5 class="card-title"><?php echo formatarHora($horario['hora']); ?></h5>
                                                        <p class="card-text">
                                                            <strong><?php echo $horario['total']; ?></strong> agendamentos
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Resumo Final -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Resumo do Relatório
                                </h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Período:</strong> <?php echo formatarData($data_inicio); ?> a <?php echo formatarData($data_fim); ?></p>
                                <p><strong>Total de Agendamentos:</strong> <?php echo number_format($agendamentos_periodo, 0, ',', '.'); ?></p>
                                <p><strong>Receita Total:</strong> R$ <?php echo number_format($receita_periodo, 2, ',', '.'); ?></p>
                                <p><strong>Taxa de Conversão:</strong> <?php echo $taxa_conversao; ?>%</p>
                                <p><strong>Novos Usuários:</strong> <?php echo number_format($novos_usuarios_periodo, 0, ',', '.'); ?></p>
                                <p><strong>Novos Salões:</strong> <?php echo number_format($novos_saloes_periodo, 0, ',', '.'); ?></p>
                                <hr>
                                <small class="text-muted">
                                    Relatório gerado em <?php echo date('d/m/Y H:i:s'); ?> por <?php echo htmlspecialchars($usuario_logado['nome']); ?>
                                </small>
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
        function toggleCustomDates() {
            const periodo = document.getElementById('periodo').value;
            const dataInicioDiv = document.getElementById('data_inicio_div');
            const dataFimDiv = document.getElementById('data_fim_div');
            
            if (periodo === 'personalizado') {
                dataInicioDiv.style.display = 'block';
                dataFimDiv.style.display = 'block';
            } else {
                dataInicioDiv.style.display = 'none';
                dataFimDiv.style.display = 'none';
            }
        }
        
        function exportarPDF() {
            alert('Funcionalidade de exportação PDF será implementada em breve.');
        }
        
        // Gráfico de Status
        const ctxStatus = document.getElementById('chartStatus').getContext('2d');
        new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: ['Pendente', 'Confirmado', 'Concluído', 'Cancelado'],
                datasets: [{
                    data: [
                        <?php echo $agendamentos_por_status['pendente'] ?? 0; ?>,
                        <?php echo $agendamentos_por_status['confirmado'] ?? 0; ?>,
                        <?php echo $agendamentos_por_status['concluido'] ?? 0; ?>,
                        <?php echo $agendamentos_por_status['cancelado'] ?? 0; ?>
                    ],
                    backgroundColor: ['#ffc107', '#28a745', '#17a2b8', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Gráfico de Dia da Semana
        const ctxDiaSemana = document.getElementById('chartDiaSemana').getContext('2d');
        new Chart(ctxDiaSemana, {
            type: 'bar',
            data: {
                labels: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                datasets: [{
                    label: 'Agendamentos',
                    data: [
                        <?php 
                        $dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
                        foreach ($dias_semana as $dia) {
                            echo ($agendamentos_por_dia[$dia] ?? 0) . ',';
                        }
                        ?>
                    ],
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gráfico de Evolução
        const ctxEvolucao = document.getElementById('chartEvolucao').getContext('2d');
        new Chart(ctxEvolucao, {
            type: 'line',
            data: {
                labels: [<?php foreach ($evolucao_mensal as $mes) echo "'" . $mes['mes'] . "',"; ?>],
                datasets: [{
                    label: 'Agendamentos',
                    data: [<?php foreach ($evolucao_mensal as $mes) echo $mes['agendamentos'] . ','; ?>],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Receita (R$)',
                    data: [<?php foreach ($evolucao_mensal as $mes) echo $mes['receita'] . ','; ?>],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>