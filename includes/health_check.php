<?php
/**
 * Health Check Middleware
 * Verifica a saúde do sistema e redireciona para manutenção se necessário
 */

/**
 * Verifica se o sistema está saudável
 * @return bool
 */
function isSystemHealthy() {
    try {
        // Verificar se a classe Database existe
        if (!class_exists('Database')) {
            require_once __DIR__ . '/../config/database.php';
        }
        
        // Tentar obter conexão
        $db = Database::getInstance();
        $conn = $db->connect();
        
        // Se não conseguiu conexão, sistema não está saudável
        if ($conn === null) {
            return false;
        }
        
        // Tentar uma query simples para verificar se a conexão está realmente funcionando
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        return ($result && $result['test'] == 1);
        
    } catch (Exception $e) {
        error_log('Health check failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Redireciona para página de manutenção se o sistema não estiver saudável
 * @param bool $force_check Força uma nova verificação
 */
function redirectToMaintenanceIfUnhealthy($force_check = false) {
    // Evitar loop infinito - não verificar se já estamos na página de manutenção
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === 'manutencao.php') {
        return;
    }
    
    // Cache do status de saúde por 60 segundos para evitar verificações excessivas
    $cache_key = 'system_health_status';
    $cache_file = sys_get_temp_dir() . '/' . $cache_key . '.tmp';
    
    $is_healthy = true;
    
    if ($force_check || !file_exists($cache_file) || (time() - filemtime($cache_file)) > 60) {
        // Verificação mais tolerante - tentar múltiplas vezes antes de considerar não saudável
        $attempts = 0;
        $max_attempts = 3;
        
        while ($attempts < $max_attempts) {
            $is_healthy = isSystemHealthy();
            if ($is_healthy) {
                break; // Se conseguiu conectar, está saudável
            }
            $attempts++;
            if ($attempts < $max_attempts) {
                usleep(500000); // Aguardar 0.5 segundos entre tentativas
            }
        }
        
        // Salvar no cache
        file_put_contents($cache_file, $is_healthy ? '1' : '0');
    } else {
        // Ler do cache
        $cached_status = file_get_contents($cache_file);
        $is_healthy = ($cached_status === '1');
    }
    
    // Só redirecionar se realmente não conseguiu conectar após múltiplas tentativas
    if (!$is_healthy) {
        error_log('Sistema redirecionado para manutenção - falha crítica de conexão');
        
        $maintenance_url = 'manutencao.php';
        
        // Ajustar URL baseado na estrutura de diretórios
        $current_dir = dirname($_SERVER['PHP_SELF']);
        if ($current_dir !== '/') {
            // Se estamos em um subdiretório, voltar para a raiz
            $levels = substr_count(trim($current_dir, '/'), '/');
            $maintenance_url = str_repeat('../', $levels) . 'manutencao.php';
        }
        
        header('Location: ' . $maintenance_url);
        exit();
    }
}

/**
 * Middleware para verificação automática de saúde
 * Inclua este arquivo no início de páginas críticas
 */
function autoHealthCheck() {
    // Verificar apenas em páginas que realmente precisam do banco de dados
    $critical_pages = ['login.php', 'dashboard.php', 'agendamentos.php', 'profissionais.php', 'salao.php'];
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if (in_array($current_page, $critical_pages)) {
        // Verificação mais tolerante - só redireciona se realmente não conseguir conectar
        redirectToMaintenanceIfUnhealthy(true); // força nova verificação
    }
}

/**
 * Função para testar a saúde do sistema via AJAX
 * @return array
 */
function getSystemHealthStatus() {
    $status = [
        'healthy' => false,
        'message' => 'Sistema indisponível',
        'timestamp' => date('Y-m-d H:i:s'),
        'details' => []
    ];
    
    try {
        // Verificar conexão com banco
        if (isSystemHealthy()) {
            $status['healthy'] = true;
            $status['message'] = 'Sistema operacional';
            
            // Adicionar detalhes da conexão
            $db = Database::getInstance();
            $conn = $db->connect();
            
            if ($conn) {
                $stmt = $conn->query("SELECT CONNECTION_ID() as conn_id, USER() as user_info, DATABASE() as db_name");
                $info = $stmt->fetch();
                
                $status['details'] = [
                    'connection_id' => $info['conn_id'],
                    'user' => $info['user_info'],
                    'database' => $info['db_name'],
                    'connection_type' => (strpos($info['user_info'], 'root@localhost') !== false) ? 'local' : 'online'
                ];
            }
        }
        
    } catch (Exception $e) {
        $status['message'] = 'Erro interno do sistema';
        error_log('System health check error: ' . $e->getMessage());
    }
    
    return $status;
}

// Auto-executar verificação se este arquivo for incluído
if (!defined('HEALTH_CHECK_DISABLED')) {
    autoHealthCheck();
}
?>