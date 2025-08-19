<?php
/**
 * Gerenciamento de Usuários - Administrador
 * Lista, filtra e gerencia todos os usuários da plataforma
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/usuario.php';
require_once '../models/salao.php';
require_once '../models/agendamento.php';

// Verificar se é administrador
requireAdmin();

$usuario_logado = getLoggedUser();
$usuarioModel = new Usuario();
$salao = new Salao();
$agendamento = new Agendamento();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Token de segurança inválido.';
        header('Location: usuarios.php');
        exit;
    }
    
    $acao = $_POST['acao'] ?? '';
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    
    if ($acao === 'ativar' && $usuario_id > 0) {
        if ($usuarioModel->atualizarStatus($usuario_id, 'ativo')) {
            logActivity($usuario_logado['id'], 'usuario_ativado', "Usuário ID $usuario_id ativado");
            $_SESSION['success'] = 'Usuário ativado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao ativar usuário.';
        }
    } elseif ($acao === 'desativar' && $usuario_id > 0) {
        if ($usuarioModel->atualizarStatus($usuario_id, 'inativo')) {
            logActivity($usuario_logado['id'], 'usuario_desativado', "Usuário ID $usuario_id desativado");
            $_SESSION['success'] = 'Usuário desativado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao desativar usuário.';
        }
    } elseif ($acao === 'excluir' && $usuario_id > 0) {
        // Verificar se pode excluir (não pode ter agendamentos)
        $tem_agendamentos = $agendamento->contarPorUsuario($usuario_id) > 0;
        
        if ($tem_agendamentos) {
            $_SESSION['error'] = 'Não é possível excluir usuário com agendamentos. Desative-o ao invés de excluir.';
        } else {
            if ($usuarioModel->excluir($usuario_id)) {
                logActivity($usuario_logado['id'], 'usuario_excluido', "Usuário ID $usuario_id excluído");
                $_SESSION['success'] = 'Usuário excluído com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao excluir usuário.';
            }
        }
    }
    
    header('Location: usuarios.php');
    exit;
}

// Filtros
$filtros = [
    'tipo' => $_GET['tipo'] ?? '',
    'status' => $_GET['status'] ?? '',
    'busca' => $_GET['busca'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? ''
];

// Paginação
$pagina = intval($_GET['pagina'] ?? 1);
$por_pagina = 20;
$offset = ($pagina - 1) * $por_pagina;

// Buscar usuários
$usuarios = $usuarioModel->listarComFiltros($filtros, $por_pagina, $offset);
$total_usuarios = $usuarioModel->contarComFiltros($filtros);
$total_paginas = ceil($total_usuarios / $por_pagina);

// Estatísticas
$total_clientes = $usuarioModel->contarPorTipo('cliente');
$total_parceiros = $usuarioModel->contarPorTipo('parceiro');
$total_admins = $usuarioModel->contarPorTipo('admin');
$usuarios_ativos = $usuarioModel->contarPorStatus('ativo');
$usuarios_inativos = $usuarioModel->contarPorStatus('inativo');

// Gerar novo token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - CorteFácil Admin</title>
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
                            <a class="nav-link active" href="usuarios.php">
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
                        <i class="fas fa-users me-2 text-primary"></i>
                        Gerenciar Usuários
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                Imprimir
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
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_clientes, 0, ',', '.'); ?></h4>
                                <small>Clientes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-store fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_parceiros, 0, ',', '.'); ?></h4>
                                <small>Parceiros</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-crown fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_admins, 0, ',', '.'); ?></h4>
                                <small>Admins</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h4><?php echo number_format($usuarios_ativos, 0, ',', '.'); ?></h4>
                                <small>Usuários Ativos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h4><?php echo number_format($usuarios_inativos, 0, ',', '.'); ?></h4>
                                <small>Usuários Inativos</small>
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
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="cliente" <?php echo $filtros['tipo'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                    <option value="parceiro" <?php echo $filtros['tipo'] === 'parceiro' ? 'selected' : ''; ?>>Parceiro</option>
                                    <option value="admin" <?php echo $filtros['tipo'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos</option>
                                    <option value="ativo" <?php echo $filtros['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?php echo $filtros['status'] === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="busca" name="busca" 
                                       placeholder="Nome ou email" value="<?php echo htmlspecialchars($filtros['busca']); ?>">
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
                
                <!-- Lista de Usuários -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Usuários (<?php echo number_format($total_usuarios, 0, ',', '.'); ?> encontrados)
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
                        <?php if (empty($usuarios)): ?>
                            <p class="text-muted text-center py-4">Nenhum usuário encontrado com os filtros aplicados.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th>Cadastro</th>
                                            <th>Último Acesso</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $user): ?>
                                            <tr>
                                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-<?php echo $user['tipo'] === 'admin' ? 'warning' : ($user['tipo'] === 'parceiro' ? 'success' : 'primary'); ?> text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="fas fa-<?php echo $user['tipo'] === 'admin' ? 'crown' : ($user['tipo'] === 'parceiro' ? 'store' : 'user'); ?> fa-sm"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($user['nome']); ?></div>
                                                            <?php if ($user['telefone']): ?>
                                                                <small class="text-muted"><?php echo htmlspecialchars($user['telefone']); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $tipo_badges = [
                                                        'cliente' => 'primary',
                                                        'parceiro' => 'success',
                                                        'admin' => 'warning'
                                                    ];
                                                    $badge = $tipo_badges[$user['tipo']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge; ?>">
                                                        <?php echo ucfirst($user['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo formatarDataHora($user['data_cadastro']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($user['ultimo_acesso']): ?>
                                                        <small><?php echo formatarDataHora($user['ultimo_acesso']); ?></small>
                                                    <?php else: ?>
                                                        <small class="text-muted">Nunca</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['id'] !== $usuario_logado['id']): ?>
                                                        <div class="btn-group" role="group">
                                                            <?php if ($user['status'] === 'ativo'): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                        onclick="confirmarAcao('desativar', <?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nome']); ?>')">
                                                                    <i class="fas fa-pause"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                                        onclick="confirmarAcao('ativar', <?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nome']); ?>')">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="confirmarAcao('excluir', <?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nome']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Você</span>
                                                    <?php endif; ?>
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
                        <input type="hidden" name="usuario_id" id="usuarioIdConfirmacao">
                        <button type="submit" class="btn btn-primary" id="botaoConfirmacao">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        function confirmarAcao(acao, usuarioId, nomeUsuario) {
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            const mensagem = document.getElementById('mensagemConfirmacao');
            const botao = document.getElementById('botaoConfirmacao');
            
            document.getElementById('acaoConfirmacao').value = acao;
            document.getElementById('usuarioIdConfirmacao').value = usuarioId;
            
            let textoAcao, classeBtn;
            
            switch (acao) {
                case 'ativar':
                    textoAcao = 'ativar';
                    classeBtn = 'btn-success';
                    break;
                case 'desativar':
                    textoAcao = 'desativar';
                    classeBtn = 'btn-warning';
                    break;
                case 'excluir':
                    textoAcao = 'excluir permanentemente';
                    classeBtn = 'btn-danger';
                    break;
            }
            
            mensagem.innerHTML = `Tem certeza que deseja <strong>${textoAcao}</strong> o usuário <strong>${nomeUsuario}</strong>?`;
            
            botao.className = `btn ${classeBtn}`;
            botao.textContent = 'Confirmar';
            
            modal.show();
        }
    </script>
</body>
</html>