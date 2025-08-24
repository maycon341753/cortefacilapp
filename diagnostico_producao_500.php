<?php
/**
 * Diagnóstico Completo - Erro 500 em Produção
 * CorteFácil - https://cortefacil.app/
 * Para ser executado diretamente no servidor Hostinger
 */

// Configurações de erro para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico Completo - Erro 500 Produção</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</p>";
echo "<p><strong>URL Atual:</strong> " . ($_SERVER['REQUEST_URI'] ?? '/') . "</p>";
echo "<hr>";

$problemas = [];
$status_ok = [];
$ambiente_producao = !in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1']);

if ($ambiente_producao) {
    echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 15px;'>";
    echo "<strong>🌐 AMBIENTE DE PRODUÇÃO DETECTADO</strong><br>";
    echo "Servidor: " . $_SERVER['SERVER_NAME'] . "<br>";
    echo "Executando diagnóstico completo...";
    echo "</div>";
} else {
    echo "<div style='background: #d1ecf1; padding: 10px; border-left: 4px solid #bee5eb; margin-bottom: 15px;'>";
    echo "<strong>🏠 AMBIENTE LOCAL DETECTADO</strong><br>";
    echo "Simulando diagnóstico de produção...";
    echo "</div>";
}

// 1. Verificar PHP e configurações básicas
echo "<h2>1. 🔧 Configurações PHP</h2>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";
echo "<p>✅ Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>✅ Max Execution Time: " . ini_get('max_execution_time') . "s</p>";
echo "<p>✅ Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p>✅ Error Reporting: " . error_reporting() . "</p>";
$status_ok[] = "Configurações PHP";
echo "<hr>";

// 2. Verificar estrutura de arquivos
echo "<h2>2. 📁 Estrutura de Arquivos</h2>";

$arquivos_criticos = [
    'index.php' => 'Arquivo principal',
    '.htaccess' => 'Configurações Apache',
    'includes/auth.php' => 'Sistema de autenticação',
    'config/database.php' => 'Configuração do banco',
    'login.php' => 'Página de login',
    'cadastro.php' => 'Página de cadastro'
];

foreach ($arquivos_criticos as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        echo "<p>✅ $descricao: <code>$arquivo</code> (" . number_format(filesize($arquivo)) . " bytes)</p>";
        $status_ok[] = $descricao;
    } else {
        echo "<p>❌ $descricao: <code>$arquivo</code> - AUSENTE</p>";
        $problemas[] = "Arquivo ausente: $arquivo";
    }
}
echo "<hr>";

// 3. Testar carregamento do auth.php
echo "<h2>3. 🔐 Sistema de Autenticação</h2>";
try {
    if (file_exists('includes/auth.php')) {
        // Capturar possíveis erros
        ob_start();
        $error_occurred = false;
        
        set_error_handler(function($severity, $message, $file, $line) use (&$error_occurred) {
            $error_occurred = true;
            echo "<p>⚠️ Warning: $message (linha $line)</p>";
        });
        
        require_once 'includes/auth.php';
        
        restore_error_handler();
        $output = ob_get_clean();
        
        if ($error_occurred) {
            echo $output;
            $problemas[] = "Warnings no auth.php";
        } else {
            echo "<p>✅ auth.php carregado sem erros</p>";
            $status_ok[] = "auth.php carregado";
        }
        
        // Testar funções CSRF
        if (function_exists('generateCSRFToken')) {
            $token = generateCSRFToken();
            echo "<p>✅ generateCSRFToken(): " . substr($token, 0, 10) . "...</p>";
            $status_ok[] = "Função generateCSRFToken";
            
            if (function_exists('verifyCSRFToken')) {
                echo "<p>✅ verifyCSRFToken(): Disponível</p>";
                $status_ok[] = "Função verifyCSRFToken";
            } else {
                echo "<p>❌ verifyCSRFToken(): Não encontrada</p>";
                $problemas[] = "Função verifyCSRFToken ausente";
            }
        } else {
            echo "<p>❌ generateCSRFToken(): Não encontrada</p>";
            $problemas[] = "Função generateCSRFToken ausente";
        }
        
    } else {
        echo "<p>❌ auth.php não encontrado</p>";
        $problemas[] = "auth.php ausente";
    }
} catch (ParseError $e) {
    echo "<p>❌ Erro de sintaxe no auth.php: " . $e->getMessage() . "</p>";
    $problemas[] = "Erro de sintaxe: " . $e->getMessage();
} catch (Error $e) {
    echo "<p>❌ Erro fatal no auth.php: " . $e->getMessage() . "</p>";
    $problemas[] = "Erro fatal: " . $e->getMessage();
} catch (Exception $e) {
    echo "<p>❌ Exceção no auth.php: " . $e->getMessage() . "</p>";
    $problemas[] = "Exceção: " . $e->getMessage();
}
echo "<hr>";

// 4. Testar conexão com banco de dados
echo "<h2>4. 🗄️ Conexão com Banco de Dados</h2>";
try {
    if (file_exists('config/database.php')) {
        require_once 'config/database.php';
        
        if (class_exists('Database')) {
            // Usar a função global getConnection() que existe no database.php
            $conn = getConnection();
            
            if ($conn) {
                echo "<p>✅ Conexão com banco estabelecida</p>";
                
                // Testar uma query simples
                $stmt = $conn->prepare("SELECT 1 as test");
                $stmt->execute();
                $result = $stmt->fetch();
                
                if ($result && $result['test'] == 1) {
                    echo "<p>✅ Query de teste executada com sucesso</p>";
                    $status_ok[] = "Banco de dados funcionando";
                } else {
                    echo "<p>❌ Falha na query de teste</p>";
                    $problemas[] = "Query de teste falhou";
                }
            } else {
                echo "<p>❌ Falha na conexão com banco</p>";
                $problemas[] = "Conexão com banco falhou";
            }
        } else {
            echo "<p>❌ Classe Database não encontrada</p>";
            $problemas[] = "Classe Database ausente";
        }
    } else {
        echo "<p>❌ database.php não encontrado</p>";
        $problemas[] = "database.php ausente";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro no banco: " . $e->getMessage() . "</p>";
    $problemas[] = "Erro no banco: " . $e->getMessage();
}
echo "<hr>";

// 5. Verificar .htaccess
echo "<h2>5. ⚙️ Configurações .htaccess</h2>";
if (file_exists('.htaccess')) {
    $htaccess_content = file_get_contents('.htaccess');
    echo "<p>✅ .htaccess existe (" . strlen($htaccess_content) . " caracteres)</p>";
    
    // Verificar configurações importantes
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<p>✅ RewriteEngine ativado</p>";
        $status_ok[] = "RewriteEngine";
    } else {
        echo "<p>❌ RewriteEngine não encontrado</p>";
        $problemas[] = "RewriteEngine ausente";
    }
    
    if (strpos($htaccess_content, 'RewriteRule') !== false) {
        echo "<p>✅ Regras de reescrita configuradas</p>";
        $status_ok[] = "Regras de reescrita";
    } else {
        echo "<p>❌ Regras de reescrita não encontradas</p>";
        $problemas[] = "Regras de reescrita ausentes";
    }
} else {
    echo "<p>❌ .htaccess não encontrado</p>";
    $problemas[] = ".htaccess ausente";
}
echo "<hr>";

// 6. Testar roteamento básico
echo "<h2>6. 🛣️ Sistema de Roteamento</h2>";
try {
    if (file_exists('index.php')) {
        echo "<p>✅ index.php existe</p>";
        
        // Verificar se o index.php tem conteúdo básico
        $index_content = file_get_contents('index.php');
        if (strlen($index_content) > 100) {
            echo "<p>✅ index.php tem conteúdo (" . strlen($index_content) . " caracteres)</p>";
            $status_ok[] = "index.php com conteúdo";
        } else {
            echo "<p>⚠️ index.php muito pequeno (" . strlen($index_content) . " caracteres)</p>";
            $problemas[] = "index.php suspeito";
        }
        
        // Verificar sintaxe do index.php
        $syntax_check = shell_exec("php -l index.php 2>&1");
        if (strpos($syntax_check, 'No syntax errors') !== false) {
            echo "<p>✅ Sintaxe do index.php válida</p>";
            $status_ok[] = "Sintaxe index.php";
        } else {
            echo "<p>❌ Erro de sintaxe no index.php: " . htmlspecialchars($syntax_check) . "</p>";
            $problemas[] = "Erro de sintaxe no index.php";
        }
    } else {
        echo "<p>❌ index.php não encontrado</p>";
        $problemas[] = "index.php ausente";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao verificar roteamento: " . $e->getMessage() . "</p>";
    $problemas[] = "Erro no roteamento: " . $e->getMessage();
}
echo "<hr>";

// 7. Verificar logs de erro
echo "<h2>7. 📋 Logs de Erro</h2>";
$log_files = ['error.log', 'error_log', '../error_log', 'logs/error.log'];
$logs_found = false;

foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        $logs_found = true;
        $log_content = file_get_contents($log_file);
        $log_lines = explode("\n", $log_content);
        $recent_lines = array_slice($log_lines, -10); // Últimas 10 linhas
        
        echo "<p>✅ Log encontrado: <code>$log_file</code></p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; margin: 10px 0;'>";
        echo "<strong>Últimas entradas:</strong><br>";
        foreach ($recent_lines as $line) {
            if (trim($line)) {
                echo "<small>" . htmlspecialchars($line) . "</small><br>";
            }
        }
        echo "</div>";
        break;
    }
}

if (!$logs_found) {
    echo "<p>⚠️ Nenhum arquivo de log encontrado</p>";
}
echo "<hr>";

// 8. Resumo e diagnóstico
echo "<h2>8. 📊 Resumo do Diagnóstico</h2>";

$total_verificacoes = count($status_ok) + count($problemas);
$percentual_ok = $total_verificacoes > 0 ? (count($status_ok) / $total_verificacoes) * 100 : 0;

if (count($problemas) == 0) {
    echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745;'>";
    echo "<h3>🎉 DIAGNÓSTICO POSITIVO</h3>";
    echo "<p><strong>Todos os componentes estão funcionando!</strong></p>";
    echo "<p>Se ainda há erro 500, pode ser um problema de cache ou configuração do servidor.</p>";
    echo "</div>";
} elseif (count($problemas) <= 2) {
    echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107;'>";
    echo "<h3>⚠️ PROBLEMAS MENORES DETECTADOS</h3>";
    echo "<p><strong>" . count($problemas) . " problema(s) encontrado(s):</strong></p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ PROBLEMAS CRÍTICOS DETECTADOS</h3>";
    echo "<p><strong>" . count($problemas) . " problema(s) crítico(s):</strong></p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($status_ok)) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin-top: 15px;'>";
    echo "<h4>✅ Componentes Funcionando:</h4>";
    echo "<ul>";
    foreach ($status_ok as $item) {
        echo "<li>$item</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #e2e3e5; padding: 15px; border-left: 4px solid #6c757d;'>";
echo "<h3>🔧 Próximas Ações Recomendadas:</h3>";
echo "<ol>";

if (count($problemas) > 0) {
    echo "<li><strong>Corrigir problemas identificados</strong> listados acima</li>";
    echo "<li><strong>Fazer upload dos arquivos corrigidos</strong> para o servidor</li>";
} else {
    echo "<li><strong>Limpar cache do navegador</strong> e tentar novamente</li>";
    echo "<li><strong>Verificar logs do servidor</strong> no hPanel da Hostinger</li>";
}

echo "<li><strong>Testar URLs específicas:</strong></li>";
echo "<ul>";
echo "<li><a href='/'>Página inicial</a></li>";
echo "<li><a href='/login.php'>Login</a></li>";
echo "<li><a href='/cadastro.php'>Cadastro</a></li>";
echo "</ul>";
echo "<li><strong>Monitorar logs</strong> após cada teste</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Diagnóstico executado em " . date('Y-m-d H:i:s') . " no servidor " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</em></p>";

// Informações adicionais para debug
echo "<details style='margin-top: 20px;'>";
echo "<summary><strong>🔍 Informações Técnicas Detalhadas</strong></summary>";
echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; font-family: monospace; font-size: 12px;'>";
echo "<strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "<br>";
echo "<strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "<br>";
echo "<strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "<br>";
echo "<strong>SCRIPT_FILENAME:</strong> " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "<br>";
echo "<strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "<br>";
echo "<strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "<br>";
echo "<strong>HTTPS:</strong> " . ($_SERVER['HTTPS'] ?? 'N/A') . "<br>";
echo "<strong>Current Directory:</strong> " . getcwd() . "<br>";
echo "<strong>Include Path:</strong> " . get_include_path() . "<br>";
echo "</div>";
echo "</details>";
?>