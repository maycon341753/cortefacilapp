<?php
/**
 * Debug da estrutura da tabela saloes no banco online
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

echo "<h2>Estrutura da Tabela Saloes - Banco Online</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão OK</p>";
        
        // Verificar estrutura da tabela saloes
        echo "<h3>Estrutura da tabela 'saloes':</h3>";
        $stmt = $conn->query("DESCRIBE saloes");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Testar inserção manual
        echo "<h3>Teste de Inserção Manual:</h3>";
        
        $sql = "INSERT INTO saloes (id_dono, nome, endereco, bairro, cidade, cep, telefone, documento, tipo_documento, razao_social, inscricao_estadual, descricao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        echo "<p>SQL: " . $sql . "</p>";
        
        $stmt = $conn->prepare($sql);
        
        $dados = [
            23, // id_dono
            'Teste Manual',
            'Rua Teste, 456',
            'Centro',
            'São Paulo',
            '01234567',
            '11999887766',
            '12345678901',
            'cpf',
            '',
            '',
            'Teste manual de inserção'
        ];
        
        echo "<p>Dados:</p><pre>" . print_r($dados, true) . "</pre>";
        
        if ($stmt->execute($dados)) {
            echo "<p style='color: green;'>✅ Inserção manual funcionou!</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro na inserção manual</p>";
            echo "<p>Erro: " . print_r($stmt->errorInfo(), true) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
}

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar</a></p>";
?>