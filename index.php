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
    
    <script>
        // Função para toggle do menu mobile
        function toggleMobileMenu() {
            const nav = document.querySelector('.nav-desktop');
            nav.classList.toggle('mobile-active');
        }

        // Smooth scroll para links de navegação
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animação de contadores nas estatísticas
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/\D/g, ''));
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (counter.textContent.includes('+')) {
                        counter.textContent = Math.floor(current) + '+';
                    } else if (counter.textContent.includes('%')) {
                        counter.textContent = Math.floor(current) + '%';
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 20);
            });
        }

        // Intersection Observer para animações
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    
                    // Animar contadores quando a seção hero estiver visível
                    if (entry.target.classList.contains('hero-stats')) {
                        animateCounters();
                    }
                }
            });
        }, observerOptions);

        // Observar elementos para animação
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.feature-card, .step-card, .cta-card, .hero-stats');
            animatedElements.forEach(el => observer.observe(el));
        });

        // Fechar menu mobile ao clicar fora
        document.addEventListener('click', (e) => {
            const nav = document.querySelector('.nav-desktop');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('mobile-active');
            }
        });
    </script>
    
    <style>
        /* Estilos específicos para a página inicial */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,0 1000,300 1000,1000 0,700"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-title .highlight {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin: 3rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFD700;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }

        /* CTA Cards */
        .hero-cta {
            margin-top: 4rem;
        }

        .cta-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .cta-card:hover {
            transform: translateY(-10px);
        }

        .cta-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .cta-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            text-align: left;
        }

        .cta-features li {
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }

        /* Welcome Back */
        .welcome-back {
            margin-top: 4rem;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Sections */
        .features, .how-it-works {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .section-header {
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: #666;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .feature-card.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        /* Steps Container */
        .steps-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .step-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 250px;
            position: relative;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.3s ease;
        }

        .step-card.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        .step-number {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .step-icon {
            font-size: 2.5rem;
            margin: 1rem 0;
        }

        .step-arrow {
            font-size: 2rem;
            color: #667eea;
            margin: 0 1rem;
        }

        /* Final CTA */
        .final-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .btn-outline-header {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-outline-header:hover {
            background: rgba(255,255,255,0.1);
        }

        .btn-primary-header {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-primary-header:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Header */
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .logo-icon {
            font-size: 2rem;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: opacity 0.3s ease;
        }

        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: white;
            margin: 3px 0;
            transition: 0.3s;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-section h4 {
            margin-bottom: 1rem;
            color: #ecf0f1;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 2rem;
            text-align: center;
            color: #bdc3c7;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .cta-cards {
                grid-template-columns: 1fr;
            }

            .steps-container {
                flex-direction: column;
            }

            .step-arrow {
                transform: rotate(90deg);
            }

            .nav-desktop {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(102, 126, 234, 0.95);
                backdrop-filter: blur(10px);
                padding: 1rem;
            }

            .nav-desktop.mobile-active {
                display: block;
            }

            .nav-menu {
                flex-direction: column;
                gap: 1rem;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</body>
</html>