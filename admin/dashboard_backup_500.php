<?php
/**
 * Dashboard do Administrador
 * Painel principal com estatísticas gerais da plataforma
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/usuario.php';
require_once '../models/salao.php';
require_once '../models/agendamento.php';
require_once '../models/profissional.php';

// Verificar se é administrador
requireAdmin();

$usuario = getLoggedUser();
$usuarioModel = new Usuario();
$salao = new Salao();
$agendamento = new Agendamento();
$profissional = new Profissional();

// Buscar estatísticas gerais
$total_clientes = $usuarioModel->contarPorTipo('cliente');
$total_parceiros = $usuarioModel->contarPorTipo('parceiro');
$total_saloes = $salao->contar();
$total_profissionais = $profissional->contar();
$total_agendamentos = $agendamento->contar();

// Estatísticas de agendamentos
$agendamentos_hoje = $agendamento->contarPorData(date('Y-m-d'));
$agendamentos_mes = $agendamento->contarPorPeriodo(date('Y-m-01'), date('Y-m-t'));
$receita_mes = $agendamento->calcularReceitaPorPeriodo(date('Y-m-01'), date('Y-m-t'));
$receita_total = $agendamento->calcularReceitaTotal();

// Agendamentos por status
$agendamentos_pendentes = $agendamento->contarPorStatus('pendente');
$agendamentos_confirmados = $agendamento->contarPorStatus('confirmado');
$agendamentos_concluidos = $agendamento->contarPorStatus('concluido');
$agendamentos_cancelados = $agendamento->contarPorStatus('cancelado');

// Últimos cadastros
$ultimos_clientes = $usuarioModel->buscarUltimosPorTipo('cliente', 5);
$ultimos_parceiros = $usuarioModel->buscarUltimosPorTipo('parceiro', 5);
$ultimos_saloes = $salao->buscarUltimos(5);

// Agendamentos recentes
$agendamentos_recentes = $agendamento->buscarRecentes(10);

// Dados para gráficos
$dados_mes = [];
for ($i = 11; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $inicio_mes = $mes . '-01';
    $fim_mes = date('Y-m-t', strtotime($inicio_mes));
    
    $dados_mes[] = [
        'mes' => date('M/Y', strtotime($inicio_mes)),
        'agendamentos' => $agendamento->contarPorPeriodo($inicio_mes, $fim_mes),
        'receita' => $agendamento->calcularReceitaPorPeriodo($inicio_mes, $fim_mes)
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CorteFácil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <a class="nav-link active" href="dashboard.php">
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
                            <a class="nav-link" href="relatorios.php">
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
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Dashboard Administrativo
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-circle me-1"></i>
                                Sistema Online
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Mensagens -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Boas-vindas -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-gradient-primary text-white">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="mb-1">Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</h4>
                                        <p class="mb-0">Aqui está um resumo da sua plataforma hoje, <?php echo formatarData(date('Y-m-d')); ?>.</p>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-crown fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas Principais -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total de Clientes</h6>
                                        <h2 class="mb-0"><?php echo number_format($total_clientes, 0, ',', '.'); ?></h2>
                                        <small class="opacity-75">Usuários ativos</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Parceiros</h6>
                                        <h2 class="mb-0"><?php echo number_format($total_parceiros, 0, ',', '.'); ?></h2>
                                        <small class="opacity-75"><?php echo number_format($total_saloes, 0, ',', '.'); ?> salões</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-store fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Agendamentos</h6>
                                        <h2 class="mb-0"><?php echo number_format($total_agendamentos, 0, ',', '.'); ?></h2>
                                        <small class="opacity-75"><?php echo number_format($agendamentos_hoje, 0, ',', '.'); ?> hoje</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Receita Total</h6>
                                        <h2 class="mb-0">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></h2>
                                        <small class="opacity-75">R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?> este mês</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status dos Agendamentos -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h4 class="text-warning"><?php echo number_format($agendamentos_pendentes, 0, ',', '.'); ?></h4>
                                <p class="mb-0">Pendentes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="text-success"><?php echo number_format($agendamentos_confirmados, 0, ',', '.'); ?></h4>
                                <p class="mb-0">Confirmados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-check-double fa-2x text-info mb-2"></i>
                                <h4 class="text-info"><?php echo number_format($agendamentos_concluidos, 0, ',', '.'); ?></h4>
                                <p class="mb-0">Concluídos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h4 class="text-danger"><?php echo number_format($agendamentos_cancelados, 0, ',', '.'); ?></h4>
                                <p class="mb-0">Cancelados</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Evolução dos Agendamentos (12 meses)
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartAgendamentos" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Status dos Agendamentos
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartStatus" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Últimas Atividades -->
                <div class="row mb-4">
                    <!-- Agendamentos Recentes -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    Agendamentos Recentes
                                </h6>
                                <a href="agendamentos.php" class="btn btn-sm btn-outline-primary">
                                    Ver todos
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($agendamentos_recentes)): ?>
                                    <p class="text-muted text-center py-3">Nenhum agendamento encontrado.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Cliente</th>
                                                    <th>Salão</th>
                                                    <th>Data/Hora</th>
                                                    <th>Status</th>
                                                    <th>Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($agendamentos_recentes as $ag): ?>
                                                    <tr>
                                                        <td><strong>#<?php echo $ag['id']; ?></strong></td>
                                                        <td><?php echo htmlspecialchars($ag['cliente_nome']); ?></td>
                                                        <td><?php echo htmlspecialchars($ag['salao_nome']); ?></td>
                                                        <td>
                                                            <small>
                                                                <?php echo formatarData($ag['data']); ?><br>
                                                                <?php echo formatarHora($ag['hora']); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $badges = [
                                                                'pendente' => 'warning',
                                                                'confirmado' => 'success',
                                                                'concluido' => 'info',
                                                                'cancelado' => 'danger'
                                                            ];
                                                            $badge = $badges[$ag['status']] ?? 'secondary';
                                                            ?>
                                                            <span class="badge bg-<?php echo $badge; ?>">
                                                                <?php echo ucfirst($ag['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>R$ <?php echo number_format($ag['valor_taxa'], 2, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Novos Cadastros -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Novos Cadastros
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Últimos Clientes -->
                                <h6 class="text-muted mb-2">Clientes</h6>
                                <?php if (empty($ultimos_clientes)): ?>
                                    <p class="text-muted small">Nenhum cliente recente.</p>
                                <?php else: ?>
                                    <?php foreach ($ultimos_clientes as $cliente): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user fa-sm"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small"><?php echo htmlspecialchars($cliente['nome']); ?></div>
                                                <div class="text-muted small"><?php echo formatarDataHora($cliente['data_cadastro']); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <!-- Últimos Parceiros -->
                                <h6 class="text-muted mb-2">Parceiros</h6>
                                <?php if (empty($ultimos_parceiros)): ?>
                                    <p class="text-muted small">Nenhum parceiro recente.</p>
                                <?php else: ?>
                                    <?php foreach ($ultimos_parceiros as $parceiro): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-store fa-sm"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small"><?php echo htmlspecialchars($parceiro['nome']); ?></div>
                                                <div class="text-muted small"><?php echo formatarDataHora($parceiro['data_cadastro']); ?></div>
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
        // Gráfico de Evolução dos Agendamentos
        const ctxAgendamentos = document.getElementById('chartAgendamentos').getContext('2d');
        new Chart(ctxAgendamentos, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dados_mes, 'mes')); ?>,
                datasets: [{
                    label: 'Agendamentos',
                    data: <?php echo json_encode(array_column($dados_mes, 'agendamentos')); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
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
                        <?php echo $agendamentos_pendentes; ?>
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
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        
        // Auto-refresh da página a cada 5 minutos
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>