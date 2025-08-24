<?php
require_once 'config/database.php';

echo "<h2>Correção da Estrutura da Tabela Salões</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        die("Erro: Não foi possível conectar ao banco de dados.");
    }
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Verificar se a tabela saloes existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'saloes'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "<h3>Criando tabela 'saloes'...</h3>";
        
        $sql = "
        CREATE TABLE saloes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_dono INT NOT NULL,
            nome VARCHAR(255) NOT NULL,
            endereco TEXT,
            telefone VARCHAR(20),
            documento VARCHAR(20),
            tipo_documento ENUM('CPF', 'CNPJ') DEFAULT 'CNPJ',
            razao_social VARCHAR(255),
            inscricao_estadual VARCHAR(50),
            descricao TEXT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($conn->exec($sql)) {
            echo "<p style='color: green;'>✅ Tabela 'saloes' criada com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar tabela 'saloes'</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Tabela 'saloes' já existe</p>";
        
        // Verificar se a coluna id_dono existe
        $stmt = $conn->prepare("DESCRIBE saloes");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        $has_id_dono = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'id_dono') {
                $has_id_dono = true;
                break;
            }
        }
        
        if (!$has_id_dono) {
            echo "<h3>Adicionando coluna 'id_dono'...</h3>";
            $sql = "ALTER TABLE saloes ADD COLUMN id_dono INT NOT NULL AFTER id";
            if ($conn->exec($sql)) {
                echo "<p style='color: green;'>✅ Coluna 'id_dono' adicionada!</p>";
                
                // Adicionar chave estrangeira
                echo "<h3>Adicionando chave estrangeira...</h3>";
                $sql = "ALTER TABLE saloes ADD FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE";
                if ($conn->exec($sql)) {
                    echo "<p style='color: green;'>✅ Chave estrangeira adicionada!</p>";
                } else {
                    echo "<p style='color: red;'>❌ Erro ao adicionar chave estrangeira</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Erro ao adicionar coluna 'id_dono'</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Coluna 'id_dono' já existe</p>";
        }
    }
    
    // Verificar se a tabela horarios_funcionamento existe
    echo "<h3>Verificando tabela 'horarios_funcionamento'...</h3>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'horarios_funcionamento'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "<h3>Criando tabela 'horarios_funcionamento'...</h3>";
        
        $sql = "
        CREATE TABLE horarios_funcionamento (
            id INT AUTO_INCREMENT PRIMARY KEY,
            salao_id INT NOT NULL,
            dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo') NOT NULL,
            horario_abertura TIME NOT NULL,
            horario_fechamento TIME NOT NULL,
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_salao_dia (salao_id, dia_semana)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        if ($conn->exec($sql)) {
            echo "<p style='color: green;'>✅ Tabela 'horarios_funcionamento' criada com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar tabela 'horarios_funcionamento'</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Tabela 'horarios_funcionamento' já existe</p>";
    }
    
    echo "<h3>✅ Correção concluída!</h3>";
    echo "<p><a href='teste_cadastro_parceiro.php'>Testar cadastro de parceiro</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>