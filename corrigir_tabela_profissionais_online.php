<?php
/**
 * Script para corrigir a estrutura da tabela profissionais no banco online
 * Adiciona os campos telefone e email que estão faltando
 */

require_once 'config/database.php';

echo "<h2>Correção da Tabela Profissionais - Banco Online</h2>";

try {
    $conn = getConnection();
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Verificar estrutura atual da tabela profissionais
    echo "<h3>1. Estrutura atual da tabela profissionais:</h3>";
    $stmt = $conn->query("DESCRIBE profissionais");
    $campos = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 15px;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    $tem_telefone = false;
    $tem_email = false;
    
    foreach ($campos as $campo) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($campo['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        if ($campo['Field'] === 'telefone') $tem_telefone = true;
        if ($campo['Field'] === 'email') $tem_email = true;
    }
    echo "</table>";
    
    echo "<h3>2. Verificação de campos necessários:</h3>";
    echo "<ul>";
    echo "<li>Campo 'telefone': " . ($tem_telefone ? "<span style='color: green;'>✅ Existe</span>" : "<span style='color: red;'>❌ Não existe</span>") . "</li>";
    echo "<li>Campo 'email': " . ($tem_email ? "<span style='color: green;'>✅ Existe</span>" : "<span style='color: red;'>❌ Não existe</span>") . "</li>";
    echo "</ul>";
    
    // Adicionar campos faltantes
    $alteracoes = [];
    
    if (!$tem_telefone) {
        $alteracoes[] = "ADD COLUMN telefone VARCHAR(20) NULL AFTER especialidade";
    }
    
    if (!$tem_email) {
        $alteracoes[] = "ADD COLUMN email VARCHAR(255) NULL AFTER telefone";
    }
    
    if (!empty($alteracoes)) {
        echo "<h3>3. Aplicando correções:</h3>";
        
        foreach ($alteracoes as $alteracao) {
            try {
                $sql = "ALTER TABLE profissionais " . $alteracao;
                echo "<p>Executando: <code>" . htmlspecialchars($sql) . "</code></p>";
                $conn->exec($sql);
                echo "<p style='color: green;'>✅ Alteração aplicada com sucesso!</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>❌ Erro ao aplicar alteração: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        
        // Verificar estrutura final
        echo "<h3>4. Estrutura final da tabela profissionais:</h3>";
        $stmt = $conn->query("DESCRIBE profissionais");
        $campos_final = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 15px;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        
        foreach ($campos_final as $campo) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($campo['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>✅ Correção concluída com sucesso!</h3>";
        echo "<p>A tabela profissionais agora possui os campos telefone e email necessários.</p>";
        
    } else {
        echo "<h3>✅ Tabela já está correta!</h3>";
        echo "<p>Todos os campos necessários já existem na tabela profissionais.</p>";
    }
    
    // Testar inserção de um profissional
    echo "<h3>5. Teste de inserção:</h3>";
    
    // Buscar um salão para teste
    $stmt = $conn->query("SELECT id FROM saloes LIMIT 1");
    $salao_teste = $stmt->fetch();
    
    if ($salao_teste) {
        try {
            $sql_teste = "INSERT INTO profissionais (id_salao, nome, especialidade, telefone, email, ativo) 
                         VALUES (:id_salao, :nome, :especialidade, :telefone, :email, :ativo)";
            
            $stmt_teste = $conn->prepare($sql_teste);
            $stmt_teste->execute([
                ':id_salao' => $salao_teste['id'],
                ':nome' => 'Profissional Teste',
                ':especialidade' => 'Cabeleireiro',
                ':telefone' => '(11) 99999-9999',
                ':email' => 'teste@profissional.com',
                ':ativo' => 1
            ]);
            
            $id_inserido = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Teste de inserção bem-sucedido! ID: {$id_inserido}</p>";
            
            // Remover o registro de teste
            $conn->exec("DELETE FROM profissionais WHERE id = {$id_inserido}");
            echo "<p style='color: blue;'>ℹ️ Registro de teste removido.</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Erro no teste de inserção: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhum salão encontrado para teste.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Script concluído!</strong></p>";
echo "<p><a href='parceiro/profissionais.php'>Testar página de profissionais</a></p>";
?>