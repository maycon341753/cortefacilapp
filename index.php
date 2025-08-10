<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Verificar se usuário já está logado
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['tipo_usuario'] === 'cliente') {
        header('Location: cliente/dashboard.php');
        exit();
    } elseif ($_SESSION['tipo_usuario'] === 'parceiro') {
        header('Location: parceiro/dashboard.php');
        exit();
    }
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
                        <li><a href="#funcionalidades" class="nav-link">Funcionalidades</a></li>
                        <li><a href="#como-funciona" class="nav-link">Como Funciona</a></li>
                        <li><a href="login.php" class="nav-link btn-outline-header">Entrar</a></li>
                        <li><a href="register.php" class="nav-link btn-primary-header">Cadastrar</a></li>
                    </ul>
                </nav>

                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-background"></div>
            <div class="container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Conecte seu salão com <span class="highlight">milhares de clientes</span>
                    </h1>
                    <p class="hero-subtitle">
                        A plataforma mais completa para agendamentos em salões de beleza. 
                        Gerencie horários, receba pagamentos e faça seu negócio crescer.
                    </p>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Salões Parceiros</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">10k+</span>
                            <span class="stat-label">Agendamentos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">98%</span>
                            <span class="stat-label">Satisfação</span>
                        </div>
                    </div>

                    <div class="hero-cta">
                        <div class="cta-cards">
                            <div class="cta-card">
                                <div class="cta-icon">👤</div>
                                <h3>Sou Cliente</h3>
                                <p>Encontre e agende com os melhores profissionais da sua região</p>
                                <a href="register.php?tipo=cliente" class="btn btn-primary">Começar Agora</a>
                            </div>
                            <div class="cta-card">
                                <div class="cta-icon">💼</div>
                                <h3>Tenho um Salão</h3>
                                <p>Cadastre seu salão e comece a receber novos clientes hoje mesmo</p>
                                <a href="register.php?tipo=parceiro" class="btn btn-secondary">Cadastrar Salão</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Funcionalidades Section -->
        <section id="funcionalidades" class="features">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Funcionalidades Completas</h2>
                    <p class="section-subtitle">Tudo que você precisa para gerenciar seu salão ou encontrar o serviço perfeito</p>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📅</div>
                        <h3>Agendamento Online</h3>
                        <p>Sistema intuitivo de agendamentos com calendário em tempo real e confirmação automática.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💳</div>
                        <h3>Pagamentos Seguros</h3>
                        <p>Processamento seguro de pagamentos com taxa baixa de apenas R$ 1,29 por agendamento.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>App Mobile</h3>
                        <p>Interface responsiva que funciona perfeitamente em qualquer dispositivo móvel.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Relatórios Detalhados</h3>
                        <p>Acompanhe o desempenho do seu salão com relatórios completos e insights valiosos.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔔</div>
                        <h3>Notificações</h3>
                        <p>Lembretes automáticos por email e SMS para clientes e profissionais.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⭐</div>
                        <h3>Avaliações</h3>
                        <p>Sistema de avaliações que ajuda a construir a reputação do seu salão.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Como Funciona Section -->
        <section id="como-funciona" class="how-it-works">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Como Funciona</h2>
                    <p class="section-subtitle">Processo simples em 3 passos</p>
                </div>

                <div class="steps-container">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Cadastre-se</h3>
                            <p>Crie sua conta como cliente ou parceiro em menos de 2 minutos</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Escolha o Serviço</h3>
                            <p>Encontre o salão perfeito e selecione o profissional ideal</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Agende e Pague</h3>
                            <p>Confirme seu horário e efetue o pagamento de forma segura</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">O que nossos usuários dizem</h2>
                </div>

                <div class="testimonials-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"O CorteFácil revolucionou meu salão! Agora tenho muito mais clientes e organização."</p>
                        </div>
                        <div class="testimonial-author">
                            <strong>Maria Silva</strong>
                            <span>Proprietária - Salão Beleza Total</span>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Nunca foi tão fácil agendar um horário. Interface simples e pagamento seguro."</p>
                        </div>
                        <div class="testimonial-author">
                            <strong>João Santos</strong>
                            <span>Cliente</span>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>"Aumentei minha receita em 40% desde que comecei a usar a plataforma."</p>
                        </div>
                        <div class="testimonial-author">
                            <strong>Ana Costa</strong>
                            <span>Cabeleireira</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Pronto para começar?</h2>
                    <p>Junte-se a milhares de profissionais e clientes que já usam o CorteFácil</p>
                    <div class="cta-buttons">
                        <a href="register.php?tipo=cliente" class="btn btn-primary btn-large">Sou Cliente</a>
                        <a href="register.php?tipo=parceiro" class="btn btn-secondary btn-large">Cadastrar Salão</a>
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
                        <span>CorteFácil</span>
                    </div>
                    <p>A plataforma que conecta clientes e profissionais de beleza de forma simples e eficiente.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook">📘</a>
                        <a href="#" aria-label="Instagram">📷</a>
                        <a href="#" aria-label="Twitter">🐦</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Para Parceiros</h3>
                    <ul>
                        <li><a href="register.php?tipo=parceiro">Cadastrar Salão</a></li>
                        <li><a href="parceiro/dashboard.php">Painel do Parceiro</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Para Clientes</h3>
                    <ul>
                        <li><a href="register.php?tipo=cliente">Criar Conta</a></li>
                        <li><a href="cliente/dashboard.php">Meus Agendamentos</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Suporte</h3>
                    <ul>
                        <li><a href="#funcionalidades">Funcionalidades</a></li>
                        <li><a href="#como-funciona">Como Funciona</a></li>
                        <li><a href="login.php">Entrar</a></li>
                        <li><a href="register.php">Cadastrar</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 CorteFácil. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>