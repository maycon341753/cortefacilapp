<?php
/**
 * Página de Agendamentos do Cliente
 * Lista todos os agendamentos do cliente com opções de filtro e cancelamento
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$agendamento = new Agendamento();

$erro = '';
$sucesso = '';

// Processar cancelamento de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_agendamento'])) {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        $id_agendamento = (int)($_POST['id_agendamento'] ?? 0);
        
        if (!$id_agendamento) {
            throw new Exception('ID do agendamento inválido.');
        }
        
        // Verificar se o agendamento pertence ao cliente
        $dados_agendamento = $agendamento->buscarPorId($id_agendamento);
        
        if (!$dados_agendamento || $dados_agendamento['id_cliente'] != $usuario['id']) {
            throw new Exception('Agendamento não encontrado.');
        }
        
        // Verificar se pode cancelar (até 2 horas antes)
        $data_hora_agendamento = strtotime($dados_agendamento['data'] . ' ' . $dados_agendamento['hora']);
        $limite_cancelamento = $data_hora_agendamento - (2 * 60 * 60); // 2 horas antes
        
        if (time() > $limite_cancelamento) {
            throw new Exception('Não é possível cancelar agendamentos com menos de 2 horas de antecedência.');
        }
        
        // Cancelar agendamento
        $resultado = $agendamento->cancelar($id_agendamento);
        
        if ($resultado) {
            $sucesso = 'Agendamento cancelado com sucesso.';
            // Log da atividade
            logActivity($usuario['id'], 'agendamento_cancelado', "Agendamento #{$id_agendamento} cancelado pelo cliente");
        } else {
            throw new Exception('Erro ao cancelar agendamento.');
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_data_inicio = $_GET['data_inicio'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';

// Buscar agendamentos do cliente
$agendamentos = $agendamento->listarPorCliente($usuario['id']);

// Aplicar filtros
if ($filtro_status) {
    $agendamentos = array_filter($agendamentos, function($ag) use ($filtro_status) {
        return $ag['status'] === $filtro_status;
    });
}

if ($filtro_data_inicio) {
    $agendamentos = array_filter($agendamentos, function($ag) use ($filtro_data_inicio) {
        return $ag['data'] >= $filtro_data_inicio;
    });
}

if ($filtro_data_fim) {
    $agendamentos = array_filter($agendamentos, function($ag) use ($filtro_data_fim) {
        return $ag['data'] <= $filtro_data_fim;
    });
}

// Ordenar por data (mais recentes primeiro)
usort($agendamentos, function($a, $b) {
    $data_a = strtotime($a['data'] . ' ' . $a['hora']);
    $data_b = strtotime($b['data'] . ' ' . $b['hora']);
    return $data_b - $data_a;
});

// Estatísticas
$total_agendamentos = count($agendamentos);
$agendamentos_pendentes = count(array_filter($agendamentos, function($a) {
    return $a['status'] === 'pendente';
}));
$agendamentos_confirmados = count(array_filter($agendamentos, function($a) {
    return $a['status'] === 'confirmado';
}));
$agendamentos_cancelados = count(array_filter($agendamentos, function($a) {
    return $a['status'] === 'cancelado';
}));
$agendamentos_concluidos = count(array_filter($agendamentos, function($a) {
    return $a['status'] === 'concluido';
}));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - CorteFácil</title>
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
                            <a class="nav-link" href="dashboard.php">
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
                            <a class="nav-link active" href="agendamentos.php">
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
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            Meus Agendamentos
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
                
                <!-- Alertas -->
                <?php if ($erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($erro); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($sucesso); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-calendar-check text-primary"></i>
                            </div>
                            <div class="number"><?php echo $total_agendamentos; ?></div>
                            <div class="label">Total</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_pendentes; ?></div>
                            <div class="label">Pendentes</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_confirmados; ?></div>
                            <div class="label">Confirmados</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-times-circle text-danger"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_cancelados; ?></div>
                            <div class="label">Cancelados</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-check-double text-info"></i>
                            </div>
                            <div class="number"><?php echo $agendamentos_concluidos; ?></div>
                            <div class="label">Concluídos</div>
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
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos os status</option>
                                    <option value="pendente" <?php echo ($filtro_status === 'pendente') ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="confirmado" <?php echo ($filtro_status === 'confirmado') ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="concluido" <?php echo ($filtro_status === 'concluido') ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="cancelado" <?php echo ($filtro_status === 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo htmlspecialchars($filtro_data_inicio); ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?php echo htmlspecialchars($filtro_data_fim); ?>">
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>
                                        Filtrar
                                    </button>
                                    <a href="agendamentos.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de Agendamentos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Agendamentos
                            <span class="badge bg-primary ms-2"><?php echo count($agendamentos); ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($agendamentos)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Salão</th>
                                            <th>Profissional</th>
                                            <th>Data/Hora</th>
                                            <th>Status</th>
                                            <th>Valor</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agendamentos as $ag): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo str_pad($ag['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($ag['nome_salao']); ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($ag['nome_profissional']); ?>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo formatarData($ag['data']); ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo formatarHora($ag['hora']); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo gerarBadgeStatus($ag['status']); ?>
                                                </td>
                                                <td>
                                                    <strong class="text-success">
                                                        R$ <?php echo number_format($ag['valor_taxa'], 2, ',', '.'); ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <!-- Botão Ver Detalhes -->
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetalhes<?php echo $ag['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <!-- Botão Cancelar (apenas para agendamentos confirmados/pendentes) -->
                                                        <?php if (in_array($ag['status'], ['pendente', 'confirmado'])): ?>
                                                            <?php 
                                                            $data_hora_agendamento = strtotime($ag['data'] . ' ' . $ag['hora']);
                                                            $limite_cancelamento = $data_hora_agendamento - (2 * 60 * 60);
                                                            $pode_cancelar = time() <= $limite_cancelamento;
                                                            ?>
                                                            
                                                            <?php if ($pode_cancelar): ?>
                                                                <button type="button" class="btn btn-outline-danger" 
                                                                        onclick="confirmarCancelamento(<?php echo $ag['id']; ?>)">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-outline-secondary" 
                                                                        disabled title="Não é possível cancelar com menos de 2h de antecedência">
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal de Detalhes -->
                                            <div class="modal fade" id="modalDetalhes<?php echo $ag['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-info-circle me-2"></i>
                                                                Detalhes do Agendamento #<?php echo str_pad($ag['id'], 6, '0', STR_PAD_LEFT); ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Salão:</label>
                                                                        <p class="mb-0"><?php echo htmlspecialchars($ag['nome_salao']); ?></p>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Profissional:</label>
                                                                        <p class="mb-0"><?php echo htmlspecialchars($ag['nome_profissional']); ?></p>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Status:</label>
                                                                        <p class="mb-0"><?php echo gerarBadgeStatus($ag['status']); ?></p>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Data:</label>
                                                                        <p class="mb-0"><?php echo formatarData($ag['data']); ?></p>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Horário:</label>
                                                                        <p class="mb-0"><?php echo formatarHora($ag['hora']); ?></p>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Taxa Paga:</label>
                                                                        <p class="mb-0 text-success fw-bold">
                                                                            R$ <?php echo number_format($ag['valor_taxa'], 2, ',', '.'); ?>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <hr>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Data de Criação:</label>
                                                                <p class="mb-0"><?php echo formatarDataHora($ag['created_at'] ?? $ag['data']); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                Fechar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum agendamento encontrado</h5>
                                <p class="text-muted">Você ainda não possui agendamentos ou nenhum agendamento corresponde aos filtros aplicados.</p>
                                <a href="agendar.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Fazer Primeiro Agendamento
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Form de Cancelamento (oculto) -->
    <form id="formCancelamento" method="POST" style="display: none;">
        <?php echo generateCsrfToken(); ?>
        <input type="hidden" name="cancelar_agendamento" value="1">
        <input type="hidden" name="id_agendamento" id="idAgendamentoCancelar">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        function confirmarCancelamento(idAgendamento) {
            if (confirm('Tem certeza que deseja cancelar este agendamento?\n\nEsta ação não pode ser desfeita.')) {
                document.getElementById('idAgendamentoCancelar').value = idAgendamento;
                document.getElementById('formCancelamento').submit();
            }
        }
    </script>
</body>
</html>