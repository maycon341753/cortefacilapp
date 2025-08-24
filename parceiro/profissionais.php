<?php
/**
 * Página de Gerenciamento de Profissionais - VERSÃO CORRIGIDA SEM CSRF
 * Permite ao parceiro cadastrar, editar e gerenciar profissionais do seu salão
 * VERSÃO SIMPLIFICADA - Agosto 2025
 */

// Configurações de erro para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        error_log('Profissionais: Conexão online forçada com sucesso');
    }
} catch (Exception $e) {
    error_log('Profissionais: Erro ao forçar conexão online: ' . $e->getMessage());
}

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../models/salao.php';
    require_once __DIR__ . '/../models/profissional.php';

    // Verificar se é parceiro
    requireParceiro();

    $usuario = getLoggedUser();
    $salao = new Salao();
    $profissional = new Profissional();

    $erro = '';
    $sucesso = '';

    // Verificar mensagens de sucesso da sessão (POST-Redirect-GET)
    if (isset($_SESSION['sucesso'])) {
        $sucesso = $_SESSION['sucesso'];
        unset($_SESSION['sucesso']);
    }

    // Verificar se tem salão cadastrado
    $meu_salao = $salao->buscarPorDono($usuario['id']);
    if (!$meu_salao) {
        header('Location: salao.php');
        exit;
    }

    // Processar ações
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['acao'])) {
        try {
            $acao = $_POST['acao'] ?? '';
            
            // Validação adicional para ações que requerem ID de profissional
            if (in_array($acao, ['editar', 'excluir'])) {
                $id_profissional = intval($_POST['id_profissional'] ?? 0);
                if ($id_profissional <= 0) {
                    throw new Exception('ID do profissional inválido.');
                }
            }
            
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
                
                // Processar nova especialidade se necessário
                if ($especialidade === '__nova__') {
                    $nova_especialidade = trim($_POST['nova_especialidade'] ?? '');
                    if (empty($nova_especialidade)) {
                        throw new Exception('Nova especialidade é obrigatória.');
                    }
                    
                    // Verificar se a especialidade já existe
                    $stmt_check = $conn->prepare("SELECT id FROM especialidades WHERE nome = :nome");
                    $stmt_check->bindParam(':nome', $nova_especialidade);
                    $stmt_check->execute();
                    
                    if ($stmt_check->fetch()) {
                        // Se já existe, usar a existente
                        $especialidade = $nova_especialidade;
                    } else {
                        // Criar nova especialidade
                        $stmt_insert = $conn->prepare("INSERT INTO especialidades (nome) VALUES (:nome)");
                        $stmt_insert->bindParam(':nome', $nova_especialidade);
                        $stmt_insert->execute();
                        $especialidade = $nova_especialidade;
                    }
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
                    
                    if ($profissional->cadastrar($dados)) {
                        // Usar POST-Redirect-GET para evitar duplicação ao atualizar
                        $_SESSION['sucesso'] = 'Profissional cadastrado com sucesso!';
                        header('Location: profissionais.php');
                        exit;
                    } else {
                        $erro = 'Erro ao cadastrar profissional. Tente novamente.';
                    }
                } else {
                    // Editar profissional existente
                    $id_profissional = intval($_POST['id_profissional'] ?? 0);
                    
                    if ($id_profissional <= 0) {
                        throw new Exception('ID do profissional inválido.');
                    }
                    
                    // Verificar se o profissional pertence ao salão do usuário
                    $prof_existente = $profissional->buscarPorId($id_profissional);
                    if (!$prof_existente) {
                        throw new Exception('Profissional não encontrado.');
                    }
                    
                    if ($prof_existente['id_salao'] != $meu_salao['id']) {
                        throw new Exception('Profissional não pertence ao seu salão.');
                    }
                    
                    if ($profissional->atualizar($id_profissional, $dados)) {
                        // Usar POST-Redirect-GET para evitar duplicação ao atualizar
                        $_SESSION['sucesso'] = 'Profissional atualizado com sucesso!';
                        header('Location: profissionais.php');
                        exit;
                    } else {
                        $erro = 'Erro ao atualizar profissional. Tente novamente.';
                    }
                }
            } elseif ($acao === 'excluir') {
                $id_profissional = intval($_POST['id_profissional'] ?? 0);
                
                if ($id_profissional <= 0) {
                    throw new Exception('ID do profissional inválido.');
                }
                
                // Verificar se o profissional pertence ao salão do usuário
                $prof_existente = $profissional->buscarPorId($id_profissional);
                if (!$prof_existente) {
                    throw new Exception('Profissional não encontrado.');
                }
                
                if ($prof_existente['id_salao'] != $meu_salao['id']) {
                    throw new Exception('Profissional não pertence ao seu salão.');
                }
                
                try {
                    if ($profissional->excluir($id_profissional)) {
                        // Usar POST-Redirect-GET para evitar duplicação ao atualizar
                        $_SESSION['sucesso'] = 'Profissional excluído permanentemente com sucesso!';
                        header('Location: profissionais.php');
                        exit;
                    } else {
                        $erro = 'Erro ao excluir profissional. Tente novamente.';
                    }
                } catch (Exception $ex) {
                    $erro = $ex->getMessage();
                }
            }
        } catch (Exception $e) {
            $erro = $e->getMessage();
            error_log('Erro na página de profissionais: ' . $e->getMessage());
        }
    }

    // Buscar profissionais do salão

    // Carregar especialidades do banco
    $stmt_esp = $conn->prepare("SELECT id, nome FROM especialidades WHERE ativo = 1 ORDER BY nome");
    $stmt_esp->execute();
    $especialidades_db = $stmt_esp->fetchAll(PDO::FETCH_ASSOC);
    
    // Carregar profissionais do salão
    $profissionais = $profissional->listarPorSalao($meu_salao['id']);
    
    // Adicionar campo 'ativo' baseado no status para compatibilidade com a interface
    foreach ($profissionais as &$prof) {
        $prof['ativo'] = ($prof['status'] === 'ativo') ? 1 : 0;
    }
    unset($prof); // Limpar referência
    
} catch (Exception $e) {
    error_log('Erro crítico na página de profissionais: ' . $e->getMessage());
    
    // Redirecionar para página de erro
    if (file_exists('../erro_500_amigavel.php')) {
        header('Location: ../erro_500_amigavel.php');
        exit;
    }
    
    // Fallback
    echo "<!DOCTYPE html><html><head><title>Erro Temporário</title></head><body>";
    echo "<h1>Sistema em Manutenção</h1>";
    echo "<p>Estamos trabalhando para resolver um problema técnico.</p>";
    echo "<p><a href='../'>Voltar ao Início</a></p>";
    echo "</body></html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profissionais - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .table th {
            background: #f8f9fa;
            border: none;
        }
        .badge {
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../includes/parceiro_navigation.php'; ?>
            
            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-users me-2"></i>Profissionais</h2>
                        <p class="text-muted mb-0">Gerencie os profissionais do seu salão</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProfissional">
                        <i class="fas fa-plus me-2"></i>Novo Profissional
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
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-primary"><?php echo count($profissionais); ?></h3>
                                <p class="mb-0">Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-success"><?php echo count(array_filter($profissionais, function($p) { return $p['ativo']; })); ?></h3>
                                <p class="mb-0">Ativos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-warning"><?php echo count(array_filter($profissionais, function($p) { return !$p['ativo']; })); ?></h3>
                                <p class="mb-0">Inativos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="text-info"><?php echo count(array_unique(array_column($profissionais, 'especialidade'))); ?></h3>
                                <p class="mb-0">Especialidades</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Profissionais -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Profissionais</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($profissionais)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum profissional cadastrado</h5>
                                <p class="text-muted">Clique em "Novo Profissional" para começar</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Especialidade</th>
                                            <th>Telefone</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($profissionais as $prof): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($prof['nome']); ?></h6>
                                                            <small class="text-muted">ID: <?php echo $prof['id']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($prof['especialidade']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($prof['telefone'] ?: '-'); ?></td>
                                                <td><?php echo htmlspecialchars($prof['email'] ?: '-'); ?></td>
                                                <td>
                                                    <?php if ($prof['ativo']): ?>
                                                        <span class="badge bg-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="editarProfissional(<?php echo htmlspecialchars(json_encode($prof)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="excluirProfissional(<?php echo $prof['id']; ?>, '<?php echo htmlspecialchars($prof['nome']); ?>')">
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
            </div>
        </div>
    </div>
    
    <!-- Modal Profissional -->
    <div class="modal fade" id="modalProfissional" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formProfissional">
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
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="especialidade" class="form-label">Especialidade *</label>
                            <select class="form-select" id="especialidade" name="especialidade" required onchange="toggleNovaEspecialidade()">
                                <option value="">Selecione...</option>
                                <?php foreach ($especialidades_db as $esp_option): ?>
                                <option value="<?php echo htmlspecialchars($esp_option['nome']); ?>"><?php echo htmlspecialchars($esp_option['nome']); ?></option>
                                <?php endforeach; ?>
                                <option value="__nova__">+ Adicionar Nova Especialidade</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="divNovaEspecialidade" style="display: none;">
                            <label for="nova_especialidade" class="form-label">Nova Especialidade *</label>
                            <input type="text" class="form-control" id="nova_especialidade" name="nova_especialidade" placeholder="Digite a nova especialidade">
                            <small class="form-text text-muted">Esta especialidade será adicionada ao sistema e ficará disponível para outros profissionais.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="(11) 99999-9999">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="profissional@email.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status do Profissional</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" checked>
                                <label class="form-check-label" for="ativo" id="statusLabel">
                                    <span class="badge bg-success" id="statusBadge">Ativo</span>
                                    <small class="text-muted d-block mt-1" id="statusDescription">Profissional visível para agendamentos</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Confirmação Exclusão -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formExcluir">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id_profissional" id="id_excluir" value="">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                            Confirmar Exclusão
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atenção!</strong> Esta é uma exclusão permanente.
                        </div>
                        
                        <p>Tem certeza que deseja excluir permanentemente o profissional <strong id="nome_excluir"></strong>?</p>
                        
                        <div class="text-muted small">
                            <p class="mb-2"><strong>O que será removido:</strong></p>
                            <ul class="mb-2">
                                <li>Todos os dados do profissional</li>
                                <li>Histórico de agendamentos antigos</li>
                                <li>Todas as informações relacionadas</li>
                            </ul>
                            <p class="text-danger"><strong>Esta ação não pode ser desfeita!</strong></p>
                            <p class="text-info"><small><i class="fas fa-info-circle me-1"></i>Profissionais com agendamentos futuros não podem ser excluídos.</small></p>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variável para controlar envio duplo
        let formSubmitting = false;
        
        function editarProfissional(profissional) {
            document.getElementById('acao').value = 'editar';
            document.getElementById('id_profissional').value = profissional.id;
            document.getElementById('nome').value = profissional.nome;
            document.getElementById('especialidade').value = profissional.especialidade;
            document.getElementById('telefone').value = profissional.telefone || '';
            document.getElementById('email').value = profissional.email || '';
            
            // Definir status baseado no campo status ou ativo
            const isActive = profissional.status === 'ativo' || profissional.ativo == 1;
            document.getElementById('ativo').checked = isActive;
            updateStatusDisplay(isActive);
            
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Profissional';
            
            new bootstrap.Modal(document.getElementById('modalProfissional')).show();
        }
        
        function excluirProfissional(id, nome) {
            document.getElementById('id_excluir').value = id;
            document.getElementById('nome_excluir').textContent = nome;
            
            new bootstrap.Modal(document.getElementById('modalExcluir')).show();
        }
        
        // Função para atualizar o visual do status
        function updateStatusDisplay(isActive) {
            const badge = document.getElementById('statusBadge');
            const description = document.getElementById('statusDescription');
            
            if (isActive) {
                badge.className = 'badge bg-success';
                badge.textContent = 'Ativo';
                description.textContent = 'Profissional visível para agendamentos';
            } else {
                badge.className = 'badge bg-secondary';
                badge.textContent = 'Inativo';
                description.textContent = 'Profissional não aparecerá para clientes';
            }
        }
        
        // Função para mostrar/ocultar campo de nova especialidade
        function toggleNovaEspecialidade() {
            const select = document.getElementById('especialidade');
            const divNova = document.getElementById('divNovaEspecialidade');
            const inputNova = document.getElementById('nova_especialidade');
            
            if (select.value === '__nova__') {
                divNova.style.display = 'block';
                inputNova.required = true;
            } else {
                divNova.style.display = 'none';
                inputNova.required = false;
                inputNova.value = '';
            }
        }
        
        // Event listener para o toggle de status
        document.getElementById('ativo').addEventListener('change', function() {
            updateStatusDisplay(this.checked);
        });
        
        // Prevenir envio duplo do formulário
        document.getElementById('formProfissional').addEventListener('submit', function(e) {
            if (formSubmitting) {
                e.preventDefault();
                return false;
            }
            
            formSubmitting = true;
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
            }
        });
        
        // Resetar modal ao fechar
        document.getElementById('modalProfissional').addEventListener('hidden.bs.modal', function() {
            document.getElementById('formProfissional').reset();
            document.getElementById('acao').value = 'cadastrar';
            document.getElementById('id_profissional').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Novo Profissional';
            
            // Resetar campo de nova especialidade
            document.getElementById('divNovaEspecialidade').style.display = 'none';
            document.getElementById('nova_especialidade').required = false;
            document.getElementById('nova_especialidade').value = '';
            // Resetar status para ativo por padrão
            document.getElementById('ativo').checked = true;
            updateStatusDisplay(true);
            
            // Resetar controle de envio duplo
            formSubmitting = false;
            const submitBtn = document.querySelector('#formProfissional button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar';
            }
        });
        
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            e.target.value = value;
        });
    </script>
</body>
</html>