<?php
/**
 * Página Principal do Sistema CorteFácil
 * Ponto de entrada e redirecionamento baseado no tipo de usuário
 */

// SISTEMA DE ROTEAMENTO VIA PARÂMETROS PARA CONTORNAR PROBLEMA DO SERVIDOR
// O servidor está redirecionando TODAS as requisições para index.php
// Solução: usar parâmetros GET para acessar páginas específicas

// Verificar se há uma página solicitada via parâmetro
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    
    // Mapeamento de páginas permitidas
    $allowed_pages = [
        // Páginas do parceiro
        'parceiro_dashboard' => 'parceiro/dashboard.php',
        'parceiro_profissionais' => 'parceiro/profissionais.php',
        'parceiro_agendamentos' => 'parceiro/agendamentos.php',
        'parceiro_salao' => 'parceiro/salao.php',
        'parceiro_agenda' => 'parceiro/agenda.php',
        'parceiro_relatorios' => 'parceiro/relatorios.php',
        
        // Páginas do cliente
        'cliente_dashboard' => 'cliente/dashboard.php',
        'cliente_agendamentos' => 'cliente/agendamentos.php',
        'cliente_agendar' => 'cliente/agendar.php',
        'cliente_saloes' => 'cliente/saloes.php',
        'cliente_perfil' => 'cliente/perfil.php',
        
        // Páginas do admin
        'admin_dashboard' => 'admin/dashboard.php',
        'admin_usuarios' => 'admin/usuarios.php',
        'admin_saloes' => 'admin/saloes.php',
        'admin_agendamentos' => 'admin/agendamentos.php',
        'admin_relatorios' => 'admin/relatorios.php',
        
        // Páginas gerais
        'login' => 'login.php',
        'cadastro' => 'cadastro.php',
        'logout' => 'logout.php',
        
        // Páginas de teste
         'teste_simples' => 'teste_simples.php',
         'diagnostico' => 'diagnostico_servidor.php',
         'teste_profissionais' => 'teste_profissionais_simples.php'
    ];
    
    // Verificar se a página é permitida
    if (array_key_exists($page, $allowed_pages)) {
        $file_path = __DIR__ . '/' . $allowed_pages[$page];
        
        // Verificar se o arquivo existe
        if (file_exists($file_path) && is_file($file_path)) {
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Incluir o arquivo solicitado
            include $file_path;
            exit();
        } else {
            // Arquivo não encontrado
            header('HTTP/1.0 404 Not Found');
            echo "<h1>Página não encontrada</h1>";
            echo "<p>O arquivo solicitado não existe: $file_path</p>";
            exit();
        }
    } else {
        // Página não permitida
        header('HTTP/1.0 403 Forbidden');
        echo "<h1>Acesso negado</h1>";
        echo "<p>Página não permitida: $page</p>";
        exit();
    }
}

// Debug de roteamento
if (isset($_GET['debug_routing'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>🔧 Debug de Roteamento CorteFácil</h1>";
    echo "<h2>Informações da Requisição</h2>";
    echo "<pre>";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
    echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
    echo "</pre>";
    
    echo "<h2>Como usar o sistema de roteamento</h2>";
    echo "<p>Devido a problemas de configuração do servidor, use os seguintes links:</p>";
    echo "<ul>";
    echo "<li><a href='?page=parceiro_profissionais'>Profissionais (Parceiro)</a></li>";
    echo "<li><a href='?page=parceiro_dashboard'>Dashboard (Parceiro)</a></li>";
    echo "<li><a href='?page=teste_simples'>Teste Simples</a></li>";
    echo "<li><a href='?page=diagnostico'>Diagnóstico do Servidor</a></li>";
    echo "</ul>";
    exit();
}

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
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