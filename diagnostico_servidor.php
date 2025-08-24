<?php
// Diagnóstico completo do servidor
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>";
echo "<html><head><title>Diagnóstico Servidor</title></head><body>";
echo "<h1>Diagnóstico do Servidor CorteFácil</h1>";

// Informações básicas
echo "<h2>Informações Básicas</h2>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>Script:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Filename:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "</p>";

// Verificar se arquivo existe
echo "<h2>Verificação de Arquivos</h2>";
$current_file = __FILE__;
echo "<p><strong>Arquivo atual:</strong> $current_file</p>";
echo "<p><strong>Arquivo existe:</strong> " . (file_exists($current_file) ? 'SIM' : 'NÃO') . "</p>";

// Verificar .htaccess
$htaccess = __DIR__ . '/.htaccess';
echo "<p><strong>.htaccess existe:</strong> " . (file_exists($htaccess) ? 'SIM' : 'NÃO') . "</p>";
if (file_exists($htaccess)) {
    echo "<p><strong>Conteúdo .htaccess:</strong></p>";
    echo "<pre>" . htmlspecialchars(file_get_contents($htaccess)) . "</pre>";
}

// Verificar index.php
$index = __DIR__ . '/index.php';
echo "<p><strong>index.php existe:</strong> " . (file_exists($index) ? 'SIM' : 'NÃO') . "</p>";

// Informações PHP
echo "<h2>Informações PHP</h2>";
echo "<p><strong>Versão PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>SAPI:</strong> " . php_sapi_name() . "</p>";

// Headers recebidos
echo "<h2>Headers da Requisição</h2>";
echo "<pre>";
foreach (getallheaders() as $name => $value) {
    echo htmlspecialchars("$name: $value") . "\n";
}
echo "</pre>";

// Variáveis de ambiente
echo "<h2>Variáveis $_SERVER Relevantes</h2>";
$relevant_vars = ['REQUEST_METHOD', 'QUERY_STRING', 'HTTP_HOST', 'SERVER_NAME', 'REQUEST_URI', 'SCRIPT_NAME', 'PATH_INFO', 'REDIRECT_STATUS'];
echo "<pre>";
foreach ($relevant_vars as $var) {
    if (isset($_SERVER[$var])) {
        echo htmlspecialchars("$var: " . $_SERVER[$var]) . "\n";
    }
}
echo "</pre>";

echo "<h2>Status Final</h2>";
echo "<p style='color: green; font-weight: bold;'>✅ Este script está sendo executado diretamente!</p>";
echo "<p>Se você está vendo esta mensagem, significa que o PHP está funcionando corretamente.</p>";

echo "</body></html>";
?>