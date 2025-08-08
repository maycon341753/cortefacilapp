<?php
/**
 * Página de Histórico de Agendamentos do Cliente
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once '../includes/auth.php';
$auth->requireAuth('cliente');

$user = $auth->getCurrentUser();
$agendamentos = [];
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todos';

try {
    // Construir query baseada no filtro
    $whereClause = "WHERE a.id_cliente = ?";
    $params = [$user['id']];
    
    switch ($filtro) {
        case 'confirmados':
            $whereClause .= " AND a.status = 'confirmado'";
            break;
        case 'concluidos':
            $whereClause .= " AND a.status = 'concluido'";
            break;
        case 'cancelados':
            $whereClause .= " AND a.status = 'cancelado'";
            break;
        case 'pendentes':
            $whereClause .= " AND a.status = 'pendente'";
            break;
    }
    
    $sql = "SELECT a.*, s.nome as salao_nome, s.endereco, s.telefone,
                   p.nome as profissional_nome, p.especialidade
            FROM agendamentos a 
            JOIN saloes s ON a.id_salao = s.id 
            JOIN profissionais p ON a.id_profissional = p.id 
            $whereClause
            ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC";
    
    $stmt = $database->query($sql, $params);
    $agendamentos = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erro ao carregar histórico: ' . $e->getMessage();
}

// Função para formatar status
function formatarStatus($status) {
    $statusMap = [
        'pendente' => ['texto' => 'Pendente', 'classe' => 'warning'],
        'confirmado' => ['texto' => 'Confirmado', 'classe' => 'success'],
        'concluido' => ['texto' => 'Concluído', 'classe' => 'info'],
        'cancelado' => ['texto' => 'Cancelado', 'classe' => 'danger']
    ];
    
    return $statusMap[$status] ?? ['texto' => 'Desconhecido', 'classe' => 'secondary'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Agendamentos - CorteFácil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">CorteFácil</a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="dashboard.php">Painel</a></li>
                        <li><a href="agendar.php">Novo Agendamento</a></li>
                        <li><a href="historico.php" class="active">Histórico</a></li>
                        <li><a href="../logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="card-title">Histórico de Agendamentos</h2>
                            <a href="agendar.php" class="btn btn-primary">
                                ➕ Novo Agendamento
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="filter-tabs">
                        <a href="?filtro=todos" class="filter-tab <?php echo $filtro === 'todos' ? 'active' : ''; ?>">
                            Todos
                        </a>
                        <a href="?filtro=confirmados" class="filter-tab <?php echo $filtro === 'confirmados' ? 'active' : ''; ?>">
                            Confirmados
                        </a>
                        <a href="?filtro=concluidos" class="filter-tab <?php echo $filtro === 'concluidos' ? 'active' : ''; ?>">
                            Concluídos
                        </a>
                        <a href="?filtro=pendentes" class="filter-tab <?php echo $filtro === 'pendentes' ? 'active' : ''; ?>">
                            Pendentes
                        </a>
                        <a href="?filtro=cancelados" class="filter-tab <?php echo $filtro === 'cancelados' ? 'active' : ''; ?>">
                            Cancelados
                        </a>
                    </div>
                    
                    <!-- Lista de Agendamentos -->
                    <?php if (empty($agendamentos)): ?>
                        <div class="empty-state">
                            <div class="text-center py-5">
                                <h3>📅 Nenhum agendamento encontrado</h3>
                                <p>Você ainda não possui agendamentos<?php echo $filtro !== 'todos' ? ' com este status' : ''; ?>.</p>
                                <a href="agendar.php" class="btn btn-primary">Fazer Primeiro Agendamento</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="agendamentos-list">
                            <?php foreach ($agendamentos as $agendamento): ?>
                                <?php $statusInfo = formatarStatus($agendamento['status']); ?>
                                <div class="agendamento-card">
                                    <div class="row align-items-center">
                                        <!-- Data e Horário -->
                                        <div class="col-md-2">
                                            <div class="date-time">
                                                <div class="date">
                                                    <?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?>
                                                </div>
                                                <div class="time">
                                                    <?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Informações do Salão -->
                                        <div class="col-md-4">
                                            <div class="salon-info">
                                                <h4><?php echo htmlspecialchars($agendamento['salao_nome']); ?></h4>
                                                <p><?php echo htmlspecialchars($agendamento['endereco']); ?></p>
                                                <small>📞 <?php echo htmlspecialchars($agendamento['telefone']); ?></small>
                                            </div>
                                        </div>
                                        
                                        <!-- Profissional -->
                                        <div class="col-md-3">
                                            <div class="professional-info">
                                                <h5><?php echo htmlspecialchars($agendamento['profissional_nome']); ?></h5>
                                                <p><?php echo htmlspecialchars($agendamento['especialidade']); ?></p>
                                                <?php if ($agendamento['servico']): ?>
                                                    <small>✂️ <?php echo htmlspecialchars($agendamento['servico']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Status e Ações -->
                                        <div class="col-md-3">
                                            <div class="status-actions text-end">
                                                <span class="badge badge-<?php echo $statusInfo['classe']; ?>">
                                                    <?php echo $statusInfo['texto']; ?>
                                                </span>
                                                
                                                <div class="actions mt-2">
                                                    <?php if ($agendamento['status'] === 'pendente'): ?>
                                                        <a href="pagamento.php?agendamento_id=<?php echo $agendamento['id']; ?>" 
                                                           class="btn btn-sm btn-success">
                                                            💳 Pagar
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (in_array($agendamento['status'], ['confirmado', 'pendente'])): ?>
                                                        <button 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="cancelarAgendamento(<?php echo $agendamento['id']; ?>)"
                                                        >
                                                            ❌ Cancelar
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <button 
                                                        class="btn btn-sm btn-outline-primary"
                                                        onclick="verDetalhes(<?php echo $agendamento['id']; ?>)"
                                                    >
                                                        👁️ Detalhes
                                                    </button>
                                                </div>
                                                
                                                <?php if ($agendamento['valor_taxa']): ?>
                                                    <div class="taxa mt-2">
                                                        <small>Taxa: R$ <?php echo number_format($agendamento['valor_taxa'], 2, ',', '.'); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Observações -->
                                    <?php if ($agendamento['observacoes']): ?>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="observacoes">
                                                    <strong>Observações:</strong> <?php echo htmlspecialchars($agendamento['observacoes']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Estatísticas -->
                        <div class="stats-summary mt-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h4><?php echo count($agendamentos); ?></h4>
                                        <p>Total de Agendamentos</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h4>R$ <?php echo number_format(array_sum(array_column($agendamentos, 'valor_taxa')), 2, ',', '.'); ?></h4>
                                        <p>Total em Taxas</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h4><?php echo count(array_filter($agendamentos, fn($a) => $a['status'] === 'confirmado')); ?></h4>
                                        <p>Confirmados</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h4><?php echo count(array_filter($agendamentos, fn($a) => $a['status'] === 'concluido')); ?></h4>
                                        <p>Concluídos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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

    <script src="../assets/js/main.js"></script>
    
    <style>
        .filter-tabs {
            display: flex;
            border-bottom: 1px solid #e1e5e9;
            padding: 0 1.5rem;
        }
        
        .filter-tab {
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: #6c757d;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .filter-tab:hover {
            color: #667eea;
            text-decoration: none;
        }
        
        .filter-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: 600;
        }
        
        .agendamentos-list {
            padding: 1.5rem;
        }
        
        .agendamento-card {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .agendamento-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .date-time {
            text-align: center;
        }
        
        .date {
            font-size: 1.1rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .time {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        
        .salon-info h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .salon-info p {
            margin-bottom: 0.25rem;
            color: #6c757d;
        }
        
        .professional-info h5 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .professional-info p {
            margin-bottom: 0.25rem;
            color: #6c757d;
        }
        
        .status-actions .badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        
        .actions .btn {
            margin: 0.25rem;
        }
        
        .taxa {
            color: #28a745;
            font-weight: bold;
        }
        
        .observacoes {
            background: #fff;
            padding: 1rem;
            border-radius: 5px;
            border-left: 4px solid #667eea;
            font-size: 0.9rem;
        }
        
        .stats-summary {
            border-top: 1px solid #e1e5e9;
            padding-top: 1.5rem;
        }
        
        .stat-card {
            text-align: center;
            padding: 1rem;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e1e5e9;
        }
        
        .stat-card h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .empty-state {
            padding: 3rem 1.5rem;
        }
        
        @media (max-width: 768px) {
            .agendamento-card .row > div {
                margin-bottom: 1rem;
            }
            
            .status-actions {
                text-align: center !important;
            }
            
            .filter-tabs {
                flex-wrap: wrap;
                padding: 0 1rem;
            }
            
            .filter-tab {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
    
    <script>
        // Função para ver detalhes do agendamento
        function verDetalhes(agendamentoId) {
            // Implementar modal ou página de detalhes
            alert('Funcionalidade de detalhes será implementada em breve!');
        }
    </script>
</body>
</html>