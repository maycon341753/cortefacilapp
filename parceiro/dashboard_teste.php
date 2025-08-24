<?php
/**
 * Dashboard do Parceiro - Versão de Teste (Sem Service Worker)
 * Para testar se a navegação funciona sem interferência
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/salao.php';

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();

// Buscar salão do parceiro
$meu_salao = $salao->buscarPorDono($usuario['id']);

// Se não tem salão, redirecionar para cadastro
if (!$meu_salao) {
    header('Location: salao.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Teste - CorteFácil Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../includes/parceiro_navigation.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard - Teste de Navegação</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Compartilhar</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
                        </div>
                    </div>
                </div>
                
                <!-- Alerta de Teste -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Teste de Navegação Ativo!</strong> Esta é uma versão de teste sem Service Worker.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                
                <!-- Informações do Salão -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-store me-2"></i>
                                    Informações do Salão
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($meu_salao['nome']); ?></p>
                                        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($meu_salao['telefone'] ?? 'Não informado'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($meu_salao['endereco'] ?? 'Não informado'); ?></p>
                                        <p><strong>Status:</strong> 
                                            <span class="badge bg-<?php echo ($meu_salao['ativo'] ?? 1) ? 'success' : 'danger'; ?>">
                                                <?php echo ($meu_salao['ativo'] ?? 1) ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cards de Estatísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card bg-primary text-white">
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="number">0</div>
                            <div class="label">Agendamentos Hoje</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card bg-success text-white">
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="number">0</div>
                            <div class="label">Profissionais</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card bg-info text-white">
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="number">0</div>
                            <div class="label">Pendentes</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-card bg-warning text-white">
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="number">R$ 0,00</div>
                            <div class="label">Faturamento Hoje</div>
                        </div>
                    </div>
                </div>
                
                <!-- Teste de Links -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-link me-2"></i>
                                    Teste de Navegação
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>Clique nos links abaixo para testar a navegação:</p>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <a href="agenda.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-calendar-alt me-2"></i>
                                            Agenda
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <a href="profissionais.php" class="btn btn-outline-success w-100">
                                            <i class="fas fa-users me-2"></i>
                                            Profissionais
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <a href="agendamentos.php" class="btn btn-outline-info w-100">
                                            <i class="fas fa-calendar-check me-2"></i>
                                            Agendamentos
                                        </a>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6 mb-2">
                                        <a href="salao.php" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-store me-2"></i>
                                            Meu Salão
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <a href="relatorios.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            Relatórios
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- NÃO incluir main.js ou vite-blocker para teste -->
    <script>
        console.log('✅ Dashboard de teste carregado sem Service Worker');
        console.log('🔍 Testando navegação limpa...');
        
        // Log de cliques para debug
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                const link = e.target.tagName === 'A' ? e.target : e.target.closest('a');
                console.log('🖱️ Clique detectado:', {
                    href: link.href,
                    text: link.textContent.trim(),
                    target: link.target || 'mesmo frame'
                });
            }
        });
    </script>
</body>
</html>