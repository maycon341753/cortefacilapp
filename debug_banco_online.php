<?php
/**
 * Debug detalhado para verificar conexão com banco online
 */

require_once __DIR__ . '/config/database.php';

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

echo "<h2>Debug Banco Online</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão estabelecida!</p>";
        
        // Verificar tabelas
        echo "<h3>Verificando Tabelas:</h3>";
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            echo "<p>📋 Tabela: $table</p>";
        }
        
        // Verificar estrutura da tabela usuarios
        if (in_array('usuarios', $tables)) {
            echo "<h3>Estrutura da tabela 'usuarios':</h3>";
            $stmt = $conn->query("DESCRIBE usuarios");
            $columns = $stmt->fetchAll();
            echo "<pre>";
            print_r($columns);
            echo "</pre>";
        }
        
        // Verificar estrutura da tabela saloes
        if (in_array('saloes', $tables)) {
            echo "<h3>Estrutura da tabela 'saloes':</h3>";
            $stmt = $conn->query("DESCRIBE saloes");
            $columns = $stmt->fetchAll();
            echo "<pre>";
            print_r($columns);
            echo "</pre>";
        }
        
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