<?php
/**
 * Página de Login - CorteFácil
 * Versão corrigida para produção - Proteção contra erro 500
 * Corrigido em 2025-08-21
 */

// Configurações de erro para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão de forma segura
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

try {
    // Incluir arquivos necessários de forma segura
    $auth_file = __DIR__ . '/includes/auth.php';
    $functions_file = __DIR__ . '/includes/functions.php';
    $usuario_file = __DIR__ . '/models/usuario.php';
    
    if (!file_exists($auth_file)) {
        throw new Exception('Arquivo de autenticação não encontrado');
    }
    if (!file_exists($functions_file)) {
        throw new Exception('Arquivo de funções não encontrado');
    }
    if (!file_exists($usuario_file)) {
        throw new Exception('Arquivo de modelo de usuário não encontrado');
    }
    
    require_once $auth_file;
    require_once $functions_file;
    require_once $usuario_file;
    
    // Se já está logado, redireciona
    if (function_exists('isLoggedIn') && isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
    
    $erro = '';
    $sucesso = '';

    // Processar login com proteção contra erros
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
            $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
            
            if (empty($email) || empty($senha)) {
                $erro = 'Por favor, preencha todos os campos.';
            } elseif (!function_exists('validateEmail') || !validateEmail($email)) {
                $erro = 'Digite um email válido.';
            } else {
                // Verificar se a classe Usuario existe
                if (!class_exists('Usuario')) {
                    throw new Exception('Classe Usuario não encontrada');
                }
                
                $usuario = new Usuario();
                $dadosUsuario = $usuario->login($email, $senha);
                
                if ($dadosUsuario && is_array($dadosUsuario)) {
                    // Verificar se a função login existe
                    if (!function_exists('login')) {
                        throw new Exception('Função de login não encontrada');
                    }
                    
                    login($dadosUsuario);
                    
                    // Redirecionar baseado no tipo de usuário
                    $tipo_usuario = isset($dadosUsuario['tipo_usuario']) ? $dadosUsuario['tipo_usuario'] : 'cliente';
                    
                    switch ($tipo_usuario) {
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
        } catch (Exception $e) {
            error_log('Erro no login: ' . $e->getMessage());
            $erro = 'Erro interno. Tente novamente.';
        }
    }

    // Verificar mensagem flash com proteção
    if (function_exists('getFlashMessage')) {
        $flash = getFlashMessage();
        if ($flash && is_array($flash)) {
            if (isset($flash['type']) && $flash['type'] === 'success' && isset($flash['message'])) {
                $sucesso = $flash['message'];
            } elseif (isset($flash['message'])) {
                $erro = $flash['message'];
            }
        }
    }
    
} catch (Exception $e) {
    // Log do erro e redirecionamento para página de erro
    error_log('Erro crítico na página de login: ' . $e->getMessage());
    
    // Verificar se existe página de erro personalizada
    $error_page = __DIR__ . '/erro_500_amigavel.php';
    if (file_exists($error_page)) {
        header('Location: erro_500_amigavel.php');
        exit();
    }
    
    // Fallback para erro genérico
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Erro</title></head><body><h1>Erro interno</h1><p>Tente novamente mais tarde.</p></body></html>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CorteFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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