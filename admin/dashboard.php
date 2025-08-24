<?php
/**
 * Dashboard do Administrador - Versão Corrigida para Erro 500
 * Painel principal com estatísticas gerais da plataforma
 */

// Configurações de erro para produção
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Função para iniciar sessão de forma segura
function iniciarSessaoSegura() {
    if (session_status() === PHP_SESSION_NONE) {
        try {
            session_start();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao iniciar sessão: " . $e->getMessage());
            return false;
        }
    }
    return true;
}

// Função para incluir arquivo com verificação
function incluirArquivoSeguro($arquivo) {
    $caminho = dirname(__DIR__) . '/' . $arquivo;
    if (file_exists($caminho)) {
        try {
            require_once $caminho;
            return true;
        } catch (Exception $e) {
            error_log("Erro ao incluir $arquivo: " . $e->getMessage());
            return false;
        }
    }
    error_log("Arquivo não encontrado: $caminho");
    return false;
}

// Função para redirecionar para página de erro
function redirecionarParaErro() {
    if (file_exists(dirname(__DIR__) . '/erro_500_amigavel.php')) {
        header('Location: ../erro_500_amigavel.php');
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo '<h1>Erro Interno do Servidor</h1><p>Tente novamente mais tarde.</p>';
    }
    exit;
}

// Iniciar sessão
if (!iniciarSessaoSegura()) {
    redirecionarParaErro();
}

// Incluir arquivos críticos
$arquivos_criticos = [
    'includes/auth.php',
    'includes/functions.php',
    'models/usuario.php',
    'models/salao.php',
    'models/agendamento.php',
    'models/profissional.php'
];

foreach ($arquivos_criticos as $arquivo) {
    if (!incluirArquivoSeguro($arquivo)) {
        redirecionarParaErro();
    }
}

// Verificações de segurança
try {
    // Verificar se função existe antes de usar
    if (!function_exists('requireAdmin')) {
        throw new Exception('Função requireAdmin não encontrada');
    }
    requireAdmin();
    
    if (!function_exists('getLoggedUser')) {
        throw new Exception('Função getLoggedUser não encontrada');
    }
    $usuario = getLoggedUser();
    
    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }
    
} catch (Exception $e) {
    error_log("Erro de autenticação no dashboard admin: " . $e->getMessage());
    redirecionarParaErro();
}

// Inicializar objetos com tratamento de erro
try {
    $usuarioModel = new Usuario();
    $salao = new Salao();
    $agendamento = new Agendamento();
    $profissional = new Profissional();
} catch (Exception $e) {
    error_log("Erro ao inicializar objetos: " . $e->getMessage());
    redirecionarParaErro();
}

// Buscar estatísticas gerais com proteção
try {
    $total_clientes = 0;
    $total_parceiros = 0;
    $total_saloes = 0;
    $total_profissionais = 0;
    $total_agendamentos = 0;
    
    if (method_exists($usuarioModel, 'contarPorTipo')) {
        $total_clientes = $usuarioModel->contarPorTipo('cliente') ?: 0;
        $total_parceiros = $usuarioModel->contarPorTipo('parceiro') ?: 0;
    }
    
    if (method_exists($salao, 'contar')) {
        $total_saloes = $salao->contar() ?: 0;
    }
    
    if (method_exists($profissional, 'contar')) {
        $total_profissionais = $profissional->contar() ?: 0;
    }
    
    if (method_exists($agendamento, 'contar')) {
        $total_agendamentos = $agendamento->contar() ?: 0;
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas gerais: " . $e->getMessage());
    $total_clientes = 0;
    $total_parceiros = 0;
    $total_saloes = 0;
    $total_profissionais = 0;
    $total_agendamentos = 0;
}

// Estatísticas de agendamentos com proteção
try {
    $agendamentos_hoje = 0;
    $agendamentos_mes = 0;
    $receita_mes = 0;
    $receita_total = 0;
    
    if (method_exists($agendamento, 'contarPorData')) {
        $agendamentos_hoje = $agendamento->contarPorData(date('Y-m-d')) ?: 0;
    }
    
    if (method_exists($agendamento, 'contarPorPeriodo')) {
        $agendamentos_mes = $agendamento->contarPorPeriodo(date('Y-m-01'), date('Y-m-t')) ?: 0;
    }
    
    if (method_exists($agendamento, 'calcularReceitaPorPeriodo')) {
        $receita_mes = $agendamento->calcularReceitaPorPeriodo(date('Y-m-01'), date('Y-m-t')) ?: 0;
    }
    
    if (method_exists($agendamento, 'calcularReceitaTotal')) {
        $receita_total = $agendamento->calcularReceitaTotal() ?: 0;
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas de agendamentos: " . $e->getMessage());
    $agendamentos_hoje = 0;
    $agendamentos_mes = 0;
    $receita_mes = 0;
    $receita_total = 0;
}

// Agendamentos por status com proteção
try {
    $agendamentos_pendentes = 0;
    $agendamentos_confirmados = 0;
    $agendamentos_concluidos = 0;
    $agendamentos_cancelados = 0;
    
    if (method_exists($agendamento, 'contarPorStatus')) {
        $agendamentos_pendentes = $agendamento->contarPorStatus('pendente') ?: 0;
        $agendamentos_confirmados = $agendamento->contarPorStatus('confirmado') ?: 0;
        $agendamentos_concluidos = $agendamento->contarPorStatus('concluido') ?: 0;
        $agendamentos_cancelados = $agendamento->contarPorStatus('cancelado') ?: 0;
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar agendamentos por status: " . $e->getMessage());
    $agendamentos_pendentes = 0;
    $agendamentos_confirmados = 0;
    $agendamentos_concluidos = 0;
    $agendamentos_cancelados = 0;
}

// Últimos cadastros com proteção
try {
    $ultimos_clientes = [];
    $ultimos_parceiros = [];
    $ultimos_saloes = [];
    
    if (method_exists($usuarioModel, 'buscarUltimosPorTipo')) {
        $ultimos_clientes = $usuarioModel->buscarUltimosPorTipo('cliente', 5) ?: [];
        $ultimos_parceiros = $usuarioModel->buscarUltimosPorTipo('parceiro', 5) ?: [];
    }
    
    if (method_exists($salao, 'buscarUltimos')) {
        $ultimos_saloes = $salao->buscarUltimos(5) ?: [];
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar últimos cadastros: " . $e->getMessage());
    $ultimos_clientes = [];
    $ultimos_parceiros = [];
    $ultimos_saloes = [];
}

// Agendamentos recentes com proteção
try {
    $agendamentos_recentes = [];
    
    if (method_exists($agendamento, 'buscarRecentes')) {
        $agendamentos_recentes = $agendamento->buscarRecentes(10) ?: [];
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar agendamentos recentes: " . $e->getMessage());
    $agendamentos_recentes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cut me-2"></i>CorteFácil Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">
                            <i class="fas fa-users me-1"></i>Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="saloes.php">
                            <i class="fas fa-store me-1"></i>Salões
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="agendamentos.php">
                            <i class="fas fa-calendar-check me-1"></i>Agendamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios.php">
                            <i class="fas fa-chart-bar me-1"></i>Relatórios
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($usuario['nome'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Administrativo
                    <small class="text-muted">Visão geral da plataforma</small>
                </h1>
            </div>
        </div>

        <!-- Estatísticas Principais -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo number_format($total_clientes); ?></h4>
                                <p class="mb-0">Total de Clientes</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo number_format($total_parceiros); ?></h4>
                                <p class="mb-0">Total de Parceiros</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-handshake fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo number_format($total_saloes); ?></h4>
                                <p class="mb-0">Total de Salões</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-store fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo number_format($total_agendamentos); ?></h4>
                                <p class="mb-0">Total de Agendamentos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas de Agendamentos -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Hoje</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $agendamentos_hoje; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-success">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Este Mês</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $agendamentos_mes; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-info">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Receita Mês</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Receita Total</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-coins fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Status dos Agendamentos -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Status dos Agendamentos</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Agendamentos Recentes -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Agendamentos Recentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($agendamentos_recentes)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Salão</th>
                                            <th>Data</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($agendamentos_recentes, 0, 5) as $ag): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ag['cliente_nome'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($ag['salao_nome'] ?? 'N/A'); ?></td>
                                            <td><?php echo isset($ag['data']) ? date('d/m/Y', strtotime($ag['data'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php 
                                                $status = $ag['status'] ?? 'indefinido';
                                                $badge_class = [
                                                    'pendente' => 'warning',
                                                    'confirmado' => 'success',
                                                    'cancelado' => 'danger',
                                                    'concluido' => 'info'
                                                ][$status] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum agendamento recente</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Cadastros -->
        <div class="row mt-4">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Últimos Clientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ultimos_clientes)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ultimos_clientes as $cliente): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($cliente['nome'] ?? 'N/A'); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($cliente['email'] ?? 'N/A'); ?></small>
                                    </div>
                                    <small><?php echo isset($cliente['data_cadastro']) ? date('d/m', strtotime($cliente['data_cadastro'])) : 'N/A'; ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Nenhum cliente cadastrado recentemente</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Últimos Parceiros</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ultimos_parceiros)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ultimos_parceiros as $parceiro): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($parceiro['nome'] ?? 'N/A'); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($parceiro['email'] ?? 'N/A'); ?></small>
                                    </div>
                                    <small><?php echo isset($parceiro['data_cadastro']) ? date('d/m', strtotime($parceiro['data_cadastro'])) : 'N/A'; ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Nenhum parceiro cadastrado recentemente</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-store me-2"></i>Últimos Salões</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ultimos_saloes)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ultimos_saloes as $salao_item): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($salao_item['nome'] ?? 'N/A'); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($salao_item['cidade'] ?? 'N/A'); ?></small>
                                    </div>
                                    <small><?php echo isset($salao_item['data_cadastro']) ? date('d/m', strtotime($salao_item['data_cadastro'])) : 'N/A'; ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Nenhum salão cadastrado recentemente</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de Status dos Agendamentos
        const ctx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendentes', 'Confirmados', 'Concluídos', 'Cancelados'],
                datasets: [{
                    data: [
                        <?php echo $agendamentos_pendentes; ?>,
                        <?php echo $agendamentos_confirmados; ?>,
                        <?php echo $agendamentos_concluidos; ?>,
                        <?php echo $agendamentos_cancelados; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#28a745',
                        '#17a2b8',
                        '#dc3545'
                    ],
                    borderWidth: 2
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
    </script>
</body>
</html>