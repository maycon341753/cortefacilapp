<?php
/**
 * Script para adicionar campos telefone e email na tabela profissionais
 * Funciona tanto no ambiente local quanto online
 */

// Configuração direta para o banco online
$host = 'srv486.hstgr.io';
$db_name = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'Maycon341753';

echo "<h2>Adicionando Campos na Tabela Profissionais - Banco Online</h2>";

try {
    $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "<p style='color: green;'>✅ Conexão com banco online estabelecida!</p>";
    
    // Verificar estrutura atual
    echo "<h3>1. Verificando estrutura atual:</h3>";
    $stmt = $conn->query("DESCRIBE profissionais");
    $campos = $stmt->fetchAll();
    
    $tem_telefone = false;
    $tem_email = false;
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th></tr>";
    
    foreach ($campos as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "</tr>";
        
        if ($campo['Field'] === 'telefone') $tem_telefone = true;
        if ($campo['Field'] === 'email') $tem_email = true;
    }
    echo "</table>";
    
    echo "<h3>2. Status dos campos:</h3>";
    echo "<ul>";
    echo "<li>Telefone: " . ($tem_telefone ? "<span style='color: green;'>✅ Existe</span>" : "<span style='color: red;'>❌ Não existe</span>") . "</li>";
    echo "<li>Email: " . ($tem_email ? "<span style='color: green;'>✅ Existe</span>" : "<span style='color: red;'>❌ Não existe</span>") . "</li>";
    echo "</ul>";
    
    // Adicionar campos se necessário
    if (!$tem_telefone || !$tem_email) {
        echo "<h3>3. Adicionando campos faltantes:</h3>";
        
        if (!$tem_telefone) {
            try {
                $conn->exec("ALTER TABLE profissionais ADD COLUMN telefone VARCHAR(20) NULL AFTER especialidade");
                echo "<p style='color: green;'>✅ Campo 'telefone' adicionado com sucesso!</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Erro ao adicionar campo 'telefone': {$e->getMessage()}</p>";
            }
        }
        
        if (!$tem_email) {
            try {
                $conn->exec("ALTER TABLE profissionais ADD COLUMN email VARCHAR(255) NULL AFTER telefone");
                echo "<p style='color: green;'>✅ Campo 'email' adicionado com sucesso!</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Erro ao adicionar campo 'email': {$e->getMessage()}</p>";
            }
        }
        
        // Verificar estrutura final
        echo "<h3>4. Estrutura final:</h3>";
        $stmt = $conn->query("DESCRIBE profissionais");
        $campos_final = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th></tr>";
        
        foreach ($campos_final as $campo) {
            echo "<tr>";
            echo "<td>{$campo['Field']}</td>";
            echo "<td>{$campo['Type']}</td>";
            echo "<td>{$campo['Null']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<h3>✅ Tabela já possui todos os campos necessários!</h3>";
    }
    
    // Teste de inserção
    echo "<h3>5. Testando inserção:</h3>";
    
    // Buscar um salão para teste
    $stmt = $conn->query("SELECT id FROM saloes LIMIT 1");
    $salao = $stmt->fetch();
    
    if ($salao) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO profissionais (id_salao, nome, especialidade, telefone, email, ativo) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $salao['id'],
                'Teste Profissional',
                'Cabeleireiro',
                '(11) 99999-9999',
                'teste@email.com',
                1
            ]);
            
            $id = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Teste de inserção bem-sucedido! ID: {$id}</p>";
            
            // Remover registro de teste
            $conn->exec("DELETE FROM profissionais WHERE id = {$id}");
            echo "<p style='color: blue;'>ℹ️ Registro de teste removido.</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erro no teste: {$e->getMessage()}</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum salão encontrado para teste.</p>";
    }
    
    echo "<h3>✅ Processo concluído!</h3>";
    echo "<p>A tabela profissionais agora está pronta para receber dados com telefone e email.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: {$e->getMessage()}</p>";
    echo "<p>Verifique as credenciais do banco de dados.</p>";
}

echo "<hr>";
echo "<p><a href='https://cortefacil.app/parceiro/profissionais.php' target='_blank'>Testar página de profissionais online</a></p>";
?>