<?php
echo "=== TESTE DE LOGIN E ACESSO À PÁGINA DE AGENDAMENTO ===\n";

// Configurar cookie jar para manter sessão
$cookieJar = tempnam(sys_get_temp_dir(), 'cookies');

// Função para fazer requisições HTTP com cURL
function makeRequest($url, $postData = null, $cookieJar = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

try {
    // Passo 1: Acessar página de login para obter token CSRF
    echo "1. Acessando página de login...\n";
    $loginPageResult = makeRequest('https://cortefacil.app/login.php', null, $cookieJar);
    
    if ($loginPageResult['error']) {
        throw new Exception("Erro ao acessar login: " . $loginPageResult['error']);
    }
    
    echo "✓ Página de login carregada (HTTP {$loginPageResult['http_code']})\n";
    
    // Extrair token CSRF
    $csrfToken = null;
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPageResult['response'], $matches)) {
        $csrfToken = $matches[1];
        echo "✓ Token CSRF encontrado: " . substr($csrfToken, 0, 20) . "...\n";
    } else {
        echo "⚠ Token CSRF não encontrado, tentando sem ele...\n";
    }
    
    // Passo 2: Fazer login
    echo "\n2. Fazendo login...\n";
    $loginData = [
        'email' => 'cliente@teste.com',
        'senha' => '123456'
    ];
    
    if ($csrfToken) {
        $loginData['csrf_token'] = $csrfToken;
    }
    
    $loginResult = makeRequest('https://cortefacil.app/login.php', http_build_query($loginData), $cookieJar);
    
    if ($loginResult['error']) {
        throw new Exception("Erro no login: " . $loginResult['error']);
    }
    
    echo "✓ Login realizado (HTTP {$loginResult['http_code']})\n";
    
    // Verificar se login foi bem-sucedido
    if (strpos($loginResult['response'], 'dashboard') !== false || 
        strpos($loginResult['response'], 'cliente/dashboard') !== false ||
        $loginResult['http_code'] == 302) {
        echo "✓ Login bem-sucedido!\n";
    } else {
        echo "⚠ Login pode ter falhou - verificando resposta...\n";
        echo "Início da resposta: " . substr($loginResult['response'], 0, 200) . "...\n";
    }
    
    // Passo 3: Acessar página de agendamento
    echo "\n3. Acessando página de agendamento...\n";
    $agendarResult = makeRequest('https://cortefacil.app/cliente/agendar.php', null, $cookieJar);
    
    if ($agendarResult['error']) {
        throw new Exception("Erro ao acessar agendamento: " . $agendarResult['error']);
    }
    
    echo "✓ Página de agendamento acessada (HTTP {$agendarResult['http_code']})\n";
    echo "Tamanho da resposta: " . strlen($agendarResult['response']) . " bytes\n";
    
    // Verificar se ainda está sendo redirecionado para login
    if (strpos($agendarResult['response'], 'login.php') !== false) {
        echo "✗ Ainda sendo redirecionado para login\n";
    } else {
        echo "✓ Não há redirecionamento para login\n";
    }
    
    // Verificar elementos da página de agendamento
    echo "\n=== VERIFICAÇÃO DE ELEMENTOS DA PÁGINA ===\n";
    $elementos = [
        'Agendar Serviço' => 'Título da página',
        'Escolha o Salão' => 'Seleção de salão',
        'Escolha o Profissional' => 'Seleção de profissional',
        'api/horarios.php' => 'Chamada para API'
    ];
    
    foreach ($elementos as $elemento => $descricao) {
        if (strpos($agendarResult['response'], $elemento) !== false) {
            echo "✓ $descricao encontrado\n";
        } else {
            echo "✗ $descricao NÃO encontrado\n";
        }
    }
    
    // Mostrar início da página para debug
    echo "\n=== INÍCIO DA PÁGINA DE AGENDAMENTO ===\n";
    echo substr($agendarResult['response'], 0, 800) . "...\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // Limpar arquivo de cookies
    if (file_exists($cookieJar)) {
        unlink($cookieJar);
    }
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>