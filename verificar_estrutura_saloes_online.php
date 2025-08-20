<?php
/**
 * Verificar estrutura da tabela saloes no banco online
 */

// Forçar ambiente online
$_SERVER['HTTP_HOST'] = 'cortefacil.app';

require_once 'config/database.php';

echo "<h2>Verificação da Estrutura da Tabela 'saloes' - Ambiente Online</h2>";
echo "<hr>";

try {
    $database = new Database();
    $db = $database->connect();
    
    if (!$db) {
        echo "❌ Erro na conexão com o banco<br>";
        exit;
    }
    
    echo "✅ Conectado ao banco online<br><br>";
    
    // Verificar se a tabela existe
    $stmt = $db->prepare("SHOW TABLES LIKE 'saloes'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "❌ Tabela 'saloes' não existe!<br>";
        
        // Listar todas as tabelas
        echo "<h3>Tabelas existentes no banco:</h3>";
        $stmt = $db->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll();
        
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table[0] . "</li>";
        }
        echo "</ul>";
        
    } else {
        echo "✅ Tabela 'saloes' existe<br><br>";
        
        // Mostrar estrutura da tabela
        echo "<h3>Estrutura da tabela 'saloes':</h3>";
        $stmt = $db->prepare("DESCRIBE saloes");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td><strong>" . $column['Field'] . "</strong></td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Verificar se usuario_id existe
        $has_usuario_id = false;
        $has_id_dono = false;
        
        foreach ($columns as $column) {
            if ($column['Field'] === 'usuario_id') {
                $has_usuario_id = true;
            }
            if ($column['Field'] === 'id_dono') {
                $has_id_dono = true;
            }
        }
        
        echo "<h3>Análise da Chave Estrangeira:</h3>";
        if ($has_usuario_id) {
            echo "✅ Campo 'usuario_id' existe<br>";
        } else {
            echo "❌ Campo 'usuario_id' NÃO existe<br>";
        }
        
        if ($has_id_dono) {
            echo "⚠️ Campo 'id_dono' existe (campo antigo)<br>";
        } else {
            echo "ℹ️ Campo 'id_dono' não existe<br>";
        }
        
        // Contar registros
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM saloes");
        $stmt->execute();
        $count = $stmt->fetch();
        echo "<br>📊 Total de salões cadastrados: " . $count['total'] . "<br>";
        
        // Se não tem usuario_id, sugerir correção
        if (!$has_usuario_id) {
            echo "<br><div style='background: #ffebee; padding: 15px; border-radius: 5px; border: 1px solid #f44336;'>";
            echo "<h4 style='color: #d32f2f; margin: 0 0 10px 0;'>🚨 PROBLEMA IDENTIFICADO!</h4>";
            echo "<p style='margin: 0; color: #d32f2f;'>A tabela 'saloes' não possui o campo 'usuario_id' necessário para o cadastro de parceiros.</p>";
            echo "<br><strong>Soluções possíveis:</strong>";
            echo "<ol>";
            if ($has_id_dono) {
                echo "<li>Renomear o campo 'id_dono' para 'usuario_id'</li>";
            } else {
                echo "<li>Adicionar o campo 'usuario_id' à tabela</li>";
            }
            echo "<li>Atualizar o método cadastrarSalao para usar o campo correto</li>";
            echo "</ol>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<br><p><a href='cadastro.php?tipo=parceiro'>← Voltar para cadastro</a></p>";
?>