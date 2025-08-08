<?php
/**
 * Página inicial do Sistema SaaS de Agendamentos
 * Sistema para Salões de Beleza - CorteFácil
 */

session_start();

// Verificar se há mensagens de feedback
$message = '';
$message_type = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $message = '✅ Logout realizado com sucesso! Obrigado por usar o CorteFácil.';
    $message_type = 'success';
}

if (isset($_GET['register']) && $_GET['register'] === 'success') {
    $message = '🎉 Cadastro realizado com sucesso! Faça login para continuar.';
    $message_type = 'success';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Sistema de Agendamentos para Salões de Beleza</title>
    <meta name="description" content="Plataforma completa para agendamentos em salões de beleza. Conecte clientes e profissionais de forma simples e eficiente.">
    <meta name="keywords" content="agendamento, salão, beleza, cabelo, manicure, pedicure">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <span class="logo-icon">✂️</span>
                    CorteFácil
                </a>
                <nav class="nav-desktop">
                    <ul class="nav-menu">
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                            <li><a href="<?php echo $_SESSION['user_type']; ?>/dashboard.php" class="nav-link">
                                <span class="nav-icon">📊</span> Painel
                            </a></li>
                            <li><a href="logout.php" class="nav-link">
                                <span class="nav-icon">🚪</span> Sair
                            </a></li>
                        <?php else: ?>
                            <li><a href="#como-funciona" class="nav-link">Como Funciona</a></li>
                            <li><a href="#funcionalidades" class="nav-link">Funcionalidades</a></li>
                            <li><a href="login.php" class="nav-link btn-outline-header">Entrar</a></li>
                            <li><a href="register.php" class="nav-link btn-primary-header">Cadastrar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="main-content">
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> container mt-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-background"></div>
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">
                        Transforme seu <span class="highlight">Salão</span> 
                        <br>com Agendamentos <span class="highlight">Inteligentes</span>
                    </h1>
                    <p class="hero-subtitle">
                        A plataforma completa que conecta clientes e profissionais de beleza. 
                        <br>Sem mensalidade para salões, apenas R$ 1,29 por agendamento para clientes.
                    </p>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Salões</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">10k+</span>
                            <span class="stat-label">Clientes</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">50k+</span>
                            <span class="stat-label">Agendamentos</span>
                        </div>
                    </div>
                
                    <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
                        <div class="hero-cta">
                            <div class="cta-cards">
                                <div class="cta-card client-card">
                                    <div class="cta-icon">👤</div>
                                    <h3>Para Clientes</h3>
                                    <p>Agende seus serviços de beleza com facilidade</p>
                                    <ul class="cta-features">
                                        <li>✨ Escolha seu salão favorito</li>
                                        <li>💇‍♀️ Selecione o profissional</li>
                                        <li>⏰ Agende no horário que preferir</li>
                                        <li>💰 Pague apenas R$ 1,29 por agendamento</li>
                                    </ul>
                                    <a href="register.php?tipo=cliente" class="btn btn-primary btn-large">
                                        Começar Agora
                                    </a>
                                </div>
                                
                                <div class="cta-card salon-card">
                                    <div class="cta-icon">🏪</div>
                                    <h3>Para Salões</h3>
                                    <p>Gerencie seus agendamentos sem custo mensal</p>
                                    <ul class="cta-features">
                                        <li>🆓 Cadastro gratuito</li>
                                        <li>💸 Sem mensalidade</li>
                                        <li>👥 Gerencie profissionais</li>
                                        <li>📊 Controle total da agenda</li>
                                    </ul>
                                    <a href="register.php?tipo=parceiro" class="btn btn-success btn-large">
                                        Cadastrar Salão
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="welcome-back">
                            <div class="welcome-card">
                                <h3>Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h3>
                                <p>Bem-vindo de volta ao seu painel de controle.</p>
                                <a href="<?php echo $_SESSION['user_type']; ?>/dashboard.php" class="btn btn-primary btn-large">
                                    Acessar Painel
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Funcionalidades -->
        <section id="funcionalidades" class="features">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">Por que escolher o CorteFácil?</h2>
                    <p class="section-subtitle">Descubra as vantagens que fazem a diferença</p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">💰</div>
                        <h3>Sem Mensalidade</h3>
                        <p>Salões parceiros não pagam mensalidade. Apenas os clientes pagam R$ 1,29 por agendamento.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">📅</div>
                        <h3>Agenda Inteligente</h3>
                        <p>Sistema que evita conflitos de horários automaticamente. Nunca mais duplo agendamento!</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Fácil de Usar</h3>
                        <p>Interface simples e intuitiva. Funciona perfeitamente em computadores e celulares.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Seguro</h3>
                        <p>Seus dados estão protegidos com as melhores práticas de segurança.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Rápido</h3>
                        <p>Agendamento em poucos cliques. Processo otimizado para economizar seu tempo.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Relatórios</h3>
                        <p>Acompanhe estatísticas e relatórios detalhados dos seus agendamentos.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Como Funciona -->
        <section id="como-funciona" class="how-it-works">
            <div class="container">
                <div class="section-header text-center">
                    <h2 class="section-title">Como Funciona</h2>
                    <p class="section-subtitle">Simples, rápido e eficiente em 4 passos</p>
                </div>
                
                <div class="steps-container">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon">📝</div>
                        <h3>Cadastre-se</h3>
                        <p>Crie sua conta como cliente ou registre seu salão de forma gratuita</p>
                    </div>
                    
                    <div class="step-arrow">→</div>
                    
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon">🔍</div>
                        <h3>Escolha</h3>
                        <p>Selecione o salão, profissional e horário que melhor se adequa a você</p>
                    </div>
                    
                    <div class="step-arrow">→</div>
                    
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon">💳</div>
                        <h3>Agende</h3>
                        <p>Confirme seu agendamento pagando apenas R$ 1,29 de forma segura</p>
                    </div>
                    
                    <div class="step-arrow">→</div>
                    
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-icon">✨</div>
                        <h3>Compareça</h3>
                        <p>Vá ao salão no horário marcado e pague o serviço diretamente lá</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="final-cta">
            <div class="container">
                <div class="cta-content text-center">
                    <h2>Pronto para revolucionar seu negócio?</h2>
                    <p>Junte-se a centenas de salões que já confiam no CorteFácil</p>
                    <div class="cta-buttons">
                        <a href="register.php?tipo=parceiro" class="btn btn-success btn-large">
                            Cadastrar Salão Grátis
                        </a>
                        <a href="register.php?tipo=cliente" class="btn btn-primary btn-large">
                            Começar como Cliente
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <span class="logo-icon">✂️</span>
                        CorteFácil
                    </div>
                    <p>Conectando clientes e profissionais de beleza com tecnologia e simplicidade.</p>
                </div>
                
                <div class="footer-section">
                    <h4>Links Rápidos</h4>
                    <ul>
                        <li><a href="#como-funciona">Como Funciona</a></li>
                        <li><a href="#funcionalidades">Funcionalidades</a></li>
                        <li><a href="login.php">Entrar</a></li>
                        <li><a href="register.php">Cadastrar</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Para Salões</h4>
                    <ul>
                        <li><a href="register.php?tipo=parceiro">Cadastrar Salão</a></li>
                        <li><a href="parceiro/dashboard.php">Painel do Parceiro</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Para Clientes</h4>
                    <ul>
                        <li><a href="register.php?tipo=cliente">Cadastrar Cliente</a></li>
                        <li><a href="cliente/dashboard.php">Meus Agendamentos</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
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