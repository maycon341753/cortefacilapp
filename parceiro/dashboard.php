<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Agendamento.php';
require_once __DIR__ . '/../models/Salao.php';
require_once __DIR__ . '/../models/Profissional.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar se é parceiro
verificarParceiro();

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// Buscar salão do parceiro
$salao = new Salao($db);
$salao->id_dono = $_SESSION['usuario_id'];
$tem_salao = $salao->buscarPorDono();

$agendamentos_hoje = 0;
$total_agendamentos = 0;
$receita_mes = 0;

if ($tem_salao) {
    // Buscar agendamentos do salão
    $agendamento = new Agendamento($db);
    $agendamento->id_salao = $salao->id;
    $stmt_agendamentos = $agendamento->listarPorSalao();
    
    // Buscar profissionais do salão
    $profissional = new Profissional($db);
    $profissional->id_salao = $salao->id;
    $stmt_profissionais = $profissional->listarPorSalao();
    
    // Calcular estatísticas
    $stmt_agendamentos->execute();
    while($agend = $stmt_agendamentos->fetch()) {
        $total_agendamentos++;
        if ($agend['data_agendamento'] === date('Y-m-d')) {
            $agendamentos_hoje++;
        }
        if ($agend['status'] === 'confirmado' && 
            date('Y-m', strtotime($agend['data_agendamento'])) === date('Y-m')) {
            $receita_mes += $agend['valor_taxa'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Parceiro - CorteFácil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
    <!-- Header -->
    <header class="dashboard-header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">
                    <span class="logo-icon">✂️</span>
                    CorteFácil
                </a>
                
                <div class="user-menu">
                    <span class="user-name">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</span>
                    <a href="../logout.php" class="btn btn-outline btn-small">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="container">
            <?php if (!$tem_salao): ?>
                <!-- Cadastro de Salão -->
                <div class="setup-salao">
                    <div class="setup-card">
                        <h1>Configure seu Salão</h1>
                        <p>Para começar a receber agendamentos, você precisa cadastrar as informações do seu salão.</p>
                        
                        <form method="POST" action="cadastrar_salao.php" class="setup-form">
                            <div class="form-group">
                                <label for="nome">Nome do Salão</label>
                                <input type="text" id="nome" name="nome" required placeholder="Ex: Salão Beleza Total">
                            </div>
                            
                            <div class="form-group">
                                <label for="endereco">Endereço Completo</label>
                                <textarea id="endereco" name="endereco" required placeholder="Rua, número, bairro, cidade"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" required placeholder="(11) 99999-9999">
                            </div>
                            
                            <div class="form-group">
                                <label for="descricao">Descrição do Salão</label>
                                <textarea id="descricao" name="descricao" placeholder="Descreva os serviços e diferenciais do seu salão"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="horario_funcionamento">Horário de Funcionamento</label>
                                <input type="text" id="horario_funcionamento" name="horario_funcionamento" required placeholder="Ex: Segunda a Sábado: 8h às 18h">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-large">Cadastrar Salão</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Dashboard Principal -->
                <div class="dashboard-grid">
                    <!-- Sidebar -->
                    <aside class="dashboard-sidebar">
                        <div class="salao-info">
                            <h3><?php echo htmlspecialchars($salao->nome); ?></h3>
                            <p><?php echo htmlspecialchars($salao->endereco); ?></p>
                        </div>
                        
                        <nav class="sidebar-nav">
                            <a href="#overview" class="nav-item active">
                                <span class="nav-icon">📊</span>
                                Visão Geral
                            </a>
                            <a href="#agendamentos" class="nav-item">
                                <span class="nav-icon">📅</span>
                                Agendamentos
                            </a>
                            <a href="#profissionais" class="nav-item">
                                <span class="nav-icon">👥</span>
                                Profissionais
                            </a>
                            <a href="#salao" class="nav-item">
                                <span class="nav-icon">🏪</span>
                                Meu Salão
                            </a>
                        </nav>
                    </aside>

                    <!-- Content -->
                    <div class="dashboard-content">
                        <!-- Overview Section -->
                        <section id="overview" class="content-section">
                            <div class="welcome-section">
                                <h1>Dashboard - <?php echo htmlspecialchars($salao->nome); ?></h1>
                                <p>Gerencie seus agendamentos e profissionais.</p>
                            </div>

                            <!-- Stats Cards -->
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">📅</div>
                                    <div class="stat-info">
                                        <span class="stat-number"><?php echo $agendamentos_hoje; ?></span>
                                        <span class="stat-label">Hoje</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon">📊</div>
                                    <div class="stat-info">
                                        <span class="stat-number"><?php echo $total_agendamentos; ?></span>
                                        <span class="stat-label">Total</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon">💰</div>
                                    <div class="stat-info">
                                        <span class="stat-number">R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?></span>
                                        <span class="stat-label">Este Mês</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon">👥</div>
                                    <div class="stat-info">
                                        <span class="stat-number"><?php echo $stmt_profissionais->rowCount(); ?></span>
                                        <span class="stat-label">Profissionais</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Agendamentos Recentes -->
                            <div class="recent-section">
                                <h2>Agendamentos de Hoje</h2>
                                <div class="agendamentos-list">
                                    <?php 
                                    $stmt_agendamentos->execute();
                                    $tem_hoje = false;
                                    while($agend = $stmt_agendamentos->fetch()):
                                        if ($agend['data_agendamento'] === date('Y-m-d')):
                                            $tem_hoje = true;
                                    ?>
                                        <div class="agendamento-card status-<?php echo $agend['status']; ?>">
                                            <div class="agendamento-info">
                                                <h4><?php echo htmlspecialchars($agend['nome_cliente']); ?></h4>
                                                <p><strong>Profissional:</strong> <?php echo htmlspecialchars($agend['nome_profissional']); ?></p>
                                                <p><strong>Serviço:</strong> <?php echo htmlspecialchars($agend['servico']); ?></p>
                                                <p><strong>Horário:</strong> <?php echo date('H:i', strtotime($agend['hora_agendamento'])); ?></p>
                                            </div>
                                            <div class="agendamento-status">
                                                <span class="status-badge status-<?php echo $agend['status']; ?>">
                                                    <?php 
                                                    switch($agend['status']) {
                                                        case 'pendente': echo 'Pendente'; break;
                                                        case 'confirmado': echo 'Confirmado'; break;
                                                        case 'concluido': echo 'Concluído'; break;
                                                        case 'cancelado': echo 'Cancelado'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php 
                                        endif;
                                    endwhile;
                                    
                                    if (!$tem_hoje):
                                    ?>
                                        <div class="empty-state">
                                            <p>Nenhum agendamento para hoje.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>

                        <!-- Agendamentos Section -->
                        <section id="agendamentos" class="content-section">
                            <div class="section-header">
                                <h2>Todos os Agendamentos</h2>
                            </div>

                            <div class="agendamentos-list">
                                <?php 
                                $stmt_agendamentos->execute();
                                if ($stmt_agendamentos->rowCount() > 0):
                                    while($agend = $stmt_agendamentos->fetch()):
                                ?>
                                    <div class="agendamento-card status-<?php echo $agend['status']; ?>">
                                        <div class="agendamento-info">
                                            <h4><?php echo htmlspecialchars($agend['nome_cliente']); ?></h4>
                                            <p><strong>Profissional:</strong> <?php echo htmlspecialchars($agend['nome_profissional']); ?></p>
                                            <p><strong>Serviço:</strong> <?php echo htmlspecialchars($agend['servico']); ?></p>
                                            <div class="agendamento-datetime">
                                                <span class="data">📅 <?php echo formatarDataBR($agend['data_agendamento']); ?></span>
                                                <span class="hora">⏰ <?php echo date('H:i', strtotime($agend['hora_agendamento'])); ?></span>
                                            </div>
                                            <?php if ($agend['observacoes']): ?>
                                                <p><strong>Observações:</strong> <?php echo htmlspecialchars($agend['observacoes']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="agendamento-status">
                                            <span class="status-badge status-<?php echo $agend['status']; ?>">
                                                <?php 
                                                switch($agend['status']) {
                                                    case 'pendente': echo 'Pendente'; break;
                                                    case 'confirmado': echo 'Confirmado'; break;
                                                    case 'concluido': echo 'Concluído'; break;
                                                    case 'cancelado': echo 'Cancelado'; break;
                                                }
                                                ?>
                                            </span>
                                            <?php if ($agend['status'] === 'confirmado'): ?>
                                                <button class="btn btn-primary btn-small" onclick="concluirAgendamento(<?php echo $agend['id']; ?>)">
                                                    Concluir
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">📅</div>
                                        <h3>Nenhum agendamento encontrado</h3>
                                        <p>Quando clientes agendarem serviços, eles aparecerão aqui.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- Profissionais Section -->
                        <section id="profissionais" class="content-section">
                            <div class="section-header">
                                <h2>Profissionais</h2>
                                <button class="btn btn-primary" onclick="adicionarProfissional()">
                                    Adicionar Profissional
                                </button>
                            </div>

                            <div class="profissionais-grid">
                                <?php 
                                if ($stmt_profissionais->rowCount() > 0):
                                    while($prof = $stmt_profissionais->fetch()):
                                ?>
                                    <div class="profissional-card">
                                        <div class="profissional-info">
                                            <h3><?php echo htmlspecialchars($prof['nome']); ?></h3>
                                            <p><strong>Especialidade:</strong> <?php echo htmlspecialchars($prof['especialidade']); ?></p>
                                            <p><strong>Telefone:</strong> <?php echo formatarTelefone($prof['telefone']); ?></p>
                                            <p><strong>Horário:</strong> <?php echo htmlspecialchars($prof['horario_trabalho']); ?></p>
                                        </div>
                                        <div class="profissional-actions">
                                            <button class="btn btn-outline btn-small" onclick="editarProfissional(<?php echo $prof['id']; ?>)">
                                                Editar
                                            </button>
                                        </div>
                                    </div>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">👥</div>
                                        <h3>Nenhum profissional cadastrado</h3>
                                        <p>Adicione profissionais para começar a receber agendamentos.</p>
                                        <button class="btn btn-primary" onclick="adicionarProfissional()">
                                            Adicionar Primeiro Profissional
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- Salão Section -->
                        <section id="salao" class="content-section">
                            <div class="section-header">
                                <h2>Informações do Salão</h2>
                                <button class="btn btn-outline" onclick="editarSalao()">
                                    Editar Informações
                                </button>
                            </div>

                            <div class="salao-details">
                                <div class="detail-card">
                                    <h3><?php echo htmlspecialchars($salao->nome); ?></h3>
                                    <p><strong>Endereço:</strong> <?php echo htmlspecialchars($salao->endereco); ?></p>
                                    <p><strong>Telefone:</strong> <?php echo formatarTelefone($salao->telefone); ?></p>
                                    <p><strong>Horário:</strong> <?php echo htmlspecialchars($salao->horario_funcionamento); ?></p>
                                    <?php if ($salao->descricao): ?>
                                        <p><strong>Descrição:</strong> <?php echo htmlspecialchars($salao->descricao); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Cadastrado em:</strong> <?php echo formatarDataBR($salao->data_cadastro); ?></p>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Navegação entre seções
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                const target = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(target);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
            });
        });

        // Funções de ação
        function concluirAgendamento(id) {
            if (confirm('Marcar este agendamento como concluído?')) {
                // Implementar API call
                alert('Funcionalidade em desenvolvimento');
            }
        }

        function adicionarProfissional() {
            alert('Funcionalidade de adicionar profissional em desenvolvimento');
        }

        function editarProfissional(id) {
            alert('Funcionalidade de editar profissional em desenvolvimento');
        }

        function editarSalao() {
            alert('Funcionalidade de editar salão em desenvolvimento');
        }

        // Mostrar primeira seção por padrão
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.content-section').forEach((section, index) => {
                section.style.display = index === 0 ? 'block' : 'none';
            });
        });

        // Máscara para telefone no formulário de cadastro
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    e.target.value = value;
                }
            });
        }
    </script>
</body>
</html>