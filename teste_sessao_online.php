<?php
/**
 * Teste de Sessão Online - Diagnóstico
 * Verifica se as sessões estão funcionando corretamente no ambiente online
 */

// Configurações de erro
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste de Sessão Online - CorteFácil</h2>";
echo "<hr>";

// 1. Verificar configurações de sessão
echo "<h3>1. Configurações de Sessão</h3>";
echo "Session Status: " . session_status() . "<br>";
echo "Session ID (antes): " . session_id() . "<br>";
echo "Session Name: " . session_name() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";
echo "Session Cookie Params: ";
print_r(session_get_cookie_params());
echo "<br><br>";

// 2. Iniciar sessão
echo "<h3>2. Iniciando Sessão</h3>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "✅ Sessão iniciada com sucesso<br>";
    } else {
        echo "ℹ️ Sessão já estava ativa<br>";
    }
    echo "Session ID (depois): " . session_id() . "<br>";
} catch (Exception $e) {
    echo "❌ Erro ao iniciar sessão: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 3. Testar escrita e leitura de sessão
echo "<h3>3. Teste de Escrita/Leitura</h3>";
$_SESSION['teste_timestamp'] = time();
$_SESSION['teste_string'] = 'CorteFácil Online Test';
$_SESSION['teste_array'] = ['key1' => 'value1', 'key2' => 'value2'];

echo "Dados escritos na sessão:<br>";
echo "- teste_timestamp: " . $_SESSION['teste_timestamp'] . "<br>";
echo "- teste_string: " . $_SESSION['teste_string'] . "<br>";
echo "- teste_array: ";
print_r($_SESSION['teste_array']);
echo "<br><br>";

// 4. Verificar dados de usuário existentes
echo "<h3>4. Dados de Usuário na Sessão</h3>";
$user_keys = ['usuario_id', 'usuario_nome', 'usuario_email', 'tipo_usuario', 'usuario_telefone'];
foreach ($user_keys as $key) {
    if (isset($_SESSION[$key])) {
        echo "✅ {$key}: " . $_SESSION[$key] . "<br>";
    } else {
        echo "❌ {$key}: não definido<br>";
    }
}
echo "<br>";

// 5. Simular login de parceiro para teste
echo "<h3>5. Simulando Login de Parceiro</h3>";
$_SESSION['usuario_id'] = 999;
$_SESSION['usuario_nome'] = 'Teste Parceiro Online';
$_SESSION['usuario_email'] = 'teste@cortefacil.app';
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_telefone'] = '11999999999';

echo "✅ Dados de parceiro simulados na sessão<br><br>";

// 6. Testar funções de autenticação
echo "<h3>6. Teste de Funções de Autenticação</h3>";
try {
    require_once __DIR__ . '/includes/auth.php';
    
    echo "isLoggedIn(): " . (isLoggedIn() ? '✅ SIM' : '❌ NÃO') . "<br>";
    echo "isParceiro(): " . (isParceiro() ? '✅ SIM' : '❌ NÃO') . "<br>";
    echo "isCliente(): " . (isCliente() ? '✅ SIM' : '❌ NÃO') . "<br>";
    echo "isAdmin(): " . (isAdmin() ? '✅ SIM' : '❌ NÃO') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar auth.php: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 7. Verificar todas as variáveis de sessão
echo "<h3>7. Todas as Variáveis de Sessão</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre><br>";

// 8. Informações do servidor
echo "<h3>8. Informações do Servidor</h3>";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'não definido') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'não definido') . "<br>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não definido') . "<br>";
echo "PHP_VERSION: " . PHP_VERSION . "<br>";
echo "<br>";

// 9. Teste de redirecionamento (comentado para não interferir)
echo "<h3>9. Teste de Redirecionamento</h3>";
echo "<p>Para testar o redirecionamento, descomente as linhas abaixo:</p>";
echo "<code>";
echo "// try {<br>";
echo "//     requireParceiro();<br>";
echo "//     echo '✅ requireParceiro() passou - usuário autenticado como parceiro';<br>";
echo "// } catch (Exception \$e) {<br>";
echo "//     echo '❌ requireParceiro() falhou: ' . \$e->getMessage();<br>";
echo "// }";
echo "</code><br><br>";

echo "<h3>✅ Teste Concluído</h3>";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
?>