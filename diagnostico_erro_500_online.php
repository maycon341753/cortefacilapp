<?php
// Diagnóstico de Erro 500 - Página Online
// Script para identificar a causa do erro 500 em https://cortefacil.app/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico de Erro 500 - Página Online</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

// 1. Verificar configuração PHP
echo "<h2>1. ⚙️ Configuração PHP</h2>";
echo "<p><strong>Versão PHP:</strong> " . phpversion() . "</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "s</p>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<hr>";

// 2. Verificar arquivos críticos
echo "<h2>2. 📁 Verificação de Arquivos Críticos</h2>";
$arquivos_criticos = [
    'index.php',
    '.htaccess',
    'includes/auth.php',
    'includes/functions.php',
    'config/database.php'
];

foreach ($arquivos_criticos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "<p>✅ <strong>$arquivo</strong> - Existe</p>";
        if (is_readable($arquivo)) {
            echo "<p>   📖 Legível: Sim</p>";
        } else {
            echo "<p>   ❌ Legível: Não</p>";
        }
    } else {
        echo "<p>❌ <strong>$arquivo</strong> - Não encontrado</p>";
    }
}
echo "<hr>";

// 3. Verificar .htaccess
echo "<h2>3. 🔧 Verificação do .htaccess</h2>";
if (file_exists('.htaccess')) {
    $htaccess_content = file_get_contents('.htaccess');
    echo "<p>✅ Arquivo .htaccess encontrado</p>";
    echo "<p><strong>Tamanho:</strong> " . strlen($htaccess_content) . " bytes</p>";
    echo "<details><summary>📄 Conteúdo do .htaccess</summary>";
    echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
    echo "</details>";
    
    // Verificar sintaxe básica
    if (strpos($htaccess_content, 'RewriteEngine') !== false) {
        echo "<p>✅ RewriteEngine encontrado</p>";
    }
} else {
    echo "<p>❌ Arquivo .htaccess não encontrado</p>";
}
echo "<hr>";

// 4. Testar conexão com banco de dados
echo "<h2>4. 🗄️ Teste de Conexão com Banco de Dados</h2>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        echo "<p>✅ Arquivo de configuração do banco carregado</p>";
        
        // Tentar conectar
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p>✅ Conexão com banco de dados bem-sucedida</p>";
        
        // Verificar tabelas principais
        $tabelas = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $tabela");
                $count = $stmt->fetchColumn();
                echo "<p>✅ Tabela <strong>$tabela</strong>: $count registros</p>";
            } catch (Exception $e) {
                echo "<p>❌ Tabela <strong>$tabela</strong>: Erro - " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p>❌ Arquivo config/database.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro na conexão com banco: " . $e->getMessage() . "</p>";
}
echo "<hr>";

// 5. Verificar includes
echo "<h2>5. 📦 Verificação de Includes</h2>";
try {
    if (file_exists('includes/auth.php')) {
        require_once 'includes/auth.php';
        echo "<p>✅ includes/auth.php carregado com sucesso</p>";
        
        // Verificar funções CSRF
        if (function_exists('generateCSRFToken')) {
            echo "<p>✅ Função generateCSRFToken disponível</p>";
        } else {
            echo "<p>❌ Função generateCSRFToken não encontrada</p>";
        }
        
        if (function_exists('verifyCSRFToken')) {
            echo "<p>✅ Função verifyCSRFToken disponível</p>";
        } else {
            echo "<p>❌ Função verifyCSRFToken não encontrada</p>";
        }
    } else {
        echo "<p>❌ includes/auth.php não encontrado</p>";
    }
    
    if (file_exists('includes/functions.php')) {
        require_once 'includes/functions.php';
        echo "<p>✅ includes/functions.php carregado com sucesso</p>";
    } else {
        echo "<p>❌ includes/functions.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar includes: " . $e->getMessage() . "</p>";
}
echo "<hr>";

// 6. Verificar permissões de arquivos
echo "<h2>6. 🔐 Verificação de Permissões</h2>";
$arquivos_permissoes = [
    'index.php',
    '.htaccess',
    'includes/',
    'config/',
    'assets/'
];

foreach ($arquivos_permissoes as $item) {
    if (file_exists($item)) {
        $perms = fileperms($item);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "<p>📁 <strong>$item</strong>: $perms_octal</p>";
    }
}
echo "<hr>";

// 7. Teste de carregamento do index.php
echo "<h2>7. 🏠 Teste de Carregamento do Index</h2>";
try {
    ob_start();
    $index_content = file_get_contents('index.php');
    ob_end_clean();
    
    echo "<p>✅ index.php lido com sucesso</p>";
    echo "<p><strong>Tamanho:</strong> " . strlen($index_content) . " bytes</p>";
    
    // Verificar sintaxe PHP básica
    if (strpos($index_content, '<?php') !== false) {
        echo "<p>✅ Tag PHP de abertura encontrada</p>";
    }
    
    if (strpos($index_content, 'require') !== false || strpos($index_content, 'include') !== false) {
        echo "<p>✅ Includes/requires encontrados</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao ler index.php: " . $e->getMessage() . "</p>";
}
echo "<hr>";

// 8. Verificar logs de erro
echo "<h2>8. 📋 Logs de Erro</h2>";
$log_files = ['error.log', 'error_log', 'php_errors.log'];
$found_logs = false;

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        $found_logs = true;
        echo "<p>📄 <strong>$log_file</strong> encontrado</p>";
        $log_content = file_get_contents($log_file);
        $log_lines = explode("\n", $log_content);
        $recent_lines = array_slice($log_lines, -10); // Últimas 10 linhas
        
        echo "<details><summary>📋 Últimas 10 linhas do log</summary>";
        echo "<pre>" . htmlspecialchars(implode("\n", $recent_lines)) . "</pre>";
        echo "</details>";
    }
}

if (!$found_logs) {
    echo "<p>ℹ️ Nenhum arquivo de log encontrado</p>";
}
echo "<hr>";

// 9. Resumo e Recomendações
echo "<h2>9. 📊 Resumo e Recomendações</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #007cba;'>";
echo "<h3>🔍 Possíveis Causas do Erro 500:</h3>";
echo "<ul>";
echo "<li><strong>Sintaxe no .htaccess:</strong> Verifique se há erros de sintaxe no arquivo .htaccess</li>";
echo "<li><strong>Permissões de arquivo:</strong> Arquivos PHP devem ter permissão 644, diretórios 755</li>";
echo "<li><strong>Versão PHP:</strong> Verifique se a versão PHP é compatível com o código</li>";
echo "<li><strong>Memory Limit:</strong> Aumente o limite de memória se necessário</li>";
echo "<li><strong>Arquivos corrompidos:</strong> Verifique se todos os arquivos estão íntegros</li>";
echo "<li><strong>Conexão com banco:</strong> Verifique as credenciais do banco de dados</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #f0fff0; padding: 15px; border-left: 4px solid #28a745; margin-top: 15px;'>";
echo "<h3>✅ Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Verificar os logs de erro do servidor</li>";
echo "<li>Testar com .htaccess renomeado temporariamente</li>";
echo "<li>Verificar se todos os arquivos foram enviados corretamente</li>";
echo "<li>Confirmar configurações do banco de dados</li>";
echo "<li>Contatar suporte da Hostinger se necessário</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Diagnóstico concluído em " . date('Y-m-d H:i:s') . "</em></p>";
?>