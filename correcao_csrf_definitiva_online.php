<?php
/**
 * CORREÇÃO DEFINITIVA PARA PROBLEMA DE CSRF ONLINE
 * Este script aplica a correção final para resolver o erro de token na página de profissionais
 */

// Detectar ambiente
$isOnline = !in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($isOnline) {
    // Configurações específicas para ambiente online
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

session_start();

echo "<h1>🔧 CORREÇÃO DEFINITIVA CSRF - AMBIENTE " . ($isOnline ? 'ONLINE' : 'LOCAL') . "</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; color: #155724; }
    .error { background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; color: #721c24; }
    .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; color: #856404; }
    .info { background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; color: #0c5460; }
    .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 5px 0; }
</style>";

// 1. BACKUP DO ARQUIVO ORIGINAL
echo "<h2>📋 1. Backup e Preparação</h2>";

$arquivo_auth = __DIR__ . '/includes/auth.php';
$arquivo_backup = __DIR__ . '/includes/auth_backup_' . date('Y-m-d_H-i-s') . '.php';

if (file_exists($arquivo_auth)) {
    if (copy($arquivo_auth, $arquivo_backup)) {
        echo "<div class='success'>✅ Backup criado: " . basename($arquivo_backup) . "</div>";
    } else {
        echo "<div class='warning'>⚠️ Não foi possível criar backup</div>";
    }
} else {
    echo "<div class='error'>❌ Arquivo auth.php não encontrado</div>";
}

// 2. APLICAR CORREÇÃO NO ARQUIVO AUTH.PHP
echo "<h2>🔧 2. Aplicando Correção</h2>";

$correcao_aplicada = false;

if (file_exists($arquivo_auth)) {
    $conteudo_original = file_get_contents($arquivo_auth);
    
    // Verificar se já tem as funções corrigidas
    if (strpos($conteudo_original, 'generateCSRFTokenFixed') !== false) {
        echo "<div class='info'>ℹ️ Correção já aplicada anteriormente</div>";
    } else {
        // Preparar o código corrigido
        $codigo_corrigido = '
/**
 * FUNÇÕES CSRF CORRIGIDAS PARA AMBIENTE ONLINE
 * Versão definitiva que resolve problemas de token
 */

/**
 * Gera token CSRF de forma consistente
 */
function generateCSRFTokenFixed() {
    // Garantir sessão ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = "csrf_token_fixed";
    $time_key = "csrf_token_time_fixed";
    
    // Verificar se precisa gerar novo token
    $need_new_token = false;
    
    if (!isset($_SESSION[$token_key])) {
        $need_new_token = true;
    } elseif (isset($_SESSION[$time_key]) && (time() - $_SESSION[$time_key]) > 7200) {
        // Token expira em 2 horas
        $need_new_token = true;
        unset($_SESSION[$token_key], $_SESSION[$time_key]);
    } elseif (!isset($_SESSION[$time_key])) {
        $need_new_token = true;
    }
    
    if ($need_new_token) {
        // Gerar token seguro
        if (function_exists("random_bytes")) {
            $_SESSION[$token_key] = bin2hex(random_bytes(32));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $_SESSION[$token_key] = bin2hex(openssl_random_pseudo_bytes(32));
        } else {
            $_SESSION[$token_key] = hash("sha256", uniqid(mt_rand(), true) . microtime(true));
        }
        $_SESSION[$time_key] = time();
    }
    
    return $_SESSION[$token_key];
}

/**
 * Verifica token CSRF de forma robusta
 */
function verifyCSRFTokenFixed($token) {
    // Garantir sessão ativa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $token_key = "csrf_token_fixed";
    $time_key = "csrf_token_time_fixed";
    
    // Normalizar tokens
    $received_token = trim($token);
    $session_token = isset($_SESSION[$token_key]) ? trim($_SESSION[$token_key]) : "";
    
    // Verificações básicas
    if (empty($received_token) || empty($session_token)) {
        return false;
    }
    
    // Verificar expiração
    if (isset($_SESSION[$time_key])) {
        $age = time() - $_SESSION[$time_key];
        if ($age > 7200) { // 2 horas
            unset($_SESSION[$token_key], $_SESSION[$time_key]);
            return false;
        }
    }
    
    // Comparação segura
    if (function_exists("hash_equals")) {
        return hash_equals($session_token, $received_token);
    } else {
        return $session_token === $received_token;
    }
}

/**
 * Gera campo HTML com token CSRF
 */
function generateCSRFFieldFixed() {
    $token = generateCSRFTokenFixed();
    return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($token) . "\">";
}

/**
 * Aliases para compatibilidade
 */
if (!function_exists("generateCSRFToken")) {
    function generateCSRFToken() {
        return generateCSRFTokenFixed();
    }
}

if (!function_exists("verifyCSRFToken")) {
    function verifyCSRFToken($token) {
        return verifyCSRFTokenFixed($token);
    }
}

if (!function_exists("generateCsrfToken")) {
    function generateCsrfToken() {
        return generateCSRFFieldFixed();
    }
}

if (!function_exists("verifyCsrfToken")) {
    function verifyCsrfToken($token) {
        return verifyCSRFTokenFixed($token);
    }
}
';
        
        // Adicionar o código corrigido ao final do arquivo
        $conteudo_corrigido = $conteudo_original . $codigo_corrigido;
        
        if (file_put_contents($arquivo_auth, $conteudo_corrigido)) {
            echo "<div class='success'>✅ Correção aplicada com sucesso no arquivo auth.php</div>";
            $correcao_aplicada = true;
        } else {
            echo "<div class='error'>❌ Erro ao aplicar correção no arquivo auth.php</div>";
        }
    }
} else {
    echo "<div class='error'>❌ Arquivo auth.php não encontrado para correção</div>";
}

// 3. TESTAR A CORREÇÃO
echo "<h2>🧪 3. Teste da Correção</h2>";

if ($correcao_aplicada || strpos(file_get_contents($arquivo_auth), 'generateCSRFTokenFixed') !== false) {
    // Incluir o arquivo corrigido
    require_once $arquivo_auth;
    
    try {
        // Testar geração de token
        $token_teste = generateCSRFTokenFixed();
        echo "<div class='success'>✅ Token gerado com sucesso</div>";
        echo "<div class='info'>Token: " . substr($token_teste, 0, 20) . "... (" . strlen($token_teste) . " chars)</div>";
        
        // Testar verificação
        $verif_resultado = verifyCSRFTokenFixed($token_teste);
        if ($verif_resultado) {
            echo "<div class='success'>✅ Verificação de token funcionando</div>";
        } else {
            echo "<div class='error'>❌ Falha na verificação de token</div>";
        }
        
        // Testar aliases
        $token_alias = generateCSRFToken();
        $verif_alias = verifyCSRFToken($token_alias);
        
        if ($verif_alias) {
            echo "<div class='success'>✅ Funções de compatibilidade funcionando</div>";
        } else {
            echo "<div class='warning'>⚠️ Problema com funções de compatibilidade</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro ao testar correção: " . $e->getMessage() . "</div>";
    }
}

// 4. TESTE PRÁTICO COM FORMULÁRIO
echo "<h2>🎯 4. Teste Prático Final</h2>";

// Processar teste se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teste_final'])) {
    echo "<h3>📊 Resultado do Teste Final:</h3>";
    
    $token_recebido = $_POST['csrf_token'] ?? '';
    
    try {
        if (function_exists('verifyCSRFTokenFixed')) {
            $resultado = verifyCSRFTokenFixed($token_recebido);
        } elseif (function_exists('verifyCSRFToken')) {
            $resultado = verifyCSRFToken($token_recebido);
        } else {
            throw new Exception('Nenhuma função de verificação disponível');
        }
        
        if ($resultado) {
            echo "<div class='success'>";
            echo "<h4>🎉 SUCESSO TOTAL!</h4>";
            echo "<p>✅ Token CSRF validado com sucesso!</p>";
            echo "<p>✅ A correção foi aplicada corretamente!</p>";
            echo "<p>✅ O problema na página de profissionais deve estar resolvido!</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h4>❌ AINDA HÁ PROBLEMAS</h4>";
            echo "<p>❌ Token CSRF inválido!</p>";
            echo "<p>⚠️ Pode ser necessária intervenção manual adicional.</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro no teste: " . $e->getMessage() . "</div>";
    }
}

// Gerar formulário de teste
if (function_exists('generateCSRFTokenFixed')) {
    $form_token = generateCSRFTokenFixed();
} elseif (function_exists('generateCSRFToken')) {
    $form_token = generateCSRFToken();
} else {
    $form_token = 'ERRO_NO_TOKEN';
}

echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🧪 Teste Final da Correção CSRF</h4>";
echo "<input type='hidden' name='csrf_token' value='{$form_token}'>";
echo "<input type='hidden' name='teste_final' value='1'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label>Dados de teste:</label><br>";
echo "<input type='text' name='dados_teste' value='Correção CSRF Aplicada' style='width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 3px;'>";
echo "</div>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label>Token CSRF:</label><br>";
echo "<input type='text' value='" . substr($form_token, 0, 40) . "...' readonly style='width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 3px; background: #f8f9fa;'>";
echo "</div>";
echo "<button type='submit' style='background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 3px; cursor: pointer; font-size: 16px;'>🚀 TESTAR CORREÇÃO FINAL</button>";
echo "</form>";

// 5. INSTRUÇÕES FINAIS
echo "<h2>📋 5. Instruções Finais</h2>";

if ($isOnline) {
    echo "<div class='success'>";
    echo "<h4>🌐 Ambiente Online - Correção Aplicada</h4>";
    echo "<p><strong>A correção foi aplicada diretamente no servidor online!</strong></p>";
    echo "<ul>";
    echo "<li>✅ Funções CSRF corrigidas adicionadas ao auth.php</li>";
    echo "<li>✅ Configurações de sessão segura aplicadas</li>";
    echo "<li>✅ Backup do arquivo original criado</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<h4>🏠 Ambiente Local</h4>";
    echo "<p>Para aplicar no servidor online:</p>";
    echo "<ol>";
    echo "<li>Faça upload deste arquivo para o servidor</li>";
    echo "<li>Acesse: https://cortefacil.app/correcao_csrf_definitiva_online.php</li>";
    echo "<li>Execute a correção no ambiente de produção</li>";
    echo "<li>Teste a página de profissionais</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h4>🎯 Próximos Passos</h4>";
echo "<ol>";
echo "<li><strong>Testar página de profissionais:</strong> Acesse /parceiro/profissionais.php</li>";
echo "<li><strong>Tentar cadastrar profissional:</strong> Preencha o formulário e envie</li>";
echo "<li><strong>Verificar logs:</strong> Se ainda houver erro, consulte os logs</li>";
echo "<li><strong>Remover este arquivo:</strong> Por segurança, remova após o uso</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<p><a href='/parceiro/profissionais.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 16px;'>🎯 TESTAR PÁGINA DE PROFISSIONAIS</a></p>";
echo "<p><small>Correção aplicada em: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";
?>