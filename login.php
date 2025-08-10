<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/includes/auth.php';

$erro = '';
$sucesso = '';

// Verificar se usuário já está logado
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['tipo_usuario'] === 'cliente') {
        header('Location: cliente/dashboard.php');
        exit();
    } elseif ($_SESSION['tipo_usuario'] === 'parceiro') {
        header('Location: parceiro/dashboard.php');
        exit();
    }
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizar($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (!validarEmail($email)) {
        $erro = 'Email inválido.';
    } else {
        // Conectar ao banco
        $database = new Database();
        $db = $database->getConnection();
        
        // Criar objeto usuário
        $usuario = new Usuario($db);
        $usuario->email = $email;
        $usuario->senha = $senha;
        
        // Tentar fazer login
        if ($usuario->login()) {
            // Definir sessões
            $_SESSION['usuario_id'] = $usuario->id;
            $_SESSION['usuario_nome'] = $usuario->nome;
            $_SESSION['usuario_email'] = $usuario->email;
            $_SESSION['tipo_usuario'] = $usuario->tipo_usuario;
            $_SESSION['usuario_telefone'] = $usuario->telefone;
            
            registrarLog("Login realizado", "Email: $email");
            
            // Redirecionar baseado no tipo de usuário
            if ($usuario->tipo_usuario === 'cliente') {
                header('Location: cliente/dashboard.php');
                exit();
            } elseif ($usuario->tipo_usuario === 'parceiro') {
                header('Location: parceiro/dashboard.php');
                exit();
            } elseif ($usuario->tipo_usuario === 'admin') {
                header('Location: admin/dashboard.php');
                exit();
            }
        } else {
            $erro = 'Email ou senha incorretos.';
            registrarLog("Tentativa de login falhada", "Email: $email");
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="auth-logo">
                    <span class="logo-icon">✂️</span>
                    CorteFácil
                </a>
                <h1>Entrar na sua conta</h1>
                <p>Bem-vindo de volta! Faça login para continuar.</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-error">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <?php echo $sucesso; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        placeholder="seu@email.com"
                    >
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        required 
                        placeholder="Sua senha"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Entrar
                </button>
            </form>

            <div class="auth-divider">
                <span>ou</span>
            </div>

            <div class="quick-login">
                <h3>Login Rápido para Demonstração</h3>
                <div class="quick-login-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="email" value="maria@email.com">
                        <input type="hidden" name="senha" value="123456">
                        <button type="submit" class="btn btn-outline btn-small">
                            👤 Entrar como Cliente
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="email" value="joao@email.com">
                        <input type="hidden" name="senha" value="123456">
                        <button type="submit" class="btn btn-outline btn-small">
                            💼 Entrar como Parceiro
                        </button>
                    </form>
                </div>
            </div>

            <div class="auth-footer">
                <p>Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
                <p><a href="index.php">← Voltar ao início</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>