<?php
require_once 'config/database.php';

try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    echo "<h2>Debug - Dias da Semana</h2>";
    echo "<style>body{font-family:Arial;padding:20px;}</style>";
    
    // Verificar dias cadastrados
    $stmt = $conn->query('SELECT DISTINCT dia_semana FROM horarios_funcionamento ORDER BY dia_semana');
    $dias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Dias cadastrados na tabela:</strong> " . implode(', ', $dias) . "</p>";
    
    // Testar conversão de datas
    $datas_teste = ['2025-01-27', '2025-01-28', '2025-01-29'];
    
    echo "<h3>Teste de Conversão de Datas:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Data</th><th>Dia da Semana (PHP)</th><th>Convertido para DB</th><th>Nome do Dia</th></tr>";
    
    foreach ($datas_teste as $data) {
        $dia_semana = date('w', strtotime($data)); // 0=domingo, 1=segunda, etc.
        $dia_semana_db = $dia_semana == 0 ? 7 : $dia_semana;
        $nome_dia = date('l', strtotime($data));
        
        echo "<tr>";
        echo "<td>{$data}</td>";
        echo "<td>{$dia_semana}</td>";
        echo "<td>{$dia_semana_db}</td>";
        echo "<td>{$nome_dia}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar se existe horário para segunda-feira (dia 1)
    $stmt = $conn->prepare('SELECT * FROM horarios_funcionamento WHERE dia_semana = ? AND id_salao = ?');
    $stmt->execute([1, 10]);
    $horario_segunda = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Horário para Segunda-feira (dia 1) do Salão 10:</h3>";
    if ($horario_segunda) {
        echo "<p style='color: green;'>✓ Encontrado: {$horario_segunda['hora_abertura']} - {$horario_segunda['hora_fechamento']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>