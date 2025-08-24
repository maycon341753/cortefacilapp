<?php
/**
 * Script para criar as tabelas faltantes no banco online
 * Baseado na verificação anterior: servicos e pagamentos
 */

require_once 'config/database.php';

echo "<h2>Criando Tabelas Faltantes no Banco Online</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    // Forçar ambiente online
    $env_file = __DIR__ . '/.env.online';
    if (!file_exists($env_file)) {
        file_put_contents($env_file, 'FORCE_ONLINE=true');
    }
    
    $database = Database::getInstance();
    $conn = $database->connect();
    
    if (!$conn) {
        throw new Exception('Erro na conexão com o banco online');
    }
    
    echo "<p class='success'>✅ Conectado ao banco online: u690889028_cortefacil</p>";
    
    // SQL para criar tabela de serviços
    $sql_servicos = "
        CREATE TABLE IF NOT EXISTS servicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_salao INT NOT NULL,
            nome VARCHAR(100) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            duracao_minutos INT NOT NULL DEFAULT 60,
            ativo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
            INDEX idx_servicos_salao (id_salao),
            INDEX idx_servicos_ativo (ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    // SQL para criar tabela de pagamentos
    $sql_pagamentos = "
        CREATE TABLE IF NOT EXISTS pagamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_agendamento INT NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            status ENUM('pendente', 'aprovado', 'rejeitado', 'cancelado') DEFAULT 'pendente',
            metodo_pagamento VARCHAR(50),
            transaction_id VARCHAR(100),
            data_pagamento TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id) ON DELETE CASCADE,
            INDEX idx_pagamentos_agendamento (id_agendamento),
            INDEX idx_pagamentos_status (status),
            INDEX idx_pagamentos_data (data_pagamento)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    echo "<h3>🔧 Criando Tabelas Faltantes</h3>";
    
    // Criar tabela servicos
    echo "<h4>Criando tabela 'servicos'...</h4>";
    try {
        $conn->exec($sql_servicos);
        echo "<p class='success'>✅ Tabela 'servicos' criada com sucesso!</p>";
        
        // Verificar estrutura criada
        $stmt = $conn->query("DESCRIBE servicos");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>{$coluna['Field']}</td>";
            echo "<td>{$coluna['Type']}</td>";
            echo "<td>{$coluna['Null']}</td>";
            echo "<td>{$coluna['Key']}</td>";
            echo "<td>{$coluna['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar tabela 'servicos': " . $e->getMessage() . "</p>";
    }
    
    // Criar tabela pagamentos
    echo "<h4>Criando tabela 'pagamentos'...</h4>";
    try {
        $conn->exec($sql_pagamentos);
        echo "<p class='success'>✅ Tabela 'pagamentos' criada com sucesso!</p>";
        
        // Verificar estrutura criada
        $stmt = $conn->query("DESCRIBE pagamentos");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>{$coluna['Field']}</td>";
            echo "<td>{$coluna['Type']}</td>";
            echo "<td>{$coluna['Null']}</td>";
            echo "<td>{$coluna['Key']}</td>";
            echo "<td>{$coluna['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao criar tabela 'pagamentos': " . $e->getMessage() . "</p>";
    }
    
    // Inserir alguns serviços padrão para teste
    echo "<h3>📋 Inserindo Dados de Teste</h3>";
    
    // Verificar se existem salões para inserir serviços
    $stmt = $conn->query("SELECT COUNT(*) FROM saloes WHERE ativo = 1");
    $total_saloes = $stmt->fetchColumn();
    
    if ($total_saloes > 0) {
        // Pegar o primeiro salão ativo
        $stmt = $conn->query("SELECT id FROM saloes WHERE ativo = 1 LIMIT 1");
        $id_salao = $stmt->fetchColumn();
        
        $servicos_padrao = [
            ['nome' => 'Corte Masculino', 'preco' => 25.00, 'duracao' => 30],
            ['nome' => 'Corte Feminino', 'preco' => 35.00, 'duracao' => 45],
            ['nome' => 'Barba', 'preco' => 15.00, 'duracao' => 20],
            ['nome' => 'Corte + Barba', 'preco' => 35.00, 'duracao' => 50]
        ];
        
        $stmt_insert = $conn->prepare("
            INSERT IGNORE INTO servicos (id_salao, nome, preco, duracao_minutos, descricao) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($servicos_padrao as $servico) {
            $descricao = "Serviço de {$servico['nome']} com duração de {$servico['duracao']} minutos";
            $stmt_insert->execute([
                $id_salao, 
                $servico['nome'], 
                $servico['preco'], 
                $servico['duracao'],
                $descricao
            ]);
        }
        
        echo "<p class='success'>✅ Serviços padrão inseridos para o salão ID: $id_salao</p>";
        
        // Mostrar serviços inseridos
        $stmt = $conn->query("SELECT * FROM servicos WHERE id_salao = $id_salao");
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($servicos)) {
            echo "<h4>Serviços inseridos:</h4>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Duração (min)</th></tr>";
            foreach ($servicos as $servico) {
                echo "<tr>";
                echo "<td>{$servico['id']}</td>";
                echo "<td>{$servico['nome']}</td>";
                echo "<td>R$ " . number_format($servico['preco'], 2, ',', '.') . "</td>";
                echo "<td>{$servico['duracao_minutos']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p class='info'>ℹ️ Nenhum salão ativo encontrado para inserir serviços de teste</p>";
    }
    
    // Resumo final
    echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;margin:20px 0;'>";
    echo "<h3>✅ Tabelas Criadas com Sucesso!</h3>";
    echo "<p><strong>Tabelas adicionadas:</strong></p>";
    echo "<ul>";
    echo "<li>✅ servicos - Para gerenciar os serviços oferecidos pelos salões</li>";
    echo "<li>✅ pagamentos - Para controlar os pagamentos dos agendamentos</li>";
    echo "</ul>";
    echo "<p><strong>Banco online agora possui todas as tabelas necessárias!</strong></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro no banco: " . $e->getMessage() . "</p>";
}

echo "<br><br>";
echo "<a href='verificar_tabelas_online.php' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-right:10px;'>🔍 Verificar Novamente</a>";
echo "<a href='cliente/agendar.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🌐 Testar Sistema</a>";
?>