<?php
/**
 * Teste de conexão com o banco de dados da Hostinger
 */

// Configurações do banco de dados
$servername = "srv488.hstgr.io";
$database = "u690889028_cortefacil";
$username = "u690889028";
$password = "Brava1997?";

try {
    // Criar conexão PDO
    $conn = new PDO(
        "mysql:host=" . $servername . ";dbname=" . $database . ";charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );
    
    echo "<h2>Conexão com banco de dados: SUCESSO!</h2>";
    echo "<p>Conectado ao banco: " . $database . "</p>";
    echo "<p>Servidor: " . $servername . "</p>";
    
    // Testar uma consulta simples
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "<p>Total de usuários na tabela: " . $result['total'] . "</p>";
    
} catch(PDOException $e) {
    echo "<h2>Erro de conexão: " . $e->getMessage() . "</h2>";
    echo "<p>Código do erro: " . $e->getCode() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}

$conn = null;
?>