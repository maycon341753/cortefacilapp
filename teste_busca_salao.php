<?php
/**
 * Teste simples para verificar a busca de salão
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/salao.php';

echo "<h2>Teste de Busca de Salão</h2>";

try {
    $salao = new Salao();
    
    // Buscar o último salão cadastrado
    echo "<h3>1. Buscando último salão cadastrado...</h3>";
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id FROM saloes ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $ultimo_salao = $stmt->fetch();
    
    if ($ultimo_salao) {
        $salao_id = $ultimo_salao['id'];
        echo "<p>Último salão ID: {$salao_id}</p>";
        
        // Tentar buscar usando o método da classe
        echo "<h3>2. Buscando usando método da classe...</h3>";
        $salao_encontrado = $salao->buscarPorId($salao_id);
        
        if ($salao_encontrado) {
            echo "<p style='color: green;'>✅ Salão encontrado!</p>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            foreach ($salao_encontrado as $campo => $valor) {
                echo "<tr><td>{$campo}</td><td>" . htmlspecialchars($valor ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao buscar salão usando método da classe</p>";
            
            // Tentar busca direta no banco
            echo "<h3>3. Tentando busca direta no banco...</h3>";
            $stmt = $conn->prepare("SELECT * FROM saloes WHERE id = :id");
            $stmt->bindParam(':id', $salao_id);
            $stmt->execute();
            $salao_direto = $stmt->fetch();
            
            if ($salao_direto) {
                echo "<p style='color: green;'>✅ Salão encontrado com busca direta!</p>";
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr><th>Campo</th><th>Valor</th></tr>";
                foreach ($salao_direto as $campo => $valor) {
                    echo "<tr><td>{$campo}</td><td>" . htmlspecialchars($valor ?? 'NULL') . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'>❌ Erro também na busca direta</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>❌ Nenhum salão encontrado na tabela</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Erro:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>