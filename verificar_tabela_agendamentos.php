<?php
/**
 * Script para verificar a estrutura da tabela agendamentos
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
    
    // Verificar se a tabela agendamentos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabela 'agendamentos' existe\n";
        
        // Verificar estrutura da tabela agendamentos
        echo "\n=== ESTRUTURA DA TABELA AGENDAMENTOS ===\n";
        $stmt = $pdo->query("DESCRIBE agendamentos");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
        
        // Verificar alguns registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "\n✓ Total de agendamentos: $total\n";
        
        if ($total > 0) {
            echo "\nPrimeiros 3 agendamentos:\n";
            $stmt = $pdo->query("SELECT * FROM agendamentos LIMIT 3");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "- ID: {$row['id']}";
                foreach ($row as $campo => $valor) {
                    if ($campo != 'id') {
                        echo ", $campo: $valor";
                    }
                }
                echo "\n";
            }
        }
    } else {
        echo "✗ Tabela 'agendamentos' NÃO existe\n";
        
        // Criar tabela agendamentos
        echo "\nCriando tabela 'agendamentos'...\n";
        $sql = "
        CREATE TABLE agendamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            profissional_id INT NOT NULL,
            salao_id INT NOT NULL,
            data_agendamento DATE NOT NULL,
            horario TIME NOT NULL,
            servico VARCHAR(255),
            status ENUM('agendado', 'confirmado', 'cancelado', 'concluido') DEFAULT 'agendado',
            observacoes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✓ Tabela 'agendamentos' criada com sucesso\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
}
?>