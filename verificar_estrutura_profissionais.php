<?php
/**
 * Script para verificar a estrutura da tabela profissionais
 */

require_once 'config/database.php';

echo "<h2>Estrutura da Tabela Profissionais</h2>";

try {
    $conn = getConnection();
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Verificar estrutura da tabela profissionais
    $stmt = $conn->query("DESCRIBE profissionais");
    $campos = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    $tem_status = false;
    
    foreach ($campos as $campo) {
        $destaque = '';
        if ($campo['Field'] === 'status') {
            $tem_status = true;
            $destaque = 'background: #e8f5e8;';
        }
        
        echo "<tr style='{$destaque}'>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Key']}</td>";
        echo "<td>" . ($campo['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar se tem campo status
    if ($tem_status) {
        echo "<p style='color: green;'>✅ Campo 'status' já existe na tabela!</p>";
        
        // Verificar dados existentes
        $stmt = $conn->query("SELECT id, nome, status FROM profissionais LIMIT 5");
        $profissionais = $stmt->fetchAll();
        
        if ($profissionais) {
            echo "<h3>Dados de exemplo:</h3>";
            echo "<ul>";
            foreach ($profissionais as $prof) {
                echo "<li>ID: {$prof['id']} - Nome: {$prof['nome']} - Status: {$prof['status']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ Campo 'status' NÃO existe na tabela!</p>";
        echo "<p>Será necessário adicionar o campo status.</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>