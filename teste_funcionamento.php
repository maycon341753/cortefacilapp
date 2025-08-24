<?php
// Teste básico de funcionamento
echo "<h1>✅ Servidor funcionando!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Data/Hora: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>Servidor: " . ($_SERVER["SERVER_SOFTWARE"] ?? "N/A") . "</p>";

// Teste de sessão
session_start();
echo "<p>Sessão: " . session_id() . "</p>";

// Teste de includes
if (file_exists("includes/auth.php")) {
    require_once "includes/auth.php";
    echo "<p>✅ auth.php carregado com sucesso</p>";
    
    if (function_exists("generateCSRFToken")) {
        $token = generateCSRFToken();
        echo "<p>✅ Token CSRF gerado: " . substr($token, 0, 10) . "...</p>";
    }
} else {
    echo "<p>❌ auth.php não encontrado</p>";
}

// Teste de banco
if (file_exists("config/database.php")) {
    echo "<p>✅ database.php encontrado</p>";
    try {
        require_once "config/database.php";
        $db = new Database();
        $conn = $db->getConnection();
        echo "<p>✅ Conexão com banco bem-sucedida</p>";
    } catch (Exception $e) {
        echo "<p>❌ Erro de conexão: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ database.php não encontrado</p>";
}
?>