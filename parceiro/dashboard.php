<?php
/**
 * Dashboard do Parceiro (Dono do Salão)
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once '../includes/auth.php';
$auth->requireAuth('parceiro');

$user = $auth->getCurrentUser();
$stats = [];
$proximosAgendamentos = [];
$error = '';

try {
    // Buscar salão do parceiro
    $sqlSalao = "SELECT * FROM saloes WHERE id_dono = ? AND ativo = 1";
    $stmtSalao = $database->query($sqlSalao, [$user['id']]);
    $salao = $stmtSalao->fetch();
    
    if (!$salao) {
        // Redirecionar para cadastro do salão se não existir
        header('Location: cadastrar_salao.php');
        exit;
    }
    
    // Estatísticas do salão
    $hoje = date('Y-m-d');
    $mesAtual = date('Y-m');
    
    // Total de agendamentos hoje
    $sqlHoje = "SELECT COUNT(*) as total FROM agendamentos 
                WHERE id_salao = ? AND data_agendamento = ? AND status != 'cancelado'";
    $stmtHoje = $database->query($sqlHoje, [$salao['id'], $hoje]);
    $stats['hoje'] = $stmtHoje->fetch()['total'];
    
    // Total de agendamentos este mês
    $sqlMes = "SELECT COUNT(*) as total FROM agendamentos 
               WHERE id_salao = ? AND DATE_FORMAT(data_agendamento, '%Y-%m') = ? AND status != 'cancelado'";
    $stmtMes = $database->query($sqlMes, [$salao['id'], $mesAtual]);
    $stats['mes'] = $stmtMes->fetch()['total'];
    
    // Total de profissionais
    $sqlProf = "SELECT COUNT(*) as total FROM profissionais WHERE id_salao = ? AND ativo = 1";
    $stmtProf = $database->query($sqlProf, [$salao['id']]);
    $stats['profissionais'] = $stmtProf->fetch()['total'];
    
    // Receita estimada do mês (baseada nos agendamentos confirmados/concluídos)
    $sqlReceita = "SELECT COUNT(*) as total FROM agendamentos 
                   WHERE id_salao = ? AND DATE_FORMAT(data_agendamento, '%Y-%m') = ? 
                   AND status IN ('confirmado', 'concluido')";
    $stmtReceita = $database->query($sqlReceita, [$salao['id'], $mesAtual]);
    $stats['receita'] = $stmtReceita->fetch()['total'] * 1.29; // Taxa por agendamento
    
    // Próximos agendamentos (próximos 7 dias)
    $dataLimite = date('Y-m-d', strtotime('+7 days'));
    $sqlProximos = "SELECT a.*, u.nome as cliente_nome, u.telefone as cliente_telefone,
                           p.nome as profissional_nome, p.especialidade
                    FROM agendamentos a 
                    JOIN usuarios u ON a.id_cliente = u.id 
                    JOIN profissionais p ON a.id_profissional = p.id 
                    WHERE a.id_salao = ? AND a.data_agendamento BETWEEN ? AND ? 
                    AND a.status IN ('confirmado', 'pendente')
                    ORDER BY a.data_agendamento ASC, a.hora_agendamento ASC 
                    LIMIT 10";
    
    $stmtProximos = $database->query($sqlProximos, [$salao['id'], $hoje, $dataLimite]);
    $proximosAgendamentos = $stmtProximos->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erro ao carregar dados: ' . $e->getMessage();
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
    <title>Painel do Parceiro - CorteFácil</title>
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
                        <li><a href="dashboard.php" class="active">Painel</a></li>
                        <li><a href="agenda.php">Agenda</a></li>
                        <li><a href="profissionais.php">Profissionais</a></li>
                        <li><a href="relatorios.php">Relatórios</a></li>
                        <li><a href="../logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger mt-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <!-- Cabeçalho do Salão -->
            <div class="salon-header mt-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1>🏪 <?php echo htmlspecialchars($salao['nome']); ?></h1>
                        <p><?php echo htmlspecialchars($salao['endereco']); ?></p>
                        <p>📞 <?php echo htmlspecialchars($salao['telefone']); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="editar_salao.php" class="btn btn-outline-primary">
                            ✏️ Editar Salão
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="stats-grid mt-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['hoje']; ?></h3>
                                <p>Agendamentos Hoje</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['mes']; ?></h3>
                                <p>Agendamentos Este Mês</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['profissionais']; ?></h3>
                                <p>Profissionais Ativos</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-content">
                                <h3>R$ <?php echo number_format($stats['receita'], 2, ',', '.'); ?></h3>
                                <p>Receita Estimada (Mês)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="quick-actions mt-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Ações Rápidas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="agenda.php" class="action-card">
                                    <div class="action-icon">📅</div>
                                    <h4>Ver Agenda</h4>
                                    <p>Visualizar todos os agendamentos</p>
                                </a>
                            </div>
                            
                            <div class="col-md-3">
                                <a href="profissionais.php" class="action-card">
                                    <div class="action-icon">👤</div>
                                    <h4>Gerenciar Profissionais</h4>
                                    <p>Adicionar ou editar profissionais</p>
                                </a>
                            </div>
                            
                            <div class="col-md-3">
                                <a href="agenda.php?data=<?php echo date('Y-m-d'); ?>" class="action-card">
                                    <div class="action-icon">📋</div>
                                    <h4>Agenda de Hoje</h4>
                                    <p>Ver agendamentos de hoje</p>
                                </a>
                            </div>
                            
                            <div class="col-md-3">
                                <a href="relatorios.php" class="action-card">
                                    <div class="action-icon">📈</div>
                                    <h4>Relatórios</h4>
                                    <p>Análises e estatísticas</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Próximos Agendamentos -->
            <div class="upcoming-appointments mt-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>Próximos Agendamentos</h3>
                            <a href="agenda.php" class="btn btn-primary">Ver Todos</a>
                        </div>
                    </div>
                    
                    <?php if (empty($proximosAgendamentos)): ?>
                        <div class="empty-state">
                            <div class="text-center py-4">
                                <h4>📅 Nenhum agendamento próximo</h4>
                                <p>Não há agendamentos confirmados para os próximos 7 dias.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="appointments-list">
                            <?php foreach ($proximosAgendamentos as $agendamento): ?>
                                <?php $statusInfo = formatarStatus($agendamento['status']); ?>
                                <div class="appointment-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <div class="appointment-date">
                                                <div class="date">
                                                    <?php echo date('d/m', strtotime($agendamento['data_agendamento'])); ?>
                                                </div>
                                                <div class="time">
                                                    <?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="client-info">
                                                <h5><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></h5>
                                                <p>📞 <?php echo htmlspecialchars($agendamento['cliente_telefone']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="professional-info">
                                                <h5><?php echo htmlspecialchars($agendamento['profissional_nome']); ?></h5>
                                                <p><?php echo htmlspecialchars($agendamento['especialidade']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <span class="badge badge-<?php echo $statusInfo['classe']; ?>">
                                                <?php echo $statusInfo['texto']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <div class="appointment-actions">
                                                <?php if ($agendamento['status'] === 'confirmado'): ?>
                                                    <button 
                                                        class="btn btn-sm btn-success"
                                                        onclick="marcarConcluido(<?php echo $agendamento['id']; ?>)"
                                                        title="Marcar como concluído"
                                                    >
                                                        ✅
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button 
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="verDetalhes(<?php echo $agendamento['id']; ?>)"
                                                    title="Ver detalhes"
                                                >
                                                    👁️
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($agendamento['servico'] || $agendamento['observacoes']): ?>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="appointment-details">
                                                    <?php if ($agendamento['servico']): ?>
                                                        <strong>Serviço:</strong> <?php echo htmlspecialchars($agendamento['servico']); ?>
                                                    <?php endif; ?>
                                                    <?php if ($agendamento['observacoes']): ?>
                                                        <br><strong>Obs:</strong> <?php echo htmlspecialchars($agendamento['observacoes']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
        .salon-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .salon-header h1 {
            margin-bottom: 0.5rem;
        }
        
        .salon-header p {
            margin-bottom: 0.25rem;
            opacity: 0.9;
        }
        
        .stats-grid .stat-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-grid .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-content h3 {
            color: #667eea;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-content p {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .action-card {
            display: block;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }
        
        .action-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .action-card h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .action-card p {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .appointments-list {
            padding: 1.5rem;
        }
        
        .appointment-item {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .appointment-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .appointment-date {
            text-align: center;
        }
        
        .appointment-date .date {
            font-size: 1.1rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .appointment-date .time {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        
        .client-info h5, .professional-info h5 {
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .client-info p, .professional-info p {
            margin-bottom: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .appointment-actions .btn {
            margin: 0.25rem;
        }
        
        .appointment-details {
            background: white;
            padding: 1rem;
            border-radius: 5px;
            border-left: 4px solid #667eea;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .salon-header {
                text-align: center;
            }
            
            .appointment-item .row > div {
                margin-bottom: 1rem;
            }
            
            .appointment-actions {
                text-align: center;
            }
        }
    </style>
    
    <script>
        // Função para marcar agendamento como concluído
        function marcarConcluido(agendamentoId) {
            if (confirm('Marcar este agendamento como concluído?')) {
                fetch('../api/atualizar_status_agendamento.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        agendamento_id: agendamentoId,
                        status: 'concluido'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Agendamento marcado como concluído!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert(data.message || 'Erro ao atualizar status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showAlert('Erro ao atualizar status', 'error');
                });
            }
        }
        
        // Função para ver detalhes do agendamento
        function verDetalhes(agendamentoId) {
            // Implementar modal ou página de detalhes
            alert('Funcionalidade de detalhes será implementada em breve!');
        }
    </script>
</body>
</html>