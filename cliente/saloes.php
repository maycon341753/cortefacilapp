<?php
/**
 * Página de Salões Parceiros do Cliente
 * Lista todos os salões ativos com informações e opção de agendamento
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../models/salao.php';
require_once '../models/profissional.php';

// Verificar se é cliente
requireCliente();

$usuario = getLoggedUser();
$salao = new Salao();
$profissional = new Profissional();

// Filtros
$filtro_busca = $_GET['busca'] ?? '';
$filtro_cidade = $_GET['cidade'] ?? '';

// Buscar salões ativos
$saloes = $salao->listarAtivos();

// Aplicar filtros
if ($filtro_busca) {
    $saloes = array_filter($saloes, function($s) use ($filtro_busca) {
        return stripos($s['nome'], $filtro_busca) !== false || 
               stripos($s['endereco'], $filtro_busca) !== false;
    });
}

if ($filtro_cidade) {
    $saloes = array_filter($saloes, function($s) use ($filtro_cidade) {
        return stripos($s['endereco'], $filtro_cidade) !== false;
    });
}

// Buscar profissionais para cada salão
foreach ($saloes as &$s) {
    $s['profissionais'] = $profissional->listarPorSalao($s['id']);
    $s['total_profissionais'] = count($s['profissionais']);
}

// Obter lista de cidades para o filtro
$cidades = [];
foreach ($salao->listarAtivos() as $s) {
    // Extrair cidade do endereço (assumindo formato: "Rua, Número, Bairro, Cidade")
    $partes_endereco = explode(',', $s['endereco']);
    if (count($partes_endereco) >= 4) {
        $cidade = trim($partes_endereco[3]);
        if (!in_array($cidade, $cidades)) {
            $cidades[] = $cidade;
        }
    }
}
sort($cidades);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salões Parceiros - CorteFácil</title>
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
                        <small class="text-white-50">Cliente</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendar.php">
                                <i class="fas fa-calendar-plus"></i>
                                Novo Agendamento
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="agendamentos.php">
                                <i class="fas fa-calendar-alt"></i>
                                Meus Agendamentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="saloes.php">
                                <i class="fas fa-store"></i>
                                Salões Parceiros
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="perfil.php">
                                <i class="fas fa-user"></i>
                                Meu Perfil
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
                        Salões Parceiros
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="agendar.php" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Agendar Agora
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-store text-primary"></i>
                            </div>
                            <div class="number"><?php echo count($saloes); ?></div>
                            <div class="label">Salões Encontrados</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-users text-success"></i>
                            </div>
                            <div class="number"><?php echo array_sum(array_column($saloes, 'total_profissionais')); ?></div>
                            <div class="label">Profissionais Disponíveis</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-map-marker-alt text-info"></i>
                            </div>
                            <div class="number"><?php echo count($cidades); ?></div>
                            <div class="label">Cidades Atendidas</div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            Buscar Salões
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label for="busca" class="form-label">Nome do Salão ou Endereço</label>
                                <input type="text" class="form-control" id="busca" name="busca" 
                                       placeholder="Digite o nome do salão ou endereço..."
                                       value="<?php echo htmlspecialchars($filtro_busca); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="cidade" class="form-label">Cidade</label>
                                <select class="form-select" id="cidade" name="cidade">
                                    <option value="">Todas as cidades</option>
                                    <?php foreach ($cidades as $cidade): ?>
                                        <option value="<?php echo htmlspecialchars($cidade); ?>"
                                                <?php echo ($filtro_cidade === $cidade) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cidade); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>
                                        Buscar
                                    </button>
                                    <a href="saloes.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de Salões -->
                <?php if (!empty($saloes)): ?>
                    <div class="row">
                        <?php foreach ($saloes as $s): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 salon-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-store me-2"></i>
                                            <?php echo htmlspecialchars($s['nome']); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                Endereço
                                            </h6>
                                            <p class="mb-0"><?php echo htmlspecialchars($s['endereco']); ?></p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-phone me-2"></i>
                                                Telefone
                                            </h6>
                                            <p class="mb-0">
                                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $s['telefone']); ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo formatarTelefone($s['telefone']); ?>
                                                </a>
                                            </p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-users me-2"></i>
                                                Profissionais
                                                <span class="badge bg-primary ms-1"><?php echo $s['total_profissionais']; ?></span>
                                            </h6>
                                            
                                            <?php if (!empty($s['profissionais'])): ?>
                                                <div class="row">
                                                    <?php foreach (array_slice($s['profissionais'], 0, 4) as $prof): ?>
                                                        <div class="col-6 mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                    <i class="fas fa-user text-muted"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold small"><?php echo htmlspecialchars($prof['nome']); ?></div>
                                                                    <div class="text-muted small"><?php echo htmlspecialchars($prof['especialidade']); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <?php if ($s['total_profissionais'] > 4): ?>
                                                    <div class="text-center">
                                                        <small class="text-muted">
                                                            +<?php echo ($s['total_profissionais'] - 4); ?> profissionais
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <p class="text-muted small mb-0">Nenhum profissional cadastrado</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <div class="d-grid gap-2">
                                            <a href="agendar.php?salao=<?php echo $s['id']; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-calendar-plus me-2"></i>
                                                Agendar Neste Salão
                                            </a>
                                            
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalDetalhes<?php echo $s['id']; ?>">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Ver Detalhes
                                                </button>
                                                
                                                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $s['telefone']); ?>" 
                                                   class="btn btn-outline-success">
                                                    <i class="fas fa-phone me-1"></i>
                                                    Ligar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal de Detalhes -->
                            <div class="modal fade" id="modalDetalhes<?php echo $s['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="fas fa-store me-2"></i>
                                                <?php echo htmlspecialchars($s['nome']); ?>
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-2">
                                                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                                            Endereço
                                                        </h6>
                                                        <p class="mb-0"><?php echo htmlspecialchars($s['endereco']); ?></p>
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-2">
                                                            <i class="fas fa-phone me-2 text-primary"></i>
                                                            Telefone
                                                        </h6>
                                                        <p class="mb-0">
                                                            <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $s['telefone']); ?>" 
                                                               class="text-decoration-none">
                                                                <?php echo formatarTelefone($s['telefone']); ?>
                                                            </a>
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-2">
                                                            <i class="fas fa-calendar me-2 text-primary"></i>
                                                            Cadastrado em
                                                        </h6>
                                                        <p class="mb-0"><?php echo formatarData($s['created_at'] ?? date('Y-m-d')); ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <h6 class="fw-bold mb-2">
                                                            <i class="fas fa-users me-2 text-primary"></i>
                                                            Profissionais (<?php echo $s['total_profissionais']; ?>)
                                                        </h6>
                                                        
                                                        <?php if (!empty($s['profissionais'])): ?>
                                                            <div class="list-group list-group-flush">
                                                                <?php foreach ($s['profissionais'] as $prof): ?>
                                                                    <div class="list-group-item px-0 py-2">
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-3">
                                                                                <i class="fas fa-user text-muted"></i>
                                                                            </div>
                                                                            <div class="flex-grow-1">
                                                                                <div class="fw-bold"><?php echo htmlspecialchars($prof['nome']); ?></div>
                                                                                <div class="text-muted small"><?php echo htmlspecialchars($prof['especialidade']); ?></div>
                                                                            </div>
                                                                            <span class="badge bg-success">Ativo</span>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-center py-3">
                                                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                                                <p class="text-muted mb-0">Nenhum profissional cadastrado</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Fechar
                                            </button>
                                            <a href="agendar.php?salao=<?php echo $s['id']; ?>" 
                                               class="btn btn-primary">
                                                <i class="fas fa-calendar-plus me-2"></i>
                                                Agendar Agora
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paginação (se necessário) -->
                    <?php if (count($saloes) > 12): ?>
                        <nav aria-label="Navegação de páginas">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <span class="page-link">Anterior</span>
                                </li>
                                <li class="page-item active">
                                    <span class="page-link">1</span>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Próximo</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-store fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum salão encontrado</h5>
                            <p class="text-muted">
                                <?php if ($filtro_busca || $filtro_cidade): ?>
                                    Não encontramos salões que correspondam aos filtros aplicados.
                                    <br>Tente ajustar os critérios de busca.
                                <?php else: ?>
                                    Ainda não temos salões parceiros cadastrados.
                                    <br>Volte em breve para conferir as novidades!
                                <?php endif; ?>
                            </p>
                            
                            <?php if ($filtro_busca || $filtro_cidade): ?>
                                <a href="saloes.php" class="btn btn-primary">
                                    <i class="fas fa-times me-2"></i>
                                    Limpar Filtros
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    
    <style>
        .salon-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .salon-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
        }
        
        .list-group-item:first-child {
            border-top: none;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }
    </style>
</body>
</html>