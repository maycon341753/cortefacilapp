<?php
/**
 * Dashboard do Cliente
 * Painel principal para clientes agendarem serviços
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';
require_once '../models/salao.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$agendamento = new Agendamento();
$salao = new Salao();

$erro = '';
$sucesso = '';

// Buscar dados para o dashboard com proteção
try {
    $agendamentos_recentes = [];
    $saloes_disponiveis = [];
    
    if (method_exists($agendamento, 'listarPorCliente')) {
        $agendamentos_recentes = $agendamento->listarPorCliente($usuario['id']) ?: [];
    }
    
    if (method_exists($salao, 'listarAtivos')) {
        $saloes_disponiveis = $salao->listarAtivos() ?: [];
    }
    
} catch (Exception $e) {
    error_log("Erro ao buscar dados do dashboard: " . $e->getMessage());
    $agendamentos_recentes = [];
    $saloes_disponiveis = [];
}

// Calcular estatísticas com proteção
try {
    $total_agendamentos = is_array($agendamentos_recentes) ? count($agendamentos_recentes) : 0;
    
    $agendamentos_pendentes = 0;
    $agendamentos_confirmados = 0;
    
    if (is_array($agendamentos_recentes)) {
        foreach ($agendamentos_recentes as $a) {
            if (isset($a['status'])) {
                if ($a['status'] === 'pendente') $agendamentos_pendentes++;
                if ($a['status'] === 'confirmado') $agendamentos_confirmados++;
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Erro ao calcular estatísticas: " . $e->getMessage());
    $total_agendamentos = 0;
    $agendamentos_pendentes = 0;
    $agendamentos_confirmados = 0;
}

// Buscar próximo agendamento com proteção
$proximo_agendamento = null;
try {
    if (is_array($agendamentos_recentes)) {
        foreach ($agendamentos_recentes as $ag) {
            if (isset($ag['status'], $ag['data'], $ag['hora']) &&
                ($ag['status'] === 'confirmado' || $ag['status'] === 'pendente') && 
                strtotime($ag['data'] . ' ' . $ag['hora']) > time()) {
                $proximo_agendamento = $ag;
                break;
            }
        }
    }
} catch (Exception $e) {
    error_log("Erro ao buscar próximo agendamento: " . $e->getMessage());
    $proximo_agendamento = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cut me-2"></i>CorteFácil
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
                        <a class="nav-link" href="agendar.php">
                            <i class="fas fa-calendar-plus me-1"></i>Agendar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="agendamentos.php">
                            <i class="fas fa-calendar-check me-1"></i>Meus Agendamentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="saloes.php">
                            <i class="fas fa-store me-1"></i>Salões
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user-edit me-2"></i>Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Cliente
                    <small class="text-muted">Bem-vindo, <?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?>!</small>
                </h1>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $total_agendamentos; ?></h4>
                                <p class="mb-0">Total de Agendamentos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-alt fa-2x"></i>
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
                                <h4><?php echo $agendamentos_pendentes; ?></h4>
                                <p class="mb-0">Pendentes</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
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
                                <h4><?php echo $agendamentos_confirmados; ?></h4>
                                <p class="mb-0">Confirmados</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
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
                                <h4><?php echo count($saloes_disponiveis); ?></h4>
                                <p class="mb-0">Salões Disponíveis</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-store fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Próximo Agendamento -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Próximo Agendamento</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($proximo_agendamento): ?>
                            <div class="alert alert-info">
                                <h6><strong><?php echo htmlspecialchars($proximo_agendamento['servico'] ?? 'Serviço'); ?></strong></h6>
                                <p class="mb-1">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('d/m/Y', strtotime($proximo_agendamento['data'])); ?>
                                    <i class="fas fa-clock ms-3 me-1"></i>
                                    <?php echo date('H:i', strtotime($proximo_agendamento['hora'])); ?>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-store me-1"></i>
                                    <?php echo htmlspecialchars($proximo_agendamento['salao_nome'] ?? 'Salão'); ?>
                                </p>
                                <span class="badge bg-<?php echo $proximo_agendamento['status'] === 'confirmado' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($proximo_agendamento['status']); ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum agendamento próximo</p>
                                <a href="agendar.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Agendar Serviço
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="agendar.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-calendar-plus me-2"></i>Novo Agendamento
                            </a>
                            <a href="agendamentos.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Ver Meus Agendamentos
                            </a>
                            <a href="saloes.php" class="btn btn-outline-info">
                                <i class="fas fa-search me-2"></i>Explorar Salões
                            </a>
                            <a href="perfil.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user-edit me-2"></i>Editar Perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agendamentos Recentes -->
        <?php if (!empty($agendamentos_recentes)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Agendamentos Recentes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Serviço</th>
                                        <th>Salão</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                        <th>Status</th>
                                        <th>Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($agendamentos_recentes, 0, 5) as $ag): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ag['servico'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($ag['salao_nome'] ?? 'N/A'); ?></td>
                                        <td><?php echo isset($ag['data']) ? date('d/m/Y', strtotime($ag['data'])) : 'N/A'; ?></td>
                                        <td><?php echo isset($ag['hora']) ? date('H:i', strtotime($ag['hora'])) : 'N/A'; ?></td>
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
                                        <td>R$ <?php echo number_format($ag['valor'] ?? 0, 2, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="agendamentos.php" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Ver Todos os Agendamentos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>