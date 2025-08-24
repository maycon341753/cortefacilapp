<?php
/**
 * Debug - Verificar dados de salões e profissionais
 */

require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Salões cadastrados:</h2>";
    $stmt = $conn->query("SELECT s.id, s.nome as salao_nome, s.dono_id, u.nome as dono_nome FROM saloes s LEFT JOIN usuarios u ON s.dono_id = u.id ORDER BY s.id");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Salão ID: {$row['id']} - Nome: {$row['salao_nome']} - Dono: {$row['dono_nome']}<br>";
    }
    
    echo "<h2>Profissionais cadastrados:</h2>";
    $stmt = $conn->query("SELECT p.id, p.nome, p.especialidade, p.id_salao, s.nome as salao_nome, p.status FROM profissionais p LEFT JOIN saloes s ON p.id_salao = s.id ORDER BY p.id_salao, p.id");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Prof ID: {$row['id']} - Nome: {$row['nome']} - Especialidade: {$row['especialidade']} - Salão: {$row['salao_nome']} (ID: {$row['id_salao']}) - Status: {$row['status']}<br>";
    }
    
    echo "<h2>Verificar duplicações:</h2>";
    $stmt = $conn->query("SELECT nome, especialidade, id_salao, COUNT(*) as total FROM profissionais GROUP BY nome, especialidade, id_salao HAVING COUNT(*) > 1");
    $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($duplicados)) {
        echo "Nenhuma duplicação encontrada.<br>";
    } else {
        foreach($duplicados as $dup) {
            echo "DUPLICADO: {$dup['nome']} - {$dup['especialidade']} - Salão ID: {$dup['id_salao']} - Total: {$dup['total']}<br>";
        }
    }
    
} catch(Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>