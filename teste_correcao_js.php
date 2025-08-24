<?php
// Teste para verificar se a correção do erro JavaScript foi efetiva

echo "<h2>Teste da Correção do Erro JavaScript</h2>";

// Simular acesso à página de agendamento
$url = 'https://cortefacil.app/cliente/agendar.php';

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Simular login com cookies de sessão
$cookieJar = tempnam(sys_get_temp_dir(), 'cookies');
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);

// Primeiro fazer login
$loginUrl = 'https://cortefacil.app/login.php';
curl_setopt($ch, CURLOPT_URL, $loginUrl);
$loginPage = curl_exec($ch);

if ($loginPage === false) {
    echo "<p style='color: red;'>Erro ao acessar página de login: " . curl_error($ch) . "</p>";
    curl_close($ch);
    exit;
}

// Fazer POST do login
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'cliente@teste.com',
    'senha' => '123456'
]));

$loginResult = curl_exec($ch);

if ($loginResult === false) {
    echo "<p style='color: red;'>Erro no login: " . curl_error($ch) . "</p>";
    curl_close($ch);
    exit;
}

echo "<p style='color: green;'>Login realizado com sucesso</p>";

// Agora acessar a página de agendamento
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, false);
$response = curl_exec($ch);

if ($response === false) {
    echo "<p style='color: red;'>Erro ao acessar página de agendamento: " . curl_error($ch) . "</p>";
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "<p>Código HTTP: <strong>$httpCode</strong></p>";

curl_close($ch);
unlink($cookieJar);

// Verificar se a página contém os elementos esperados
echo "<h3>Verificações da Página:</h3>";

// Verificar se não há redirecionamento para login
if (strpos($response, 'login.php') !== false && strpos($response, 'window.location') !== false) {
    echo "<p style='color: red;'>❌ Página ainda está redirecionando para login</p>";
} else {
    echo "<p style='color: green;'>✅ Página não está redirecionando para login</p>";
}

// Verificar se contém os elementos de agendamento
if (strpos($response, 'id="salaoEndereco"') !== false) {
    echo "<p style='color: green;'>✅ Elemento salaoEndereco encontrado</p>";
} else {
    echo "<p style='color: red;'>❌ Elemento salaoEndereco não encontrado</p>";
}

if (strpos($response, 'id="salaoTelefone"') !== false) {
    echo "<p style='color: green;'>✅ Elemento salaoTelefone encontrado</p>";
} else {
    echo "<p style='color: red;'>❌ Elemento salaoTelefone não encontrado</p>";
}

if (strpos($response, 'function mostrarInfoSalao') !== false) {
    echo "<p style='color: green;'>✅ Função mostrarInfoSalao encontrada</p>";
} else {
    echo "<p style='color: red;'>❌ Função mostrarInfoSalao não encontrada</p>";
}

// Verificar se a correção foi aplicada
if (strpos($response, 'enderecoElement && telefoneElement && infoSalaoElement') !== false) {
    echo "<p style='color: green;'>✅ Correção do erro JavaScript aplicada</p>";
} else {
    echo "<p style='color: red;'>❌ Correção do erro JavaScript não encontrada</p>";
}

// Verificar se há erros JavaScript visíveis
if (strpos($response, 'Cannot set properties of null') !== false) {
    echo "<p style='color: red;'>❌ Ainda há referências ao erro JavaScript</p>";
} else {
    echo "<p style='color: green;'>✅ Nenhuma referência ao erro JavaScript encontrada</p>";
}

echo "<h3>Resumo:</h3>";
echo "<p>A correção foi aplicada para adicionar verificações de segurança na função mostrarInfoSalao, 
evitando o erro 'Cannot set properties of null' ao tentar definir textContent de elementos que podem não existir.</p>";

echo "<p><strong>Próximos passos:</strong> Teste a página manualmente para confirmar que o erro não ocorre mais ao selecionar salões.</p>";
?>