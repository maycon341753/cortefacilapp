<?php
/**
 * Script para verificar estrutura das tabelas no banco online
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
    
    // Verificar estrutura da tabela usuarios
    echo "\n=== ESTRUTURA DA TABELA USUARIOS ===\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    // Verificar alguns registros da tabela usuarios
    echo "\n=== PRIMEIROS USUARIOS ===\n";
    $stmt = $pdo->query("SELECT id, nome, email FROM usuarios LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- ID: {$row['id']}, Nome: {$row['nome']}, Email: {$row['email']}\n";
    }
    
    // Já sabemos que os horários estão usando profissional_id = 20 e salao_id = 10
    // Vamos confirmar e criar um teste simples da API
    echo "\n=== TESTE SIMPLES DA API ===\n";
    
    $salao_id = 10;
    $profissional_id = 20;
    $data = '2025-01-23';
    
    echo "Testando com: Salão $salao_id, Profissional $profissional_id, Data $data\n";
    
    $stmt = $pdo->prepare("
        SELECT hora_inicio, hora_fim 
        FROM horarios 
        WHERE salao_id = ? AND profissional_id = ? AND ativo = 1 
        ORDER BY hora_inicio
    ");
    $stmt->execute([$salao_id, $profissional_id]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✓ Horários encontrados: " . count($horarios) . "\n";
    
    if (count($horarios) > 0) {
        echo "\nPrimeiros 5 horários:\n";
        for ($i = 0; $i < min(5, count($horarios)); $i++) {
            $inicio = $horarios[$i]['hora_inicio'];
            $fim = $horarios[$i]['hora_fim'];
            echo "- $inicio - $fim\n";
        }
        
        // Simular resposta da API
        $horarios_formatados = [];
        foreach ($horarios as $horario) {
            $horarios_formatados[] = [
                'hora_inicio' => $horario['hora_inicio'],
                'hora_fim' => $horario['hora_fim'],
                'display' => substr($horario['hora_inicio'], 0, 5) . ' - ' . substr($horario['hora_fim'], 0, 5)
            ];
        }
        
        $resposta_api = [
            'success' => true,
            'horarios' => $horarios_formatados,
            'total' => count($horarios_formatados),
            'data' => $data,
            'profissional_id' => $profissional_id,
            'salao_id' => $salao_id
        ];
        
        echo "\n=== RESPOSTA DA API (JSON) ===\n";
        echo json_encode($resposta_api, JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "✗ Nenhum horário encontrado para essa combinação\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>