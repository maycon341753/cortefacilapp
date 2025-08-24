<?php
/**
 * Script para corrigir problema da coluna salao_id na tabela horarios
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
    
    // Verificar estrutura atual da tabela horarios
    echo "\n=== ESTRUTURA ATUAL DA TABELA HORARIOS ===\n";
    $stmt = $pdo->query("DESCRIBE horarios");
    $colunas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colunas[] = $row['Field'];
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    // Verificar se a coluna salao_id existe
    if (!in_array('salao_id', $colunas)) {
        echo "\n✗ Coluna 'salao_id' não encontrada. Adicionando...\n";
        $pdo->exec("ALTER TABLE horarios ADD COLUMN salao_id INT NOT NULL DEFAULT 1");
        echo "✓ Coluna 'salao_id' adicionada\n";
    } else {
        echo "\n✓ Coluna 'salao_id' já existe\n";
    }
    
    // Verificar se a coluna profissional_id existe
    if (!in_array('profissional_id', $colunas)) {
        echo "\n✗ Coluna 'profissional_id' não encontrada. Adicionando...\n";
        $pdo->exec("ALTER TABLE horarios ADD COLUMN profissional_id INT NOT NULL DEFAULT 1");
        echo "✓ Coluna 'profissional_id' adicionada\n";
    } else {
        echo "\n✓ Coluna 'profissional_id' já existe\n";
    }
    
    // Mostrar estrutura final
    echo "\n=== ESTRUTURA FINAL DA TABELA HORARIOS ===\n";
    $stmt = $pdo->query("DESCRIBE horarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    // Testar consulta da API
    echo "\n=== TESTE DA CONSULTA DA API ===\n";
    $salao_id = 1;
    $profissional_id = 1;
    $data = '2025-01-23';
    
    $sql = "SELECT hora_inicio, hora_fim FROM horarios 
            WHERE salao_id = :salao_id 
            AND profissional_id = :profissional_id 
            AND ativo = 1 
            ORDER BY hora_inicio";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':salao_id', $salao_id, PDO::PARAM_INT);
    $stmt->bindParam(':profissional_id', $profissional_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Consulta executada com sucesso\n";
    echo "✓ Horários encontrados: " . count($horarios) . "\n";
    
    if (count($horarios) > 0) {
        echo "\nPrimeiros 3 horários:\n";
        for ($i = 0; $i < min(3, count($horarios)); $i++) {
            echo "- {$horarios[$i]['hora_inicio']} - {$horarios[$i]['hora_fim']}\n";
        }
    }
    
    echo "\n✓ Correção da coluna salao_id concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>