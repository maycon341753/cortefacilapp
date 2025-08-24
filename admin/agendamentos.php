<?php
/**
 * Gerenciamento de Agendamentos - Administrador
 * Lista, filtra e gerencia todos os agendamentos da plataforma
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/agendamento.php';
require_once '../models/usuario.php';
require_once '../models/salao.php';

// Verificar se é administrador
requireAdmin();

$usuario_logado = getLoggedUser();
$agendamento = new Agendamento();
$usuario = new Usuario();
$salao = new Salao();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Token de segurança inválido.';
        header('Location: agendamentos.php');
        exit;
    }
    
    $acao = $_POST['acao'] ?? '';
    $agendamento_id = intval($_POST['agendamento_id'] ?? 0);
    
    if ($acao === 'cancelar' && $agendamento_id > 0) {
        if ($agendamento->atualizarStatus($agendamento_id, 'cancelado')) {
            logActivity($usuario_logado['id'], 'agendamento_cancelado_admin', "Agendamento ID $agendamento_id cancelado pelo admin");
            $_SESSION['success'] = 'Agendamento cancelado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao cancelar agendamento.';
        }
    } elseif ($acao === 'confirmar' && $agendamento_id > 0) {
        if ($agendamento->atualizarStatus($agendamento_id, 'confirmado')) {
            logActivity($usuario_logado['id'], 'agendamento_confirmado_admin', "Agendamento ID $agendamento_id confirmado pelo admin");
            $_SESSION['success'] = 'Agendamento confirmado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao confirmar agendamento.';
        }
    } elseif ($acao === 'concluir' && $agendamento_id > 0) {
        if ($agendamento->atualizarStatus($agendamento_id, 'concluido')) {
            logActivity($usuario_logado['id'], 'agendamento_concluido_admin', "Agendamento ID $agendamento_id marcado como concluído pelo admin");
            $_SESSION['success'] = 'Agendamento marcado como concluído!';
        } else {
            $_SESSION['error'] = 'Erro ao marcar agendamento como concluído.';
        }
    } elseif ($acao === 'excluir' && $agendamento_id > 0) {
        if ($agendamento->excluir($agendamento_id)) {
            logActivity($usuario_logado['id'], 'agendamento_excluido_admin', "Agendamento ID $agendamento_id excluído pelo admin");
            $_SESSION['success'] = 'Agendamento excluído com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao excluir agendamento.';
        }
    }
    
    header('Location: agendamentos.php');
    exit;
}

// Filtros
$filtros = [
    'status' => $_GET['status'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? '',
    'salao_id' => $_GET['salao_id'] ?? '',
    'cliente_busca' => $_GET['cliente_busca'] ?? ''
];

// Paginação
$pagina = intval($_GET['pagina'] ?? 1);
$por_pagina = 20;
$offset = ($pagina - 1) * $por_pagina;

// Buscar agendamentos
$agendamentos = $agendamento->listarComFiltrosAdmin($filtros, $por_pagina, $offset);
$total_agendamentos = $agendamento->contarComFiltrosAdmin($filtros);
$total_paginas = ceil($total_agendamentos / $por_pagina);

// Estatísticas
$total_geral = $agendamento->contar();
$pendentes = $agendamento->contarPorStatus('pendente');
$confirmados = $agendamento->contarPorStatus('confirmado');
$concluidos = $agendamento->contarPorStatus('concluido');
$cancelados = $agendamento->contarPorStatus('cancelado');

// Receita
$receita_total = $agendamento->calcularReceitaTotal();
$receita_mes = $agendamento->calcularReceitaPorPeriodo(date('Y-m-01'), date('Y-m-t'));

// Lista de salões para filtro
$saloes_lista = $salao->listarAtivos();

// Gerar novo token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - CorteFácil Admin</title>
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
                            <a class="nav-link active" href="agendamentos.php">
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
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Gerenciar Agendamentos
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="exportarCSV()">
                                <i class="fas fa-download me-2"></i>
                                Exportar
                            </button>
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
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_geral, 0, ',', '.'); ?></h4>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4><?php echo number_format($pendentes, 0, ',', '.'); ?></h4>
                                <small>Pendentes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h4><?php echo number_format($confirmados, 0, ',', '.'); ?></h4>
                                <small>Confirmados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-check-double fa-2x mb-2"></i>
                                <h4><?php echo number_format($concluidos, 0, ',', '.'); ?></h4>
                                <small>Concluídos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h4><?php echo number_format($cancelados, 0, ',', '.'); ?></h4>
                                <small>Cancelados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                <h4>R$ <?php echo number_format($receita_mes, 0, ',', '.'); ?></h4>
                                <small>Receita/Mês</small>
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
                                    <option value="pendente" <?php echo $filtros['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="confirmado" <?php echo $filtros['status'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                    <option value="concluido" <?php echo $filtros['status'] === 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                    <option value="cancelado" <?php echo $filtros['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="salao_id" class="form-label">Salão</label>
                                <select class="form-select" id="salao_id" name="salao_id">
                                    <option value="">Todos os salões</option>
                                    <?php foreach ($saloes_lista as $sal): ?>
                                        <option value="<?php echo $sal['id']; ?>" 
                                                <?php echo $filtros['salao_id'] == $sal['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sal['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="cliente_busca" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="cliente_busca" name="cliente_busca" 
                                       placeholder="Nome do cliente" value="<?php echo htmlspecialchars($filtros['cliente_busca']); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="data_inicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                       value="<?php echo htmlspecialchars($filtros['data_inicio']); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="data_fim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                       value="<?php echo htmlspecialchars($filtros['data_fim']); ?>">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de Agendamentos -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Agendamentos (<?php echo number_format($total_agendamentos, 0, ',', '.'); ?> encontrados)
                        </h6>
                        <div>
                            <?php if ($pagina > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <span class="mx-2">Página <?php echo $pagina; ?> de <?php echo $total_paginas; ?></span>
                            
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($agendamentos)): ?>
                            <p class="text-muted text-center py-4">Nenhum agendamento encontrado com os filtros aplicados.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Salão</th>
                                            <th>Profissional</th>
                                            <th>Data/Hora</th>
                                            <th>Status</th>
                                            <th>Valor</th>
                                            <th>Criado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($agendamentos as $ag): ?>
                                            <tr>
                                                <td><strong>#<?php echo $ag['id']; ?></strong></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($ag['cliente_nome']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($ag['cliente_email']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($ag['salao_nome']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($ag['salao_endereco']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($ag['profissional_nome']); ?></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo formatarData($ag['data']); ?></div>
                                                    <small class="text-muted"><?php echo formatarHora($ag['hora']); ?></small>
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
                                                <td>
                                                    <small><?php echo formatarDataHora($ag['data_criacao']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="verDetalhes(<?php echo $ag['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <?php if ($ag['status'] === 'pendente'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                                    onclick="confirmarAcao('confirmar', <?php echo $ag['id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($ag['status'] === 'confirmado'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                                    onclick="confirmarAcao('concluir', <?php echo $ag['id']; ?>)">
                                                                <i class="fas fa-check-double"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (in_array($ag['status'], ['pendente', 'confirmado'])): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                    onclick="confirmarAcao('cancelar', <?php echo $ag['id']; ?>)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmarAcao('excluir', <?php echo $ag['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
    
    <!-- Modal de Confirmação -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Ação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="mensagemConfirmacao"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="acao" id="acaoConfirmacao">
                        <input type="hidden" name="agendamento_id" id="agendamentoIdConfirmacao">
                        <button type="submit" class="btn btn-primary" id="botaoConfirmacao">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Detalhes do Agendamento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="conteudoDetalhes">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        function confirmarAcao(acao, agendamentoId) {
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            const mensagem = document.getElementById('mensagemConfirmacao');
            const botao = document.getElementById('botaoConfirmacao');
            
            document.getElementById('acaoConfirmacao').value = acao;
            document.getElementById('agendamentoIdConfirmacao').value = agendamentoId;
            
            let textoAcao, classeBtn;
            
            switch (acao) {
                case 'confirmar':
                    textoAcao = 'confirmar';
                    classeBtn = 'btn-success';
                    break;
                case 'cancelar':
                    textoAcao = 'cancelar';
                    classeBtn = 'btn-warning';
                    break;
                case 'concluir':
                    textoAcao = 'marcar como concluído';
                    classeBtn = 'btn-info';
                    break;
                case 'excluir':
                    textoAcao = 'excluir permanentemente';
                    classeBtn = 'btn-danger';
                    break;
            }
            
            mensagem.innerHTML = `Tem certeza que deseja <strong>${textoAcao}</strong> o agendamento <strong>#${agendamentoId}</strong>?`;
            
            botao.className = `btn ${classeBtn}`;
            botao.textContent = 'Confirmar';
            
            modal.show();
        }
        
        function verDetalhes(agendamentoId) {
            const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
            const conteudo = document.getElementById('conteudoDetalhes');
            
            // Mostrar loading
            conteudo.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            // Carregar detalhes via AJAX (simulado)
            setTimeout(() => {
                conteudo.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Funcionalidade de detalhes será implementada em breve.
                    </div>
                `;
            }, 1000);
        }
        
        function exportarCSV() {
            // Simular exportação
            alert('Funcionalidade de exportação será implementada em breve.');
        }
        
        // Auto-refresh da página a cada 2 minutos
        setTimeout(function() {
            location.reload();
        }, 120000);
    </script>
</body>
</html>