<?php
/**
 * Dashboard do Cliente
 * Painel principal para clientes agendarem serviços
 */

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/models/agendamento.php';
require_once dirname(__DIR__) . '/models/salao.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$agendamento = new Agendamento();
$salao = new Salao();

// Buscar dados para o dashboard
$agendamentos_recentes = $agendamento->listarPorCliente($usuario['id']);
$saloes_disponiveis = $salao->listarAtivos();

// Estatísticas
$total_agendamentos = count($agendamentos_recentes);
$agendamentos_pendentes = count(array_filter($agendamentos_recentes, function($a) {
    return $a['status'] === 'pendente';
}));
$agendamentos_confirmados = count(array_filter($agendamentos_recentes, function($a) {
    return $a['status'] === 'confirmado';
}));

// Próximo agendamento
$proximo_agendamento = null;
foreach ($agendamentos_recentes as $ag) {
    if (($ag['status'] === 'confirmado' || $ag['status'] === 'pendente') && 
        strtotime($ag['data'] . ' ' . $ag['hora']) > time()) {
        $proximo_agendamento = $ag;
        break;
    }
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
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendar.php">
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
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                            Dashboard
                        </h1>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="agendar.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Novo Agendamento
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Boas-vindas -->
                <div class="alert alert-primary" role="alert">
                    <h4 class="alert-heading">
                        <i class="fas fa-hand-wave me-2"></i>
                        Olá, <?php echo htmlspecialchars($usuario['nome']); ?>!
                    </h4>
                    <p class="mb-0">
                        Bem-vindo ao seu painel. Aqui você pode agendar serviços, acompanhar seus agendamentos e muito mais.
                    </p>
                </div>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-calendar-check text-primary"></i>
                            </div>
                            <div class="number"><?php echo $total_agendamentos; ?></div>
                            <div class="label">Total de Agendamentos</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_pendentes; ?></div>
                            <div class="label">Pendentes</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_confirmados; ?></div>
                            <div class="label">Confirmados</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-store text-info"></i>
                            </div>
                            <div class="number"><?php echo count($saloes_disponiveis); ?></div>
                            <div class="label">Salões Disponíveis</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Próximo Agendamento -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Próximo Agendamento
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($proximo_agendamento): ?>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-cut fa-lg"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($proximo_agendamento['nome_salao']); ?></h6>
                                            <p class="mb-1 text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <?php echo htmlspecialchars($proximo_agendamento['nome_profissional']); ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo formatarData($proximo_agendamento['data']); ?>
                                                <i class="fas fa-clock ms-2 me-1"></i>
                                                <?php echo formatarHora($proximo_agendamento['hora']); ?>
                                            </p>
                                            <span class="badge badge-<?php echo $proximo_agendamento['status'] === 'confirmado' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($proximo_agendamento['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Nenhum agendamento próximo</h6>
                                        <a href="agendar.php" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus me-2"></i>
                                            Agendar Agora
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Agendamentos Recentes -->
                    <div class="col-lg-6 mb-4">
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
                                <?php if (!empty($agendamentos_recentes)): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach (array_slice($agendamentos_recentes, 0, 5) as $ag): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($ag['nome_salao']); ?></h6>
                                                        <p class="mb-1 small text-muted">
                                                            <?php echo htmlspecialchars($ag['nome_profissional']); ?> - 
                                                            <?php echo formatarData($ag['data']); ?> às 
                                                            <?php echo formatarHora($ag['hora']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <?php echo gerarBadgeStatus($ag['status']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted">Nenhum agendamento ainda</h6>
                                        <p class="text-muted small">Faça seu primeiro agendamento!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Salões em Destaque -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>
                            Salões Parceiros
                        </h5>
                        <a href="saloes.php" class="btn btn-sm btn-outline-primary">
                            Ver Todos
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($saloes_disponiveis)): ?>
                            <div class="row">
                                <?php foreach (array_slice($saloes_disponiveis, 0, 6) as $s): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="fas fa-store me-2 text-primary"></i>
                                                    <?php echo htmlspecialchars($s['nome']); ?>
                                                </h6>
                                                <p class="card-text small text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo limitarTexto(htmlspecialchars($s['endereco']), 50); ?>
                                                </p>
                                                <p class="card-text small text-muted">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <?php echo formatTelefone($s['telefone']); ?>
                                                </p>
                                                <a href="agendar.php?salao=<?php echo $s['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-calendar-plus me-1"></i>
                                                    Agendar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-store-slash fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Nenhum salão disponível no momento</h6>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>