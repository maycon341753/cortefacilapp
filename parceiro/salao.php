<?php
/**
 * Página de Gerenciamento do Salão
 * Permite ao parceiro cadastrar ou editar informações do seu salão
 */

// ===== FORÇAR CONEXÃO ONLINE PARA PRODUÇÃO =====
try {
    // Verificar se estamos em produção e forçar conexão online
    $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($serverName, 'cortefacil.app') !== false || file_exists(__DIR__ . '/../.env.online')) {
        require_once __DIR__ . '/../config/database.php';
        $db = Database::getInstance();
        $db->forceOnlineConfig();
        $conn = $db->connect();
        if (!$conn) {
            throw new Exception('Falha na conexão online forçada');
        }
        error_log('Salao: Conexão online forçada com sucesso');
    }
} catch (Exception $e) {
    error_log('Salao: Erro ao forçar conexão online: ' . $e->getMessage());
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/salao.php';

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
        $rua = trim($_POST['rua'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        
        // Montar endereço completo
        $endereco_partes = [];
        if (!empty($rua)) $endereco_partes[] = $rua;
        if (!empty($numero)) $endereco_partes[] = $numero;
        if (!empty($complemento)) $endereco_partes[] = $complemento;
        if (!empty($bairro)) $endereco_partes[] = $bairro;
        if (!empty($cidade)) $endereco_partes[] = $cidade;
        if (!empty($estado)) $endereco_partes[] = $estado;
        if (!empty($cep)) $endereco_partes[] = $cep;
        
        $endereco = implode(', ', $endereco_partes);
        
        if (empty($nome)) {
            throw new Exception('Nome do salão é obrigatório.');
        }
        
        if (strlen($nome) < 3) {
            throw new Exception('Nome do salão deve ter pelo menos 3 caracteres.');
        }
        
        if (empty($rua) || empty($numero) || empty($bairro) || empty($cidade) || empty($estado) || empty($cep)) {
            throw new Exception('Todos os campos de endereço são obrigatórios (exceto complemento).');
        }
        
        // Validar CEP
        $cep_limpo = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep_limpo) !== 8) {
            throw new Exception('CEP deve ter 8 dígitos.');
        }
        
        if (empty($telefone)) {
            throw new Exception('Telefone é obrigatório.');
        }
        
        // Validar email se fornecido
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido.');
        }
        
        // Preparar dados com campos separados
        $dados = [
            'nome' => $nome,
            'endereco' => $endereco,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'cep' => $cep_limpo,
            'telefone' => formatarTelefone($telefone),
            'email' => $email,
            'descricao' => $descricao
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
    $email = $meu_salao['email'] ?? '';
    
    // Usar dados das colunas separadas se existirem, senão tentar extrair do endereço
    $bairro = $meu_salao['bairro'] ?? '';
    $cidade = $meu_salao['cidade'] ?? '';
    $cep = $meu_salao['cep'] ?? '';
    
    // Se não há dados nas colunas separadas, tentar extrair do endereço concatenado
    if (empty($bairro) && empty($cidade) && empty($cep)) {
        $endereco_partes = explode(', ', $endereco);
        $rua = $endereco_partes[0] ?? '';
        $numero = $endereco_partes[1] ?? '';
        $complemento = '';
        $estado = '';
        
        // Se há mais partes, tentar identificar
        if (count($endereco_partes) > 2) {
            // Última parte pode ser CEP
            $ultima_parte = end($endereco_partes);
            if (preg_match('/\d{5}-?\d{3}/', $ultima_parte)) {
                $cep = $ultima_parte;
                array_pop($endereco_partes);
            }
            
            // Penúltima pode ser estado
            if (count($endereco_partes) > 2) {
                $estado = array_pop($endereco_partes);
            }
            
            // Antepenúltima pode ser cidade
            if (count($endereco_partes) > 2) {
                $cidade = array_pop($endereco_partes);
            }
            
            // O que sobrar pode ser bairro
            if (count($endereco_partes) > 2) {
                $bairro = array_pop($endereco_partes);
            }
            
            // Se ainda há partes, pode ser complemento
            if (count($endereco_partes) > 2) {
                $complemento = $endereco_partes[2];
            }
        }
    } else {
        // Se há dados nas colunas separadas, extrair rua e número do endereço
        $endereco_partes = explode(', ', $endereco);
        $rua = $endereco_partes[0] ?? '';
        $numero = $endereco_partes[1] ?? '';
        $complemento = $endereco_partes[2] ?? '';
        $estado = 'SP'; // Valor padrão
    }
} else {
    $nome = $_POST['nome'] ?? '';
    $rua = $_POST['rua'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editando ? 'Editar' : 'Cadastrar'; ?> Salão - CorteFácil Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../includes/parceiro_navigation.php'; ?>
            
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
                                    
                                    <!-- Endereço Separado -->
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label for="rua" class="form-label">Rua/Avenida *</label>
                                            <input type="text" class="form-control" id="rua" name="rua" 
                                                   value="<?php echo htmlspecialchars($rua ?? ''); ?>" 
                                                   placeholder="Ex: Rua das Flores" 
                                                   maxlength="150" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="numero" class="form-label">Número *</label>
                                            <input type="text" class="form-control" id="numero" name="numero" 
                                                   value="<?php echo htmlspecialchars($numero ?? ''); ?>" 
                                                   placeholder="123" 
                                                   maxlength="10" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="bairro" class="form-label">Bairro *</label>
                                            <input type="text" class="form-control" id="bairro" name="bairro" 
                                                   value="<?php echo htmlspecialchars($bairro ?? ''); ?>" 
                                                   placeholder="Ex: Centro" 
                                                   maxlength="100" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="cidade" class="form-label">Cidade *</label>
                                            <input type="text" class="form-control" id="cidade" name="cidade" 
                                                   value="<?php echo htmlspecialchars($cidade ?? ''); ?>" 
                                                   placeholder="Ex: São Paulo" 
                                                   maxlength="100" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="cep" class="form-label">CEP *</label>
                                            <input type="text" class="form-control" id="cep" name="cep" 
                                                   value="<?php echo htmlspecialchars($cep ?? ''); ?>" 
                                                   placeholder="00000-000" 
                                                   data-mask="00000-000" 
                                                   maxlength="9" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="estado" class="form-label">Estado *</label>
                                            <select class="form-select" id="estado" name="estado" required>
                                                <option value="">Selecione...</option>
                                                <option value="AC" <?php echo ($estado ?? '') === 'AC' ? 'selected' : ''; ?>>Acre</option>
                                                <option value="AL" <?php echo ($estado ?? '') === 'AL' ? 'selected' : ''; ?>>Alagoas</option>
                                                <option value="AP" <?php echo ($estado ?? '') === 'AP' ? 'selected' : ''; ?>>Amapá</option>
                                                <option value="AM" <?php echo ($estado ?? '') === 'AM' ? 'selected' : ''; ?>>Amazonas</option>
                                                <option value="BA" <?php echo ($estado ?? '') === 'BA' ? 'selected' : ''; ?>>Bahia</option>
                                                <option value="CE" <?php echo ($estado ?? '') === 'CE' ? 'selected' : ''; ?>>Ceará</option>
                                                <option value="DF" <?php echo ($estado ?? '') === 'DF' ? 'selected' : ''; ?>>Distrito Federal</option>
                                                <option value="ES" <?php echo ($estado ?? '') === 'ES' ? 'selected' : ''; ?>>Espírito Santo</option>
                                                <option value="GO" <?php echo ($estado ?? '') === 'GO' ? 'selected' : ''; ?>>Goiás</option>
                                                <option value="MA" <?php echo ($estado ?? '') === 'MA' ? 'selected' : ''; ?>>Maranhão</option>
                                                <option value="MT" <?php echo ($estado ?? '') === 'MT' ? 'selected' : ''; ?>>Mato Grosso</option>
                                                <option value="MS" <?php echo ($estado ?? '') === 'MS' ? 'selected' : ''; ?>>Mato Grosso do Sul</option>
                                                <option value="MG" <?php echo ($estado ?? '') === 'MG' ? 'selected' : ''; ?>>Minas Gerais</option>
                                                <option value="PA" <?php echo ($estado ?? '') === 'PA' ? 'selected' : ''; ?>>Pará</option>
                                                <option value="PB" <?php echo ($estado ?? '') === 'PB' ? 'selected' : ''; ?>>Paraíba</option>
                                                <option value="PR" <?php echo ($estado ?? '') === 'PR' ? 'selected' : ''; ?>>Paraná</option>
                                                <option value="PE" <?php echo ($estado ?? '') === 'PE' ? 'selected' : ''; ?>>Pernambuco</option>
                                                <option value="PI" <?php echo ($estado ?? '') === 'PI' ? 'selected' : ''; ?>>Piauí</option>
                                                <option value="RJ" <?php echo ($estado ?? '') === 'RJ' ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                                <option value="RN" <?php echo ($estado ?? '') === 'RN' ? 'selected' : ''; ?>>Rio Grande do Norte</option>
                                                <option value="RS" <?php echo ($estado ?? '') === 'RS' ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                                                <option value="RO" <?php echo ($estado ?? '') === 'RO' ? 'selected' : ''; ?>>Rondônia</option>
                                                <option value="RR" <?php echo ($estado ?? '') === 'RR' ? 'selected' : ''; ?>>Roraima</option>
                                                <option value="SC" <?php echo ($estado ?? '') === 'SC' ? 'selected' : ''; ?>>Santa Catarina</option>
                                                <option value="SP" <?php echo ($estado ?? '') === 'SP' ? 'selected' : ''; ?>>São Paulo</option>
                                                <option value="SE" <?php echo ($estado ?? '') === 'SE' ? 'selected' : ''; ?>>Sergipe</option>
                                                <option value="TO" <?php echo ($estado ?? '') === 'TO' ? 'selected' : ''; ?>>Tocantins</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="complemento" class="form-label">Complemento</label>
                                            <input type="text" class="form-control" id="complemento" name="complemento" 
                                                   value="<?php echo htmlspecialchars($complemento ?? ''); ?>" 
                                                   placeholder="Apto, Sala, etc." 
                                                   maxlength="50">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="telefone" class="form-label">Telefone *</label>
                                        <input type="tel" class="form-control" id="telefone" name="telefone" 
                                               value="<?php echo htmlspecialchars($telefone); ?>" 
                                               placeholder="(11) 99999-9999" 
                                               data-mask="(00) 00000-0000" required>
                                        <div class="form-text">Telefone para contato dos clientes</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="email" class="form-label">E-mail</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                               placeholder="contato@seusalao.com.br" 
                                               maxlength="100">
                                        <div class="form-text">E-mail para contato dos clientes (opcional)</div>
                                    </div>
                                    
                                    <!-- Campo de descrição do salão -->
                                    <div class="mb-3">
                                        <label for="descricao" class="form-label">Descrição do Salão</label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descreva seu salão, serviços oferecidos, horário de funcionamento, etc."><?php echo htmlspecialchars($meu_salao['descricao'] ?? ''); ?></textarea>
                                        <div class="form-text">Uma boa descrição ajuda seus clientes a conhecerem melhor seu salão.</div>
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
                                            <span class="text-muted">Última atualização:</span>
                                            <span class="fw-bold"><?php echo formatarData($meu_salao['updated_at'] ?? date('Y-m-d')); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">ID do Salão:</span>
                                            <span class="fw-bold">#<?php echo str_pad($meu_salao['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Status:</span>
                                            <span class="fw-bold <?php echo $meu_salao['ativo'] ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $meu_salao['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estatísticas em Tempo Real -->
                            <div class="card mb-4" id="estatisticas-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Estatísticas do Salão
                                    </h6>
                                    <button class="btn btn-sm btn-outline-primary" onclick="atualizarEstatisticas()" id="btn-atualizar">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="bg-primary text-white rounded p-2">
                                                <h4 class="mb-0" id="total-profissionais">-</h4>
                                                <small>Profissionais</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="bg-success text-white rounded p-2">
                                                <h4 class="mb-0" id="profissionais-ativos">-</h4>
                                                <small>Ativos</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="bg-info text-white rounded p-2">
                                                <h4 class="mb-0" id="agendamentos-hoje">-</h4>
                                                <small>Hoje</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="bg-warning text-white rounded p-2">
                                                <h4 class="mb-0" id="agendamentos-semana">-</h4>
                                                <small>Esta Semana</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <h6 class="text-muted mb-2">Últimos Profissionais</h6>
                                        <div id="ultimos-profissionais">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <h6 class="text-muted mb-2">Próximos Agendamentos</h6>
                                        <div id="proximos-agendamentos">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Informações Detalhadas do Salão -->
                        <?php if ($editando): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações Detalhadas do Salão
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Nome do Salão</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($meu_salao['nome'] ?? ''); ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Endereço Completo</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($meu_salao['endereco'] ?? ''); ?></p>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Telefone</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($meu_salao['telefone'] ?? ''); ?></p>
                                </div>
                                
                                <?php if (!empty($meu_salao['descricao'])): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Descrição</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($meu_salao['descricao'])); ?></p>
                                </div>
                                <?php endif; ?>
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
            const rua = document.getElementById('rua').value.trim();
            const numero = document.getElementById('numero').value.trim();
            const bairro = document.getElementById('bairro').value.trim();
            const cidade = document.getElementById('cidade').value.trim();
            const telefone = document.getElementById('telefone').value.trim();
            
            if (nome.length < 3) {
                e.preventDefault();
                alert('O nome do salão deve ter pelo menos 3 caracteres.');
                document.getElementById('nome').focus();
                return false;
            }
            
            if (rua.length < 3) {
                e.preventDefault();
                alert('Por favor, informe a rua/avenida.');
                document.getElementById('rua').focus();
                return false;
            }
            
            if (numero.length < 1) {
                e.preventDefault();
                alert('Por favor, informe o número.');
                document.getElementById('numero').focus();
                return false;
            }
            
            if (bairro.length < 2) {
                e.preventDefault();
                alert('Por favor, informe o bairro.');
                document.getElementById('bairro').focus();
                return false;
            }
            
            if (cidade.length < 2) {
                e.preventDefault();
                alert('Por favor, informe a cidade.');
                document.getElementById('cidade').focus();
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
        
        // Contador de caracteres para rua
        const ruaInput = document.getElementById('rua');
        if (ruaInput) {
            ruaInput.addEventListener('input', function() {
                const maxLength = 150;
                const currentLength = this.value.length;
                
                if (currentLength > maxLength * 0.9) {
                    this.style.borderColor = '#ffc107';
                } else {
                    this.style.borderColor = '';
                }
            });
        }
        
        // Função para atualizar estatísticas em tempo real
        function atualizarEstatisticas() {
            const btnAtualizar = document.getElementById('btn-atualizar');
            const idSalao = <?php echo $meu_salao['id'] ?? 0; ?>;
            
            if (!idSalao) return;
            
            // Mostrar loading
            btnAtualizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btnAtualizar.disabled = true;
            
            fetch(`salao_tempo_real.php?action=get_stats&id_salao=${idSalao}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.data;
                        
                        // Atualizar contadores
                        document.getElementById('total-profissionais').textContent = stats.total_profissionais;
                        document.getElementById('profissionais-ativos').textContent = stats.profissionais_ativos;
                        document.getElementById('agendamentos-hoje').textContent = stats.agendamentos_hoje;
                        document.getElementById('agendamentos-semana').textContent = stats.agendamentos_semana;
                        
                        // Atualizar últimos profissionais
                        const ultimosProfissionais = document.getElementById('ultimos-profissionais');
                        if (stats.ultimos_profissionais.length > 0) {
                            ultimosProfissionais.innerHTML = stats.ultimos_profissionais.map(prof => 
                                `<div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>${prof.nome}</strong><br>
                                        <small class="text-muted">${prof.especialidade}</small>
                                    </div>
                                    <small class="text-muted">${prof.data_cadastro}</small>
                                </div>`
                            ).join('');
                        } else {
                            ultimosProfissionais.innerHTML = '<div class="text-center text-muted">Nenhum profissional cadastrado</div>';
                        }
                        
                        // Atualizar próximos agendamentos
                        const proximosAgendamentos = document.getElementById('proximos-agendamentos');
                        if (stats.proximos_agendamentos.length > 0) {
                            proximosAgendamentos.innerHTML = stats.proximos_agendamentos.map(agend => 
                                `<div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>${agend.cliente}</strong><br>
                                        <small class="text-muted">${agend.profissional}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">${formatarData(agend.data_agendamento)}</small><br>
                                        <small class="text-muted">${agend.horario}</small>
                                    </div>
                                </div>`
                            ).join('');
                        } else {
                            proximosAgendamentos.innerHTML = '<div class="text-center text-muted">Nenhum agendamento próximo</div>';
                        }
                    } else {
                        console.error('Erro ao carregar estatísticas:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                })
                .finally(() => {
                    // Restaurar botão
                    btnAtualizar.innerHTML = '<i class="fas fa-sync-alt"></i>';
                    btnAtualizar.disabled = false;
                });
        }
        
        // Função auxiliar para formatar data
        function formatarData(data) {
            const date = new Date(data);
            return date.toLocaleDateString('pt-BR');
        }
        
        // Carregar estatísticas ao carregar a página (se estiver editando)
        <?php if ($editando): ?>
        document.addEventListener('DOMContentLoaded', function() {
            atualizarEstatisticas();
            
            // Atualizar automaticamente a cada 30 segundos
            setInterval(atualizarEstatisticas, 30000);
        });
        <?php endif; ?>
    </script>
    
    <style>
        .avatar-lg {
            width: 60px;
            height: 60px;
        }
    </style>
</body>
</html>