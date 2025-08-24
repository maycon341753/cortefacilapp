<?php
/**
 * Página de Gerenciamento de Serviços
 * Permite ao parceiro cadastrar, editar e gerenciar serviços do seu salão
 */

// Configurações de erro para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Iniciar sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        error_log('Serviços: Conexão online forçada com sucesso');
    }
} catch (Exception $e) {
    error_log('Serviços: Erro ao forçar conexão online: ' . $e->getMessage());
}

try {
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/functions.php';
    require_once __DIR__ . '/../models/salao.php';
    require_once __DIR__ . '/../models/servico.php';

    // Verificar se é parceiro
    requireParceiro();

    $usuario = getLoggedUser();
    $salao = new Salao();
    $servico = new Servico();

    $erro = '';
    $sucesso = '';

    // Buscar salão do parceiro
    $meu_salao = $salao->buscarPorDono($usuario['id']);

    if (!$meu_salao) {
        header('Location: salao.php');
        exit;
    }

    // Processar ações
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';
        
        switch ($acao) {
            case 'criar':
                $dados = [
                    'id_salao' => $meu_salao['id'],
                    'nome' => trim($_POST['nome'] ?? ''),
                    'descricao' => trim($_POST['descricao'] ?? ''),
                    'preco' => floatval($_POST['preco'] ?? 0),
                    'duracao_minutos' => intval($_POST['duracao_minutos'] ?? 60),
                    'ativo' => isset($_POST['ativo']) ? 1 : 0
                ];
                
                if (empty($dados['nome'])) {
                    $erro = 'Nome do serviço é obrigatório.';
                } elseif ($dados['preco'] <= 0) {
                    $erro = 'Preço deve ser maior que zero.';
                } elseif ($dados['duracao_minutos'] <= 0) {
                    $erro = 'Duração deve ser maior que zero.';
                } else {
                    if ($servico->criar($dados)) {
                        $sucesso = 'Serviço cadastrado com sucesso!';
                    } else {
                        $erro = 'Erro ao cadastrar serviço. Tente novamente.';
                    }
                }
                break;
                
            case 'editar':
                $id = intval($_POST['id'] ?? 0);
                $dados = [
                    'nome' => trim($_POST['nome'] ?? ''),
                    'descricao' => trim($_POST['descricao'] ?? ''),
                    'preco' => floatval($_POST['preco'] ?? 0),
                    'duracao_minutos' => intval($_POST['duracao_minutos'] ?? 60),
                    'ativo' => isset($_POST['ativo']) ? 1 : 0
                ];
                
                if (empty($dados['nome'])) {
                    $erro = 'Nome do serviço é obrigatório.';
                } elseif ($dados['preco'] <= 0) {
                    $erro = 'Preço deve ser maior que zero.';
                } elseif ($dados['duracao_minutos'] <= 0) {
                    $erro = 'Duração deve ser maior que zero.';
                } else {
                    if ($servico->atualizar($id, $dados)) {
                        $sucesso = 'Serviço atualizado com sucesso!';
                    } else {
                        $erro = 'Erro ao atualizar serviço. Tente novamente.';
                    }
                }
                break;
                
            case 'excluir':
                $id = intval($_POST['id'] ?? 0);
                if ($servico->excluir($id)) {
                    $sucesso = 'Serviço excluído com sucesso!';
                } else {
                    $erro = 'Erro ao excluir serviço. Tente novamente.';
                }
                break;
                
            case 'alterar_status':
                $id = intval($_POST['id'] ?? 0);
                $ativo = intval($_POST['ativo'] ?? 0);
                if ($servico->alterarStatus($id, $ativo)) {
                    $sucesso = $ativo ? 'Serviço ativado com sucesso!' : 'Serviço desativado com sucesso!';
                } else {
                    $erro = 'Erro ao alterar status do serviço.';
                }
                break;
        }
    }

    // Buscar serviços do salão
    $servicos = $servico->listarPorSalao($meu_salao['id']);
    $total_servicos = count($servicos);
    $servicos_ativos = count(array_filter($servicos, function($s) { return $s['ativo']; }));

} catch (Exception $e) {
    error_log('Erro na página de serviços: ' . $e->getMessage());
    $erro = 'Erro interno do sistema. Tente novamente mais tarde.';
    $servicos = [];
    $total_servicos = 0;
    $servicos_ativos = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Serviços - CorteFácil Parceiro</title>
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
                    <div>
                        <h1 class="h2">
                            <i class="fas fa-concierge-bell me-2 text-primary"></i>
                            Gerenciar Serviços
                        </h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-store me-1"></i>
                            <?php echo htmlspecialchars($meu_salao['nome']); ?>
                        </p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServico">
                            <i class="fas fa-plus me-2"></i>
                            Novo Serviço
                        </button>
                    </div>
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

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-concierge-bell text-primary"></i>
                            </div>
                            <div class="number"><?php echo $total_servicos; ?></div>
                            <div class="label">Total de Serviços</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <div class="number"><?php echo $servicos_ativos; ?></div>
                            <div class="label">Serviços Ativos</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-pause-circle text-warning"></i>
                            </div>
                            <div class="number"><?php echo $total_servicos - $servicos_ativos; ?></div>
                            <div class="label">Serviços Inativos</div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-dollar-sign text-info"></i>
                            </div>
                            <div class="number">
                                R$ <?php 
                                    $preco_medio = 0;
                                    if ($servicos_ativos > 0) {
                                        $soma_precos = array_sum(array_map(function($s) { 
                                            return $s['ativo'] ? $s['preco'] : 0; 
                                        }, $servicos));
                                        $preco_medio = $soma_precos / $servicos_ativos;
                                    }
                                    echo number_format($preco_medio, 2, ',', '.'); 
                                ?>
                            </div>
                            <div class="label">Preço Médio</div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Serviços -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Meus Serviços
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($servicos)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-concierge-bell fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum serviço cadastrado</h5>
                                <p class="text-muted">Comece cadastrando os serviços que seu salão oferece.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServico">
                                    <i class="fas fa-plus me-2"></i>
                                    Cadastrar Primeiro Serviço
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Serviço</th>
                                            <th>Preço</th>
                                            <th>Duração</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($servicos as $s): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($s['nome']); ?></strong>
                                                        <?php if ($s['descricao']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($s['descricao']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">
                                                        R$ <?php echo number_format($s['preco'], 2, ',', '.'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo $s['duracao_minutos']; ?> min
                                                </td>
                                                <td>
                                                    <?php if ($s['ativo']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Ativo
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-pause me-1"></i>Inativo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="editarServico(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Tem certeza que deseja <?php echo $s['ativo'] ? 'desativar' : 'ativar'; ?> este serviço?')">
                                                            <input type="hidden" name="acao" value="alterar_status">
                                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                                            <input type="hidden" name="ativo" value="<?php echo $s['ativo'] ? 0 : 1; ?>">
                                                            <button type="submit" class="btn btn-sm <?php echo $s['ativo'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                                                <i class="fas <?php echo $s['ativo'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Tem certeza que deseja excluir este serviço? Esta ação não pode ser desfeita.')">
                                                            <input type="hidden" name="acao" value="excluir">
                                                            <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Cadastro/Edição de Serviço -->
    <div class="modal fade" id="modalServico" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formServico">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalServicoTitle">
                            <i class="fas fa-plus me-2"></i>
                            Novo Serviço
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" id="modalAcao" value="criar">
                        <input type="hidden" name="id" id="modalId" value="">
                        
                        <div class="mb-3">
                            <label for="modalNome" class="form-label">Nome do Serviço *</label>
                            <input type="text" class="form-control" id="modalNome" name="nome" required maxlength="100">
                        </div>
                        
                        <div class="mb-3">
                            <label for="modalDescricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="modalDescricao" name="descricao" rows="3" maxlength="500"></textarea>
                            <div class="form-text">Descreva brevemente o serviço (opcional)</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="modalPreco" class="form-label">Preço (R$) *</label>
                                    <input type="number" class="form-control" id="modalPreco" name="preco" 
                                           min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="modalDuracao" class="form-label">Duração (minutos) *</label>
                                    <input type="number" class="form-control" id="modalDuracao" name="duracao_minutos" 
                                           min="1" max="480" value="60" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modalAtivo" name="ativo" checked>
                                <label class="form-check-label" for="modalAtivo">
                                    Serviço ativo (disponível para agendamento)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Salvar Serviço
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarServico(servico) {
            document.getElementById('modalAcao').value = 'editar';
            document.getElementById('modalId').value = servico.id;
            document.getElementById('modalNome').value = servico.nome;
            document.getElementById('modalDescricao').value = servico.descricao || '';
            document.getElementById('modalPreco').value = servico.preco;
            document.getElementById('modalDuracao').value = servico.duracao_minutos;
            document.getElementById('modalAtivo').checked = servico.ativo == 1;
            
            document.getElementById('modalServicoTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Serviço';
            
            new bootstrap.Modal(document.getElementById('modalServico')).show();
        }
        
        // Reset modal quando fechar
        document.getElementById('modalServico').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formServico').reset();
            document.getElementById('modalAcao').value = 'criar';
            document.getElementById('modalId').value = '';
            document.getElementById('modalServicoTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Novo Serviço';
            document.getElementById('modalAtivo').checked = true;
        });
    </script>
</body>
</html>