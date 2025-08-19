<?php
/**
 * Página Principal do Sistema CorteFácil
 * Ponto de entrada e redirecionamento baseado no tipo de usuário
 */

// Definir charset UTF-8
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Se usuário está logado, redireciona para o painel apropriado
if (isLoggedIn()) {
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    switch ($tipo_usuario) {
        case 'cliente':
            header('Location: cliente/dashboard.php');
            break;
        case 'parceiro':
            header('Location: parceiro/dashboard.php');
            break;
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        default:
            logout();
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CorteFácil - Sistema de Agendamentos para Salões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cut me-2"></i>
                CorteFácil
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#como-funciona">Como Funciona</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vantagens">Vantagens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Entrar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light ms-2 px-3" href="cadastro.php">Cadastrar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient text-white py-5">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Agende seu corte com facilidade
                    </h1>
                    <p class="lead mb-4">
                        Conectamos clientes e salões de beleza de forma simples e eficiente. 
                        Agende seus serviços favoritos com apenas alguns cliques!
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="cadastro.php?tipo=cliente" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-user me-2"></i>
                            Sou Cliente
                        </a>
                        <a href="cadastro.php?tipo=parceiro" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-store me-2"></i>
                            Tenho um Salão
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-image">
                        <i class="fas fa-calendar-alt" style="font-size: 15rem; opacity: 0.1;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Como Funciona -->
    <section id="como-funciona" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Como Funciona</h2>
                    <p class="lead text-muted">Simples, rápido e eficiente</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-card p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-search fa-3x text-primary"></i>
                        </div>
                        <h4>1. Escolha o Salão</h4>
                        <p class="text-muted">
                            Navegue pelos salões parceiros e escolha o que mais combina com você.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-card p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-calendar-check fa-3x text-primary"></i>
                        </div>
                        <h4>2. Agende o Horário</h4>
                        <p class="text-muted">
                            Selecione o profissional, data e horário que melhor se adequa à sua agenda.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="feature-card p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-credit-card fa-3x text-primary"></i>
                        </div>
                        <h4>3. Confirme o Agendamento</h4>
                        <p class="text-muted">
                            Pague apenas R$ 1,29 para confirmar e compareça no horário marcado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vantagens -->
    <section id="vantagens" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5 fw-bold">Vantagens</h2>
                    <p class="lead text-muted">Por que escolher o CorteFácil?</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Economia de Tempo</h5>
                            <p class="text-muted">
                                Sem filas, sem espera. Agende online e chegue na hora certa.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Segurança</h5>
                            <p class="text-muted">
                                Seus dados estão protegidos e suas informações são confidenciais.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Facilidade</h5>
                            <p class="text-muted">
                                Interface simples e intuitiva, funciona em qualquer dispositivo.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-dollar-sign fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Preço Justo</h5>
                            <p class="text-muted">
                                Apenas R$ 1,29 por agendamento. Sem mensalidades ou taxas ocultas.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Pronto para começar?</h2>
            <p class="lead mb-4">
                Junte-se a milhares de clientes satisfeitos e salões parceiros.
            </p>
            <a href="cadastro.php" class="btn btn-light btn-lg px-5">
                Cadastre-se Agora
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>
                        <i class="fas fa-cut me-2"></i>
                        CorteFácil
                    </h5>
                    <p class="text-muted">
                        Conectando clientes e salões de beleza com tecnologia e praticidade.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; 2024 CorteFácil. Todos os direitos reservados.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>