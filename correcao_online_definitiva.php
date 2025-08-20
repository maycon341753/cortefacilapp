<?php
/**
 * Correção Definitiva para CSRF Online
 * Este arquivo deve ser enviado para o servidor de produção
 * para resolver o problema de "Token de segurança inválido"
 */

// Forçar configurações específicas para ambiente online
if (session_status() == PHP_SESSION_NONE) {
    // Configurações robustas para servidor online
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 7200); // 2 horas
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 1000);
    
    // Configurações específicas para HTTPS (ambiente online)
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Configurar SameSite para compatibilidade
    if (PHP_VERSION_ID >= 70300) {
        ini_set('session.cookie_samesite', 'Lax');
    }
    
    // Nome específico da sessão
    session_name('CORTEFACIL_ONLINE');
    
    // Iniciar sessão
    session_start();
}

/**
 * Função CSRF corrigida para ambiente online
 * Resolve o problema de tokens diferentes
 */
function generateCSRFTokenOnlineFixed() {
    // Garantir sessão ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Chave específica para evitar conflitos
    $token_key = 'csrf_token_online';
    $time_key = 'csrf_token_time_online';
    
    // Gerar token APENAS se não existir
    if (!isset($_SESSION[$token_key])) {
        // Método mais robusto de geração
        if (function_exists('random_bytes')) {
            $_SESSION[$token_key] = bin2hex(random_bytes(32));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $_SESSION[$token_key] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            // Fallback seguro
            $_SESSION[$token_key] = hash('sha256', uniqid(mt_rand(), true) . microtime(true));
        }
        $_SESSION[$time_key] = time();
    }
    
    return $_SESSION[$token_key];
}

/**
 * Função de verificação CSRF corrigida
 */
function verifyCSRFTokenOnlineFixed($token) {
    // Garantir sessão ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = 'csrf_token_online';
    $time_key = 'csrf_token_time_online';
    
    // Verificações básicas
    if (empty($token)) {
        return false;
    }
    
    if (!isset($_SESSION[$token_key])) {
        return false;
    }
    
    // Verificar expiração (2 horas)
    if (isset($_SESSION[$time_key]) && (time() - $_SESSION[$time_key]) > 7200) {
        unset($_SESSION[$token_key], $_SESSION[$time_key]);
        return false;
    }
    
    // Comparação ultra-segura
    $session_token = $_SESSION[$token_key];
    
    // Normalizar tokens (remover espaços, quebras de linha)
    $token = trim($token);
    $session_token = trim($session_token);
    
    // Comparação com hash_equals se disponível
    if (function_exists('hash_equals')) {
        return hash_equals($session_token, $token);
    }
    
    // Fallback com comparação direta
    return $session_token === $token;
}

/**
 * Função para gerar campo HTML
 */
function generateCSRFFieldOnlineFixed() {
    $token = generateCSRFTokenOnlineFixed();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Se acessado diretamente, mostrar diagnóstico
if (basename($_SERVER['PHP_SELF']) === 'correcao_online_definitiva.php') {
    echo "<!DOCTYPE html>";
    echo "<html><head><meta charset='UTF-8'><title>Correção CSRF Online</title></head><body>";
    echo "<h1>🔧 Correção CSRF - Ambiente Online</h1>";
    echo "<p><strong>Status:</strong> Arquivo carregado com sucesso!</p>";
    echo "<hr>";
    
    // Simular usuário logado para teste
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_nome'] = 'Teste Online';
        $_SESSION['tipo_usuario'] = 'parceiro';
    }
    
    echo "<h2>📊 Diagnóstico do Sistema Online</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><td><strong>Servidor</strong></td><td>" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</td></tr>";
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
    unset($_SESSION['csrf_token_online'], $_SESSION['csrf_token_time_online']);
    
    $token_gerado = generateCSRFTokenOnlineFixed();
    echo "<p><strong>Token gerado:</strong> " . substr($token_gerado, 0, 30) . "...</p>";
    echo "<p><strong>Tamanho do token:</strong> " . strlen($token_gerado) . " caracteres</p>";
    
    $verificacao = verifyCSRFTokenOnlineFixed($token_gerado);
    echo "<p><strong>Verificação:</strong> " . ($verificacao ? 'VÁLIDO ✓' : 'INVÁLIDO ✗') . "</p>";
    
    // Teste de persistência
    $token_segundo = generateCSRFTokenOnlineFixed();
    echo "<p><strong>Segundo token (deve ser igual):</strong> " . substr($token_segundo, 0, 30) . "...</p>";
    echo "<p><strong>Tokens iguais:</strong> " . ($token_gerado === $token_segundo ? 'SIM ✓' : 'NÃO ✗') . "</p>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<div style='background: #f8f9fa; padding: 20px; border: 2px solid #007bff; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4>📋 Resultado do Teste POST</h4>";
        
        $csrf_recebido = $_POST['csrf_token'] ?? '';
        
        echo "<h5>🔍 Debug Detalhado:</h5>";
        echo "<ul>";
        echo "<li><strong>Token recebido:</strong> " . (empty($csrf_recebido) ? 'VAZIO ✗' : substr($csrf_recebido, 0, 30) . '... (' . strlen($csrf_recebido) . ' chars)') . "</li>";
        echo "<li><strong>Token na sessão:</strong> " . (isset($_SESSION['csrf_token_online']) ? substr($_SESSION['csrf_token_online'], 0, 30) . '... (' . strlen($_SESSION['csrf_token_online']) . ' chars)' : 'NÃO EXISTE ✗') . "</li>";
        
        if (!empty($csrf_recebido) && isset($_SESSION['csrf_token_online'])) {
            echo "<li><strong>Comparação direta (===):</strong> " . ($csrf_recebido === $_SESSION['csrf_token_online'] ? 'IGUAIS ✓' : 'DIFERENTES ✗') . "</li>";
            if (function_exists('hash_equals')) {
                echo "<li><strong>hash_equals():</strong> " . (hash_equals($_SESSION['csrf_token_online'], $csrf_recebido) ? 'IGUAIS ✓' : 'DIFERENTES ✗') . "</li>";
            }
        }
        
        if (isset($_SESSION['csrf_token_time_online'])) {
            $idade_token = time() - $_SESSION['csrf_token_time_online'];
            echo "<li><strong>Idade do token:</strong> " . $idade_token . " segundos</li>";
            echo "<li><strong>Token expirado:</strong> " . ($idade_token > 7200 ? 'SIM (>2h) ✗' : 'NÃO ✓') . "</li>";
        }
        echo "</ul>";
        
        try {
            if (!verifyCSRFTokenOnlineFixed($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de segurança inválido.');
            }
            
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724;'>";
            echo "<h5>🎉 SUCESSO TOTAL!</h5>";
            echo "<p>✅ Token CSRF validado com sucesso!</p>";
            echo "<p>✅ A correção funcionou no ambiente online!</p>";
            echo "<p>✅ O problema foi resolvido definitivamente!</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24;'>";
            echo "<h5>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</h5>";
            echo "<p>⚠️ Ainda há problemas no ambiente online.</p>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    echo "<h3>🧪 Formulário de Teste Online</h3>";
    echo "<form method='POST' style='background: #ffffff; padding: 25px; border: 2px solid #dc3545; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #dc3545;'>🌐 Teste Ambiente Online</h4>";
    
    echo generateCSRFFieldOnlineFixed();
    
    echo "<div style='background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
    echo "<small><strong>Token do formulário:</strong> " . substr(generateCSRFTokenOnlineFixed(), 0, 40) . "...</small>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 15px;'>";
    echo "<label style='display: block; font-weight: bold; margin-bottom: 5px;'>Nome do Salão *</label>";
    echo "<input type='text' name='nome' value='Salão Online Corrigido' style='width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 5px;' required>";
    echo "</div>";
    
    echo "<button type='submit' style='background: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;'>🧪 Testar Correção Online</button>";
    echo "</form>";
    
    echo "<h2>📋 Instruções de Implementação</h2>";
    echo "<div style='background: #d1ecf1; padding: 20px; border: 1px solid #bee5eb; border-radius: 8px;'>";
    echo "<h4>🔧 Para aplicar a correção no servidor online:</h4>";
    echo "<ol>";
    echo "<li><strong>Substitua as funções no auth.php:</strong><br>";
    echo "- Substitua <code>generateCSRFToken()</code> por <code>generateCSRFTokenOnlineFixed()</code><br>";
    echo "- Substitua <code>verifyCSRFToken()</code> por <code>verifyCSRFTokenOnlineFixed()</code></li>";
    echo "<li><strong>Ou inclua este arquivo:</strong> <code>require_once 'correcao_online_definitiva.php';</code> no início do auth.php</li>";
    echo "<li><strong>Atualize as chamadas:</strong> Use as novas funções em todos os formulários</li>";
    echo "<li><strong>Teste:</strong> Verifique se o formulário do salão funciona sem erros</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h2>🔗 Próximos Passos</h2>";
    echo "<ul>";
    echo "<li>Se este teste funcionar, a correção está pronta para produção</li>";
    echo "<li>Aplique as alterações no servidor online</li>";
    echo "<li>Teste em <strong>https://cortefacil.app/parceiro/salao.php</strong></li>";
    echo "<li>Monitore os logs para garantir que não há mais erros de CSRF</li>";
    echo "</ul>";
    
    echo "</body></html>";
}
?>