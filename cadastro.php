<?php
/**
 * Página de Cadastro
 * Registro de novos usuários (clientes e parceiros)
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/usuario.php';

// Se já está logado, redireciona
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$erro = '';
$sucesso = '';
$tipo_usuario = $_GET['tipo'] ?? 'cliente';

// Validar tipo de usuário
if (!in_array($tipo_usuario, ['cliente', 'parceiro'])) {
    $tipo_usuario = 'cliente';
}

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizeInput($_POST['nome'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $tipo_usuario = sanitizeInput($_POST['tipo_usuario'] ?? 'cliente');
    
    // Validações
    if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (!validateEmail($email)) {
        $erro = 'Digite um email válido.';
    } elseif (!validateTelefone($telefone)) {
        $erro = 'Digite um telefone válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $usuario = new Usuario();
        
        // Verificar se email já existe
        if ($usuario->emailExiste($email)) {
            $erro = 'Este email já está cadastrado.';
        } else {
            // Limpar telefone
            $telefone = preg_replace('/[^0-9]/', '', $telefone);
            
            $dados = [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'senha' => $senha,
                'tipo_usuario' => $tipo_usuario
            ];
            
            if ($usuario->cadastrar($dados)) {
                setFlashMessage('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
                header('Location: login.php');
                exit();
            } else {
                $erro = 'Erro ao realizar cadastro. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-cut me-2"></i>
                            CorteFácil
                        </h3>
                        <p class="text-white-50 mb-0">
                            <?php if ($tipo_usuario === 'cliente'): ?>
                                Cadastre-se como Cliente
                            <?php else: ?>
                                Cadastre-se como Parceiro
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($erro): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($erro); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Seletor de tipo de usuário -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <a href="cadastro.php?tipo=cliente" 
                                   class="btn <?php echo $tipo_usuario === 'cliente' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <i class="fas fa-user me-2"></i>
                                    Cliente
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="cadastro.php?tipo=parceiro" 
                                   class="btn <?php echo $tipo_usuario === 'parceiro' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <i class="fas fa-store me-2"></i>
                                    Parceiro
                                </a>
                            </div>
                        </div>
                        
                        <form method="POST" action="cadastro.php">
                            <input type="hidden" name="tipo_usuario" value="<?php echo $tipo_usuario; ?>">
                            
                            <div class="form-group mb-3">
                                <label for="nome" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nome Completo
                                </label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($nome ?? ''); ?>" 
                                       placeholder="Digite seu nome completo" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="Digite seu email" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="telefone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>
                                    Telefone
                                </label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" 
                                       value="<?php echo htmlspecialchars($telefone ?? ''); ?>" 
                                       placeholder="(11) 99999-9999" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="senha" class="form-label">
                                            <i class="fas fa-lock me-1"></i>
                                            Senha
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="senha" name="senha" 
                                                   placeholder="Mínimo 6 caracteres" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="confirmar_senha" class="form-label">
                                            <i class="fas fa-lock me-1"></i>
                                            Confirmar Senha
                                        </label>
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                               placeholder="Confirme sua senha" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="termos" required>
                                <label class="form-check-label" for="termos">
                                    Eu concordo com os <a href="#" class="text-primary">Termos de Uso</a> 
                                    e <a href="#" class="text-primary">Política de Privacidade</a>
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Criar Conta
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Já tem uma conta?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Fazer Login
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Voltar ao início
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Informações sobre o tipo de conta -->
                <div class="card mt-3">
                    <div class="card-body">
                        <?php if ($tipo_usuario === 'cliente'): ?>
                            <h6 class="card-title">
                                <i class="fas fa-info-circle me-2 text-primary"></i>
                                Conta de Cliente
                            </h6>
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li><i class="fas fa-check text-success me-2"></i>Agende serviços em salões parceiros</li>
                                <li><i class="fas fa-check text-success me-2"></i>Pague apenas R$ 1,29 por agendamento</li>
                                <li><i class="fas fa-check text-success me-2"></i>Histórico completo de agendamentos</li>
                                <li><i class="fas fa-check text-success me-2"></i>Notificações de confirmação</li>
                            </ul>
                        <?php else: ?>
                            <h6 class="card-title">
                                <i class="fas fa-info-circle me-2 text-primary"></i>
                                Conta de Parceiro
                            </h6>
                            <ul class="list-unstyled mb-0 small text-muted">
                                <li><i class="fas fa-check text-success me-2"></i>Cadastre seu salão gratuitamente</li>
                                <li><i class="fas fa-check text-success me-2"></i>Gerencie profissionais e horários</li>
                                <li><i class="fas fa-check text-success me-2"></i>Receba agendamentos automaticamente</li>
                                <li><i class="fas fa-check text-success me-2"></i>Sem mensalidades ou taxas</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Toggle mostrar/ocultar senha
        document.getElementById('toggleSenha').addEventListener('click', function() {
            const senhaInput = document.getElementById('senha');
            const icon = this.querySelector('i');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                senhaInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Validação de confirmação de senha
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = this.value;
            
            if (confirmarSenha && senha !== confirmarSenha) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirmarSenha) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }
        });
        
        // Focar no primeiro campo
        document.getElementById('nome').focus();
    </script>
</body>
</html>