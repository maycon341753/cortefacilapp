<?php
/**
 * Página de Gerenciamento do Salão
 * Permite ao parceiro cadastrar ou editar informações do seu salão
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/salao.php';

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();

$erro = '';
$sucesso = '';

// Buscar salão existente
$meu_salao = $salao->buscarPorDono($usuario['id']);
$editando = !empty($meu_salao);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de segurança inválido.');
        }
        
        // Validar dados
        $nome = trim($_POST['nome'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        
        if (empty($nome)) {
            throw new Exception('Nome do salão é obrigatório.');
        }
        
        if (strlen($nome) < 3) {
            throw new Exception('Nome do salão deve ter pelo menos 3 caracteres.');
        }
        
        if (empty($endereco)) {
            throw new Exception('Endereço é obrigatório.');
        }
        
        if (empty($telefone)) {
            throw new Exception('Telefone é obrigatório.');
        }
        
        // Preparar dados
        $dados = [
            'nome' => $nome,
            'endereco' => $endereco,
            'telefone' => formatarTelefone($telefone)
        ];
        
        if ($editando) {
            // Atualizar salão existente
            $resultado = $salao->atualizar($meu_salao['id'], $dados);
            $mensagem = 'Salão atualizado com sucesso!';
            $acao = 'salao_atualizado';
        } else {
            // Cadastrar novo salão
            $dados['id_dono'] = $usuario['id'];
            $resultado = $salao->cadastrar($dados);
            $mensagem = 'Salão cadastrado com sucesso!';
            $acao = 'salao_cadastrado';
        }
        
        if ($resultado) {
            $sucesso = $mensagem;
            // Recarregar dados do salão
            $meu_salao = $salao->buscarPorDono($usuario['id']);
            $editando = true;
            
            // Log da atividade
            logActivity($usuario['id'], $acao, "Salão: {$nome}");
        } else {
            throw new Exception('Erro ao salvar dados do salão.');
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Se está editando, preencher dados do formulário
if ($editando && empty($_POST)) {
    $nome = $meu_salao['nome'];
    $endereco = $meu_salao['endereco'];
    $telefone = $meu_salao['telefone'];
} else {
    $nome = $_POST['nome'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editando ? 'Editar' : 'Cadastrar'; ?> Salão - CorteFácil Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            <i class="fas fa-cut me-2"></i>
                            CorteFácil
                        </h5>
                        <small class="text-white-50">Parceiro</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agenda.php">
                                <i class="fas fa-calendar-alt"></i>
                                Agenda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profissionais.php">
                                <i class="fas fa-users"></i>
                                Profissionais
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendamentos.php">
                                <i class="fas fa-list"></i>
                                Agendamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="salao.php">
                                <i class="fas fa-store"></i>
                                Meu Salão
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="relatorios.php">
                                <i class="fas fa-chart-bar"></i>
                                Relatórios
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white-50">
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-store me-2 text-primary"></i>
                        <?php echo $editando ? 'Editar' : 'Cadastrar'; ?> Salão
                    </h1>
                    <?php if ($editando): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Voltar ao Dashboard
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Alertas -->
                <?php if ($erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($erro); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($sucesso); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Formulário -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    Informações do Salão
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="formSalao">
                                    <?php echo generateCsrfToken(); ?>
                                    
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome do Salão *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" 
                                               value="<?php echo htmlspecialchars($nome); ?>" 
                                               placeholder="Ex: Salão Beleza & Estilo" 
                                               minlength="3" maxlength="100" required>
                                        <div class="form-text">Mínimo de 3 caracteres</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="endereco" class="form-label">Endereço Completo *</label>
                                        <textarea class="form-control" id="endereco" name="endereco" rows="3" 
                                                  placeholder="Rua, número, bairro, cidade - CEP" 
                                                  maxlength="255" required><?php echo htmlspecialchars($endereco); ?></textarea>
                                        <div class="form-text">Inclua rua, número, bairro, cidade e CEP</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="telefone" class="form-label">Telefone *</label>
                                        <input type="tel" class="form-control" id="telefone" name="telefone" 
                                               value="<?php echo htmlspecialchars($telefone); ?>" 
                                               placeholder="(11) 99999-9999" 
                                               data-mask="(00) 00000-0000" required>
                                        <div class="form-text">Telefone para contato dos clientes</div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <?php if ($editando): ?>
                                            <a href="dashboard.php" class="btn btn-outline-secondary me-md-2">
                                                <i class="fas fa-times me-2"></i>
                                                Cancelar
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            <?php echo $editando ? 'Atualizar' : 'Cadastrar'; ?> Salão
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações Adicionais -->
                    <div class="col-lg-4">
                        <?php if ($editando): ?>
                            <!-- Status do Salão -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Status do Salão
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="avatar-lg bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2">
                                            <i class="fas fa-check fa-2x"></i>
                                        </div>
                                        <h6 class="mb-1">Salão Ativo</h6>
                                        <p class="text-muted small mb-0">Seu salão está disponível para agendamentos</p>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Cadastrado em:</span>
                                            <span class="fw-bold"><?php echo formatarData($meu_salao['created_at'] ?? date('Y-m-d')); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">ID do Salão:</span>
                                            <span class="fw-bold">#<?php echo str_pad($meu_salao['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Dicas -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Dicas Importantes
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informações Importantes
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li>Use um nome atrativo e fácil de lembrar</li>
                                        <li>Inclua o endereço completo com CEP</li>
                                        <li>Mantenha o telefone sempre atualizado</li>
                                        <li>Clientes usarão essas informações para encontrar seu salão</li>
                                    </ul>
                                </div>
                                
                                <?php if (!$editando): ?>
                                    <div class="alert alert-warning">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Primeiro Acesso
                                        </h6>
                                        <p class="mb-0 small">
                                            Você precisa cadastrar seu salão antes de adicionar profissionais e receber agendamentos.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($editando): ?>
                            <!-- Próximos Passos -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-tasks me-2"></i>
                                        Próximos Passos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="profissionais.php" class="btn btn-primary btn-sm">
                                            <i class="fas fa-users me-2"></i>
                                            Gerenciar Profissionais
                                        </a>
                                        
                                        <a href="agenda.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Ver Agenda
                                        </a>
                                        
                                        <a href="agendamentos.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-list me-2"></i>
                                            Ver Agendamentos
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <script>
        // Validação do formulário
        document.getElementById('formSalao').addEventListener('submit', function(e) {
            const nome = document.getElementById('nome').value.trim();
            const endereco = document.getElementById('endereco').value.trim();
            const telefone = document.getElementById('telefone').value.trim();
            
            if (nome.length < 3) {
                e.preventDefault();
                alert('O nome do salão deve ter pelo menos 3 caracteres.');
                document.getElementById('nome').focus();
                return false;
            }
            
            if (endereco.length < 10) {
                e.preventDefault();
                alert('Por favor, informe o endereço completo.');
                document.getElementById('endereco').focus();
                return false;
            }
            
            if (telefone.length < 14) {
                e.preventDefault();
                alert('Por favor, informe um telefone válido.');
                document.getElementById('telefone').focus();
                return false;
            }
        });
        
        // Contador de caracteres para o nome
        document.getElementById('nome').addEventListener('input', function() {
            const maxLength = 100;
            const currentLength = this.value.length;
            const formText = this.nextElementSibling;
            
            if (currentLength >= 3) {
                formText.textContent = `${currentLength}/${maxLength} caracteres`;
                formText.className = 'form-text text-success';
            } else {
                formText.textContent = 'Mínimo de 3 caracteres';
                formText.className = 'form-text';
            }
        });
        
        // Contador de caracteres para o endereço
        document.getElementById('endereco').addEventListener('input', function() {
            const maxLength = 255;
            const currentLength = this.value.length;
            const formText = this.nextElementSibling;
            
            formText.textContent = `${currentLength}/${maxLength} caracteres - Inclua rua, número, bairro, cidade e CEP`;
            
            if (currentLength > maxLength * 0.9) {
                formText.className = 'form-text text-warning';
            } else {
                formText.className = 'form-text';
            }
        });
    </script>
    
    <style>
        .avatar-lg {
            width: 60px;
            height: 60px;
        }
    </style>
</body>
</html>