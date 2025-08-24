<?php
require_once 'config/database.php';

try {
    $database = Database::getInstance();
    $conn = $database->connect();
    
    echo "<h2>Corrigir Horários de Funcionamento</h2>";
    echo "<style>body{font-family:Arial;padding:20px;}</style>";
    
    // Verificar salões existentes
    $stmt = $conn->query('SELECT id, nome FROM saloes ORDER BY id');
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Salões Existentes:</h3>";
    foreach ($saloes as $salao) {
        echo "<p>ID: {$salao['id']} - Nome: {$salao['nome']}</p>";
    }
    
    // Verificar horários existentes
    $stmt = $conn->query('SELECT DISTINCT id_salao FROM horarios_funcionamento');
    $saloes_com_horarios = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Salões com Horários:</h3>";
    echo "<p>" . implode(', ', $saloes_com_horarios) . "</p>";
    
    // Verificar se o salão 10 existe
    $stmt = $conn->prepare('SELECT * FROM saloes WHERE id = ?');
    $stmt->execute([10]);
    $salao_10 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao_10) {
        echo "<p style='color: red;'>❌ Salão ID 10 não existe!</p>";
        
        // Pegar o primeiro salão disponível
        if (!empty($saloes)) {
            $primeiro_salao = $saloes[0];
            echo "<p style='color: blue;'>ℹ️ Usando salão ID {$primeiro_salao['id']} - {$primeiro_salao['nome']}</p>";
            
            // Verificar se este salão tem horários
            $stmt = $conn->prepare('SELECT * FROM horarios_funcionamento WHERE id_salao = ?');
            $stmt->execute([$primeiro_salao['id']]);
            $horarios_salao = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($horarios_salao)) {
                echo "<p style='color: orange;'>⚠️ Salão {$primeiro_salao['id']} não tem horários. Criando...</p>";
                
                // Criar horários padrão para este salão
                $horarios_padrao = [
                    [1, '08:00:00', '18:00:00'], // Segunda
                    [2, '08:00:00', '18:00:00'], // Terça
                    [3, '08:00:00', '18:00:00'], // Quarta
                    [4, '08:00:00', '18:00:00'], // Quinta
                    [5, '08:00:00', '18:00:00'], // Sexta
                    [6, '08:00:00', '16:00:00'], // Sábado
                ];
                
                $stmt_insert = $conn->prepare('INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (?, ?, ?, ?)');
                
                foreach ($horarios_padrao as $horario) {
                    $stmt_insert->execute([$primeiro_salao['id'], $horario[0], $horario[1], $horario[2]]);
                }
                
                echo "<p style='color: green;'>✅ Horários criados para salão {$primeiro_salao['id']}</p>";
            } else {
                echo "<p style='color: green;'>✅ Salão {$primeiro_salao['id']} já tem horários</p>";
            }
            
            // Testar API com este salão
            echo "<h3>Teste da API:</h3>";
            echo "<p><a href='api/horarios.php?profissional_id=20&salao_id={$primeiro_salao['id']}&data=2025-01-27' target='_blank'>Testar API com Salão {$primeiro_salao['id']}</a></p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Salão ID 10 existe: {$salao_10['nome']}</p>";
        
        // Verificar se tem horários
        $stmt = $conn->prepare('SELECT * FROM horarios_funcionamento WHERE id_salao = ?');
        $stmt->execute([10]);
        $horarios_salao_10 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($horarios_salao_10)) {
            echo "<p style='color: orange;'>⚠️ Salão 10 não tem horários. Criando...</p>";
            
            // Criar horários padrão para o salão 10
            $horarios_padrao = [
                [1, '08:00:00', '18:00:00'], // Segunda
                [2, '08:00:00', '18:00:00'], // Terça
                [3, '08:00:00', '18:00:00'], // Quarta
                [4, '08:00:00', '18:00:00'], // Quinta
                [5, '08:00:00', '18:00:00'], // Sexta
                [6, '08:00:00', '16:00:00'], // Sábado
            ];
            
            $stmt_insert = $conn->prepare('INSERT INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES (?, ?, ?, ?)');
            
            foreach ($horarios_padrao as $horario) {
                $stmt_insert->execute([10, $horario[0], $horario[1], $horario[2]]);
            }
            
            echo "<p style='color: green;'>✅ Horários criados para salão 10</p>";
        } else {
            echo "<p style='color: green;'>✅ Salão 10 já tem horários</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>