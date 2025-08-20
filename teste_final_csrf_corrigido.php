<?php
// TESTE FINAL - VERIFICAÇÃO DA CORREÇÃO CSRF
// Este arquivo testa se a correção aplicada no auth.php funcionou

require_once 'includes/auth.php';

// Simular usuário logado
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'parceiro';

// Processar formulário se enviado
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $result = verifyCSRFToken($token);
}

// Gerar token para o formulário
$currentToken = generateCSRFToken();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Final - CSRF Corrigido</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .debug { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        input[type="submit"] { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background: #0056b3; }
        .token-display { word-break: break-all; font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🧪 Teste Final - CSRF Corrigido</h1>
    
    <div class="info">
        <h3>📊 Informações da Sessão:</h3>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Usuário Logado:</strong> ID <?php echo $_SESSION['user_id']; ?> (<?php echo $_SESSION['user_type']; ?>)</p>
        <p><strong>Token Atual:</strong></p>
        <div class="token-display"><?php echo $currentToken; ?></div>
        <p><strong>Comprimento:</strong> <?php echo strlen($currentToken); ?> caracteres</p>
    </div>
    
    <?php if ($result !== null): ?>
        <?php if ($result): ?>
            <div class="success">
                <h3>✅ SUCESSO!</h3>
                <p><strong>Token CSRF validado com sucesso!</strong></p>
                <p>A correção funcionou perfeitamente. O problema de "Token de segurança inválido" foi resolvido.</p>
            </div>
        <?php else: ?>
            <div class="error">
                <h3>❌ ERRO: Token de segurança inválido</h3>
                <p>O problema ainda persiste. Verifique a implementação.</p>
            </div>
        <?php endif; ?>
        
        <div class="debug">
            <h4>🔍 Debug do Teste:</h4>
            <p><strong>Token Recebido:</strong> <?php echo $_POST['csrf_token'] ?? 'N/A'; ?></p>
            <p><strong>Token da Sessão:</strong> <?php echo $_SESSION['csrf_token_final_fix'] ?? 'N/A'; ?></p>
            <p><strong>Resultado da Validação:</strong> <?php echo $result ? 'VÁLIDO' : 'INVÁLIDO'; ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <h3>🎯 Teste de Validação CSRF</h3>
        <p>Este formulário usa o token gerado pela função corrigida:</p>
        
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($currentToken, ENT_QUOTES, 'UTF-8'); ?>">
        
        <input type="submit" value="Testar Validação CSRF">
    </form>
    
    <div class="info">
        <h3>🎯 Teste de Persistência</h3>
        <p>Clique no botão abaixo para verificar se o mesmo token é mantido:</p>
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: inline-block; background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Recarregar Página</a>
    </div>
    
    <div class="debug">
        <h4>📋 Status da Correção:</h4>
        <ul>
            <li>✅ Função <code>generateCSRFToken()</code> corrigida</li>
            <li>✅ Função <code>verifyCSRFToken()</code> corrigida</li>
            <li>✅ Token persistente na sessão</li>
            <li>✅ Chave específica para evitar conflitos</li>
            <li>✅ Normalização de tokens</li>
            <li>✅ Tempo de expiração estendido (3 horas)</li>
        </ul>
    </div>
    
    <div class="success">
        <h3>🚀 Próximos Passos:</h3>
        <ol>
            <li><strong>Se este teste PASSAR:</strong> A correção está funcionando localmente</li>
            <li><strong>Aplicar no servidor online:</strong> Faça upload do arquivo <code>includes/auth.php</code> corrigido</li>
            <li><strong>Testar online:</strong> Acesse <code>https://cortefacil.app/parceiro/salao.php</code></li>
            <li><strong>Verificar funcionamento:</strong> O erro "Token de segurança inválido" deve desaparecer</li>
        </ol>
    </div>
</body>
</html>