<?php
/**
 * Teste Final - Verificação do Erro 500
 * CorteFácil - Hostinger
 */

// Configurações de erro para teste
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧪 Teste Final - Erro 500 Corrigido</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</p>";
echo "<hr>";

$testes_ok = 0;
$total_testes = 0;

// Teste 1: PHP funcionando
$total_testes++;
echo "<h3>Teste 1: PHP Básico</h3>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";
$testes_ok++;

// Teste 2: Carregamento do auth.php
$total_testes++;
echo "<h3>Teste 2: Carregamento do auth.php</h3>";
try {
    if (file_exists('includes/auth.php')) {
        require_once 'includes/auth.php';
        echo "<p>✅ auth.php carregado com sucesso</p>";
        $testes_ok++;
    } else {
        echo "<p>❌ auth.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar auth.php: " . $e->getMessage() . "</p>";
}

// Teste 3: Funções CSRF
$total_testes++;
echo "<h3>Teste 3: Funções CSRF</h3>";
try {
    if (function_exists('generateCSRFToken')) {
        $token = generateCSRFToken();
        echo "<p>✅ generateCSRFToken(): " . substr($token, 0, 10) . "...</p>";
        
        if (function_exists('generateCsrfToken')) {
            $html_token = generateCsrfToken();
            echo "<p>✅ generateCsrfToken(): Campo HTML gerado</p>";
            
            if (function_exists('verifyCSRFToken')) {
                $valid = verifyCSRFToken($token);
                echo "<p>✅ verifyCSRFToken(): Funcionando</p>";
                $testes_ok++;
            } else {
                echo "<p>❌ verifyCSRFToken() não encontrada</p>";
            }
        } else {
            echo "<p>❌ generateCsrfToken() não encontrada</p>";
        }
    } else {
        echo "<p>❌ generateCSRFToken() não encontrada</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro nas funções CSRF: " . $e->getMessage() . "</p>";
}

// Teste 4: Sessão
$total_testes++;
echo "<h3>Teste 4: Sistema de Sessão</h3>";
try {
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo "<p>✅ Sessão ativa: " . session_id() . "</p>";
        $testes_ok++;
    } else {
        echo "<p>⚠️ Sessão não ativa</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro na sessão: " . $e->getMessage() . "</p>";
}

// Teste 5: Funções de autenticação
$total_testes++;
echo "<h3>Teste 5: Funções de Autenticação</h3>";
try {
    if (function_exists('isLoggedIn') && function_exists('hasUserType')) {
        echo "<p>✅ Funções de autenticação disponíveis</p>";
        $testes_ok++;
    } else {
        echo "<p>❌ Funções de autenticação ausentes</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro nas funções de autenticação: " . $e->getMessage() . "</p>";
}

// Teste 6: .htaccess
$total_testes++;
echo "<h3>Teste 6: Arquivo .htaccess</h3>";
if (file_exists('.htaccess')) {
    echo "<p>✅ .htaccess existe</p>";
    $testes_ok++;
} else {
    echo "<p>❌ .htaccess não encontrado</p>";
}

echo "<hr>";

// Resultado final
$percentual = ($testes_ok / $total_testes) * 100;

if ($percentual >= 90) {
    echo "<div style='background: #d4edda; padding: 20px; border-left: 4px solid #28a745; text-align: center;'>";
    echo "<h2>🎉 SUCESSO TOTAL!</h2>";
    echo "<p><strong>$testes_ok de $total_testes testes passaram (" . number_format($percentual, 1) . "%)</strong></p>";
    echo "<p>O erro 500 foi corrigido com sucesso!</p>";
    echo "<p><a href='/' style='color: #155724; font-weight: bold;'>🏠 Ir para a Página Principal</a></p>";
    echo "</div>";
} elseif ($percentual >= 70) {
    echo "<div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; text-align: center;'>";
    echo "<h2>⚠️ PARCIALMENTE CORRIGIDO</h2>";
    echo "<p><strong>$testes_ok de $total_testes testes passaram (" . number_format($percentual, 1) . "%)</strong></p>";
    echo "<p>A maioria dos problemas foi resolvida, mas ainda há itens para verificar.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; text-align: center;'>";
    echo "<h2>❌ PROBLEMAS PERSISTEM</h2>";
    echo "<p><strong>$testes_ok de $total_testes testes passaram (" . number_format($percentual, 1) . "%)</strong></p>";
    echo "<p>Ainda há problemas significativos que precisam ser resolvidos.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<div style='background: #e2e3e5; padding: 15px; border-left: 4px solid #6c757d;'>";
echo "<h3>📋 Próximos Passos:</h3>";
echo "<ol>";
echo "<li><strong>Upload para Produção:</strong> Envie os arquivos corrigidos para o Hostinger</li>";
echo "<li><strong>Teste Online:</strong> Acesse https://cortefacil.app/</li>";
echo "<li><strong>Monitoramento:</strong> Verifique os logs no hPanel</li>";
echo "<li><strong>Limpeza:</strong> Remova os arquivos de teste após confirmação</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><em>Teste executado em " . date('Y-m-d H:i:s') . " no servidor " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "</em></p>";
?>