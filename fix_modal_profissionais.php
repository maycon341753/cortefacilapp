<?php
/**
 * Correção para o modal de profissionais
 * Resolve problemas de carregamento e funcionamento do modal
 */

require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'models/salao.php';
require_once 'models/profissional.php';

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
            $telefone = trim($_POST['telefone'] ?? '');
            $email = trim($_POST['email'] ?? '');
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
            
            // Validar email se fornecido
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }
            
            // Preparar dados
            $dados = [
                'nome' => $nome,
                'especialidade' => $especialidade,
                'telefone' => $telefone,
                'email' => $email,
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
    <title>Profissionais - CorteFácil Parceiro (Corrigido)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
        }
        .avatar-sm {
            width: 40px;
            height: 40px;
            font-size: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
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
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agenda.php">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Agenda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="profissionais.php">
                                <i class="fas fa-users me-2"></i>
                                Profissionais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendamentos.php">
                                <i class="fas fa-list me-2"></i>
                                Agendamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="salao.php">
                                <i class="fas fa-store me-2"></i>
                                Meu Salão
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="relatorios.php">
                                <i class="fas fa-chart-bar me-2"></i>
                                Relatórios
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                    
                    <div class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($usuario['nome']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Conteúdo Principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-users me-2 text-primary"></i>
                        Profissionais
                    </h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProfissional">
                        <i class="fas fa-user-plus me-2"></i>
                        Novo Profissional
                    </button>
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
                
                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-0">Total</h6>
                                        <h3 class="mb-0"><?php echo count($profissionais); ?></h3>
                                    </div>
                                    <div class="ms-3">
                                        <i class="fas fa-users fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-0">Ativos</h6>
                                        <h3 class="mb-0"><?php echo count(array_filter($profissionais, function($p) { return $p['ativo'] == 1; })); ?></h3>
                                    </div>
                                    <div class="ms-3">
                                        <i class="fas fa-user-check fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-0">Inativos</h6>
                                        <h3 class="mb-0"><?php echo count(array_filter($profissionais, function($p) { return $p['ativo'] == 0; })); ?></h3>
                                    </div>
                                    <div class="ms-3">
                                        <i class="fas fa-user-times fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-0">Especialidades</h6>
                                        <h3 class="mb-0"><?php echo count(array_unique(array_column($profissionais, 'especialidade'))); ?></h3>
                                    </div>
                                    <div class="ms-3">
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
                        <h5 class="card-title mb-0">
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
                                    <i class="fas fa-user-plus me-2"></i>
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
                                            <th>Telefone</th>
                                            <th>E-mail</th>
                                            <th>Status</th>
                                            <th>Cadastrado em</th>
                                            <th width="120">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($profissionais as $prof): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white me-3">
                                                            <?php echo strtoupper(substr($prof['nome'], 0, 2)); ?>
                                                        </div>
                                                        <strong><?php echo htmlspecialchars($prof['nome']); ?></strong>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($prof['especialidade']); ?></td>
                                                <td><?php echo !empty($prof['telefone']) ? htmlspecialchars($prof['telefone']) : '-'; ?></td>
                                                <td>
                                                    <?php if (!empty($prof['email'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($prof['email']); ?>">
                                                            <?php echo htmlspecialchars($prof['email']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($prof['ativo']): ?>
                                                        <span class="badge bg-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatarData($prof['created_at'] ?? date('Y-m-d')); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                onclick="editarProfissional(<?php echo htmlspecialchars(json_encode($prof)); ?>)" 
                                                                title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="excluirProfissional(<?php echo $prof['id']; ?>, '<?php echo addslashes($prof['nome']); ?>')" 
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
    <div class="modal fade" id="modalProfissional" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-user-plus me-2"></i>
                        Novo Profissional
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="formProfissional" method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="acao" id="acao" value="cadastrar">
                        <input type="hidden" name="id_profissional" id="id_profissional" value="">
                        <?php echo generateCsrfToken(); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required 
                                           placeholder="Ex: Maria Silva" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="especialidade" class="form-label">Especialidade *</label>
                                    <input type="text" class="form-control" id="especialidade" name="especialidade" required 
                                           placeholder="Ex: Corte e Escova" maxlength="100">
                                    <div class="form-text">Principais serviços oferecidos</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                                           placeholder="(11) 99999-9999" maxlength="20">
                                    <div class="form-text">Telefone para contato do profissional</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="profissional@email.com" maxlength="100">
                                    <div class="form-text">E-mail para contato do profissional</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                                    <label class="form-check-label" for="ativo">
                                        Profissional ativo
                                    </label>
                                    <div class="form-text">Profissionais inativos não aparecem para agendamento</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnText">
                            <i class="fas fa-save me-2"></i>
                            Cadastrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Form para exclusão -->
    <form id="formExcluir" method="POST" action="" style="display: none;">
        <input type="hidden" name="acao" value="excluir">
        <input type="hidden" name="id_profissional" id="id_excluir">
        <?php echo generateCsrfToken(); ?>
    </form>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Aguardar carregamento completo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado - Inicializando modal de profissionais');
            
            // Verificar se Bootstrap está carregado
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap não carregado!');
                return;
            }
            
            // Função para editar profissional
            window.editarProfissional = function(profissional) {
                console.log('Editando profissional:', profissional);
                
                try {
                    document.getElementById('acao').value = 'editar';
                    document.getElementById('id_profissional').value = profissional.id;
                    document.getElementById('nome').value = profissional.nome;
                    document.getElementById('especialidade').value = profissional.especialidade;
                    document.getElementById('telefone').value = profissional.telefone || '';
                    document.getElementById('email').value = profissional.email || '';
                    document.getElementById('ativo').checked = profissional.ativo == 1;
                    
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Profissional';
                    document.getElementById('btnText').innerHTML = '<i class="fas fa-save me-2"></i>Atualizar';
                    
                    // Abrir modal
                    const modal = new bootstrap.Modal(document.getElementById('modalProfissional'));
                    modal.show();
                    
                } catch (error) {
                    console.error('Erro ao editar profissional:', error);
                    alert('Erro ao abrir formulário de edição.');
                }
            };
            
            // Função para excluir profissional
            window.excluirProfissional = function(id, nome) {
                console.log('Excluindo profissional:', id, nome);
                
                if (confirm(`Tem certeza que deseja excluir o profissional "${nome}"?\n\nEsta ação não pode ser desfeita e só é possível se não houver agendamentos futuros.`)) {
                    document.getElementById('id_excluir').value = id;
                    document.getElementById('formExcluir').submit();
                }
            };
            
            // Reset do modal ao fechar
            const modalElement = document.getElementById('modalProfissional');
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    console.log('Modal fechado - resetando formulário');
                    
                    document.getElementById('formProfissional').reset();
                    document.getElementById('acao').value = 'cadastrar';
                    document.getElementById('id_profissional').value = '';
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Novo Profissional';
                    document.getElementById('btnText').innerHTML = '<i class="fas fa-save me-2"></i>Cadastrar';
                    document.getElementById('ativo').checked = true;
                });
            }
            
            // Validação do formulário
            const form = document.getElementById('formProfissional');
            if (form) {
                form.addEventListener('submit', function(e) {
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
                    
                    console.log('Formulário válido - enviando...');
                });
            }
            
            // Máscara para telefone
            const telefoneInput = document.getElementById('telefone');
            if (telefoneInput) {
                telefoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 11) {
                        value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                    } else if (value.length >= 7) {
                        value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                    } else if (value.length >= 3) {
                        value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
                    }
                    e.target.value = value;
                });
            }
            
            console.log('Inicialização concluída');
        });
    </script>
</body>
</html>