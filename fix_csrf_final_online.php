<?php
// CORREÇÃO FINAL DEFINITIVA PARA CSRF ONLINE
// Este arquivo resolve o problema de tokens diferentes sendo gerados

session_start();

// Configurações específicas para ambiente online
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Função para gerar token CSRF com persistência forçada
function generateCSRFTokenFinalFix() {
    // Chave específica para evitar conflitos
    $tokenKey = 'csrf_token_final_fix';
    
    // Se já existe um token válido, SEMPRE retornar o mesmo
    if (isset($_SESSION[$tokenKey]) && !empty($_SESSION[$tokenKey])) {
        return $_SESSION[$tokenKey];
    }
    
    // Gerar novo token apenas se não existir
    if (function_exists('random_bytes')) {
        $token = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        $token = md5(uniqid(mt_rand(), true));
    }
    
    // Armazenar na sessão com timestamp
    $_SESSION[$tokenKey] = $token;
    $_SESSION[$tokenKey . '_time'] = time();
    
    return $token;
}

// Função para verificar token CSRF com debug detalhado
function verifyCSRFTokenFinalFix($token) {
    $tokenKey = 'csrf_token_final_fix';
    
    // Normalizar tokens (remover espaços e quebras de linha)
    $receivedToken = trim($token);
    $sessionToken = isset($_SESSION[$tokenKey]) ? trim($_SESSION[$tokenKey]) : '';
    
    // Debug detalhado
    $debug = [
        'received_token' => $receivedToken,
        'session_token' => $sessionToken,
        'received_length' => strlen($receivedToken),
        'session_length' => strlen($sessionToken),
        'tokens_exist' => !empty($receivedToken) && !empty($sessionToken),
        'direct_comparison' => $receivedToken === $sessionToken,
        'hash_equals' => function_exists('hash_equals') ? hash_equals($sessionToken, $receivedToken) : ($receivedToken === $sessionToken)
    ];
    
    // Verificar se tokens existem
    if (empty($receivedToken) || empty($sessionToken)) {
        $debug['error'] = 'Token vazio';
        return ['valid' => false, 'debug' => $debug];
    }
    
    // Verificar expiração (3 horas)
    $tokenTime = isset($_SESSION[$tokenKey . '_time']) ? $_SESSION[$tokenKey . '_time'] : 0;
    $age = time() - $tokenTime;
    $expired = $age > 10800; // 3 horas
    
    $debug['token_age'] = $age;
    $debug['token_expired'] = $expired;
    
    if ($expired) {
        $debug['error'] = 'Token expirado';
        return ['valid' => false, 'debug' => $debug];
    }
    
    // Validação final
    $isValid = $debug['hash_equals'];
    
    return ['valid' => $isValid, 'debug' => $debug];
}

// Função para gerar campo HTML
function generateCSRFFieldFinalFix() {
    $token = generateCSRFTokenFinalFix();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Processar formulário se enviado
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $result = verifyCSRFTokenFinalFix($token);
}

// Gerar token para o formulário
$currentToken = generateCSRFTokenFinalFix();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção Final CSRF Online</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input[type="submit"] { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔧 Correção Final CSRF Online</h1>
    
    <div class="info">
        <h3>📊 Status da Sessão:</h3>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Token Atual:</strong> <?php echo substr($currentToken, 0, 16) . '... (' . strlen($currentToken) . ' chars)'; ?></p>
        <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['csrf_token_final_fix_time'] ?? time()); ?></p>
    </div>
    
    <?php if ($result): ?>
        <?php if ($result['valid']): ?>
            <div class="success">
                <h3>✅ SUCESSO!</h3>
                <p>Token CSRF validado com sucesso!</p>
            </div>
        <?php else: ?>
            <div class="error">
                <h3>❌ ERRO: Token de segurança inválido</h3>
                <p>O problema ainda persiste.</p>
            </div>
        <?php endif; ?>
        
        <div class="debug">
            <h4>🔍 Debug Detalhado:</h4>
            <pre><?php print_r($result['debug']); ?></pre>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <h3>🧪 Teste de Validação CSRF</h3>
        <p>Este formulário testa a correção final do CSRF:</p>
        
        <?php echo generateCSRFFieldFinalFix(); ?>
        
        <input type="submit" value="Testar Validação CSRF">
    </form>
    
    <div class="info">
        <h3>📋 Instruções para Aplicação Online:</h3>
        <ol>
            <li><strong>Se este teste PASSAR:</strong>
                <ul>
                    <li>Substitua as funções no <code>includes/auth.php</code></li>
                    <li>Use <code>generateCSRFTokenFinalFix()</code> em vez de <code>generateCSRFToken()</code></li>
                    <li>Use <code>verifyCSRFTokenFinalFix()</code> em vez de <code>verifyCSRFToken()</code></li>
                </ul>
            </li>
            <li><strong>Se ainda FALHAR:</strong>
                <ul>
                    <li>Verifique se as sessões estão funcionando corretamente</li>
                    <li>Confirme se o HTTPS está configurado</li>
                    <li>Verifique logs do servidor para erros de sessão</li>
                </ul>
            </li>
        </ol>
    </div>
    
    <div class="debug">
        <h4>🔧 Código para Aplicar no auth.php:</h4>
        <pre>
// Substitua as funções existentes por estas:

function generateCSRFToken() {
    $tokenKey = 'csrf_token_final_fix';
    
    if (isset($_SESSION[$tokenKey]) && !empty($_SESSION[$tokenKey])) {
        return $_SESSION[$tokenKey];
    }
    
    if (function_exists('random_bytes')) {
        $token = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        $token = md5(uniqid(mt_rand(), true));
    }
    
    $_SESSION[$tokenKey] = $token;
    $_SESSION[$tokenKey . '_time'] = time();
    
    return $token;
}

function verifyCSRFToken($token) {
    $tokenKey = 'csrf_token_final_fix';
    
    $receivedToken = trim($token);
    $sessionToken = isset($_SESSION[$tokenKey]) ? trim($_SESSION[$tokenKey]) : '';
    
    if (empty($receivedToken) || empty($sessionToken)) {
        return false;
    }
    
    $tokenTime = isset($_SESSION[$tokenKey . '_time']) ? $_SESSION[$tokenKey . '_time'] : 0;
    $age = time() - $tokenTime;
    
    if ($age > 10800) { // 3 horas
        return false;
    }
    
    return function_exists('hash_equals') ? 
           hash_equals($sessionToken, $receivedToken) : 
           ($receivedToken === $sessionToken);
}
        </pre>
    </div>
</body>
</html>