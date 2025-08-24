<?php
/**
 * Template de Navegação do Parceiro
 * Sidebar padronizada para todas as páginas do parceiro
 */

// Definir URL base do sistema
$base_url = 'https://cortefacil.app';
$parceiro_url = $base_url . '/parceiro';

// Obter página atual para marcar como ativa
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h5 class="text-white">
                <i class="fas fa-cut me-2"></i>
                CorteFácil
            </h5>
            <small class="text-white-50">Parceiro</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'agenda.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/agenda.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Agenda
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'profissionais.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/profissionais.php">
                    <i class="fas fa-users me-2"></i>
                    Profissionais
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'agendamentos.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/agendamentos.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Agendamentos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'servicos.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/servicos.php">
                    <i class="fas fa-concierge-bell me-2"></i>
                    Serviços
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'salao.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/salao.php">
                    <i class="fas fa-store me-2"></i>
                    Meu Salão
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'relatorios.php') ? 'active' : ''; ?>" href="<?php echo $parceiro_url; ?>/relatorios.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Relatórios
                </a>
            </li>
        </ul>
        
        <hr class="text-white-50">
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base_url; ?>/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Sair
                </a>
            </li>
        </ul>
    </div>
</nav>