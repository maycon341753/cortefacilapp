<?php
/**
 * Página de Cadastro
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
    } else {
        $documento = sanitizeInput($_POST['documento'] ?? '');
        $tipo_documento = sanitizeInput($_POST['tipo_documento'] ?? '');
        $razao_social = sanitizeInput($_POST['razao_social'] ?? '');
        $inscricao_estadual = sanitizeInput($_POST['inscricao_estadual'] ?? '');
        $endereco = sanitizeInput($_POST['endereco'] ?? '');
        $bairro = sanitizeInput($_POST['bairro'] ?? '');
        $cidade = sanitizeInput($_POST['cidade'] ?? '');
        $cep = sanitizeInput($_POST['cep'] ?? '');
    }
    
    // Validações básicas
    if (empty($nome) || empty($email) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (!validateEmail($email)) {
        $erro = 'Digite um email válido.';
    } elseif (!validateTelefone($telefone)) {
        $erro = 'Digite um telefone válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } 
    // Validações específicas por tipo
    elseif ($tipo_usuario === 'cliente' && empty($cpf)) {
        $erro = 'CPF é obrigatório para clientes.';
    } elseif ($tipo_usuario === 'cliente' && !validarCPF($cpf)) {
        $erro = 'CPF inválido. <a href="ajuda_cadastro.php" target="_blank">Ver exemplos de CPFs válidos</a>';
    } elseif ($tipo_usuario === 'parceiro' && (empty($documento) || empty($tipo_documento))) {
        $erro = 'Documento e tipo são obrigatórios para parceiros.';
    } elseif ($tipo_usuario === 'parceiro' && $tipo_documento === 'cpf' && !validarCPF($documento)) {
        $erro = 'CPF inválido. <a href="ajuda_cadastro.php" target="_blank">Ver exemplos de CPFs válidos</a>';
    } elseif ($tipo_usuario === 'parceiro' && $tipo_documento === 'cnpj' && !validarCNPJ($documento)) {
        $erro = 'CNPJ inválido. <a href="ajuda_cadastro.php" target="_blank">Ver ajuda para cadastro</a>';
    } elseif ($tipo_usuario === 'parceiro' && $tipo_documento === 'cnpj' && empty($razao_social)) {
        $erro = 'Razão Social é obrigatória para CNPJ.';
    } elseif ($tipo_usuario === 'parceiro' && empty($endereco)) {
        $erro = 'Endereço é obrigatório para parceiros.';
    } elseif ($tipo_usuario === 'parceiro' && empty($bairro)) {
        $erro = 'Bairro é obrigatório para parceiros.';
    } elseif ($tipo_usuario === 'parceiro' && empty($cidade)) {
        $erro = 'Cidade é obrigatória para parceiros.';
    } elseif ($tipo_usuario === 'parceiro' && empty($cep)) {
        $erro = 'CEP é obrigatório para parceiros.';
    } else {
        $usuario = new Usuario();
        
        // Verificar se email já existe
        if ($usuario->emailExiste($email)) {
            $erro = 'Este email já está cadastrado.';
        } 
        // Verificar CPF duplicado para clientes
        elseif ($tipo_usuario === 'cliente' && $usuario->cpfExiste($cpf)) {
            $erro = 'Este CPF já está cadastrado.';
        }
        // Verificar documento duplicado para parceiros
        elseif ($tipo_usuario === 'parceiro' && $usuario->documentoSalaoExiste($documento)) {
            $erro = 'Este documento já está cadastrado.';
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
            
            // Adicionar CPF para clientes
            if ($tipo_usuario === 'cliente') {
                $dados['cpf'] = $cpf;
            }
            
            // Para parceiros, usar método específico que gerencia a transação
             if ($tipo_usuario === 'parceiro') {
                $dadosSalao = [
                    'nome' => $nome, // Nome do salão igual ao nome do usuário inicialmente
                    'endereco' => $endereco,
                    'bairro' => $bairro,
                    'cidade' => $cidade,
                    'cep' => $cep,
                    'telefone' => $telefone, // Usar o telefone do usuário
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
             } else {
                // Para clientes, cadastro simples
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
            }
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
                            
                            <?php if ($tipo_usuario === 'cliente'): ?>
                            <!-- Campo CPF para clientes -->
                            <div class="form-group mb-3">
                                <label for="cpf" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>
                                    CPF
                                </label>
                                <input type="text" class="form-control" id="cpf" name="cpf" 
                                       placeholder="000.000.000-00" maxlength="14" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um CPF válido.
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Campo CPF/CNPJ para parceiros -->
                            <div class="form-group mb-3">
                                <label for="tipo_documento" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Tipo de Documento
                                </label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="cpf">CPF (Pessoa Física)</option>
                                    <option value="cnpj">CNPJ (Pessoa Jurídica)</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="documento" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>
                                    <span id="documento_label">CPF/CNPJ</span>
                                </label>
                                <input type="text" class="form-control" id="documento" name="documento" 
                                       placeholder="Digite o documento" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um documento válido.
                                </div>
                            </div>
                            
                            <div class="form-group mb-3" id="razao_social_group" style="display: none;">
                                <label for="razao_social" class="form-label">
                                    <i class="fas fa-building me-1"></i>
                                    Razão Social
                                </label>
                                <input type="text" class="form-control" id="razao_social" name="razao_social" 
                                       placeholder="Nome da empresa">
                            </div>
                            
                            <div class="form-group mb-3" id="inscricao_estadual_group" style="display: none;">
                                <label for="inscricao_estadual" class="form-label">
                                    <i class="fas fa-file-invoice me-1"></i>
                                    Inscrição Estadual (opcional)
                                </label>
                                <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual" 
                                       placeholder="000.000.000.000">
                            </div>
                            
                            <!-- Campos de Endereço Detalhado para Parceiros -->
                            <div class="form-group mb-3">
                                <label for="endereco" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Endereço Completo
                                </label>
                                <input type="text" class="form-control" id="endereco" name="endereco" 
                                       placeholder="Rua, número e complemento" required>
                                <div class="invalid-feedback">
                                    Por favor, insira o endereço completo.
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="bairro" class="form-label">
                                            <i class="fas fa-home me-1"></i>
                                            Bairro
                                        </label>
                                        <input type="text" class="form-control" id="bairro" name="bairro" 
                                               placeholder="Nome do bairro" required>
                                        <div class="invalid-feedback">
                                            Por favor, insira o bairro.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="cidade" class="form-label">
                                            <i class="fas fa-city me-1"></i>
                                            Cidade
                                        </label>
                                        <input type="text" class="form-control" id="cidade" name="cidade" 
                                               placeholder="Nome da cidade" required>
                                        <div class="invalid-feedback">
                                            Por favor, insira a cidade.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="cep" class="form-label">
                                    <i class="fas fa-mail-bulk me-1"></i>
                                    CEP
                                </label>
                                <input type="text" class="form-control" id="cep" name="cep" 
                                       placeholder="00000-000" maxlength="9" required>
                                <div class="invalid-feedback">
                                    Por favor, insira um CEP válido.
                                </div>
                            </div>
                            <?php endif; ?>
                            
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
        
        // Máscaras e validações para CPF/CNPJ
        function aplicarMascaraCPF(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = value;
        }
        
        function aplicarMascaraCNPJ(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1/$2');
            value = value.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            input.value = value;
        }
        
        function aplicarMascaraTelefone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            input.value = value;
        }
        
        function validarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
            
            let soma = 0;
            for (let i = 0; i < 9; i++) {
                soma += parseInt(cpf.charAt(i)) * (10 - i);
            }
            let resto = 11 - (soma % 11);
            let dv1 = resto < 2 ? 0 : resto;
            
            soma = 0;
            for (let i = 0; i < 10; i++) {
                soma += parseInt(cpf.charAt(i)) * (11 - i);
            }
            resto = 11 - (soma % 11);
            let dv2 = resto < 2 ? 0 : resto;
            
            return dv1 === parseInt(cpf.charAt(9)) && dv2 === parseInt(cpf.charAt(10));
        }
        
        function validarCNPJ(cnpj) {
            cnpj = cnpj.replace(/\D/g, '');
            if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) return false;
            
            let tamanho = cnpj.length - 2;
            let numeros = cnpj.substring(0, tamanho);
            let digitos = cnpj.substring(tamanho);
            let soma = 0;
            let pos = tamanho - 7;
            
            for (let i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }
            
            let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            if (resultado !== parseInt(digitos.charAt(0))) return false;
            
            tamanho = tamanho + 1;
            numeros = cnpj.substring(0, tamanho);
            soma = 0;
            pos = tamanho - 7;
            
            for (let i = tamanho; i >= 1; i--) {
                soma += numeros.charAt(tamanho - i) * pos--;
                if (pos < 2) pos = 9;
            }
            
            resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
            return resultado === parseInt(digitos.charAt(1));
        }
        
        // Aplicar máscara no telefone
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function() {
                aplicarMascaraTelefone(this);
            });
        }
        
        // Para clientes - CPF
        const cpfInput = document.getElementById('cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function() {
                aplicarMascaraCPF(this);
                
                const cpfLimpo = this.value.replace(/\D/g, '');
                if (cpfLimpo.length === 11) {
                    if (validarCPF(this.value)) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            });
        }
        
        // Para parceiros - CPF/CNPJ dinâmico
        const tipoDocumentoSelect = document.getElementById('tipo_documento');
        const documentoInput = document.getElementById('documento');
        const documentoLabel = document.getElementById('documento_label');
        const razaoSocialGroup = document.getElementById('razao_social_group');
        const inscricaoEstadualGroup = document.getElementById('inscricao_estadual_group');
        const razaoSocialInput = document.getElementById('razao_social');
        
        if (tipoDocumentoSelect && documentoInput) {
            tipoDocumentoSelect.addEventListener('change', function() {
                const tipo = this.value;
                documentoInput.value = '';
                documentoInput.classList.remove('is-valid', 'is-invalid');
                
                if (tipo === 'cpf') {
                    documentoLabel.textContent = 'CPF';
                    documentoInput.placeholder = '000.000.000-00';
                    documentoInput.maxLength = 14;
                    razaoSocialGroup.style.display = 'none';
                    inscricaoEstadualGroup.style.display = 'none';
                    if (razaoSocialInput) razaoSocialInput.required = false;
                    razaoSocialInput.required = false;
                } else if (tipo === 'cnpj') {
                    documentoLabel.textContent = 'CNPJ';
                    documentoInput.placeholder = '00.000.000/0000-00';
                    documentoInput.maxLength = 18;
                    razaoSocialGroup.style.display = 'block';
                    inscricaoEstadualGroup.style.display = 'block';
                    razaoSocialInput.required = true;
                } else {
                    documentoLabel.textContent = 'CPF/CNPJ';
                    documentoInput.placeholder = 'Digite o documento';
                    razaoSocialGroup.style.display = 'none';
                    inscricaoEstadualGroup.style.display = 'none';
                    razaoSocialInput.required = false;
                }
            });
            
            documentoInput.addEventListener('input', function() {
                const tipo = tipoDocumentoSelect.value;
                
                if (tipo === 'cpf') {
                    aplicarMascaraCPF(this);
                    const cpfLimpo = this.value.replace(/\D/g, '');
                    if (cpfLimpo.length === 11) {
                        if (validarCPF(this.value)) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                } else if (tipo === 'cnpj') {
                    aplicarMascaraCNPJ(this);
                    const cnpjLimpo = this.value.replace(/\D/g, '');
                    if (cnpjLimpo.length === 14) {
                        if (validarCNPJ(this.value)) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        }
                    } else {
                        this.classList.remove('is-valid', 'is-invalid');
                    }
                }
            });
        }
        
        // Validação e máscara para CEP
        const cepInput = document.getElementById('cep');
        if (cepInput) {
            cepInput.addEventListener('input', function() {
                // Aplicar máscara CEP
                let valor = this.value.replace(/\D/g, '');
                if (valor.length > 5) {
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                }
                this.value = valor;
                
                // Validar CEP
                const cepLimpo = valor.replace(/\D/g, '');
                if (cepLimpo.length === 8) {
                    // Validação básica de CEP (8 dígitos)
                    if (/^\d{8}$/.test(cepLimpo)) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                    }
                } else {
                    this.classList.remove('is-valid', 'is-invalid');
                }
            });
        }
    </script>
</body>
</html>
<?php
error_log("[CADASTRO] HTML renderizado com sucesso");
?>