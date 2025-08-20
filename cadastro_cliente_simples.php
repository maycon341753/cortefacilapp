<?php
/**
 * Versão simplificada do cadastro de clientes para debug
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir dependências básicas
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
$tipo_usuario = 'cliente';

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizeInput($_POST['nome'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telefone = sanitizeInput($_POST['telefone'] ?? '');
    $cpf = sanitizeInput($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($cpf) || empty($senha)) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } elseif (!validarCPF($cpf)) {
        $erro = 'CPF inválido.';
    } else {
        try {
            $usuario = new Usuario();
            
            // Verificar se email já existe
            if ($usuario->emailExiste($email)) {
                $erro = 'Este email já está cadastrado.';
            } elseif ($usuario->cpfExiste($cpf)) {
                $erro = 'Este CPF já está cadastrado.';
            } else {
                // Dados do usuário
                $dadosUsuario = [
                    'nome' => $nome,
                    'email' => $email,
                    'telefone' => $telefone,
                    'cpf' => $cpf,
                    'senha' => $senha,
                    'tipo_usuario' => 'cliente'
                ];
                
                // Cadastrar usuário
                if ($usuario->cadastrar($dadosUsuario)) {
                    $sucesso = 'Cadastro realizado com sucesso! Você já pode fazer login.';
                } else {
                    $erro = 'Erro ao realizar cadastro. Tente novamente.';
                }
            }
        } catch (Exception $e) {
            $erro = 'Erro interno: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Cadastro de Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary">
                                <i class="fas fa-cut me-2"></i>
                                CorteFácil
                            </h2>
                            <p class="text-muted">Cadastre-se como Cliente</p>
                        </div>
                        
                        <?php if ($erro): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $erro; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($sucesso): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $sucesso; ?>
                                <br><br>
                                <a href="login.php" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Fazer Login
                                </a>
                            </div>
                        <?php else: ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nome Completo
                                </label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($nome ?? ''); ?>" 
                                       placeholder="Digite seu nome completo" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="Digite seu email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefone" class="form-label">
                                    <i class="fas fa-phone me-1"></i>
                                    Telefone
                                </label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" 
                                       value="<?php echo htmlspecialchars($telefone ?? ''); ?>" 
                                       placeholder="(11) 99999-9999" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="cpf" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>
                                    CPF
                                </label>
                                <input type="text" class="form-control" id="cpf" name="cpf" 
                                       value="<?php echo htmlspecialchars($cpf ?? ''); ?>" 
                                       placeholder="000.000.000-00" required>
                                <div class="form-text">
                                    <small>Digite apenas os números do CPF</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Senha
                                </label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       placeholder="Digite sua senha" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirmar_senha" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Confirmar Senha
                                </label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                       placeholder="Confirme sua senha" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>
                                Cadastrar
                            </button>
                        </form>
                        
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <p class="mb-2">Já tem uma conta?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Fazer Login
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="cadastro.php?tipo=parceiro" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-store me-2"></i>
                                Cadastrar como Parceiro
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            if (valor.length > 10) {
                valor = valor.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (valor.length > 6) {
                valor = valor.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (valor.length > 2) {
                valor = valor.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            this.value = valor;
        });
        
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            if (valor.length > 9) {
                valor = valor.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (valor.length > 6) {
                valor = valor.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
            } else if (valor.length > 3) {
                valor = valor.replace(/(\d{3})(\d{0,3})/, '$1.$2');
            }
            this.value = valor;
        });
    </script>
</body>
</html>