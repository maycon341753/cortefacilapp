<?php
/**
 * Index.php Corrigido - Versão Robusta para Produção
 * CorteFácil - Correção do Erro 500
 * Versão simplificada e com tratamento de erros
 */

// Configurações iniciais para produção
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Definir charset UTF-8
header('Content-Type: text/html; charset=utf-8');

// Função para log de erros personalizado
function logError($message, $file = '', $line = '') {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] ERRO: $message";
    if ($file) $log_message .= " em $file";
    if ($line) $log_message .= " linha $line";
    $log_message .= "\n";
    
    error_log($log_message, 3, 'error.log');
}

// Tratamento de erros global
set_error_handler(function($severity, $message, $file, $line) {
    logError($message, $file, $line);
    
    // Em produção, não mostrar erros
    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost') {
        return true; // Suprimir erro
    }
    
    return false; // Mostrar erro em desenvolvimento
});

// Tratamento de exceções não capturadas
set_exception_handler(function($exception) {
    logError('Exceção não capturada: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine());
    
    // Página de erro amigável
    http_response_code(500);
    include 'erro_500_amigavel.php';
    exit;
});

try {
    // Verificar se arquivos críticos existem antes de incluir
    $arquivos_criticos = [
        __DIR__ . '/includes/auth.php',
        __DIR__ . '/includes/functions.php'
    ];
    
    foreach ($arquivos_criticos as $arquivo) {
        if (!file_exists($arquivo)) {
            throw new Exception("Arquivo crítico não encontrado: $arquivo");
        }
    }
    
    // Incluir arquivos com tratamento de erro
    require_once __DIR__ . '/includes/auth.php';
    require_once __DIR__ . '/includes/functions.php';
    
    // Verificar se funções essenciais existem
    if (!function_exists('isLoggedIn')) {
        throw new Exception('Função isLoggedIn não encontrada');
    }
    
    // Iniciar sessão de forma segura
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Se usuário está logado, redireciona para o painel apropriado
    if (isLoggedIn()) {
        $tipo_usuario = $_SESSION['tipo_usuario'] ?? '';
        
        switch ($tipo_usuario) {
            case 'cliente':
                header('Location: cliente/dashboard.php');
                exit();
            case 'parceiro':
                header('Location: parceiro/dashboard.php');
                exit();
            case 'admin':
                header('Location: admin/dashboard.php');
                exit();
            default:
                // Tipo de usuário inválido, fazer logout
                if (function_exists('logout')) {
                    logout();
                } else {
                    session_destroy();
                }
                break;
        }
    }
    
} catch (Exception $e) {
    // Log do erro
    logError('Erro no index.php: ' . $e->getMessage(), $e->getFile(), $e->getLine());
    
    // Em produção, mostrar página de erro amigável
    if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost') {
        http_response_code(500);
        
        // Tentar incluir página de erro personalizada
        if (file_exists('erro_500_amigavel.php')) {
            include 'erro_500_amigavel.php';
        } else {
            // Página de erro básica
            echo '<!DOCTYPE html><html><head><title>Erro Temporário</title></head><body>';
            echo '<h1>Serviço Temporariamente Indisponível</h1>';
            echo '<p>Estamos trabalhando para resolver este problema. Tente novamente em alguns minutos.</p>';
            echo '<p><a href="/">Voltar à página inicial</a></p>';
            echo '</body></html>';
        }
        exit;
    } else {
        // Em desenvolvimento, mostrar erro detalhado
        echo '<h1>Erro de Desenvolvimento</h1>';
        echo '<p><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
        echo '<p><strong>Stack Trace:</strong></p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        exit;
    }
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
    <?php if (file_exists('assets/css/style.css')): ?>
    <link href="assets/css/style.css" rel="stylesheet">
    <?php endif; ?>
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
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
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Agende seus serviços de beleza com facilidade
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
                            Receba confirmação por email e SMS. Simples assim!
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
                    <h2 class="display-5 fw-bold">Por que escolher o CorteFácil?</h2>
                    <p class="lead text-muted">Vantagens para clientes e salões</p>
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
                                Sem filas, sem espera. Agende quando quiser, de onde estiver.
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
                                Compare preços e escolha a melhor opção para seu orçamento.
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
            <p class="lead mb-4">Junte-se a milhares de pessoas que já usam o CorteFácil</p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="cadastro.php?tipo=cliente" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-user me-2"></i>
                    Cadastrar como Cliente
                </a>
                <a href="cadastro.php?tipo=parceiro" class="btn btn-outline-light btn-lg px-4">
                    <i class="fas fa-store me-2"></i>
                    Cadastrar meu Salão
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-cut me-2"></i>CorteFácil</h5>
                    <p class="text-muted">Conectando beleza e praticidade.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> CorteFácil. Todos os direitos reservados.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>