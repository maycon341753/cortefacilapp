<?php
/**
 * Página de Gerenciamento de Profissionais
 * Permite ao parceiro cadastrar, editar e gerenciar profissionais do seu salão
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/salao.php';
require_once '../models/profissional.php';

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();
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
        
        if ($acao === 'cadastrar' || $acao === 'editar') {
            // Validar dados
            $nome = trim($_POST['nome'] ?? '');
            $especialidade = trim($_POST['especialidade'] ?? '');
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (empty($nome)) {
                throw new Exception('Nome do profissional é obrigatório.');
            }
            
            if (strlen($nome) < 3) {
                throw new Exception('Nome deve ter pelo menos 3 caracteres.');
            }
            
            if (empty($especialidade)) {
                throw new Exception('Especialidade é obrigatória.');
            }
            
            // Preparar dados
            $dados = [
                'nome' => $nome,
                'especialidade' => $especialidade,
                'ativo' => $ativo
            ];
            
            if ($acao === 'cadastrar') {
                // Cadastrar novo profissional
                $dados['id_salao'] = $meu_salao['id'];
                $resultado = $profissional->cadastrar($dados);
                $mensagem = 'Profissional cadastrado com sucesso!';
                $log_acao = 'profissional_cadastrado';
            } else {
                // Editar profissional existente
                $id_profissional = (int)($_POST['id_profissional'] ?? 0);
                if (!$id_profissional) {
                    throw new Exception('ID do profissional inválido.');
                }
                
                // Verificar se o profissional pertence ao salão do usuário
                $prof_existente = $profissional->buscarPorId($id_profissional);
                if (!$prof_existente || $prof_existente['id_salao'] != $meu_salao['id']) {
                    throw new Exception('Profissional não encontrado.');
                }
                
                $resultado = $profissional->atualizar($id_profissional, $dados);
                $mensagem = 'Profissional atualizado com sucesso!';
                $log_acao = 'profissional_atualizado';
            }
            
            if ($resultado) {
                $sucesso = $mensagem;
                logActivity($usuario['id'], $log_acao, "Profissional: {$nome}");
            } else {
                throw new Exception('Erro ao salvar dados do profissional.');
            }
            
        } elseif ($acao === 'excluir') {
            $id_profissional = (int)($_POST['id_profissional'] ?? 0);
            if (!$id_profissional) {
                throw new Exception('ID do profissional inválido.');
            }
            
            // Verificar se o profissional pertence ao salão do usuário
            $prof_existente = $profissional->buscarPorId($id_profissional);
            if (!$prof_existente || $prof_existente['id_salao'] != $meu_salao['id']) {
                throw new Exception('Profissional não encontrado.');
            }
            
            // Verificar se tem agendamentos futuros
            if ($profissional->temAgendamentosFuturos($id_profissional)) {
                throw new Exception('Não é possível excluir profissional com agendamentos futuros. Desative-o ao invés de excluir.');
            }
            
            $resultado = $profissional->excluir($id_profissional);
            if ($resultado) {
                $sucesso = 'Profissional excluído com sucesso!';
                logActivity($usuario['id'], 'profissional_excluido', "Profissional: {$prof_existente['nome']}");
            } else {
                throw new Exception('Erro ao excluir profissional.');
            }
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar profissionais do salão
$profissionais = $profissional->listarPorSalao($meu_salao['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profissionais - CorteFácil Parceiro</title>
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
                            <a class="nav-link active" href="profissionais.php">
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
                    <h1 class="h2">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Profissionais
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProfissional">
                                <i class="fas fa-plus me-2"></i>
                                Novo Profissional
                            </button>
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
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total</h6>
                                        <h3 class="mb-0"><?php echo count($profissionais); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Ativos</h6>
                                        <h3 class="mb-0"><?php echo count(array_filter($profissionais, fn($p) => $p['ativo'])); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Inativos</h6>
                                        <h3 class="mb-0"><?php echo count(array_filter($profissionais, fn($p) => !$p['ativo'])); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-times fa-2x opacity-75"></i>
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
                                        <h6 class="card-title">Especialidades</h6>
                                        <h3 class="mb-0"><?php echo count(array_unique(array_column($profissionais, 'especialidade'))); ?></h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-cut fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Profissionais -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Lista de Profissionais
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($profissionais)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum profissional cadastrado</h5>
                                <p class="text-muted">Cadastre seu primeiro profissional para começar a receber agendamentos.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProfissional">
                                    <i class="fas fa-plus me-2"></i>
                                    Cadastrar Primeiro Profissional
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Especialidade</th>
                                            <th>Status</th>
                                            <th>Cadastrado em</th>
                                            <th width="150">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($profissionais as $prof): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <?php echo strtoupper(substr($prof['nome'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($prof['nome']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($prof['especialidade']); ?></td>
                                                <td>
                                                    <?php if ($prof['ativo']): ?>
                                                        <span class="badge bg-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatarData($prof['created_at'] ?? date('Y-m-d')); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                onclick="editarProfissional(<?php echo htmlspecialchars(json_encode($prof)); ?>)" 
                                                                title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="excluirProfissional(<?php echo $prof['id']; ?>, '<?php echo htmlspecialchars($prof['nome']); ?>')" 
                                                                title="Excluir">
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
    
    <!-- Modal Profissional -->
    <div class="modal fade" id="modalProfissional" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formProfissional">
                    <?php echo generateCsrfToken(); ?>
                    <input type="hidden" name="acao" id="acao" value="cadastrar">
                    <input type="hidden" name="id_profissional" id="id_profissional" value="">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">
                            <i class="fas fa-user-plus me-2"></i>
                            Novo Profissional
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   placeholder="Ex: Maria Silva" 
                                   minlength="3" maxlength="100" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="especialidade" class="form-label">Especialidade *</label>
                            <input type="text" class="form-control" id="especialidade" name="especialidade" 
                                   placeholder="Ex: Corte e Escova, Manicure, Pedicure" 
                                   maxlength="100" required>
                            <div class="form-text">Principais serviços que o profissional oferece</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                                <label class="form-check-label" for="ativo">
                                    Profissional ativo
                                </label>
                                <div class="form-text">Profissionais inativos não aparecem para agendamento</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <span id="btnText">Cadastrar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Form para exclusão -->
    <form method="POST" id="formExcluir" style="display: none;">
        <?php echo generateCsrfToken(); ?>
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id_profissional" id="id_excluir">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Função para editar profissional
        function editarProfissional(profissional) {
            document.getElementById('acao').value = 'editar';
            document.getElementById('id_profissional').value = profissional.id;
            document.getElementById('nome').value = profissional.nome;
            document.getElementById('especialidade').value = profissional.especialidade;
            document.getElementById('ativo').checked = profissional.ativo == 1;
            
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Profissional';
            document.getElementById('btnText').textContent = 'Atualizar';
            
            new bootstrap.Modal(document.getElementById('modalProfissional')).show();
        }
        
        // Função para excluir profissional
        function excluirProfissional(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o profissional "${nome}"?\n\nEsta ação não pode ser desfeita e só é possível se não houver agendamentos futuros.`)) {
                document.getElementById('id_excluir').value = id;
                document.getElementById('formExcluir').submit();
            }
        }
        
        // Reset do modal ao fechar
        document.getElementById('modalProfissional').addEventListener('hidden.bs.modal', function() {
            document.getElementById('formProfissional').reset();
            document.getElementById('acao').value = 'cadastrar';
            document.getElementById('id_profissional').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Novo Profissional';
            document.getElementById('btnText').textContent = 'Cadastrar';
            document.getElementById('ativo').checked = true;
        });
        
        // Validação do formulário
        document.getElementById('formProfissional').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const especialidade = document.getElementById('especialidade').value.trim();
            
            if (nome.length < 3) {
                e.preventDefault();
                alert('O nome deve ter pelo menos 3 caracteres.');
                document.getElementById('nome').focus();
                return false;
            }
            
            if (especialidade.length < 3) {
                e.preventDefault();
                alert('A especialidade deve ter pelo menos 3 caracteres.');
                document.getElementById('especialidade').focus();
                return false;
            }
        });
    </script>
    
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
    </style>
</body>
</html>