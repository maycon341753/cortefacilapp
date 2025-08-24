<?php
// Script para verificar a estrutura da tabela saloes

echo "<h2>🔍 Estrutura da Tabela Saloes</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

try {
    // Tentar conexão online primeiro
    $conn = new PDO('mysql:host=srv1421.hstgr.io;dbname=u508889028_cortefacil', 'u508889028_cortefacil', 'Cortefacil@2024');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Conectado ao banco online</p>";
    $ambiente = "online";
} catch(PDOException $e) {
    try {
        // Fallback para conexão local
        $conn = new PDO('mysql:host=localhost;dbname=cortefacil', 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='success'>✅ Conectado ao banco local</p>";
        $ambiente = "local";
    } catch(PDOException $e2) {
        echo "<p class='error'>❌ Erro de conexão: " . $e2->getMessage() . "</p>";
        exit;
    }
}

echo "<h3>Estrutura da Tabela 'saloes' ($ambiente):</h3>";

try {
    
    // Verificar estrutura da tabela saloes
    echo "<h3>Estrutura da tabela 'saloes':</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    if ($columns) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Tabela 'saloes' não encontrada!</p>";
    }
    
    // Verificar chaves estrangeiras
    echo "<h3>Chaves estrangeiras da tabela 'saloes':</h3>";
    $stmt = $conn->prepare("SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM 
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE 
        TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'saloes' 
        AND REFERENCED_TABLE_NAME IS NOT NULL");
    $stmt->execute();
    $foreign_keys = $stmt->fetchAll();
    
    if ($foreign_keys) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Nome da Constraint</th><th>Coluna</th><th>Tabela Referenciada</th><th>Coluna Referenciada</th></tr>";
        
        foreach ($foreign_keys as $fk) {
            echo "<tr>";
            echo "<td>{$fk['CONSTRAINT_NAME']}</td>";
            echo "<td>{$fk['COLUMN_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_TABLE_NAME']}</td>";
            echo "<td>{$fk['REFERENCED_COLUMN_NAME']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma chave estrangeira encontrada.</p>";
    }
    
    // Verificar estrutura da tabela usuarios
    echo "<h3>Estrutura da tabela 'usuarios':</h3>";
    $stmt = $conn->prepare("DESCRIBE usuarios");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    if ($columns) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>Tabela 'usuarios' não encontrada!</p>";
    }
    
    // Verificar se existe algum usuário com ID 33
    echo "<h3>Verificar usuário ID 33:</h3>";
    $stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE id = 33");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p style='color: green;'>✅ Usuário encontrado: ID {$user['id']}, Nome: {$user['nome']}, Email: {$user['email']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Usuário com ID 33 não encontrado!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}

echo "<br><a href='teste_cadastro_parceiro.php'>← Voltar para o teste</a>";
?>