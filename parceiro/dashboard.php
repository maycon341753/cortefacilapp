<?php
/**
 * Dashboard do Parceiro (Dono do Salão)
 * Painel principal com estatísticas e informações do salão
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/salao.php';
require_once '../models/profissional.php';
require_once '../models/agendamento.php';

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();
$profissional = new Profissional();
$agendamento = new Agendamento();

// Buscar salão do parceiro
$meu_salao = $salao->buscarPorDono($usuario['id']);

// Se não tem salão, redirecionar para cadastro
if (!$meu_salao) {
    header('Location: salao.php');
    exit;
}

// Buscar profissionais do salão
$profissionais = $profissional->listarPorSalao($meu_salao['id']);

// Buscar agendamentos do salão
$agendamentos = $agendamento->listarPorSalao($meu_salao['id']);

// Estatísticas
$total_profissionais = count($profissionais);
$profissionais_ativos = count(array_filter($profissionais, function($p) {
    return $p['status'] === 'ativo';
}));

$total_agendamentos = count($agendamentos);
$agendamentos_hoje = count(array_filter($agendamentos, function($a) {
    return $a['data'] === date('Y-m-d');
}));
$agendamentos_pendentes = count(array_filter($agendamentos, function($a) {
    return $a['status'] === 'pendente';
}));
$agendamentos_confirmados = count(array_filter($agendamentos, function($a) {
    return $a['status'] === 'confirmado';
}));

// Receita total (taxa da plataforma)
$receita_total = array_sum(array_column($agendamentos, 'valor_taxa'));
$receita_mes = array_sum(array_column(array_filter($agendamentos, function($a) {
    return date('Y-m', strtotime($a['data'])) === date('Y-m');
}), 'valor_taxa'));

// Próximos agendamentos (hoje e amanhã)
$proximos_agendamentos = array_filter($agendamentos, function($a) {
    $data_agendamento = $a['data'];
    $hoje = date('Y-m-d');
    $amanha = date('Y-m-d', strtotime('+1 day'));
    return ($data_agendamento === $hoje || $data_agendamento === $amanha) && 
           in_array($a['status'], ['pendente', 'confirmado']);
});

// Ordenar por data e hora
usort($proximos_agendamentos, function($a, $b) {
    $datetime_a = strtotime($a['data'] . ' ' . $a['hora']);
    $datetime_b = strtotime($b['data'] . ' ' . $b['hora']);
    return $datetime_a - $datetime_b;
});

// Limitar a 5 próximos
$proximos_agendamentos = array_slice($proximos_agendamentos, 0, 5);

// Agendamentos recentes (últimos 5)
$agendamentos_recentes = array_slice(array_reverse($agendamentos), 0, 5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CorteFácil Parceiro</title>
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
                        <small class="text-white-50">Parceiro</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agenda.php">
                                <i class="fas fa-calendar-alt"></i>
                                Agenda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profissionais.php">
                                <i class="fas fa-users"></i>
                                Profissionais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendamentos.php">
                                <i class="fas fa-list"></i>
                                Agendamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="salao.php">
                                <i class="fas fa-store"></i>
                                Meu Salão
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
                    <div>
                        <h1 class="h2">
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                            Dashboard
                        </h1>
                        <p class="text-muted mb-0">
                            Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!
                            <br>
                            <small>
                                <i class="fas fa-store me-1"></i>
                                <?php echo htmlspecialchars($meu_salao['nome']); ?>
                            </small>
                        </p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="agenda.php" class="btn btn-primary">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Ver Agenda
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                            <div class="number"><?php echo $profissionais_ativos; ?>/<?php echo $total_profissionais; ?></div>
                            <div class="label">Profissionais Ativos</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-calendar-check text-success"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_hoje; ?></div>
                            <div class="label">Agendamentos Hoje</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_pendentes; ?></div>
                            <div class="label">Pendentes</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-dollar-sign text-info"></i>
                            </div>
                            <div class="number">R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?></div>
                            <div class="label">Receita do Mês</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Próximos Agendamentos -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Próximos Agendamentos
                                </h5>
                                <a href="agenda.php" class="btn btn-sm btn-outline-primary">
                                    Ver Agenda Completa
                                </a>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($proximos_agendamentos)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($proximos_agendamentos as $ag): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <strong class="me-2"><?php echo htmlspecialchars($ag['nome_cliente']); ?></strong>
                                                            <?php echo gerarBadgeStatus($ag['status']); ?>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-user me-1"></i>
                                                            <?php echo htmlspecialchars($ag['nome_profissional']); ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold">
                                                            <?php echo formatarData($ag['data']); ?>
                                                        </div>
                                                        <div class="text-muted small">
                                                            <?php echo formatarHora($ag['hora']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Nenhum agendamento próximo</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resumo do Salão -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-store me-2"></i>
                                    Resumo do Salão
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-store fa-2x"></i>
                                    </div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($meu_salao['nome']); ?></h6>
                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($meu_salao['endereco']); ?></p>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Total de Agendamentos:</span>
                                        <span class="fw-bold"><?php echo $total_agendamentos; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Confirmados:</span>
                                        <span class="fw-bold text-success"><?php echo $agendamentos_confirmados; ?></span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Receita Total:</span>
                                        <span class="fw-bold text-success">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <a href="salao.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit me-2"></i>
                                        Editar Salão
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ações Rápidas -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Ações Rápidas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="profissionais.php?action=add" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Adicionar Profissional
                                    </a>
                                    
                                    <a href="agenda.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Ver Agenda
                                    </a>
                                    
                                    <a href="relatorios.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Relatórios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Agendamentos Recentes -->
                <?php if (!empty($agendamentos_recentes)): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i>
                                        Agendamentos Recentes
                                    </h5>
                                    <a href="agendamentos.php" class="btn btn-sm btn-outline-primary">
                                        Ver Todos
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Profissional</th>
                                                    <th>Data/Hora</th>
                                                    <th>Status</th>
                                                    <th>Taxa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($agendamentos_recentes as $ag): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($ag['nome_cliente']); ?></td>
                                                        <td><?php echo htmlspecialchars($ag['nome_profissional']); ?></td>
                                                        <td>
                                                            <div><?php echo formatarData($ag['data']); ?></div>
                                                            <small class="text-muted"><?php echo formatarHora($ag['hora']); ?></small>
                                                        </td>
                                                        <td><?php echo gerarBadgeStatus($ag['status']); ?></td>
                                                        <td class="text-success fw-bold">
                                                            R$ <?php echo number_format($ag['valor_taxa'], 2, ',', '.'); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <style>
        .avatar-lg {
            width: 60px;
            height: 60px;
        }
    </style>
</body>
</html>