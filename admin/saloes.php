<?php
/**
 * Gerenciamento de Salões - Administrador
 * Lista, filtra e gerencia todos os salões da plataforma
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/salao.php';
require_once '../models/usuario.php';
require_once '../models/profissional.php';
require_once '../models/agendamento.php';

// Verificar se é administrador
requireAdmin();

$usuario_logado = getLoggedUser();
$salao = new Salao();
$usuario = new Usuario();
$profissional = new Profissional();
$agendamento = new Agendamento();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Token de segurança inválido.';
        header('Location: saloes.php');
        exit;
    }
    
    $acao = $_POST['acao'] ?? '';
    $salao_id = intval($_POST['salao_id'] ?? 0);
    
    if ($acao === 'ativar' && $salao_id > 0) {
        if ($salao->atualizarStatus($salao_id, 'ativo')) {
            logActivity($usuario_logado['id'], 'salao_ativado', "Salão ID $salao_id ativado");
            $_SESSION['success'] = 'Salão ativado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao ativar salão.';
        }
    } elseif ($acao === 'desativar' && $salao_id > 0) {
        if ($salao->atualizarStatus($salao_id, 'inativo')) {
            logActivity($usuario_logado['id'], 'salao_desativado', "Salão ID $salao_id desativado");
            $_SESSION['success'] = 'Salão desativado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao desativar salão.';
        }
    } elseif ($acao === 'excluir' && $salao_id > 0) {
        // Verificar se pode excluir (não pode ter agendamentos)
        $tem_agendamentos = $agendamento->contarPorSalao($salao_id) > 0;
        
        if ($tem_agendamentos) {
            $_SESSION['error'] = 'Não é possível excluir salão com agendamentos. Desative-o ao invés de excluir.';
        } else {
            if ($salao->excluir($salao_id)) {
                logActivity($usuario_logado['id'], 'salao_excluido', "Salão ID $salao_id excluído");
                $_SESSION['success'] = 'Salão excluído com sucesso!';
            } else {
                $_SESSION['error'] = 'Erro ao excluir salão.';
            }
        }
    }
    
    header('Location: saloes.php');
    exit;
}

// Filtros
$filtros = [
    'status' => $_GET['status'] ?? '',
    'busca' => $_GET['busca'] ?? '',
    'cidade' => $_GET['cidade'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim' => $_GET['data_fim'] ?? ''
];

// Paginação
$pagina = intval($_GET['pagina'] ?? 1);
$por_pagina = 15;
$offset = ($pagina - 1) * $por_pagina;

// Buscar salões
$saloes = $salao->listarComFiltros($filtros, $por_pagina, $offset);
$total_saloes = $salao->contarComFiltros($filtros);
$total_paginas = ceil($total_saloes / $por_pagina);

// Estatísticas
$total_saloes_geral = $salao->contar();
$saloes_ativos = $salao->contarPorStatus('ativo');
$saloes_inativos = $salao->contarPorStatus('inativo');
$total_profissionais = $profissional->contar();
$cidades = $salao->listarCidades();

// Gerar novo token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salões - CorteFácil Admin</title>
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
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users"></i>
                                Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="saloes.php">
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
                        <i class="fas fa-store me-2 text-primary"></i>
                        Gerenciar Salões
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
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-store fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_saloes_geral, 0, ',', '.'); ?></h4>
                                <small>Total de Salões</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h4><?php echo number_format($saloes_ativos, 0, ',', '.'); ?></h4>
                                <small>Salões Ativos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-times-circle fa-2x mb-2"></i>
                                <h4><?php echo number_format($saloes_inativos, 0, ',', '.'); ?></h4>
                                <small>Salões Inativos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4><?php echo number_format($total_profissionais, 0, ',', '.'); ?></h4>
                                <small>Total Profissionais</small>
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
                                    <option value="ativo" <?php echo $filtros['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?php echo $filtros['status'] === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="busca" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="busca" name="busca" 
                                       placeholder="Nome do salão" value="<?php echo htmlspecialchars($filtros['busca']); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="cidade" class="form-label">Cidade</label>
                                <select class="form-select" id="cidade" name="cidade">
                                    <option value="">Todas</option>
                                    <?php foreach ($cidades as $cidade): ?>
                                        <option value="<?php echo htmlspecialchars($cidade); ?>" 
                                                <?php echo $filtros['cidade'] === $cidade ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cidade); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                
                <!-- Lista de Salões -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Salões (<?php echo number_format($total_saloes, 0, ',', '.'); ?> encontrados)
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
                        <?php if (empty($saloes)): ?>
                            <p class="text-muted text-center py-4">Nenhum salão encontrado com os filtros aplicados.</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($saloes as $sal): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 <?php echo $sal['status'] === 'inativo' ? 'border-secondary' : ''; ?>">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-store me-2"></i>
                                                    <?php echo htmlspecialchars($sal['nome']); ?>
                                                </h6>
                                                <span class="badge bg-<?php echo $sal['status'] === 'ativo' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($sal['status']); ?>
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($sal['endereco']); ?>
                                                    </small>
                                                </div>
                                                
                                                <?php if ($sal['telefone']): ?>
                                                    <div class="mb-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($sal['telefone']); ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user me-1"></i>
                                                        Dono: <?php echo htmlspecialchars($sal['dono_nome']); ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        Cadastrado: <?php echo formatarData($sal['data_cadastro']); ?>
                                                    </small>
                                                </div>
                                                
                                                <div class="row text-center mt-3">
                                                    <div class="col-4">
                                                        <div class="fw-bold text-primary"><?php echo $sal['total_profissionais'] ?? 0; ?></div>
                                                        <small class="text-muted">Profissionais</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="fw-bold text-success"><?php echo $sal['total_agendamentos'] ?? 0; ?></div>
                                                        <small class="text-muted">Agendamentos</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="fw-bold text-info">R$ <?php echo number_format($sal['receita_total'] ?? 0, 2, ',', '.'); ?></div>
                                                        <small class="text-muted">Receita</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalhes(<?php echo $sal['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($sal['status'] === 'ativo'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="confirmarAcao('desativar', <?php echo $sal['id']; ?>, '<?php echo htmlspecialchars($sal['nome']); ?>')">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="confirmarAcao('ativar', <?php echo $sal['id']; ?>, '<?php echo htmlspecialchars($sal['nome']); ?>')">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmarAcao('excluir', <?php echo $sal['id']; ?>, '<?php echo htmlspecialchars($sal['nome']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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
                        <input type="hidden" name="salao_id" id="salaoIdConfirmacao">
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
                        <i class="fas fa-store me-2"></i>
                        Detalhes do Salão
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
        function confirmarAcao(acao, salaoId, nomeSalao) {
            const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
            const mensagem = document.getElementById('mensagemConfirmacao');
            const botao = document.getElementById('botaoConfirmacao');
            
            document.getElementById('acaoConfirmacao').value = acao;
            document.getElementById('salaoIdConfirmacao').value = salaoId;
            
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
            
            mensagem.innerHTML = `Tem certeza que deseja <strong>${textoAcao}</strong> o salão <strong>${nomeSalao}</strong>?`;
            
            botao.className = `btn ${classeBtn}`;
            botao.textContent = 'Confirmar';
            
            modal.show();
        }
        
        function verDetalhes(salaoId) {
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
    </script>
</body>
</html>