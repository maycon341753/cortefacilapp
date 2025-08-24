<?php
/**
 * Verificar estrutura da tabela usuarios no banco local
 */

echo "<h2>🔍 Estrutura da Tabela Usuarios</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=u690889028_cortefacil', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='success'>✅ Conectado ao banco local</p>";
    
    // Verificar estrutura da tabela usuarios
    echo "<h3>Estrutura da Tabela 'usuarios':</h3>";
    $stmt = $pdo->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($columns) {
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        $hasTypeColumn = false;
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
            
            if ($column['Field'] === 'tipo') {
                $hasTypeColumn = true;
            }
        }
        echo "</table>";
        
        if (!$hasTypeColumn) {
            echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:4px;'>";
            echo "<h4>⚠️ Coluna 'tipo' não encontrada!</h4>";
            echo "<p>A tabela usuarios não possui a coluna 'tipo' necessária para distinguir parceiros.</p>";
            echo "<p><strong>Solução:</strong> Adicionar a coluna 'tipo' à tabela.</p>";
            echo "</div>";
            
            // Adicionar coluna tipo
            echo "<h4>Adicionando coluna 'tipo':</h4>";
            try {
                $pdo->exec("ALTER TABLE usuarios ADD COLUMN tipo VARCHAR(20) DEFAULT 'cliente' AFTER email");
                echo "<p class='success'>✅ Coluna 'tipo' adicionada com sucesso!</p>";
                
                // Atualizar registros existentes para 'parceiro' se necessário
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
                $total = $stmt->fetch()['total'];
                
                if ($total > 0) {
                    // Assumir que usuários existentes são parceiros
                    $pdo->exec("UPDATE usuarios SET tipo = 'parceiro' WHERE tipo = 'cliente'");
                    echo "<p class='info'>ℹ️ $total usuários existentes marcados como 'parceiro'</p>";
                }
                
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Erro ao adicionar coluna: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='success'>✅ Coluna 'tipo' encontrada!</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Não foi possível obter estrutura da tabela</p>";
    }
    
    // Contar usuários por tipo
    echo "<h3>Contagem de Usuários:</h3>";
    try {
        $stmt = $pdo->query("SELECT tipo, COUNT(*) as total FROM usuarios GROUP BY tipo");
        $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($counts) {
            echo "<ul>";
            foreach ($counts as $count) {
                echo "<li><strong>" . ucfirst($count['tipo']) . ":</strong> " . $count['total'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='info'>ℹ️ Nenhum usuário encontrado</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro ao contar usuários: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='solucao_temporaria.php'>← Voltar</a> | <a href='login.php'>🔐 Testar Login</a></p>";
?>