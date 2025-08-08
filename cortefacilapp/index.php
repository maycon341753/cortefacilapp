<?php
/**
 * Página inicial do Sistema SaaS de Agendamentos
 * Sistema para Salões de Beleza
 */

session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Sistema de Agendamentos para Salões</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">CorteFácil</a>
                <nav>
                    <ul class="nav-menu">
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                            <li><a href="<?php echo $_SESSION['user_type']; ?>/dashboard.php">Painel</a></li>
                            <li><a href="logout.php">Sair</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Entrar</a></li>
                            <li><a href="register.php">Cadastrar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert alert-success mt-4">
                ✅ Logout realizado com sucesso! Obrigado por usar o CorteFácil.
            </div>
        <?php endif; ?>
        <!-- Hero Section -->
        <section class="hero mt-5">
            <div class="card text-center">
                <h1 class="card-title">Bem-vindo ao CorteFácil</h1>
                <p class="mb-4">A plataforma completa para agendamentos em salões de beleza. Conectamos clientes e profissionais de forma simples e eficiente.</p>
                
                <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Para Clientes</h3>
                                </div>
                                <p>Agende seus serviços de beleza com facilidade</p>
                                <ul class="text-left mb-4">
                                    <li>Escolha seu salão favorito</li>
                                    <li>Selecione o profissional</li>
                                    <li>Agende no horário que preferir</li>
                                    <li>Pague apenas R$ 1,29 por agendamento</li>
                                </ul>
                                <a href="register.php?tipo=cliente" class="btn btn-primary">Cadastrar como Cliente</a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Para Salões</h3>
                                </div>
                                <p>Gerencie seus agendamentos sem custo mensal</p>
                                <ul class="text-left mb-4">
                                    <li>Cadastro gratuito</li>
                                    <li>Sem mensalidade</li>
                                    <li>Gerencie profissionais</li>
                                    <li>Controle total da agenda</li>
                                </ul>
                                <a href="register.php?tipo=parceiro" class="btn btn-success">Cadastrar Salão</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="dashboard-preview">
                        <h3>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h3>
                        <p class="mb-4">Bem-vindo de volta ao seu painel de controle.</p>
                        <a href="<?php echo $_SESSION['user_type']; ?>/dashboard.php" class="btn btn-primary">Acessar Painel</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section class="features mt-5">
            <div class="card">
                <div class="card-header text-center">
                    <h2 class="card-title">Por que escolher o CorteFácil?</h2>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <h4>💰 Sem Mensalidade</h4>
                            <p>Salões parceiros não pagam mensalidade. Apenas os clientes pagam R$ 1,29 por agendamento.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <h4>📅 Agenda Inteligente</h4>
                            <p>Sistema que evita conflitos de horários automaticamente. Nunca mais duplo agendamento!</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <h4>📱 Fácil de Usar</h4>
                            <p>Interface simples e intuitiva. Funciona perfeitamente em computadores e celulares.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <h4>🔒 Seguro</h4>
                            <p>Seus dados estão protegidos com as melhores práticas de segurança.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <h4>⚡ Rápido</h4>
                            <p>Agendamento em poucos cliques. Processo otimizado para economizar seu tempo.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="feature-item text-center">
                            <h4>📊 Relatórios</h4>
                            <p>Acompanhe estatísticas e relatórios detalhados dos seus agendamentos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Como Funciona -->
        <section class="how-it-works mt-5">
            <div class="card">
                <div class="card-header text-center">
                    <h2 class="card-title">Como Funciona</h2>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="step text-center">
                            <div class="step-number">1</div>
                            <h4>Cadastre-se</h4>
                            <p>Crie sua conta como cliente ou registre seu salão</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="step text-center">
                            <div class="step-number">2</div>
                            <h4>Escolha</h4>
                            <p>Selecione o salão, profissional e horário desejado</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="step text-center">
                            <div class="step-number">3</div>
                            <h4>Agende</h4>
                            <p>Confirme seu agendamento pagando apenas R$ 1,29</p>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="step text-center">
                            <div class="step-number">4</div>
                            <h4>Compareça</h4>
                            <p>Vá ao salão no horário marcado e pague o serviço lá</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Estatísticas -->
        <section class="stats mt-5 mb-5">
            <div class="card">
                <div class="card-header text-center">
                    <h2 class="card-title">Números que Impressionam</h2>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Salões Parceiros</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">10.000+</div>
                        <div class="stat-label">Clientes Satisfeitos</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">50.000+</div>
                        <div class="stat-label">Agendamentos Realizados</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfação</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="header mt-5">
        <div class="container">
            <div class="text-center">
                <p>&copy; 2024 CorteFácil - Sistema de Agendamentos para Salões de Beleza</p>
                <p>Desenvolvido com ❤️ para conectar clientes e profissionais</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 3rem;
        }
        
        .hero .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature-item {
            padding: 2rem 1rem;
        }
        
        .feature-item h4 {
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .step {
            padding: 2rem 1rem;
        }
        
        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .step h4 {
            color: #333;
            margin-bottom: 1rem;
        }
    </style>
</body>
</html>