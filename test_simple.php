<?php
/**
 * Teste simples para verificar se PHP está funcionando
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste PHP - CorteFácil</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

// Testar se as extensões necessárias estão disponíveis
echo "<h2>Extensões PHP:</h2>";
echo "<ul>";
echo "<li>PDO: " . (extension_loaded('pdo') ? 'OK' : 'NÃO DISPONÍVEL') . "</li>";
echo "<li>PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'OK' : 'NÃO DISPONÍVEL') . "</li>";
echo "<li>MySQLi: " . (extension_loaded('mysqli') ? 'OK' : 'NÃO DISPONÍVEL') . "</li>";
echo "<li>cURL: " . (extension_loaded('curl') ? 'OK' : 'NÃO DISPONÍVEL') . "</li>";
echo "<li>JSON: " . (extension_loaded('json') ? 'OK' : 'NÃO DISPONÍVEL') . "</li>";
echo "</ul>";

// Testar include de arquivos
echo "<h2>Teste de Includes:</h2>";

try {
    echo "<p>Testando include do database.php...</p>";
    require_once __DIR__ . '/config/database.php';
    echo "<p style='color: green;'>✓ database.php carregado com sucesso</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao carregar database.php: " . $e->getMessage() . "</p>";
}

try {
    echo "<p>Testando include do functions.php...</p>";
    require_once __DIR__ . '/includes/functions.php';
    echo "<p style='color: green;'>✓ functions.php carregado com sucesso</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao carregar functions.php: " . $e->getMessage() . "</p>";
}

try {
    echo "<p>Testando include do auth.php...</p>";
    require_once __DIR__ . '/includes/auth.php';
    echo "<p style='color: green;'>✓ auth.php carregado com sucesso</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro ao carregar auth.php: " . $e->getMessage() . "</p>";
}

echo "<h2>Configurações PHP:</h2>";
echo "<ul>";
echo "<li>Memory Limit: " . ini_get('memory_limit') . "</li>";
echo "<li>Max Execution Time: " . ini_get('max_execution_time') . "</li>";
echo "<li>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>Post Max Size: " . ini_get('post_max_size') . "</li>";
echo "</ul>";

echo "<p style='color: green; font-weight: bold;'>Teste concluído com sucesso!</p>";
?>