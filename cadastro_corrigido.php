<?php
/**
 * Página de Cadastro - Versão Corrigida
 * Registro de novos usuários (clientes e parceiros)
 */

// Ativar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log de debug
error_log("[CADASTRO] Iniciando página de cadastro");

try {
    require_once __DIR__ . '/includes/auth.php';
    error_log("[CADASTRO] Auth carregado");
    
    require_once __DIR__ . '/includes/functions.php';
    error_log("[CADASTRO] Functions carregado");
    
    require_once __DIR__ . '/models/usuario.php';
    error_log("[CADASTRO] Usuario carregado");
    
} catch (Exception $e) {
    error_log("[CADASTRO] Erro ao carregar dependências: " . $e->getMessage());
    die("Erro ao carregar sistema. Tente novamente.");
}

// Verificar se já está logado
try {
    if (isLoggedIn()) {
        error_log("[CADASTRO] Usuário já logado, redirecionando");
        header('Location: index.php');
        exit();
    }
    error_log("[CADASTRO] Usuário não logado, continuando");
} catch (Exception $e) {
    error_log("[CADASTRO] Erro ao verificar login: " . $e->getMessage());
    // Continuar mesmo com erro
}

$erro = '';
$sucesso = '';
$tipo_usuario = $_GET['tipo'] ?? 'cliente';

// Validar tipo de usuário
if (!in_array($tipo_usuario, ['cliente', 'parceiro'])) {
    $tipo_usuario = 'cliente';
}

error_log("[CADASTRO] Tipo de usuário: " . $tipo_usuario);

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("[CADASTRO] Processando POST");
    
    try {
        $nome = sanitizeInput($_POST['nome'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $telefone = sanitizeInput($_POST['telefone'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        $tipo_usuario = sanitizeInput($_POST['tipo_usuario'] ?? 'cliente');
        
        error_log("[CADASTRO] Dados básicos coletados para: " . $email);
        
        // Campos específicos por tipo de usuário
        $cpf = '';
        $documento = '';
        $tipo_documento = '';
        $razao_social = '';
        $inscricao_estadual = '';
        $endereco = '';
        $bairro = '';
        $cidade = '';
        $cep = '';
        
        if ($tipo_usuario === 'cliente') {
            $cpf = sanitizeInput($_POST['cpf'] ?? '');
            error_log("[CADASTRO] CPF coletado: " . $cpf);
        } else {
            $documento = sanitizeInput($_POST['documento'] ?? '');
            $tipo_documento = sanitizeInput($_POST['tipo_documento'] ?? '');
            $razao_social = sanitizeInput($_POST['razao_social'] ?? '');
            $inscricao_estadual = sanitizeInput($_POST['inscricao_estadual'] ?? '');
            $endereco = sanitizeInput($_POST['endereco'] ?? '');
            $bairro = sanitizeInput($_POST['bairro'] ?? '');
            $cidade = sanitizeInput($_POST['cidade'] ?? '');
            $cep = sanitizeInput($_POST['cep'] ?? '');
            error_log("[CADASTRO] Dados de parceiro coletados");
        }
        
        // Validações básicas
        if (empty($nome) || empty($email) || empty($senha)) {
            $erro = 'Nome, email e senha são obrigatórios.';
            error_log("[CADASTRO] Erro: campos obrigatórios");
        } elseif ($senha !== $confirmar_senha) {
            $erro = 'As senhas não coincidem.';
            error_log("[CADASTRO] Erro: senhas não coincidem");
        } elseif ($tipo_usuario === 'cliente' && !validarCPF($cpf)) {
            $erro = 'CPF inválido. <a href="ajuda_cadastro.php" target="_blank">Ver exemplos de CPFs válidos</a>';
            error_log("[CADASTRO] Erro: CPF inválido - " . $cpf);
        } elseif ($tipo_usuario === 'parceiro' && empty($documento)) {
            $erro = 'Documento (CPF/CNPJ) é obrigatório para parceiros.';
            error_log("[CADASTRO] Erro: documento obrigatório para parceiro");
        } elseif ($tipo_usuario === 'parceiro' && !validarDocumento($documento, $tipo_documento)) {
            $erro = 'Documento inválido. <a href="ajuda_cadastro.php" target="_blank">Ver exemplos</a>';
            error_log("[CADASTRO] Erro: documento inválido - " . $documento);
        } else {
            error_log("[CADASTRO] Validações básicas passaram");
            
            try {
                $usuario = new Usuario();
                error_log("[CADASTRO] Objeto Usuario criado");
                
                // Verificar duplicatas
                if ($usuario->emailExiste($email)) {
                    $erro = 'Este email já está cadastrado. <a href="login.php">Fazer login</a>';
                    error_log("[CADASTRO] Erro: email já existe - " . $email);
                } elseif ($tipo_usuario === 'cliente' && $usuario->cpfExiste($cpf)) {
                    $erro = 'Este CPF já está cadastrado.';
                    error_log("[CADASTRO] Erro: CPF já existe - " . $cpf);
                } elseif ($tipo_usuario === 'parceiro' && $usuario->documentoSalaoExiste($documento)) {
                    $erro = 'Este documento já está cadastrado.';
                    error_log("[CADASTRO] Erro: documento já existe - " . $documento);
                } else {
                    error_log("[CADASTRO] Verificações de duplicata passaram");
                    
                    // Preparar dados para cadastro
                    $dados = [
                        'nome' => $nome,
                        'email' => $email,
                        'telefone' => $telefone,
                        'senha' => $senha,
                        'tipo_usuario' => $tipo_usuario
                    ];
                    
                    if ($tipo_usuario === 'cliente') {
                        $dados['cpf'] = $cpf;
                        error_log("[CADASTRO] Tentando cadastrar cliente");
                        
                        if ($usuario->cadastrar($dados)) {
                            error_log("[CADASTRO] Cliente cadastrado com sucesso");
                            setFlashMessage('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
                            header('Location: login.php');
                            exit();
                        } else {
                            $erro = 'Erro ao realizar cadastro. Tente novamente.';
                            error_log("[CADASTRO] Erro ao cadastrar cliente");
                        }
                    } else {
                        // Para parceiros
                        $dadosSalao = [
                            'nome' => $nome . ' - Salão',
                            'endereco' => $endereco,
                            'bairro' => $bairro,
                            'cidade' => $cidade,
                            'cep' => $cep,
                            'telefone' => $telefone,
                            'documento' => $documento,
                            'tipo_documento' => $tipo_documento,
                            'razao_social' => $razao_social,
                            'inscricao_estadual' => $inscricao_estadual,
                            'descricao' => 'Salão cadastrado via sistema'
                        ];
                        
                        error_log("[CADASTRO] Tentando cadastrar parceiro");
                        
                        if ($usuario->cadastrarParceiro($dados, $dadosSalao)) {
                            error_log("[CADASTRO] Parceiro cadastrado com sucesso");
                            setFlashMessage('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
                            header('Location: login.php');
                            exit();
                        } else {
                            $erro = 'Erro ao realizar cadastro. Tente novamente.';
                            error_log("[CADASTRO] Erro ao cadastrar parceiro");
                        }
                    }
                }
            } catch (Exception $e) {
                $erro = 'Erro interno: ' . $e->getMessage();
                error_log("[CADASTRO] Exceção durante cadastro: " . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        $erro = 'Erro ao processar dados: ' . $e->getMessage();
        error_log("[CADASTRO] Exceção ao processar POST: " . $e->getMessage());
    }
}

error_log("[CADASTRO] Iniciando renderização HTML");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary mb-2">
                                <i class="fas fa-cut me-2"></i>
                                CorteFácil
                            </h2>
                            <p class="text-muted">
                                Cadastre-se como <?php echo $tipo_usuario === 'cliente' ? 'Cliente' : 'Parceiro'; ?>
                            </p>
                        </div>
                        
                        <!-- Seletor de tipo -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <a href="cadastro_corrigido.php?tipo=cliente" 
                                   class="btn <?php echo $tipo_usuario === 'cliente' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <i class="fas fa-user me-2"></i>
                                    Cliente
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="cadastro_corrigido.php?tipo=parceiro" 
                                   class="btn <?php echo $tipo_usuario === 'parceiro' ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <i class="fas fa-store me-2"></i>
                                    Parceiro
                                </a>
                            </div>
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
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="cadastro_corrigido.php">
                            <input type="hidden" name="tipo_usuario" value="<?php echo $tipo_usuario; ?>">
                            
                            <!-- Campos comuns -->
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
                            
                            <?php if ($tipo_usuario === 'cliente'): ?>
                            <!-- Campos específicos para clientes -->
                            <div class="form-group mb-3">
                                <label for="cpf" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>
                                    CPF
                                </label>
                                <input type="text" class="form-control" id="cpf" name="cpf" 
                                       value="<?php echo htmlspecialchars($cpf ?? ''); ?>" 
                                       placeholder="000.000.000-00" required>
                                <div class="form-text">
                                    <small>Digite apenas os números do CPF. <a href="ajuda_cadastro.php" target="_blank">Ver exemplos</a></small>
                                </div>
                            </div>
                            
                            <?php else: ?>
                            <!-- Campos específicos para parceiros -->
                            <div class="form-group mb-3">
                                <label for="tipo_documento" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Tipo de Documento
                                </label>
                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="CPF" <?php echo ($tipo_documento ?? '') === 'CPF' ? 'selected' : ''; ?>>CPF</option>
                                    <option value="CNPJ" <?php echo ($tipo_documento ?? '') === 'CNPJ' ? 'selected' : ''; ?>>CNPJ</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="documento" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>
                                    CPF/CNPJ
                                </label>
                                <input type="text" class="form-control" id="documento" name="documento" 
                                       value="<?php echo htmlspecialchars($documento ?? ''); ?>" 
                                       placeholder="Digite o CPF ou CNPJ" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="endereco" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Endereço
                                </label>
                                <input type="text" class="form-control" id="endereco" name="endereco" 
                                       value="<?php echo htmlspecialchars($endereco ?? ''); ?>" 
                                       placeholder="Rua, número">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="bairro" class="form-label">Bairro</label>
                                        <input type="text" class="form-control" id="bairro" name="bairro" 
                                               value="<?php echo htmlspecialchars($bairro ?? ''); ?>" 
                                               placeholder="Bairro">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="cidade" class="form-label">Cidade</label>
                                        <input type="text" class="form-control" id="cidade" name="cidade" 
                                               value="<?php echo htmlspecialchars($cidade ?? ''); ?>" 
                                               placeholder="Cidade">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep" 
                                       value="<?php echo htmlspecialchars($cep ?? ''); ?>" 
                                       placeholder="00000-000">
                            </div>
                            <?php endif; ?>
                            
                            <!-- Campos de senha -->
                            <div class="form-group mb-3">
                                <label for="senha" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Senha
                                </label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       placeholder="Digite sua senha" required>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="confirmar_senha" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Confirmar Senha
                                </label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" 
                                       placeholder="Confirme sua senha" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>
                                Cadastrar <?php echo $tipo_usuario === 'cliente' ? 'Cliente' : 'Parceiro'; ?>
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-2">Já tem uma conta?</p>
                            <a href="login.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Fazer Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Máscaras de entrada
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
        
        <?php if ($tipo_usuario === 'cliente'): ?>
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
        <?php endif; ?>
    </script>
</body>
</html>
<?php
error_log("[CADASTRO] HTML renderizado com sucesso");
?>