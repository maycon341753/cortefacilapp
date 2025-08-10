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

// Determinar tipo de usuário baseado no parâmetro GET
$tipo_usuario = isset($_GET['tipo']) && in_array($_GET['tipo'], ['cliente', 'parceiro']) ? $_GET['tipo'] : 'cliente';

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizar($_POST['nome']);
    $email = sanitizar($_POST['email']);
    $telefone = sanitizar($_POST['telefone']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo = sanitizar($_POST['tipo_usuario']);
    
    // Validações
    if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } elseif (!validarEmail($email)) {
        $erro = 'Email inválido.';
    } elseif (!validarTelefone($telefone)) {
        $erro = 'Telefone inválido. Use o formato (11) 99999-9999.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } elseif (!in_array($tipo, ['cliente', 'parceiro'])) {
        $erro = 'Tipo de usuário inválido.';
    } else {
        // Conectar ao banco
        $database = new Database();
        $db = $database->getConnection();
        
        // Criar objeto usuário
        $usuario = new Usuario($db);
        $usuario->email = $email;
        
        // Verificar se email já existe
        if ($usuario->emailExiste()) {
            $erro = 'Este email já está cadastrado.';
        } else {
            // Criar novo usuário
            $usuario->nome = $nome;
            $usuario->telefone = $telefone;
            $usuario->senha = $senha;
            $usuario->tipo_usuario = $tipo;
            
            if ($usuario->criar()) {
                $sucesso = 'Cadastro realizado com sucesso! Você pode fazer login agora.';
                registrarLog("Novo usuário cadastrado", "Email: $email, Tipo: $tipo");
                
                // Limpar campos
                $nome = $email = $telefone = '';
            } else {
                $erro = 'Erro ao criar conta. Tente novamente.';
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
                <h1>Criar sua conta</h1>
                <p>
                    <?php if ($tipo_usuario === 'parceiro'): ?>
                        Cadastre seu salão e comece a receber novos clientes.
                    <?php else: ?>
                        Encontre e agende com os melhores profissionais.
                    <?php endif; ?>
                </p>
            </div>

            <div class="user-type-selector">
                <a href="register.php?tipo=cliente" class="type-option <?php echo $tipo_usuario === 'cliente' ? 'active' : ''; ?>">
                    <span class="type-icon">👤</span>
                    <span>Sou Cliente</span>
                </a>
                <a href="register.php?tipo=parceiro" class="type-option <?php echo $tipo_usuario === 'parceiro' ? 'active' : ''; ?>">
                    <span class="type-icon">💼</span>
                    <span>Tenho um Salão</span>
                </a>
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
                <input type="hidden" name="tipo_usuario" value="<?php echo $tipo_usuario; ?>">
                
                <div class="form-group">
                    <label for="nome">
                        <?php echo $tipo_usuario === 'parceiro' ? 'Nome do Responsável' : 'Nome Completo'; ?>
                    </label>
                    <input 
                        type="text" 
                        id="nome" 
                        name="nome" 
                        required 
                        value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
                        placeholder="<?php echo $tipo_usuario === 'parceiro' ? 'Nome do responsável pelo salão' : 'Seu nome completo'; ?>"
                    >
                </div>

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
                    <label for="telefone">Telefone</label>
                    <input 
                        type="tel" 
                        id="telefone" 
                        name="telefone" 
                        required 
                        value="<?php echo isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : ''; ?>"
                        placeholder="(11) 99999-9999"
                        data-mask="(00) 00000-0000"
                    >
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        required 
                        placeholder="Mínimo 6 caracteres"
                        minlength="6"
                    >
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input 
                        type="password" 
                        id="confirmar_senha" 
                        name="confirmar_senha" 
                        required 
                        placeholder="Digite a senha novamente"
                        minlength="6"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <?php echo $tipo_usuario === 'parceiro' ? 'Cadastrar Salão' : 'Criar Conta'; ?>
                </button>
            </form>

            <div class="auth-footer">
                <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
                <p><a href="index.php">← Voltar ao início</a></p>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
            }
        });

        // Validação de senhas
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = this.value;
            
            if (senha !== confirmarSenha) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>