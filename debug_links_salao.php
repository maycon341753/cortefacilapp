<?php
/**
 * Debug de Links do Salão
 * Verifica problemas específicos de redirecionamento
 */

// Simular sessão de parceiro
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'teste@teste.com';
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_telefone'] = '11999999999';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Links Salão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Debug de Links do Salão</h1>
        
        <div class="alert alert-info">
            <h5>Informações de Debug</h5>
            <p><strong>Sessão Ativa:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Sim' : 'Não'; ?></p>
            <p><strong>Usuário ID:</strong> <?php echo $_SESSION['usuario_id'] ?? 'N/A'; ?></p>
            <p><strong>Tipo Usuário:</strong> <?php echo $_SESSION['tipo_usuario'] ?? 'N/A'; ?></p>
            <p><strong>Server Name:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'N/A'; ?></p>
            <p><strong>HTTP Host:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <h3>Links Originais (como no salao.php)</h3>
                <div class="d-grid gap-2">
                    <a href="profissionais.php" class="btn btn-primary btn-sm" id="link1">
                        <i class="fas fa-users me-2"></i>
                        Gerenciar Profissionais
                    </a>
                    
                    <a href="agenda.php" class="btn btn-outline-primary btn-sm" id="link2">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Ver Agenda
                    </a>
                    
                    <a href="agendamentos.php" class="btn btn-outline-secondary btn-sm" id="link3">
                        <i class="fas fa-list me-2"></i>
                        Ver Agendamentos
                    </a>
                    
                    <a href="dashboard.php" class="btn btn-info btn-sm" id="link4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>
            
            <div class="col-md-6">
                <h3>Links com Caminho Completo</h3>
                <div class="d-grid gap-2">
                    <a href="/cortefacil/cortefacilapp/parceiro/profissionais.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-users me-2"></i>
                        Gerenciar Profissionais (Completo)
                    </a>
                    
                    <a href="/cortefacil/cortefacilapp/parceiro/agenda.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Ver Agenda (Completo)
                    </a>
                    
                    <a href="/cortefacil/cortefacilapp/parceiro/agendamentos.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-2"></i>
                        Ver Agendamentos (Completo)
                    </a>
                    
                    <a href="/cortefacil/cortefacilapp/parceiro/dashboard.php" class="btn btn-info btn-sm">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard (Completo)
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h3>Log de Eventos</h3>
            <div id="log" class="alert alert-light" style="height: 200px; overflow-y: auto;"></div>
        </div>
    </div>
    
    <script>
        // Log de eventos para debug
        const log = document.getElementById('log');
        
        function addLog(message) {
            const time = new Date().toLocaleTimeString();
            log.innerHTML += `[${time}] ${message}<br>`;
            log.scrollTop = log.scrollHeight;
        }
        
        // Interceptar todos os cliques em links
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                const link = e.target.tagName === 'A' ? e.target : e.target.closest('a');
                addLog(`Clique detectado no link: ${link.href}`);
                addLog(`Texto do link: ${link.textContent.trim()}`);
                addLog(`Target: ${link.target || 'mesmo frame'}`);
                
                // Verificar se o link será redirecionado
                setTimeout(() => {
                    addLog(`Redirecionamento executado para: ${link.href}`);
                }, 100);
            }
        });
        
        // Verificar se há JavaScript interferindo
        addLog('Debug iniciado');
        addLog(`Base URL detectada: ${window.location.origin}`);
        addLog(`Caminho atual: ${window.location.pathname}`);
        
        // Verificar se há event listeners nos links
        document.querySelectorAll('a').forEach((link, index) => {
            addLog(`Link ${index + 1}: ${link.href} - Listeners: ${getEventListeners ? 'Verificando...' : 'Não disponível'}`);
        });
    </script>
</body>
</html>