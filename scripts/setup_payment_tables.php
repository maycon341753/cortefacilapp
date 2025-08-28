<?php
try {
    // Conectar ao banco de dados
    $pdo = new PDO("mysql:host=localhost;dbname=cortefacil", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado ao banco de dados com sucesso!\n";
    
    // Verificar se a coluna status_pagamento já existe
    $checkColumn = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'status_pagamento'");
    if ($checkColumn->rowCount() == 0) {
        // Adicionar coluna status_pagamento na tabela agendamentos
        $sql1 = "ALTER TABLE agendamentos 
                 ADD COLUMN status_pagamento ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente' AFTER valor_taxa";
        $pdo->exec($sql1);
        echo "Coluna status_pagamento adicionada à tabela agendamentos!\n";
    } else {
        echo "Coluna status_pagamento já existe na tabela agendamentos.\n";
    }
    
    // Verificar se a tabela pagamentos já existe
    $checkTable = $pdo->query("SHOW TABLES LIKE 'pagamentos'");
    if ($checkTable->rowCount() == 0) {
        // Criar tabela de pagamentos
        $sql2 = "CREATE TABLE pagamentos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    agendamento_id INT NOT NULL,
                    mercadopago_payment_id VARCHAR(100) UNIQUE,
                    status VARCHAR(50) NOT NULL,
                    valor DECIMAL(10,2) NOT NULL,
                    metodo_pagamento VARCHAR(50) NOT NULL,
                    dados_pagamento JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE
                )";
        $pdo->exec($sql2);
        echo "Tabela pagamentos criada com sucesso!\n";
        
        // Criar índices
        $indexes = [
            "CREATE INDEX idx_pagamentos_agendamento ON pagamentos(agendamento_id)",
            "CREATE INDEX idx_pagamentos_mercadopago ON pagamentos(mercadopago_payment_id)",
            "CREATE INDEX idx_pagamentos_status ON pagamentos(status)",
            "CREATE INDEX idx_agendamentos_status_pagamento ON agendamentos(status_pagamento)"
        ];
        
        foreach ($indexes as $index) {
            try {
                $pdo->exec($index);
                echo "Índice criado: " . substr($index, 13, 30) . "...\n";
            } catch (Exception $e) {
                echo "Erro ao criar índice (pode já existir): " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "Tabela pagamentos já existe.\n";
    }
    
    echo "\nSetup das tabelas de pagamento concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>