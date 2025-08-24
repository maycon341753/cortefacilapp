<?php
/**
 * Teste simples para verificar ambiente online
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste Online - CorteFácil</h2>";
echo "<p>Servidor: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "</p>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // Testar conexão com banco
    require_once 'config/database.php';
    
    $conn = getConnection();
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão com banco OK</p>";
        
        // Verificar tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p><strong>Tabelas encontradas:</strong> " . implode(', ', $tabelas) . "</p>";
        
        // Verificar se tabelas necessárias existem
        $necessarias = ['usuarios', 'saloes', 'profissionais'];
        $faltantes = array_diff($necessarias, $tabelas);
        
        if (empty($faltantes)) {
            echo "<p style='color: green;'>✅ Todas as tabelas necessárias existem</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabelas faltantes: " . implode(', ', $faltantes) . "</p>";
        }
        
        // Contar registros
        foreach (['usuarios', 'saloes', 'profissionais'] as $tabela) {
            if (in_array($tabela, $tabelas)) {
                $stmt = $conn->query("SELECT COUNT(*) FROM $tabela");
                $count = $stmt->fetchColumn();
                echo "<p>$tabela: $count registros</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='parceiro/profissionais.php'>Profissionais</a> | <a href='parceiro/salao.php'>Salão</a></p>";
?>