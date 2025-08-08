<?php
/**
 * Página de Login
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once 'includes/auth.php';

// Se já estiver logado, redireciona
if ($auth->isLoggedIn()) {
    $userType = $_SESSION['user_type'];
    header("Location: $userType/dashboard.php");
    exit;
}

$error = '';
$success = '';

// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos';
    } else {
        $result = $auth->login($email, $senha);
        
        if ($result['success']) {
            header('Location: ' . $result['redirect']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CorteFácil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">CorteFácil</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php">Início</a></li>
                        <li><a href="register.php">Cadastrar</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2 class="card-title">Entrar na Conta</h2>
                        <p>Acesse seu painel de controle</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" data-validate>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                required
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                placeholder="seu@email.com"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="senha" class="form-label">Senha</label>
                            <input 
                                type="password" 
                                id="senha" 
                                name="senha" 
                                class="form-control" 
                                required
                                placeholder="Sua senha"
                            >
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
                    </div>
                    
                    <!-- Dados de teste -->
                    <div class="card mt-4" style="background-color: #f8f9fa;">
                        <div class="card-header">
                            <h4>Dados para Teste</h4>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Administrador</h5>
                                <p><strong>Email:</strong> admin@cortefacil.com<br>
                                <strong>Senha:</strong> password</p>
                            </div>
                            <div class="col-md-6">
                                <h5>Parceiro (Salão)</h5>
                                <p><strong>Email:</strong> joao@email.com<br>
                                <strong>Senha:</strong> password</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Cliente</h5>
                                <p><strong>Email:</strong> maria@email.com<br>
                                <strong>Senha:</strong> password</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="header mt-5">
        <div class="container">
            <div class="text-center">
                <p>&copy; 2024 CorteFácil - Sistema de Agendamentos</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>