<?php
/**
 * Dashboard do Parceiro (Dono do Salão)
 * Painel principal com estatísticas e informações do salão
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
        error_log('Dashboard Parceiro: Conexão online forçada com sucesso');
    }
} catch (Exception $e) {
    error_log('Dashboard Parceiro: Erro ao forçar conexão online: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/salao.php';
require_once __DIR__ . '/../models/profissional.php';
require_once __DIR__ . '/../models/agendamento.php';

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

$erro = '';
$sucesso = '';

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


try {
    // Estatísticas
    $total_profissionais = count($profissionais);
    $profissionais_ativos = count(array_filter($profissionais, function($p) {
        return isset($p['status']) && $p['status'] === 'ativo';
    }));
    
    $total_agendamentos = count($agendamentos);
    $agendamentos_hoje = count(array_filter($agendamentos, function($a) {
        return isset($a['data']) && $a['data'] === date('Y-m-d');
    }));
    $agendamentos_pendentes = count(array_filter($agendamentos, function($a) {
        return isset($a['status']) && $a['status'] === 'pendente';
    }));
    $agendamentos_confirmados = count(array_filter($agendamentos, function($a) {
        return isset($a['status']) && $a['status'] === 'confirmado';
    }));
    
    // Receita total (taxa da plataforma)
    $receita_total = 0;
    $receita_mes = 0;
    
    foreach ($agendamentos as $a) {
        if (isset($a['valor_taxa']) && is_numeric($a['valor_taxa'])) {
            $receita_total += $a['valor_taxa'];
            
            if (isset($a['data']) && date('Y-m', strtotime($a['data'])) === date('Y-m')) {
                $receita_mes += $a['valor_taxa'];
            }
        }
    }
    
    // Próximos agendamentos (hoje e amanhã)
    $proximos_agendamentos = array_filter($agendamentos, function($a) {
        if (!isset($a['data']) || !isset($a['status'])) return false;
        
        $data_agendamento = $a['data'];
        $hoje = date('Y-m-d');
        $amanha = date('Y-m-d', strtotime('+1 day'));
        return ($data_agendamento === $hoje || $data_agendamento === $amanha) && 
               in_array($a['status'], ['pendente', 'confirmado']);
    });
    
    // Ordenar por data e hora
    usort($proximos_agendamentos, function($a, $b) {
        $datetime_a = strtotime(($a['data'] ?? '1970-01-01') . ' ' . ($a['hora'] ?? '00:00'));
        $datetime_b = strtotime(($b['data'] ?? '1970-01-01') . ' ' . ($b['hora'] ?? '00:00'));
        return $datetime_a - $datetime_b;
    });
    
    // Limitar a 5 próximos
    $proximos_agendamentos = array_slice($proximos_agendamentos, 0, 5);
    
    // Agendamentos recentes (últimos 5)
    $agendamentos_recentes = array_slice(array_reverse($agendamentos), 0, 5);
    
} catch (Exception $e) {
    error_log("Erro ao calcular estatísticas: " . $e->getMessage());
    
    // Valores padrão em caso de erro
    $total_profissionais = 0;
    $profissionais_ativos = 0;
    $total_agendamentos = 0;
    $agendamentos_hoje = 0;
    $agendamentos_pendentes = 0;
    $agendamentos_confirmados = 0;
    $receita_total = 0;
    $receita_mes = 0;
    $proximos_agendamentos = [];
    $agendamentos_recentes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CorteFácil Parceiro</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>
                                Exportar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do Salão -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-store me-2"></i>
                                    <?php echo htmlspecialchars($meu_salao['nome'] ?? 'Meu Salão'); ?>
                                </h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($meu_salao['endereco'] ?? 'Endereço não informado'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Profissionais
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $profissionais_ativos; ?>/<?php echo $total_profissionais; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Agendamentos Hoje
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $agendamentos_hoje; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Pendentes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $agendamentos_pendentes; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Receita Mês
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximos Agendamentos -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Próximos Agendamentos
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($proximos_agendamentos)): ?>
                                    <p class="text-muted text-center py-3">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                        Nenhum agendamento próximo
                                    </p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($proximos_agendamentos as $agendamento): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">
                                                        <?php echo htmlspecialchars($agendamento['cliente_nome'] ?? 'Cliente'); ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m', strtotime($agendamento['data'] ?? 'now')); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo htmlspecialchars($agendamento['hora'] ?? '00:00'); ?>
                                                    - 
                                                    <?php echo htmlspecialchars($agendamento['servico'] ?? 'Serviço'); ?>
                                                </p>
                                                <small class="text-muted">
                                                    Status: 
                                                    <span class="badge bg-<?php echo ($agendamento['status'] ?? '') === 'confirmado' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($agendamento['status'] ?? 'pendente'); ?>
                                                    </span>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Agendamentos Recentes -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-history me-2"></i>
                                    Agendamentos Recentes
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($agendamentos_recentes)): ?>
                                    <p class="text-muted text-center py-3">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Nenhum agendamento recente
                                    </p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($agendamentos_recentes as $agendamento): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">
                                                        <?php echo htmlspecialchars($agendamento['cliente_nome'] ?? 'Cliente'); ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m', strtotime($agendamento['data'] ?? 'now')); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1">
                                                    <i class="fas fa-scissors me-1"></i>
                                                    <?php echo htmlspecialchars($agendamento['servico'] ?? 'Serviço'); ?>
                                                    - R$ <?php echo number_format($agendamento['valor'] ?? 0, 2, ',', '.'); ?>
                                                </p>
                                                <small class="text-muted">
                                                    Status: 
                                                    <span class="badge bg-<?php 
                                                        $status = $agendamento['status'] ?? 'pendente';
                                                        echo $status === 'confirmado' ? 'success' : 
                                                             ($status === 'cancelado' ? 'danger' : 'warning');
                                                    ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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
</body>
</html>