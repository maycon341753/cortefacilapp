<?php
/**
 * Atualização do Index.php para Servidor Online
 * Sistema de Roteamento via Parâmetros GET para Resolver Problemas de Redirecionamento
 * 
 * INSTRUÇÕES DE INSTALAÇÃO:
 * 1. Faça backup do index.php atual do servidor online
 * 2. Substitua o conteúdo do index.php online por este arquivo
 * 3. Teste o acesso usando: https://cortefacil.app/?page=parceiro_profissionais
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
        'logout' => 'logout.php'
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

// Debug de roteamento para servidor online
if (isset($_GET['debug_routing'])) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>🔧 Debug de Roteamento CorteFácil Online</h1>";
    echo "<h2>Informações da Requisição</h2>";
    echo "<pre>";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
    echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
    echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
    echo "</pre>";
    
    echo "<h2>Como usar o sistema de roteamento online</h2>";
    echo "<p>Use os seguintes links para acessar as páginas:</p>";
    echo "<ul>";
    echo "<li><a href='https://cortefacil.app/?page=parceiro_profissionais'>Profissionais (Parceiro)</a></li>";
    echo "<li><a href='https://cortefacil.app/?page=parceiro_dashboard'>Dashboard (Parceiro)</a></li>";
    echo "<li><a href='https://cortefacil.app/?page=cliente_dashboard'>Dashboard (Cliente)</a></li>";
    echo "<li><a href='https://cortefacil.app/?page=admin_dashboard'>Dashboard (Admin)</a></li>";
    echo "</ul>";
    
    echo "<h2>Status do Sistema</h2>";
    echo "<p>✅ Sistema de roteamento via parâmetros GET ativo</p>";
    echo "<p>✅ Mapeamento de páginas configurado</p>";
    echo "<p>✅ Verificação de segurança ativa</p>";
    exit();
}

// CONTEÚDO ORIGINAL DO INDEX.PHP (Página de apresentação)
// A partir daqui, manter o conteúdo original do index.php do servidor online
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
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="cadastro.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>
                            Começar Agora
                        </a>
                        <a href="#como-funciona" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-play me-2"></i>
                            Como Funciona
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-cut display-1 mb-4 text-white-50"></i>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                <small class="d-block">Agendamento Fácil</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <small class="d-block">Horários Flexíveis</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <small class="d-block">Qualidade Garantida</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Resto do conteúdo da página de apresentação seria mantido aqui -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>