<?php
/**
 * Teste Específico de Roteamento - Erro 500
 * CorteFácil - Diagnóstico de Rotas
 * Para identificar problemas no sistema de roteamento
 */

// Configurações de erro para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🛣️ Teste de Roteamento - Diagnóstico Erro 500</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>URL Atual:</strong> " . ($_SERVER['REQUEST_URI'] ?? '/') . "</p>";
echo "<p><strong>Método:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'GET') . "</p>";
echo "<hr>";

$problemas = [];
$status_ok = [];

// 1. Testar carregamento básico do PHP
echo "<h2>1. 🔧 Teste Básico PHP</h2>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";
echo "<p>✅ Script executando normalmente</p>";
$status_ok[] = "PHP básico funcionando";
echo "<hr>";

// 2. Testar includes críticos
echo "<h2>2. 📁 Teste de Includes</h2>";

// Testar auth.php
echo "<h3>2.1 Testando includes/auth.php</h3>";
try {
    if (file_exists('includes/auth.php')) {
        echo "<p>✅ Arquivo auth.php existe</p>";
        
        // Capturar output e erros
        ob_start();
        $error_buffer = '';
        
        set_error_handler(function($severity, $message, $file, $line) use (&$error_buffer) {
            $error_buffer .= "Warning: $message (linha $line)\n";
        });
        
        require_once 'includes/auth.php';
        
        restore_error_handler();
        $output = ob_get_clean();
        
        if (!empty($error_buffer)) {
            echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>";
            echo "<strong>⚠️ Warnings encontrados:</strong><br>";
            echo "<pre>" . htmlspecialchars($error_buffer) . "</pre>";
            echo "</div>";
            $problemas[] = "Warnings no auth.php";
        } else {
            echo "<p>✅ auth.php carregado sem erros</p>";
            $status_ok[] = "auth.php OK";
        }
        
        if (!empty($output)) {
            echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6;'>";
            echo "<strong>Output capturado:</strong><br>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
            echo "</div>";
        }
        
    } else {
        echo "<p>❌ auth.php não encontrado</p>";
        $problemas[] = "auth.php ausente";
    }
} catch (ParseError $e) {
    echo "<p>❌ Erro de sintaxe no auth.php: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    $problemas[] = "Erro de sintaxe no auth.php";
} catch (Error $e) {
    echo "<p>❌ Erro fatal no auth.php: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    $problemas[] = "Erro fatal no auth.php";
} catch (Exception $e) {
    echo "<p>❌ Exceção no auth.php: " . htmlspecialchars($e->getMessage()) . "</p>";
    $problemas[] = "Exceção no auth.php";
}

// Testar functions.php
echo "<h3>2.2 Testando includes/functions.php</h3>";
try {
    if (file_exists('includes/functions.php')) {
        echo "<p>✅ Arquivo functions.php existe</p>";
        
        ob_start();
        $error_buffer = '';
        
        set_error_handler(function($severity, $message, $file, $line) use (&$error_buffer) {
            $error_buffer .= "Warning: $message (linha $line)\n";
        });
        
        require_once 'includes/functions.php';
        
        restore_error_handler();
        $output = ob_get_clean();
        
        if (!empty($error_buffer)) {
            echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>";
            echo "<strong>⚠️ Warnings encontrados:</strong><br>";
            echo "<pre>" . htmlspecialchars($error_buffer) . "</pre>";
            echo "</div>";
            $problemas[] = "Warnings no functions.php";
        } else {
            echo "<p>✅ functions.php carregado sem erros</p>";
            $status_ok[] = "functions.php OK";
        }
        
    } else {
        echo "<p>❌ functions.php não encontrado</p>";
        $problemas[] = "functions.php ausente";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro no functions.php: " . htmlspecialchars($e->getMessage()) . "</p>";
    $problemas[] = "Erro no functions.php";
}

echo "<hr>";

// 3. Testar simulação do index.php
echo "<h2>3. 🏠 Simulação do Index.php</h2>";
try {
    echo "<p>🔍 Simulando carregamento do index.php...</p>";
    
    // Simular o que o index.php faz
    if (function_exists('isLoggedIn')) {
        echo "<p>✅ Função isLoggedIn() disponível</p>";
        
        // Testar a função (sem iniciar sessão real)
        try {
            // Verificar se sessão já está ativa
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
                echo "<p>✅ Sessão iniciada para teste</p>";
            } else {
                echo "<p>✅ Sessão já ativa</p>";
            }
            
            $logged_in = isLoggedIn();
            echo "<p>✅ isLoggedIn() retornou: " . ($logged_in ? 'true' : 'false') . "</p>";
            $status_ok[] = "Função isLoggedIn testada";
            
        } catch (Exception $e) {
            echo "<p>❌ Erro ao testar isLoggedIn(): " . htmlspecialchars($e->getMessage()) . "</p>";
            $problemas[] = "Erro na função isLoggedIn";
        }
        
    } else {
        echo "<p>❌ Função isLoggedIn() não encontrada</p>";
        $problemas[] = "Função isLoggedIn ausente";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro na simulação do index.php: " . htmlspecialchars($e->getMessage()) . "</p>";
    $problemas[] = "Erro na simulação do index";
}

echo "<hr>";

// 4. Testar roteamento via .htaccess
echo "<h2>4. ⚙️ Teste de Roteamento .htaccess</h2>";

// Verificar se estamos sendo chamados via rewrite
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$query_string = $_SERVER['QUERY_STRING'] ?? '';

echo "<p><strong>REQUEST_URI:</strong> " . htmlspecialchars($request_uri) . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . htmlspecialchars($script_name) . "</p>";
echo "<p><strong>QUERY_STRING:</strong> " . htmlspecialchars($query_string) . "</p>";

if (strpos($script_name, 'teste_roteamento_500.php') !== false) {
    echo "<p>✅ Chamada direta ao script de teste</p>";
    $status_ok[] = "Acesso direto funcionando";
} else {
    echo "<p>🔄 Possível chamada via rewrite do .htaccess</p>";
    $status_ok[] = "Rewrite pode estar funcionando";
}

// Verificar .htaccess
if (file_exists('.htaccess')) {
    echo "<p>✅ .htaccess existe</p>";
    $htaccess_content = file_get_contents('.htaccess');
    
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<p>✅ RewriteEngine está ativado</p>";
        $status_ok[] = "RewriteEngine ativo";
    } else {
        echo "<p>❌ RewriteEngine não encontrado</p>";
        $problemas[] = "RewriteEngine ausente";
    }
    
    if (strpos($htaccess_content, 'index.php') !== false) {
        echo "<p>✅ Regras apontam para index.php</p>";
        $status_ok[] = "Regras de rewrite OK";
    } else {
        echo "<p>❌ Regras de rewrite não encontradas</p>";
        $problemas[] = "Regras de rewrite ausentes";
    }
} else {
    echo "<p>❌ .htaccess não encontrado</p>";
    $problemas[] = ".htaccess ausente";
}

echo "<hr>";

// 5. Teste de URLs específicas
echo "<h2>5. 🔗 Teste de URLs Específicas</h2>";

$urls_teste = [
    '/' => 'Página inicial',
    '/login.php' => 'Página de login',
    '/cadastro.php' => 'Página de cadastro',
    '/cliente/dashboard.php' => 'Dashboard cliente',
    '/parceiro/dashboard.php' => 'Dashboard parceiro'
];

echo "<p><strong>URLs para testar manualmente:</strong></p>";
echo "<ul>";
foreach ($urls_teste as $url => $descricao) {
    $full_url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'cortefacil.app') . $url;
    echo "<li><a href='$full_url' target='_blank'>$descricao</a> - <code>$url</code></li>";
}
echo "</ul>";

echo "<hr>";

// 6. Informações do servidor
echo "<h2>6. 🖥️ Informações do Servidor</h2>";

echo "<div style='background: #f8f9fa; padding: 15px; font-family: monospace; font-size: 12px;'>";
echo "<strong>SERVER_SOFTWARE:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "<br>";
echo "<strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "<br>";
echo "<strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "<br>";
echo "<strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "<strong>SCRIPT_FILENAME:</strong> " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "<br>";
echo "<strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "<br>";
echo "<strong>HTTPS:</strong> " . ($_SERVER['HTTPS'] ?? 'N/A') . "<br>";
echo "<strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "<br>";
echo "<strong>Current Directory:</strong> " . getcwd() . "<br>";
echo "</div>";

echo "<hr>";

// 7. Resumo final
echo "<h2>7. 📊 Resumo do Diagnóstico</h2>";

$total_testes = count($status_ok) + count($problemas);
$percentual_ok = $total_testes > 0 ? (count($status_ok) / $total_testes) * 100 : 0;

if (count($problemas) == 0) {
    echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745;'>";
    echo "<h3>🎉 TODOS OS TESTES PASSARAM</h3>";
    echo "<p><strong>" . count($status_ok) . " componentes funcionando corretamente!</strong></p>";
    echo "<p>Se ainda há erro 500, o problema pode estar em:</p>";
    echo "<ul>";
    echo "<li>Cache do servidor ou navegador</li>";
    echo "<li>Configurações específicas do Hostinger</li>";
    echo "<li>Módulos Apache não disponíveis</li>";
    echo "<li>Limites de recursos do servidor</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ PROBLEMAS ENCONTRADOS</h3>";
    echo "<p><strong>" . count($problemas) . " problema(s) identificado(s):</strong></p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($status_ok)) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin-top: 15px;'>";
    echo "<h4>✅ Componentes Funcionando (" . count($status_ok) . "):</h4>";
    echo "<ul>";
    foreach ($status_ok as $item) {
        echo "<li>$item</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #e2e3e5; padding: 15px; border-left: 4px solid #6c757d;'>";
echo "<h3>🔧 Próximas Ações:</h3>";
echo "<ol>";

if (count($problemas) > 0) {
    echo "<li><strong>Corrigir problemas identificados</strong> listados acima</li>";
    echo "<li><strong>Fazer upload dos arquivos corrigidos</strong></li>";
    echo "<li><strong>Testar novamente</strong> após correções</li>";
} else {
    echo "<li><strong>Verificar logs do servidor</strong> no painel Hostinger</li>";
    echo "<li><strong>Limpar cache</strong> do servidor e navegador</li>";
    echo "<li><strong>Testar URLs individuais</strong> listadas acima</li>";
    echo "<li><strong>Verificar configurações PHP</strong> no painel</li>";
}

echo "<li><strong>Executar diagnóstico completo:</strong> <a href='diagnostico_producao_500.php'>diagnostico_producao_500.php</a></li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Teste de roteamento executado em " . date('Y-m-d H:i:s') . "</em></p>";
?>