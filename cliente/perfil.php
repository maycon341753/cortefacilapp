<?php
/**
 * Página de Perfil do Cliente
 * Permite visualizar e editar dados pessoais
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/usuario.php';

// Verificar se é cliente
requireCliente();

$usuario_logado = getLoggedUser();
$usuario = new Usuario();

$erro = '';
$sucesso = '';

// Processar atualização do perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_perfil'])) {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        
        if (empty($nome)) {
            throw new Exception('Nome é obrigatório.');
        }
        
        if (empty($email)) {
            throw new Exception('E-mail é obrigatório.');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido.');
        }
        
        if (empty($telefone)) {
            throw new Exception('Telefone é obrigatório.');
        }
        
        // Verificar se o e-mail já existe (exceto o próprio usuário)
        if ($email !== $usuario_logado['email'] && $usuario->emailExiste($email)) {
            throw new Exception('Este e-mail já está sendo usado por outro usuário.');
        }
        
        // Atualizar dados
        $dados = [
            'nome' => $nome,
            'email' => $email,
            'telefone' => formatarTelefone($telefone)
        ];
        
        $resultado = $usuario->atualizar($usuario_logado['id'], $dados);
        
        if ($resultado) {
            $sucesso = 'Perfil atualizado com sucesso!';
            // Atualizar sessão
            $_SESSION['usuario'] = array_merge($_SESSION['usuario'], $dados);
            $usuario_logado = $_SESSION['usuario'];
            
            // Log da atividade
            logActivity($usuario_logado['id'], 'perfil_atualizado', 'Cliente atualizou dados do perfil');
        } else {
            throw new Exception('Erro ao atualizar perfil.');
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        
        if (empty($senha_atual)) {
            throw new Exception('Senha atual é obrigatória.');
        }
        
        if (empty($nova_senha)) {
            throw new Exception('Nova senha é obrigatória.');
        }
        
        if (strlen($nova_senha) < 6) {
            throw new Exception('Nova senha deve ter pelo menos 6 caracteres.');
        }
        
        if ($nova_senha !== $confirmar_senha) {
            throw new Exception('Confirmação de senha não confere.');
        }
        
        // Verificar senha atual
        if (!password_verify($senha_atual, $usuario_logado['senha'])) {
            throw new Exception('Senha atual incorreta.');
        }
        
        // Atualizar senha
        $dados = [
            'senha' => password_hash($nova_senha, PASSWORD_DEFAULT)
        ];
        
        $resultado = $usuario->atualizar($usuario_logado['id'], $dados);
        
        if ($resultado) {
            $sucesso = 'Senha alterada com sucesso!';
            // Log da atividade
            logActivity($usuario_logado['id'], 'senha_alterada', 'Cliente alterou a senha');
        } else {
            throw new Exception('Erro ao alterar senha.');
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - CorteFácil</title>
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
                            <a class="nav-link" href="agendamentos.php">
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
                            <a class="nav-link active" href="perfil.php">
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
                            <i class="fas fa-user me-2 text-primary"></i>
                            Meu Perfil
                        </h1>
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
                
                <div class="row">
                    <!-- Informações do Perfil -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-edit me-2"></i>
                                    Dados Pessoais
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php echo generateCsrfToken(); ?>
                                    <input type="hidden" name="atualizar_perfil" value="1">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nome" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="nome" name="nome" 
                                                   value="<?php echo htmlspecialchars($usuario_logado['nome']); ?>" 
                                                   required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">E-mail *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($usuario_logado['email']); ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="telefone" class="form-label">Telefone *</label>
                                            <input type="tel" class="form-control" id="telefone" name="telefone" 
                                                   value="<?php echo htmlspecialchars($usuario_logado['telefone']); ?>" 
                                                   data-mask="(00) 00000-0000" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="tipo_usuario" class="form-label">Tipo de Conta</label>
                                            <input type="text" class="form-control" id="tipo_usuario" 
                                                   value="<?php echo ucfirst($usuario_logado['tipo_usuario']); ?>" 
                                                   readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Alterar Senha -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-lock me-2"></i>
                                    Alterar Senha
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formAlterarSenha">
                                    <?php echo generateCsrfToken(); ?>
                                    <input type="hidden" name="alterar_senha" value="1">
                                    
                                    <div class="mb-3">
                                        <label for="senha_atual" class="form-label">Senha Atual *</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                            <button class="btn btn-outline-secondary" type="button" 
                                                    onclick="togglePassword('senha_atual')">
                                                <i class="fas fa-eye" id="senha_atual_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nova_senha" class="form-label">Nova Senha *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" 
                                                       minlength="6" required>
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        onclick="togglePassword('nova_senha')">
                                                    <i class="fas fa-eye" id="nova_senha_icon"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Mínimo de 6 caracteres</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                                       minlength="6" required>
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        onclick="togglePassword('confirmar_senha')">
                                                    <i class="fas fa-eye" id="confirmar_senha_icon"></i>
                                                </button>
                                            </div>
                                            <div id="senha_feedback" class="form-text"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key me-2"></i>
                                            Alterar Senha
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações da Conta -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações da Conta
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                                        <i class="fas fa-user fa-2x"></i>
                                    </div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($usuario_logado['nome']); ?></h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($usuario_logado['email']); ?></p>
                                    <span class="badge bg-primary mt-2">
                                        <?php echo ucfirst($usuario_logado['tipo_usuario']); ?>
                                    </span>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Membro desde:</span>
                                        <span class="fw-bold">
                                            <?php echo formatarData($usuario_logado['created_at'] ?? date('Y-m-d')); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Status:</span>
                                        <span class="badge bg-success">Ativo</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">ID do Cliente:</span>
                                        <span class="fw-bold">#<?php echo str_pad($usuario_logado['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ações Rápidas -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>
                                    Ações Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="agendar.php" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus me-2"></i>
                                        Novo Agendamento
                                    </a>
                                    
                                    <a href="agendamentos.php" class="btn btn-outline-primary">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Ver Agendamentos
                                    </a>
                                    
                                    <a href="saloes.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-store me-2"></i>
                                        Explorar Salões
                                    </a>
                                    
                                    <hr>
                                    
                                    <a href="../logout.php" class="btn btn-outline-danger" 
                                       onclick="return confirm('Tem certeza que deseja sair?')">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Sair da Conta
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Função para alternar visibilidade da senha
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Validação em tempo real da confirmação de senha
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = this.value;
            const feedback = document.getElementById('senha_feedback');
            
            if (confirmarSenha.length > 0) {
                if (novaSenha === confirmarSenha) {
                    feedback.textContent = 'Senhas conferem ✓';
                    feedback.className = 'form-text text-success';
                } else {
                    feedback.textContent = 'Senhas não conferem ✗';
                    feedback.className = 'form-text text-danger';
                }
            } else {
                feedback.textContent = '';
                feedback.className = 'form-text';
            }
        });
        
        // Validação do formulário de alteração de senha
        document.getElementById('formAlterarSenha').addEventListener('submit', function(e) {
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            
            if (novaSenha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não conferem. Verifique e tente novamente.');
                return false;
            }
            
            if (novaSenha.length < 6) {
                e.preventDefault();
                alert('A nova senha deve ter pelo menos 6 caracteres.');
                return false;
            }
        });
    </script>
    
    <style>
        .avatar-lg {
            width: 80px;
            height: 80px;
        }
    </style>
</body>
</html>