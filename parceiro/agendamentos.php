<?php
/**
 * Página de Gerenciamento de Agendamentos do Parceiro
 * Lista e gerencia todos os agendamentos do salão
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/salao.php';
require_once '../models/agendamento.php';
require_once '../models/profissional.php';

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();
$agendamento = new Agendamento();
$profissional = new Profissional();

$erro = '';
$sucesso = '';

// Verificar se tem salão cadastrado
$meu_salao = $salao->buscarPorDono($usuario['id']);
if (!$meu_salao) {
    header('Location: salao.php');
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        $acao = $_POST['acao'] ?? '';
        $agendamento_id = (int)($_POST['agendamento_id'] ?? 0);
        
        if (!$agendamento_id) {
            throw new Exception('ID do agendamento inválido.');
        }
        
        // Verificar se o agendamento pertence ao salão
        $ag = $agendamento->buscarPorId($agendamento_id);
        if (!$ag || $ag['id_salao'] != $meu_salao['id']) {
            throw new Exception('Agendamento não encontrado.');
        }
        
        if ($acao === 'confirmar') {
            if ($ag['status'] !== 'pendente') {
                throw new Exception('Apenas agendamentos pendentes podem ser confirmados.');
            }
            
            $resultado = $agendamento->atualizarStatus($agendamento_id, 'confirmado');
            if ($resultado) {
                $sucesso = 'Agendamento confirmado com sucesso!';
                logActivity($usuario['id'], 'agendamento_confirmado', "Agendamento #{$agendamento_id}");
            } else {
                throw new Exception('Erro ao confirmar agendamento.');
            }
            
        } elseif ($acao === 'cancelar') {
            if ($ag['status'] === 'concluido') {
                throw new Exception('Agendamentos concluídos não podem ser cancelados.');
            }
            
            $resultado = $agendamento->atualizarStatus($agendamento_id, 'cancelado');
            if ($resultado) {
                $sucesso = 'Agendamento cancelado com sucesso!';
                logActivity($usuario['id'], 'agendamento_cancelado', "Agendamento #{$agendamento_id}");
            } else {
                throw new Exception('Erro ao cancelar agendamento.');
            }
            
        } elseif ($acao === 'concluir') {
            if ($ag['status'] !== 'confirmado') {
                throw new Exception('Apenas agendamentos confirmados podem ser concluídos.');
            }
            
            // Verificar se a data/hora já passou
            $data_hora_agendamento = $ag['data'] . ' ' . $ag['hora'];
            if (strtotime($data_hora_agendamento) > time()) {
                throw new Exception('Só é possível concluir agendamentos que já passaram.');
            }
            
            $resultado = $agendamento->atualizarStatus($agendamento_id, 'concluido');
            if ($resultado) {
                $sucesso = 'Agendamento marcado como concluído!';
                logActivity($usuario['id'], 'agendamento_concluido', "Agendamento #{$agendamento_id}");
            } else {
                throw new Exception('Erro ao concluir agendamento.');
            }
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Parâmetros de filtro
$status = $_GET['status'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$profissional_id = $_GET['profissional_id'] ?? '';
$cliente_nome = $_GET['cliente_nome'] ?? '';

// Buscar profissionais do salão
$profissionais = $profissional->listarPorSalao($meu_salao['id']);

// Buscar agendamentos
$filtros = [];

if ($status) {
    $filtros['status'] = $status;
}

if ($data_inicio) {
    $filtros['data_inicio'] = $data_inicio;
}

if ($data_fim) {
    $filtros['data_fim'] = $data_fim;
}

if ($profissional_id) {
    $filtros['profissional_id'] = $profissional_id;
}

if ($cliente_nome) {
    $filtros['cliente_nome'] = $cliente_nome;
}

$agendamentos = $agendamento->listarPorSalao($meu_salao['id'], $filtros);

// Estatísticas
$total_agendamentos = count($agendamentos);
$agendamentos_pendentes = count(array_filter($agendamentos, fn($a) => $a['status'] === 'pendente'));
$agendamentos_confirmados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'confirmado'));
$agendamentos_concluidos = count(array_filter($agendamentos, fn($a) => $a['status'] === 'concluido'));
$agendamentos_cancelados = count(array_filter($agendamentos, fn($a) => $a['status'] === 'cancelado'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - CorteFácil Parceiro</title>
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
                            <a class="nav-link" href="dashboard.php">
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
                            <a class="nav-link active" href="agendamentos.php">
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
                    <h1 class="h2">
                        <i class="fas fa-list me-2 text-primary"></i>
                        Agendamentos
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="agenda.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Ver Agenda
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
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $total_agendamentos; ?></h3>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $agendamentos_pendentes; ?></h3>
                                <small>Pendentes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $agendamentos_confirmados; ?></h3>
                                <small>Confirmados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $agendamentos_concluidos; ?></h3>
                                <small>Concluídos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?php echo $agendamentos_cancelados; ?></h3>
                                <small>Cancelados</small>
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
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos</option>
                                    <option value="pendente" <?php echo $status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="confirmado" <?php echo $status === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="concluido" <?php echo $status === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo htmlspecialchars($data_inicio); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?php echo htmlspecialchars($data_fim); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="profissional_id" class="form-label">Profissional</label>
                                <select class="form-select" id="profissional_id" name="profissional_id">
                                    <option value="">Todos</option>
                                    <?php foreach ($profissionais as $prof): ?>
                                        <option value="<?php echo $prof['id']; ?>" 
                                                <?php echo $profissional_id == $prof['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prof['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="cliente_nome" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" 
                                       value="<?php echo htmlspecialchars($cliente_nome); ?>" 
                                       placeholder="Nome do cliente">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <a href="agendamentos.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Limpar Filtros
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de Agendamentos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Lista de Agendamentos
                            <?php if ($total_agendamentos > 0): ?>
                                <span class="badge bg-primary ms-2"><?php echo $total_agendamentos; ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($agendamentos)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum agendamento encontrado</h5>
                                <p class="text-muted">Não há agendamentos que correspondam aos filtros selecionados.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Profissional</th>
                                            <th>Data/Hora</th>
                                            <th>Status</th>
                                            <th>Taxa</th>
                                            <th width="200">Ações</th>
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
                                                        <strong><?php echo htmlspecialchars($ag['cliente_nome']); ?></strong>
                                                        <?php if ($ag['cliente_telefone']): ?>
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-phone me-1"></i>
                                                                <?php echo htmlspecialchars($ag['cliente_telefone']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($ag['profissional_nome']); ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo formatarData($ag['data']); ?></strong>
                                                        <br><small><?php echo substr($ag['hora'], 0, 5); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $status_class = [
                                                        'pendente' => 'warning',
                                                        'confirmado' => 'success',
                                                        'concluido' => 'info',
                                                        'cancelado' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class[$ag['status']] ?? 'secondary'; ?>">
                                                        <?php echo ucfirst($ag['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>R$ <?php echo number_format($ag['valor_taxa'], 2, ',', '.'); ?></strong>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" 
                                                                onclick="verDetalhes(<?php echo htmlspecialchars(json_encode($ag)); ?>)" 
                                                                title="Ver detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <?php if ($ag['status'] === 'pendente'): ?>
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    onclick="confirmarAgendamento(<?php echo $ag['id']; ?>)" 
                                                                    title="Confirmar">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($ag['status'] === 'confirmado' && strtotime($ag['data'] . ' ' . $ag['hora']) <= time()): ?>
                                                            <button type="button" class="btn btn-outline-info" 
                                                                    onclick="concluirAgendamento(<?php echo $ag['id']; ?>)" 
                                                                    title="Marcar como concluído">
                                                                <i class="fas fa-flag-checkered"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($ag['status'] !== 'concluido'): ?>
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="cancelarAgendamento(<?php echo $ag['id']; ?>)" 
                                                                    title="Cancelar">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
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
            </main>
        </div>
    </div>
    
    <!-- Modal Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog modal-lg">
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
    
    <!-- Forms para ações -->
    <form method="POST" id="formAcao" style="display: none;">
        <?php echo generateCsrfToken(); ?>
        <input type="hidden" name="acao" id="acao">
        <input type="hidden" name="agendamento_id" id="agendamento_id">
    </form>
    
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
                        <h6><i class="fas fa-user me-2"></i>Informações do Cliente</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Nome:</strong></td><td>${agendamento.cliente_nome}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${agendamento.cliente_email || 'Não informado'}</td></tr>
                            <tr><td><strong>Telefone:</strong></td><td>${agendamento.cliente_telefone || 'Não informado'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-calendar me-2"></i>Informações do Agendamento</h6>
                        <table class="table table-sm">
                            <tr><td><strong>ID:</strong></td><td>#${String(agendamento.id).padStart(6, '0')}</td></tr>
                            <tr><td><strong>Data:</strong></td><td>${new Date(agendamento.data + 'T00:00:00').toLocaleDateString('pt-BR')}</td></tr>
                            <tr><td><strong>Horário:</strong></td><td>${agendamento.hora.substring(0, 5)}</td></tr>
                            <tr><td><strong>Profissional:</strong></td><td>${agendamento.profissional_nome}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge bg-${statusClass[agendamento.status] || 'secondary'}">${agendamento.status.charAt(0).toUpperCase() + agendamento.status.slice(1)}</span></td></tr>
                            <tr><td><strong>Taxa:</strong></td><td>R$ ${parseFloat(agendamento.valor_taxa || 0).toFixed(2).replace('.', ',')}</td></tr>
                            <tr><td><strong>Criado em:</strong></td><td>${new Date(agendamento.created_at).toLocaleString('pt-BR')}</td></tr>
                        </table>
                    </div>
                </div>
                ${agendamento.observacoes ? `
                <hr>
                <h6><i class="fas fa-sticky-note me-2"></i>Observações</h6>
                <p class="bg-light p-3 rounded">${agendamento.observacoes}</p>
                ` : ''}
            `;
            
            document.getElementById('detalhesContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('modalDetalhes')).show();
        }
        
        // Função para confirmar agendamento
        function confirmarAgendamento(id) {
            if (confirm('Tem certeza que deseja confirmar este agendamento?')) {
                document.getElementById('acao').value = 'confirmar';
                document.getElementById('agendamento_id').value = id;
                document.getElementById('formAcao').submit();
            }
        }
        
        // Função para cancelar agendamento
        function cancelarAgendamento(id) {
            if (confirm('Tem certeza que deseja cancelar este agendamento?\n\nEsta ação não pode ser desfeita.')) {
                document.getElementById('acao').value = 'cancelar';
                document.getElementById('agendamento_id').value = id;
                document.getElementById('formAcao').submit();
            }
        }
        
        // Função para concluir agendamento
        function concluirAgendamento(id) {
            if (confirm('Tem certeza que deseja marcar este agendamento como concluído?')) {
                document.getElementById('acao').value = 'concluir';
                document.getElementById('agendamento_id').value = id;
                document.getElementById('formAcao').submit();
            }
        }
        
        // Atualizar automaticamente a cada 2 minutos
        setInterval(function() {
            if (!document.hidden) {
                location.reload();
            }
        }, 120000); // 2 minutos
    </script>
</body>
</html>