<?php
/**
 * Dashboard do Cliente
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

require_once '../includes/auth.php';
$auth->requireAuth('cliente');

$user = $auth->getCurrentUser();

// Buscar agendamentos do cliente
try {
    $sql = "SELECT a.*, s.nome as salao_nome, p.nome as profissional_nome, p.especialidade 
            FROM agendamentos a 
            JOIN saloes s ON a.id_salao = s.id 
            JOIN profissionais p ON a.id_profissional = p.id 
            WHERE a.id_cliente = ? 
            ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC 
            LIMIT 10";
    $stmt = $database->query($sql, [$user['id']]);
    $agendamentos = $stmt->fetchAll();
    
    // Estatísticas do cliente
    $sqlStats = "SELECT 
                    COUNT(*) as total_agendamentos,
                    COUNT(CASE WHEN status = 'confirmado' THEN 1 END) as confirmados,
                    COUNT(CASE WHEN status = 'concluido' THEN 1 END) as concluidos,
                    SUM(valor_taxa) as total_gasto
                 FROM agendamentos WHERE id_cliente = ?";
    $stmtStats = $database->query($sqlStats, [$user['id']]);
    $stats = $stmtStats->fetch();
    
} catch (Exception $e) {
    $error = "Erro ao carregar dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Cliente - CorteFácil</title>
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
                        <li><a href="historico.php">Histórico</a></li>
                        <li><a href="../logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <!-- Boas-vindas -->
        <section class="welcome mt-4">
            <div class="card">
                <h1>Bem-vindo, <?php echo htmlspecialchars($user['nome']); ?>!</h1>
                <p>Gerencie seus agendamentos e agende novos serviços de beleza.</p>
                <a href="agendar.php" class="btn btn-primary">Fazer Novo Agendamento</a>
            </div>
        </section>

        <!-- Estatísticas -->
        <section class="stats mt-4">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_agendamentos'] ?? 0; ?></div>
                    <div class="stat-label">Total de Agendamentos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['confirmados'] ?? 0; ?></div>
                    <div class="stat-label">Confirmados</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['concluidos'] ?? 0; ?></div>
                    <div class="stat-label">Concluídos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number">R$ <?php echo number_format($stats['total_gasto'] ?? 0, 2, ',', '.'); ?></div>
                    <div class="stat-label">Total Gasto em Taxas</div>
                </div>
            </div>
        </section>

        <!-- Próximos Agendamentos -->
        <section class="upcoming-appointments mt-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Próximos Agendamentos</h2>
                </div>
                
                <?php if (empty($agendamentos)): ?>
                    <div class="text-center">
                        <p>Você ainda não tem agendamentos.</p>
                        <a href="agendar.php" class="btn btn-primary">Fazer Primeiro Agendamento</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Horário</th>
                                    <th>Salão</th>
                                    <th>Profissional</th>
                                    <th>Serviço</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['salao_nome']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($agendamento['profissional_nome']); ?>
                                            <br><small><?php echo htmlspecialchars($agendamento['especialidade']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($agendamento['servico'] ?? 'Não especificado'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo getStatusClass($agendamento['status']); ?>">
                                                <?php echo getStatusText($agendamento['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($agendamento['status'] === 'pendente'): ?>
                                                <a href="pagamento.php?id=<?php echo $agendamento['id']; ?>" 
                                                   class="btn btn-success btn-sm">Pagar</a>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($agendamento['status'], ['pendente', 'confirmado'])): ?>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="cancelarAgendamento(<?php echo $agendamento['id']; ?>)">
                                                    Cancelar
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="historico.php" class="btn btn-outline">Ver Histórico Completo</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Ações Rápidas -->
        <section class="quick-actions mt-4 mb-5">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Ações Rápidas</h2>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="action-card text-center">
                            <h4>📅 Agendar Serviço</h4>
                            <p>Encontre o salão perfeito e agende seu horário</p>
                            <a href="agendar.php" class="btn btn-primary">Agendar Agora</a>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="action-card text-center">
                            <h4>📋 Ver Histórico</h4>
                            <p>Consulte todos os seus agendamentos anteriores</p>
                            <a href="historico.php" class="btn btn-secondary">Ver Histórico</a>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="action-card text-center">
                            <h4>👤 Meu Perfil</h4>
                            <p>Atualize suas informações pessoais</p>
                            <a href="perfil.php" class="btn btn-secondary">Editar Perfil</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="header">
        <div class="container">
            <div class="text-center">
                <p>&copy; 2024 CorteFácil - Sistema de Agendamentos</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    
    <script>
        function cancelarAgendamento(agendamentoId) {
            if (confirm('Tem certeza que deseja cancelar este agendamento?')) {
                fetch('../api/cancelar_agendamento.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        agendamento_id: agendamentoId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Agendamento cancelado com sucesso', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showAlert(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showAlert('Erro ao cancelar agendamento', 'danger');
                });
            }
        }
    </script>
    
    <style>
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        
        .action-card {
            padding: 2rem 1rem;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .action-card h4 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</body>
</html>

<?php
function getStatusClass($status) {
    switch ($status) {
        case 'pendente': return 'warning';
        case 'confirmado': return 'success';
        case 'cancelado': return 'danger';
        case 'concluido': return 'info';
        default: return 'secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pendente': return 'Pendente';
        case 'confirmado': return 'Confirmado';
        case 'cancelado': return 'Cancelado';
        case 'concluido': return 'Concluído';
        default: return 'Desconhecido';
    }
}
?>