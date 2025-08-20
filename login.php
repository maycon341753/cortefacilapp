<?php
/**
 * Página de Login
 * Autenticação de usuários do sistema
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

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (!validateEmail($email)) {
        $erro = 'Digite um email válido.';
    } else {
        $usuario = new Usuario();
        $dadosUsuario = $usuario->login($email, $senha);
        
        if ($dadosUsuario) {
            login($dadosUsuario);
            
            // Redirecionar baseado no tipo de usuário
            switch ($dadosUsuario['tipo_usuario']) {
                case 'cliente':
                    header('Location: cliente/dashboard.php');
                    break;
                case 'parceiro':
                    header('Location: parceiro/dashboard.php');
                    break;
                case 'admin':
                    header('Location: admin/dashboard.php');
                    break;
                default:
                    header('Location: index.php');
                    break;
            }
            exit();
        } else {
            $erro = 'Email ou senha incorretos.';
        }
    }
}

// Verificar mensagem flash
$flash = getFlashMessage();
if ($flash) {
    if ($flash['type'] === 'success') {
        $sucesso = $flash['message'];
    } else {
        $erro = $flash['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-cut me-2"></i>
                            CorteFácil
                        </h3>
                        <p class="text-white-50 mb-0">Faça login em sua conta</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($erro): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($erro); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($sucesso): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($sucesso); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="Digite seu email" required>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="senha" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Senha
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="senha" name="senha" 
                                           placeholder="Digite sua senha" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Entrar
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Ainda não tem uma conta?</p>
                            <a href="cadastro.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>
                                Criar Conta
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
                

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
        
        // Focar no primeiro campo
        document.getElementById('email').focus();
    </script>
</body>
</html>