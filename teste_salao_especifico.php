<?php
/**
 * Teste específico para inserção na tabela saloes
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

echo "<h2>Teste Específico - Estrutura Salões</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão OK</p>";
        
        // Verificar estrutura da tabela saloes
        echo "<h3>Estrutura da tabela saloes:</h3>";
        $stmt = $conn->prepare("DESCRIBE saloes");
        $stmt->execute();
        $estrutura = $stmt->fetchAll();
        echo "<pre>" . print_r($estrutura, true) . "</pre>";
        
        // Verificar constraints
        echo "<h3>Constraints da tabela saloes:</h3>";
        $stmt = $conn->prepare("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'saloes' AND TABLE_SCHEMA = DATABASE()");
        $stmt->execute();
        $constraints = $stmt->fetchAll();
        echo "<pre>" . print_r($constraints, true) . "</pre>";
        
        // Verificar usuários existentes
        echo "<h3>Usuários existentes (últimos 5):</h3>";
        $stmt = $conn->prepare("SELECT id, nome, email FROM usuarios ORDER BY id DESC LIMIT 5");
        $stmt->execute();
        $usuarios = $stmt->fetchAll();
        echo "<pre>" . print_r($usuarios, true) . "</pre>";
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar</a></p>";
?>