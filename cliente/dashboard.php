<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Agendamento.php';
require_once __DIR__ . '/../models/Salao.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar se é cliente
verificarCliente();

// Conectar ao banco
$database = new Database();
$db = $database->getConnection();

// Buscar agendamentos do cliente
$agendamento = new Agendamento($db);
$agendamento->id_cliente = $_SESSION['usuario_id'];
$stmt_agendamentos = $agendamento->listarPorCliente();

// Buscar salões disponíveis
$salao = new Salao($db);
$stmt_saloes = $salao->listarComProfissionais();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente - CorteFácil</title>
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
            <div class="dashboard-grid">
                <!-- Sidebar -->
                <aside class="dashboard-sidebar">
                    <nav class="sidebar-nav">
                        <a href="#agendamentos" class="nav-item active">
                            <span class="nav-icon">📅</span>
                            Meus Agendamentos
                        </a>
                        <a href="#novo-agendamento" class="nav-item">
                            <span class="nav-icon">➕</span>
                            Novo Agendamento
                        </a>
                        <a href="#perfil" class="nav-item">
                            <span class="nav-icon">👤</span>
                            Meu Perfil
                        </a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="dashboard-content">
                    <!-- Welcome Section -->
                    <div class="welcome-section">
                        <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
                        <p>Gerencie seus agendamentos e encontre os melhores profissionais.</p>
                    </div>

                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-info">
                                <span class="stat-number">
                                    <?php echo $stmt_agendamentos->rowCount(); ?>
                                </span>
                                <span class="stat-label">Agendamentos</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">⏰</div>
                            <div class="stat-info">
                                <span class="stat-number">
                                    <?php 
                                    $stmt_agendamentos->execute();
                                    $pendentes = 0;
                                    while($row = $stmt_agendamentos->fetch()) {
                                        if($row['status'] === 'pendente') $pendentes++;
                                    }
                                    echo $pendentes;
                                    ?>
                                </span>
                                <span class="stat-label">Pendentes</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✅</div>
                            <div class="stat-info">
                                <span class="stat-number">
                                    <?php 
                                    $stmt_agendamentos->execute();
                                    $confirmados = 0;
                                    while($row = $stmt_agendamentos->fetch()) {
                                        if($row['status'] === 'confirmado') $confirmados++;
                                    }
                                    echo $confirmados;
                                    ?>
                                </span>
                                <span class="stat-label">Confirmados</span>
                            </div>
                        </div>
                    </div>

                    <!-- Agendamentos Section -->
                    <section id="agendamentos" class="content-section">
                        <div class="section-header">
                            <h2>Meus Agendamentos</h2>
                            <a href="../booking.php" class="btn btn-primary">Novo Agendamento</a>
                        </div>

                        <div class="agendamentos-list">
                            <?php 
                            $stmt_agendamentos->execute();
                            if ($stmt_agendamentos->rowCount() > 0):
                                while($agend = $stmt_agendamentos->fetch()):
                            ?>
                                <div class="agendamento-card status-<?php echo $agend['status']; ?>">
                                    <div class="agendamento-info">
                                        <h3><?php echo htmlspecialchars($agend['nome_salao']); ?></h3>
                                        <p class="profissional">
                                            <strong>Profissional:</strong> <?php echo htmlspecialchars($agend['nome_profissional']); ?>
                                        </p>
                                        <p class="servico">
                                            <strong>Serviço:</strong> <?php echo htmlspecialchars($agend['servico']); ?>
                                        </p>
                                        <div class="agendamento-datetime">
                                            <span class="data">📅 <?php echo formatarDataBR($agend['data_agendamento']); ?></span>
                                            <span class="hora">⏰ <?php echo date('H:i', strtotime($agend['hora_agendamento'])); ?></span>
                                        </div>
                                        <?php if ($agend['observacoes']): ?>
                                            <p class="observacoes">
                                                <strong>Observações:</strong> <?php echo htmlspecialchars($agend['observacoes']); ?>
                                            </p>
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
                                        <?php if ($agend['status'] === 'pendente'): ?>
                                            <div class="agendamento-actions">
                                                <a href="../payment.php?id=<?php echo $agend['id']; ?>" class="btn btn-primary btn-small">
                                                    Pagar Taxa
                                                </a>
                                                <button class="btn btn-outline btn-small" onclick="cancelarAgendamento(<?php echo $agend['id']; ?>)">
                                                    Cancelar
                                                </button>
                                            </div>
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
                                    <p>Você ainda não fez nenhum agendamento. Que tal começar agora?</p>
                                    <a href="../booking.php" class="btn btn-primary">Fazer Agendamento</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Salões Disponíveis -->
                    <section id="novo-agendamento" class="content-section">
                        <div class="section-header">
                            <h2>Salões Disponíveis</h2>
                        </div>

                        <div class="saloes-grid">
                            <?php while($sal = $stmt_saloes->fetch()): ?>
                                <div class="salao-card">
                                    <div class="salao-info">
                                        <h3><?php echo htmlspecialchars($sal['nome']); ?></h3>
                                        <p class="endereco">📍 <?php echo htmlspecialchars($sal['endereco']); ?></p>
                                        <p class="telefone">📞 <?php echo formatarTelefone($sal['telefone']); ?></p>
                                        <p class="horario">🕒 <?php echo htmlspecialchars($sal['horario_funcionamento']); ?></p>
                                        <?php if ($sal['total_profissionais'] > 0): ?>
                                            <p class="profissionais">
                                                👥 <?php echo $sal['total_profissionais']; ?> profissional(is)
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($sal['descricao']): ?>
                                            <p class="descricao"><?php echo htmlspecialchars($sal['descricao']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="salao-actions">
                                        <a href="../booking.php?salao=<?php echo $sal['id']; ?>" class="btn btn-primary">
                                            Agendar
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </section>

                    <!-- Perfil Section -->
                    <section id="perfil" class="content-section">
                        <div class="section-header">
                            <h2>Meu Perfil</h2>
                        </div>

                        <div class="profile-card">
                            <div class="profile-info">
                                <h3><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></h3>
                                <p>📧 <?php echo htmlspecialchars($_SESSION['usuario_email']); ?></p>
                                <p>📞 <?php echo formatarTelefone($_SESSION['usuario_telefone']); ?></p>
                                <p>👤 Cliente</p>
                            </div>
                            <div class="profile-actions">
                                <button class="btn btn-outline" onclick="editarPerfil()">
                                    Editar Perfil
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Navegação entre seções
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all nav items
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show target section
                const target = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(target);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
            });
        });

        // Cancelar agendamento
        function cancelarAgendamento(id) {
            if (confirm('Tem certeza que deseja cancelar este agendamento?')) {
                fetch('../api/cancelar_agendamento.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao cancelar agendamento: ' + data.message);
                    }
                });
            }
        }

        // Editar perfil
        function editarPerfil() {
            alert('Funcionalidade de edição de perfil em desenvolvimento.');
        }

        // Mostrar primeira seção por padrão
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.content-section').forEach((section, index) => {
                section.style.display = index === 0 ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>