<?php
/**
 * Página de Cadastro
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

// Determina o tipo de usuário baseado no parâmetro GET
$tipoUsuario = $_GET['tipo'] ?? 'cliente';
if (!in_array($tipoUsuario, ['cliente', 'parceiro'])) {
    $tipoUsuario = 'cliente';
}

// Processa o cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    $tipo = $_POST['tipo_usuario'] ?? 'cliente';
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos obrigatórios';
    } elseif ($senha !== $confirmarSenha) {
        $error = 'As senhas não coincidem';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } else {
        $result = $auth->register($nome, $email, $senha, $tipo, $telefone);
        
        if ($result['success']) {
            $success = $result['message'] . ' Você pode fazer login agora.';
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
    <title>Cadastro - CorteFácil</title>
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
                        <li><a href="login.php">Entrar</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2 class="card-title">
                            Cadastrar <?php echo $tipoUsuario === 'cliente' ? 'Cliente' : 'Salão Parceiro'; ?>
                        </h2>
                        <p>
                            <?php if ($tipoUsuario === 'cliente'): ?>
                                Crie sua conta para agendar serviços de beleza
                            <?php else: ?>
                                Cadastre seu salão na nossa plataforma
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Seletor de tipo de usuário -->
                    <div class="text-center mb-4">
                        <div class="btn-group">
                            <a href="register.php?tipo=cliente" 
                               class="btn <?php echo $tipoUsuario === 'cliente' ? 'btn-primary' : 'btn-outline'; ?>">
                                Sou Cliente
                            </a>
                            <a href="register.php?tipo=parceiro" 
                               class="btn <?php echo $tipoUsuario === 'parceiro' ? 'btn-primary' : 'btn-outline'; ?>">
                                Tenho um Salão
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br><a href="login.php" class="btn btn-primary mt-2">Fazer Login</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$success): ?>
                    <form method="POST" data-validate>
                        <input type="hidden" name="tipo_usuario" value="<?php echo $tipoUsuario; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome" class="form-label">
                                        <?php echo $tipoUsuario === 'cliente' ? 'Nome Completo' : 'Nome do Responsável'; ?> *
                                    </label>
                                    <input 
                                        type="text" 
                                        id="nome" 
                                        name="nome" 
                                        class="form-control" 
                                        required
                                        value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>"
                                        placeholder="Digite seu nome completo"
                                    >
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
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
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input 
                                        type="tel" 
                                        id="telefone" 
                                        name="telefone" 
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"
                                        placeholder="(11) 99999-9999"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="senha" class="form-label">Senha *</label>
                                    <input 
                                        type="password" 
                                        id="senha" 
                                        name="senha" 
                                        class="form-control" 
                                        required
                                        placeholder="Mínimo 6 caracteres"
                                    >
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirmar_senha" class="form-label">Confirmar Senha *</label>
                                    <input 
                                        type="password" 
                                        id="confirmar_senha" 
                                        name="confirmar_senha" 
                                        class="form-control" 
                                        required
                                        placeholder="Digite a senha novamente"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações específicas por tipo -->
                        <?php if ($tipoUsuario === 'cliente'): ?>
                            <div class="alert alert-info">
                                <h5>Informações para Clientes:</h5>
                                <ul class="mb-0">
                                    <li>Cadastro gratuito</li>
                                    <li>Pague apenas R$ 1,29 por agendamento</li>
                                    <li>Acesso a centenas de salões parceiros</li>
                                    <li>Histórico completo de agendamentos</li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <h5>Vantagens para Salões Parceiros:</h5>
                                <ul class="mb-0">
                                    <li>Cadastro e uso totalmente gratuitos</li>
                                    <li>Sem mensalidade ou taxas fixas</li>
                                    <li>Gerencie profissionais e horários</li>
                                    <li>Aumente sua visibilidade</li>
                                    <li>Sistema anti-conflito de horários</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary w-100">
                                Cadastrar <?php echo $tipoUsuario === 'cliente' ? 'Cliente' : 'Salão'; ?>
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
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
    
    <style>
        .btn-group {
            display: flex;
            gap: 0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .btn-group .btn {
            border-radius: 0;
            border-right: 1px solid #ddd;
        }
        
        .btn-group .btn:last-child {
            border-right: none;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
        }
    </style>
</body>
</html>