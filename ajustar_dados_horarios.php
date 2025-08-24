<?php
/**
 * Script para ajustar os dados da tabela horarios com IDs corretos
 */

// Configuração do banco online
$host = 'srv486.hstgr.io';
$dbname = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'Maycon341753';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conectado ao banco online\n";
    
    // Verificar dados atuais na tabela horarios
    echo "\n=== DADOS ATUAIS NA TABELA HORARIOS ===\n";
    $stmt = $pdo->query("SELECT id, hora_inicio, hora_fim, profissional_id, salao_id FROM horarios LIMIT 5");
    $horarios_atuais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($horarios_atuais) > 0) {
        foreach ($horarios_atuais as $horario) {
            echo "- ID: {$horario['id']}, {$horario['hora_inicio']}-{$horario['hora_fim']}, Prof: {$horario['profissional_id']}, Salão: {$horario['salao_id']}\n";
        }
    } else {
        echo "✗ Nenhum horário encontrado\n";
    }
    
    // Verificar salões disponíveis
    echo "\n=== SALÕES DISPONÍVEIS ===\n";
    $stmt = $pdo->query("SELECT id, nome FROM saloes WHERE ativo = 1 LIMIT 3");
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($saloes) > 0) {
        foreach ($saloes as $salao) {
            echo "- Salão ID: {$salao['id']}, Nome: {$salao['nome']}\n";
        }
        $primeiro_salao_id = $saloes[0]['id'];
    } else {
        echo "✗ Nenhum salão ativo encontrado\n";
        $primeiro_salao_id = 1; // usar padrão
    }
    
    // Verificar profissionais disponíveis
    echo "\n=== PROFISSIONAIS DISPONÍVEIS ===\n";
    $stmt = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'profissional' AND ativo = 1 LIMIT 3");
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($profissionais) > 0) {
        foreach ($profissionais as $prof) {
            echo "- Profissional ID: {$prof['id']}, Nome: {$prof['nome']}\n";
        }
        $primeiro_prof_id = $profissionais[0]['id'];
    } else {
        echo "✗ Nenhum profissional ativo encontrado\n";
        $primeiro_prof_id = 1; // usar padrão
    }
    
    // Atualizar horários existentes ou criar novos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM horarios");
    $total_horarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total_horarios > 0) {
        echo "\n=== ATUALIZANDO HORÁRIOS EXISTENTES ===\n";
        // Atualizar horários existentes com IDs corretos
        $stmt = $pdo->prepare("UPDATE horarios SET profissional_id = ?, salao_id = ? WHERE ativo = 1");
        $stmt->execute([$primeiro_prof_id, $primeiro_salao_id]);
        echo "✓ Horários atualizados com Profissional ID: $primeiro_prof_id, Salão ID: $primeiro_salao_id\n";
    } else {
        echo "\n=== CRIANDO HORÁRIOS PADRÃO ===\n";
        // Criar horários padrão
        $horarios_padrao = [
            ['08:00:00', '08:30:00'], ['08:30:00', '09:00:00'], ['09:00:00', '09:30:00'],
            ['09:30:00', '10:00:00'], ['10:00:00', '10:30:00'], ['10:30:00', '11:00:00'],
            ['11:00:00', '11:30:00'], ['11:30:00', '12:00:00'], ['13:00:00', '13:30:00'],
            ['13:30:00', '14:00:00'], ['14:00:00', '14:30:00'], ['14:30:00', '15:00:00'],
            ['15:00:00', '15:30:00'], ['15:30:00', '16:00:00'], ['16:00:00', '16:30:00'],
            ['16:30:00', '17:00:00'], ['17:00:00', '17:30:00'], ['17:30:00', '18:00:00']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO horarios (hora_inicio, hora_fim, profissional_id, salao_id, ativo) VALUES (?, ?, ?, ?, 1)");
        foreach ($horarios_padrao as $horario) {
            $stmt->execute([$horario[0], $horario[1], $primeiro_prof_id, $primeiro_salao_id]);
        }
        echo "✓ " . count($horarios_padrao) . " horários padrão criados\n";
    }
    
    // Testar consulta final
    echo "\n=== TESTE FINAL DA CONSULTA ===\n";
    $sql = "SELECT hora_inicio, hora_fim FROM horarios 
            WHERE salao_id = :salao_id 
            AND profissional_id = :profissional_id 
            AND ativo = 1 
            ORDER BY hora_inicio";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':salao_id', $primeiro_salao_id, PDO::PARAM_INT);
    $stmt->bindParam(':profissional_id', $primeiro_prof_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $horarios_finais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Horários encontrados para Salão $primeiro_salao_id e Profissional $primeiro_prof_id: " . count($horarios_finais) . "\n";
    
    if (count($horarios_finais) > 0) {
        echo "\nPrimeiros 5 horários disponíveis:\n";
        for ($i = 0; $i < min(5, count($horarios_finais)); $i++) {
            echo "- {$horarios_finais[$i]['hora_inicio']} - {$horarios_finais[$i]['hora_fim']}\n";
        }
    }
    
    echo "\n✓ Ajuste dos dados concluído com sucesso!\n";
    echo "\n=== INFORMAÇÕES PARA TESTE ===\n";
    echo "- Use Salão ID: $primeiro_salao_id\n";
    echo "- Use Profissional ID: $primeiro_prof_id\n";
    echo "- Total de horários disponíveis: " . count($horarios_finais) . "\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>