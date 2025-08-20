<?php
/**
 * Correção específica para o problema de CSRF no ambiente online
 * Este arquivo deve ser enviado para o servidor de produção
 */

// Configurações específicas para ambiente online
if (!defined('CSRF_FIX_ONLINE')) {
    define('CSRF_FIX_ONLINE', true);
}

// Forçar configurações de sessão para ambiente online
if (session_status() == PHP_SESSION_NONE) {
    // Configurações robustas para servidor online
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600); // 1 hora
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    
    // Configurações específicas para HTTPS (ambiente online)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para compatibilidade com navegadores modernos
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    // Definir nome da sessão específico
    session_name('CORTEFACIL_SESSION');
    
    // Iniciar sessão
    session_start();
    
    // Regenerar ID da sessão periodicamente
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Função melhorada para gerar token CSRF
 * Versão específica para ambiente online
 */
function generateCSRFTokenOnline() {
    // Garantir que a sessão está ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Gerar novo token se necessário
    $regenerate_token = false;
    
    if (!isset($_SESSION['csrf_token'])) {
        $regenerate_token = true;
    } elseif (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        // Token expira em 1 hora
        $regenerate_token = true;
    } elseif (!isset($_SESSION['csrf_token_time'])) {
        // Se não tem timestamp, regenerar
        $regenerate_token = true;
    }
    
    if ($regenerate_token) {
        // Usar método mais robusto para gerar token
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback para servidores mais antigos
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        }
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Função melhorada para verificar token CSRF
 * Versão específica para ambiente online
 */
function verifyCSRFTokenOnline($token) {
    // Garantir que a sessão está ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Verificações básicas
    if (empty($token)) {
        return false;
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Verificar se o token não expirou
    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    
    // Comparação segura
    if (function_exists('hash_equals')) {
        return hash_equals($_SESSION['csrf_token'], $token);
    } else {
        // Fallback para servidores sem hash_equals
        return $_SESSION['csrf_token'] === $token;
    }
}

/**
 * Função para gerar campo hidden do CSRF
 */
function generateCSRFFieldOnline() {
    $token = generateCSRFTokenOnline();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Se este arquivo for acessado diretamente, mostrar diagnóstico
if (basename($_SERVER['PHP_SELF']) === 'fix_csrf_online.php') {
    echo "<h1>🔧 Correção CSRF - Ambiente Online</h1>";
    echo "<p><strong>Status:</strong> Arquivo de correção carregado com sucesso!</p>";
    echo "<hr>";
    
    // Simular usuário logado para teste
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_nome'] = 'Teste Online';
        $_SESSION['tipo_usuario'] = 'parceiro';
    }
    
    echo "<h2>📊 Diagnóstico do Sistema</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr><td><strong>PHP Version</strong></td><td>" . PHP_VERSION . "</td></tr>";
    echo "<tr><td><strong>Session Status</strong></td><td>" . (session_status() === PHP_SESSION_ACTIVE ? 'ATIVA ✓' : 'INATIVA ✗') . "</td></tr>";
    echo "<tr><td><strong>Session ID</strong></td><td>" . session_id() . "</td></tr>";
    echo "<tr><td><strong>Session Name</strong></td><td>" . session_name() . "</td></tr>";
    echo "<tr><td><strong>HTTPS</strong></td><td>" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'SIM ✓' : 'NÃO ✗') . "</td></tr>";
    echo "<tr><td><strong>Cookie Secure</strong></td><td>" . (ini_get('session.cookie_secure') ? 'SIM ✓' : 'NÃO ✗') . "</td></tr>";
    echo "<tr><td><strong>Cookie HttpOnly</strong></td><td>" . (ini_get('session.cookie_httponly') ? 'SIM ✓' : 'NÃO ✗') . "</td></tr>";
    echo "<tr><td><strong>random_bytes</strong></td><td>" . (function_exists('random_bytes') ? 'DISPONÍVEL ✓' : 'NÃO DISPONÍVEL ✗') . "</td></tr>";
    echo "<tr><td><strong>hash_equals</strong></td><td>" . (function_exists('hash_equals') ? 'DISPONÍVEL ✓' : 'NÃO DISPONÍVEL ✗') . "</td></tr>";
    echo "</table>";
    
    echo "<h2>🧪 Teste das Funções Corrigidas</h2>";
    
    // Limpar tokens para teste limpo
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    
    $token_gerado = generateCSRFTokenOnline();
    echo "<p><strong>Token gerado:</strong> " . substr($token_gerado, 0, 30) . "...</p>";
    
    $verificacao = verifyCSRFTokenOnline($token_gerado);
    echo "<p><strong>Verificação:</strong> " . ($verificacao ? 'VÁLIDO ✓' : 'INVÁLIDO ✗') . "</p>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; margin: 15px 0;'>";
        echo "<h4>Resultado do Teste POST</h4>";
        
        try {
            if (!verifyCSRFTokenOnline($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de segurança inválido.');
            }
            
            echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! Token CSRF válido!</p>";
            echo "<p>✅ A correção funcionou no ambiente online!</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red; font-weight: bold;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
            
            $csrf_recebido = $_POST['csrf_token'] ?? '';
            echo "<p><strong>Debug:</strong></p>";
            echo "<ul>";
            echo "<li>Token recebido: " . (empty($csrf_recebido) ? 'VAZIO' : substr($csrf_recebido, 0, 30) . '...') . "</li>";
            echo "<li>Token na sessão: " . (isset($_SESSION['csrf_token']) ? substr($_SESSION['csrf_token'], 0, 30) . '...' : 'NÃO EXISTE') . "</li>";
            echo "<li>Tokens iguais: " . (isset($_SESSION['csrf_token']) && $csrf_recebido === $_SESSION['csrf_token'] ? 'SIM' : 'NÃO') . "</li>";
            echo "</ul>";
        }
        
        echo "</div>";
    }
    
    echo "<h3>Formulário de Teste</h3>";
    echo "<form method='POST' style='background: #ffffff; padding: 20px; border: 2px solid #28a745; border-radius: 8px;'>";
    echo "<h4 style='color: #28a745;'>🧪 Teste da Correção Online</h4>";
    echo generateCSRFFieldOnline();
    echo "<div style='margin: 10px 0;'>";
    echo "<label><strong>Nome do Salão:</strong></label><br>";
    echo "<input type='text' name='nome' value='Salão Teste Correção' style='padding: 8px; width: 300px;'>";
    echo "</div>";
    echo "<button type='submit' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Testar Correção</button>";
    echo "</form>";
    
    echo "<h2>📋 Instruções de Implementação</h2>";
    echo "<div style='background: #d1ecf1; padding: 20px; border: 1px solid #bee5eb; border-radius: 8px;'>";
    echo "<h4>Para corrigir o problema no servidor online:</h4>";
    echo "<ol>";
    echo "<li><strong>Substitua as funções CSRF</strong> no arquivo <code>includes/auth.php</code> pelas versões <code>generateCSRFTokenOnline()</code> e <code>verifyCSRFTokenOnline()</code></li>";
    echo "<li><strong>Ou inclua este arquivo</strong> no início do <code>auth.php</code>: <code>require_once 'fix_csrf_online.php';</code></li>";
    echo "<li><strong>Atualize a página do salão</strong> para usar as novas funções</li>";
    echo "<li><strong>Teste</strong> o formulário do salão no ambiente online</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>🔗 Próximos Passos</h2>";
    echo "<ul>";
    echo "<li>Se este teste funcionar, implemente as correções no servidor online</li>";
    echo "<li>Verifique se o problema foi resolvido em <a href='https://cortefacil.app/parceiro/salao.php' target='_blank'>https://cortefacil.app/parceiro/salao.php</a></li>";
    echo "<li>Monitore os logs do servidor para garantir que não há mais erros de CSRF</li>";
    echo "</ul>";
}
?>