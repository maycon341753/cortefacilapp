<?php
/**
 * Script para verificar salões no banco de dados
 */

require_once 'config/database.php';

echo "<h2>Verificação de Salões</h2>";

try {
    $db = Database::getInstance();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco de dados.');
    }
    
    echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela saloes existe
    $stmt = $conn->query("SHOW TABLES LIKE 'saloes'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'saloes' não existe!</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✓ Tabela 'saloes' existe</p>";
    
    // Buscar todos os salões
    $stmt = $conn->query('SELECT id, nome, ativo FROM saloes ORDER BY id');
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Salões encontrados: " . count($saloes) . "</h3>";
    
    if (count($saloes) == 0) {
        echo "<p style='color: red;'>❌ Nenhum salão encontrado no banco de dados!</p>";
        echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Status</th></tr>";
        
        foreach($saloes as $salao) {
            $status = $salao['ativo'] ? 'ATIVO' : 'INATIVO';
            $cor = $salao['ativo'] ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$salao['id']}</td>";
            echo "<td>{$salao['nome']}</td>";
            echo "<td style='color: {$cor}; font-weight: bold;'>{$status}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Contar salões ativos
        $saloes_ativos = array_filter($saloes, function($s) { return $s['ativo']; });
        echo "<p><strong>Salões ativos: " . count($saloes_ativos) . "</strong></p>";
        
        if (count($saloes_ativos) == 0) {
            echo "<p style='color: red;'>❌ Nenhum salão está ativo!</p>";
            echo "<p>Isso explica por que não aparecem horários na página de agendamento.</p>";
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>