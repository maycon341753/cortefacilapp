<?php
/**
 * Correção definitiva para CSRF em ambiente de produção
 * Resolve problemas específicos de servidores online
 */

// Configurações específicas para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Configurar sessão para produção
if (session_status() === PHP_SESSION_NONE) {
    // Configurações de sessão seguras para produção
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 7200); // 2 horas
    
    // Configurar cookie seguro apenas se HTTPS estiver disponível
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para proteção adicional
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    session_start();
}

// Função melhorada para gerar token CSRF
function generateProductionCSRFToken() {
    // Verificar se já existe um token válido na sessão
    if (isset($_SESSION['csrf_token_prod']) && isset($_SESSION['csrf_token_time'])) {
        // Token válido por 2 horas
        if (time() - $_SESSION['csrf_token_time'] < 7200) {
            return $_SESSION['csrf_token_prod'];
        }
    }
    
    // Gerar novo token
    try {
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $token = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback para servidores mais antigos
            $token = md5(uniqid(mt_rand(), true));
        }
        
        $_SESSION['csrf_token_prod'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        // Log para debug (apenas em desenvolvimento)
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("CSRF Token gerado: " . substr($token, 0, 10) . "...");
        }
        
        return $token;
        
    } catch (Exception $e) {
        error_log("Erro ao gerar token CSRF: " . $e->getMessage());
        // Fallback de emergência
        $token = md5(session_id() . time() . mt_rand());
        $_SESSION['csrf_token_prod'] = $token;
        $_SESSION['csrf_token_time'] = time();
        return $token;
    }
}

// Função melhorada para verificar token CSRF
function verifyProductionCSRFToken($token) {
    if (empty($token)) {
        error_log("CSRF: Token vazio recebido");
        return false;
    }
    
    if (!isset($_SESSION['csrf_token_prod'])) {
        error_log("CSRF: Token não encontrado na sessão");
        return false;
    }
    
    if (!isset($_SESSION['csrf_token_time'])) {
        error_log("CSRF: Timestamp do token não encontrado");
        return false;
    }
    
    // Verificar expiração (2 horas)
    if (time() - $_SESSION['csrf_token_time'] > 7200) {
        error_log("CSRF: Token expirado");
        unset($_SESSION['csrf_token_prod'], $_SESSION['csrf_token_time']);
        return false;
    }
    
    // Comparação segura
    $isValid = hash_equals($_SESSION['csrf_token_prod'], $token);
    
    if (!$isValid) {
        error_log("CSRF: Token inválido. Esperado: " . substr($_SESSION['csrf_token_prod'], 0, 10) . "..., Recebido: " . substr($token, 0, 10) . "...");
    }
    
    return $isValid;
}

// Incluir arquivos necessários
try {
    require_once 'includes/auth.php';
    require_once 'includes/functions.php';
    require_once 'models/salao.php';
    require_once 'models/profissional.php';
} catch (Exception $e) {
    die("Erro ao carregar arquivos necessários: " . $e->getMessage());
}

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
        // Log da requisição para debug
        error_log("POST recebido para profissionais - Usuario: " . $usuario['id']);
        
        // Validar CSRF com função melhorada
        $tokenRecebido = $_POST['csrf_token'] ?? '';
        
        if (!verifyProductionCSRFToken($tokenRecebido)) {
            // Tentar regenerar token e dar uma segunda chance
            $novoToken = generateProductionCSRFToken();
            throw new Exception('Token de segurança inválido ou expirado. A página será recarregada automaticamente.');
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
                
                // Regenerar token após sucesso
                generateProductionCSRFToken();
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
                
                // Regenerar token após sucesso
                generateProductionCSRFToken();
            } else {
                throw new Exception('Erro ao excluir profissional.');
            }
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
        error_log("Erro no processamento de profissionais: " . $e->getMessage());
    }
}

// Buscar profissionais do salão
$profissionais = $profissional->listarPorSalao($meu_salao['id']);

// Gerar token fresco para o formulário
$csrfToken = generateProductionCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profissionais - CorteFácil Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>CorteFácil Parceiro</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
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
                    </ul>
                    
                    <hr>
                    
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-2"></i>
                            <strong><?php echo htmlspecialchars($usuario['nome']); ?></strong>
                        </a>
                        <ul class="dropdown-menu text-small shadow">
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
                        <?php if (strpos($erro, 'recarregada automaticamente') !== false): ?>
                        <script>
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        </script>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($sucesso); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
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
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($profissionais as $prof): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($prof['nome']); ?></strong></td>
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
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        
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
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone" 
                                           placeholder="(11) 99999-9999" maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="profissional@email.com" maxlength="100">
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
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
    </form>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Aguardar carregamento completo do DOM
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Inicializando modal de profissionais - Produção');
            
            // Token atual
            let currentToken = '<?php echo $csrfToken; ?>';
            
            // Função para atualizar tokens
            function atualizarTokens() {
                const tokenInput = document.getElementById('csrf_token');
                if (tokenInput) {
                    tokenInput.value = currentToken;
                }
                const formExcluir = document.querySelector('#formExcluir input[name="csrf_token"]');
                if (formExcluir) {
                    formExcluir.value = currentToken;
                }
            }
            
            // Função para editar profissional
            window.editarProfissional = function(profissional) {
                try {
                    atualizarTokens();
                    
                    document.getElementById('acao').value = 'editar';
                    document.getElementById('id_profissional').value = profissional.id;
                    document.getElementById('nome').value = profissional.nome;
                    document.getElementById('especialidade').value = profissional.especialidade;
                    document.getElementById('telefone').value = profissional.telefone || '';
                    document.getElementById('email').value = profissional.email || '';
                    document.getElementById('ativo').checked = profissional.ativo == 1;
                    
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Profissional';
                    document.getElementById('btnText').innerHTML = '<i class="fas fa-save me-2"></i>Atualizar';
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalProfissional'));
                    modal.show();
                    
                } catch (error) {
                    console.error('Erro ao editar profissional:', error);
                    alert('Erro ao abrir formulário de edição.');
                }
            };
            
            // Função para excluir profissional
            window.excluirProfissional = function(id, nome) {
                if (confirm(`Tem certeza que deseja excluir o profissional "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
                    atualizarTokens();
                    document.getElementById('id_excluir').value = id;
                    document.getElementById('formExcluir').submit();
                }
            };
            
            // Reset do modal ao fechar
            const modalElement = document.getElementById('modalProfissional');
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('formProfissional').reset();
                    document.getElementById('acao').value = 'cadastrar';
                    document.getElementById('id_profissional').value = '';
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus me-2"></i>Novo Profissional';
                    document.getElementById('btnText').innerHTML = '<i class="fas fa-save me-2"></i>Cadastrar';
                    document.getElementById('ativo').checked = true;
                    atualizarTokens();
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
                    
                    // Atualizar token antes de enviar
                    atualizarTokens();
                });
            }
            
            console.log('Modal inicializado com sucesso - Produção');
        });
    </script>
</body>
</html>