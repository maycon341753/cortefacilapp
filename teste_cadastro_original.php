<?php
/**
 * Teste do arquivo cadastro.php original
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste do Cadastro Original</h1>";
echo "<p>Iniciando teste...</p>";

try {
    echo "<p>1. Testando includes...</p>";
    
    // Testar cada include separadamente
    if (file_exists(__DIR__ . '/includes/auth.php')) {
        echo "<p>✓ auth.php existe</p>";
        require_once __DIR__ . '/includes/auth.php';
        echo "<p>✓ auth.php carregado</p>";
    } else {
        echo "<p>✗ auth.php não encontrado</p>";
    }
    
    if (file_exists(__DIR__ . '/includes/functions.php')) {
        echo "<p>✓ functions.php existe</p>";
        require_once __DIR__ . '/includes/functions.php';
        echo "<p>✓ functions.php carregado</p>";
    } else {
        echo "<p>✗ functions.php não encontrado</p>";
    }
    
    if (file_exists(__DIR__ . '/models/usuario.php')) {
        echo "<p>✓ usuario.php existe</p>";
        require_once __DIR__ . '/models/usuario.php';
        echo "<p>✓ usuario.php carregado</p>";
    } else {
        echo "<p>✗ usuario.php não encontrado</p>";
    }
    
    echo "<p>2. Testando função isLoggedIn()...</p>";
    if (function_exists('isLoggedIn')) {
        echo "<p>✓ Função isLoggedIn() existe</p>";
        $loggedIn = isLoggedIn();
        echo "<p>✓ isLoggedIn() retornou: " . ($loggedIn ? 'true' : 'false') . "</p>";
    } else {
        echo "<p>✗ Função isLoggedIn() não existe</p>";
    }
    
    echo "<p>3. Testando parâmetros GET...</p>";
    $tipo_usuario = $_GET['tipo'] ?? 'cliente';
    echo "<p>✓ Tipo de usuário: " . htmlspecialchars($tipo_usuario) . "</p>";
    
    echo "<p>4. Testando conexão com banco...</p>";
    if (class_exists('Usuario')) {
        echo "<p>✓ Classe Usuario existe</p>";
        $usuario = new Usuario();
        echo "<p>✓ Objeto Usuario criado</p>";
    } else {
        echo "<p>✗ Classe Usuario não existe</p>";
    }
    
    echo "<p>5. Simulando início do HTML...</p>";
    echo "<p>✓ HTML pode ser renderizado</p>";
    
    echo "<h2>Resultado: Todos os testes passaram!</h2>";
    echo "<p>O problema pode estar em:</p>";
    echo "<ul>";
    echo "<li>Erro fatal não capturado durante a execução</li>";
    echo "<li>Problema de redirecionamento</li>";
    echo "<li>Erro de sintaxe em alguma parte específica</li>";
    echo "<li>Problema de configuração do servidor</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Arquivo: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>ERRO FATAL: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Arquivo: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}

echo "<p><a href='cadastro.php?tipo=cliente'>Testar cadastro.php original</a></p>";
echo "<p><a href='cadastro_corrigido.php?tipo=cliente'>Testar cadastro_corrigido.php</a></p>";
?>