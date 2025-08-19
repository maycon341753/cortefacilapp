<?php
/**
 * Debug para erro 500 - Hostinger
 * Arquivo para diagnosticar problemas que causam erro 500
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para capturar erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
        echo "<h3 style='color: #f44336;'>ERRO FATAL DETECTADO:</h3>";
        echo "<p><strong>Tipo:</strong> " . $error['type'] . "</p>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $error['file'] . "</p>";
        echo "<p><strong>Linha:</strong> " . $error['line'] . "</p>";
        echo "</div>";
    }
});

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Debug 500 - CorteFácil</title></head><body>";
echo "<h1>Diagnóstico de Erro 500 - CorteFácil</h1>";

try {
    echo "<h2>1. Informações do Servidor</h2>";
    echo "<ul>";
    echo "<li>PHP Version: " . phpversion() . "</li>";
    echo "<li>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</li>";
    echo "<li>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</li>";
    echo "<li>Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</li>";
    echo "</ul>";
    
    echo "<h2>2. Teste de Extensões PHP</h2>";
    $extensions = ['pdo', 'pdo_mysql', 'mysqli', 'curl', 'json', 'mbstring', 'openssl'];
    echo "<ul>";
    foreach ($extensions as $ext) {
        $status = extension_loaded($ext) ? '✓ OK' : '✗ NÃO DISPONÍVEL';
        $color = extension_loaded($ext) ? 'green' : 'red';
        echo "<li style='color: $color;'>$ext: $status</li>";
    }
    echo "</ul>";
    
    echo "<h2>3. Teste de Arquivos de Configuração</h2>";
    
    // Testar database.php
    echo "<h3>3.1 Testando database.php</h3>";
    $db_file = __DIR__ . '/config/database.php';
    if (file_exists($db_file)) {
        echo "<p style='color: green;'>✓ Arquivo existe: $db_file</p>";
        try {
            require_once $db_file;
            echo "<p style='color: green;'>✓ Arquivo carregado com sucesso</p>";
            
            // Testar se a classe Database existe
            if (class_exists('Database')) {
                echo "<p style='color: green;'>✓ Classe Database encontrada</p>";
            } else {
                echo "<p style='color: red;'>✗ Classe Database não encontrada</p>";
            }
            
            // Testar se a função getConnection existe
            if (function_exists('getConnection')) {
                echo "<p style='color: green;'>✓ Função getConnection encontrada</p>";
            } else {
                echo "<p style='color: red;'>✗ Função getConnection não encontrada</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Erro ao carregar: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Arquivo não encontrado: $db_file</p>";
    }
    
    // Testar functions.php
    echo "<h3>3.2 Testando functions.php</h3>";
    $func_file = __DIR__ . '/includes/functions.php';
    if (file_exists($func_file)) {
        echo "<p style='color: green;'>✓ Arquivo existe: $func_file</p>";
        try {
            require_once $func_file;
            echo "<p style='color: green;'>✓ Arquivo carregado com sucesso</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Erro ao carregar: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Arquivo não encontrado: $func_file</p>";
    }
    
    // Testar auth.php
    echo "<h3>3.3 Testando auth.php</h3>";
    $auth_file = __DIR__ . '/includes/auth.php';
    if (file_exists($auth_file)) {
        echo "<p style='color: green;'>✓ Arquivo existe: $auth_file</p>";
        try {
            require_once $auth_file;
            echo "<p style='color: green;'>✓ Arquivo carregado com sucesso</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Erro ao carregar: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Arquivo não encontrado: $auth_file</p>";
    }
    
    echo "<h2>4. Configurações PHP Críticas</h2>";
    $configs = [
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_input_vars' => ini_get('max_input_vars'),
        'display_errors' => ini_get('display_errors') ? 'On' : 'Off',
        'log_errors' => ini_get('log_errors') ? 'On' : 'Off'
    ];
    
    echo "<ul>";
    foreach ($configs as $key => $value) {
        echo "<li><strong>$key:</strong> $value</li>";
    }
    echo "</ul>";
    
    echo "<h2>5. Teste Básico de Cadastro</h2>";
    echo "<p>Simulando carregamento básico do cadastro.php...</p>";
    
    // Simular o início do cadastro.php sem executar tudo
    $cadastro_file = __DIR__ . '/cadastro.php';
    if (file_exists($cadastro_file)) {
        echo "<p style='color: green;'>✓ Arquivo cadastro.php existe</p>";
        
        // Verificar se conseguimos ler o arquivo
        $content = file_get_contents($cadastro_file);
        if ($content !== false) {
            echo "<p style='color: green;'>✓ Arquivo cadastro.php é legível</p>";
            echo "<p>Tamanho do arquivo: " . strlen($content) . " bytes</p>";
        } else {
            echo "<p style='color: red;'>✗ Não foi possível ler o arquivo cadastro.php</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Arquivo cadastro.php não encontrado</p>";
    }
    
    echo "<h2>Diagnóstico Concluído</h2>";
    echo "<p style='color: green; font-weight: bold;'>Se você está vendo esta mensagem, o PHP está funcionando!</p>";
    echo "<p>Se o erro 500 persistir no servidor, pode ser:</p>";
    echo "<ul>";
    echo "<li>Problema de permissões de arquivo (chmod 644 para arquivos, 755 para diretórios)</li>";
    echo "<li>Arquivo .htaccess com configurações incompatíveis</li>";
    echo "<li>Limite de memória PHP insuficiente no servidor</li>";
    echo "<li>Versão PHP incompatível no servidor</li>";
    echo "<li>Problema na conexão com banco de dados remoto</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px;'>";
    echo "<h3 style='color: #f44336;'>ERRO CAPTURADO:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>