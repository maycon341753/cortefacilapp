<?php
/**
 * Debug da estrutura do banco de dados online
 * Verificar se existe tabela de especialidades e sua estrutura
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configuração do banco
require_once 'config/database.php';

echo "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Debug Banco - Especialidades</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".error { color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 15px 0; }\n";
echo ".success { color: green; background: #e6ffe6; padding: 15px; border: 1px solid green; margin: 15px 0; }\n";
echo ".info { color: blue; background: #e6f3ff; padding: 15px; border: 1px solid blue; margin: 15px 0; }\n";
echo "table { border-collapse: collapse; width: 100%; margin: 15px 0; }\n";
echo "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }\n";
echo "th { background-color: #f2f2f2; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔍 Debug do Banco de Dados Online - Especialidades</h1>\n";

try {
    // Conectar ao banco usando Singleton
    $database = Database::getInstance();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<div class='success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
    
    // Verificar se existe tabela especialidades
    echo "<h2>📋 Verificando Tabelas Relacionadas</h2>\n";
    
    $tables_to_check = ['especialidades', 'profissionais', 'saloes'];
    
    foreach ($tables_to_check as $table) {
        echo "<h3>Tabela: {$table}</h3>\n";
        
        // Verificar se tabela existe usando INFORMATION_SCHEMA
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        $result = $stmt->fetch();
        $table_exists = $result['count'] > 0;
        
        if ($table_exists) {
            echo "<div class='success'>\n";
            echo "<p>✅ Tabela '{$table}' existe</p>\n";
            echo "</div>\n";
            
            // Mostrar estrutura da tabela
            $stmt = $pdo->prepare("DESCRIBE {$table}");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Estrutura da tabela {$table}:</h4>\n";
            echo "<table>\n";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>\n";
            
            foreach ($columns as $column) {
                echo "<tr>\n";
                echo "<td>" . htmlspecialchars($column['Field']) . "</td>\n";
                echo "<td>" . htmlspecialchars($column['Type']) . "</td>\n";
                echo "<td>" . htmlspecialchars($column['Null']) . "</td>\n";
                echo "<td>" . htmlspecialchars($column['Key']) . "</td>\n";
                echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>\n";
                echo "<td>" . htmlspecialchars($column['Extra']) . "</td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
            
            // Se for tabela especialidades, mostrar dados
            if ($table === 'especialidades') {
                $stmt = $pdo->prepare("SELECT * FROM especialidades LIMIT 10");
                $stmt->execute();
                $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Dados na tabela especialidades (primeiros 10):</h4>\n";
                if (empty($especialidades)) {
                    echo "<div class='info'>\n";
                    echo "<p>⚠️ Tabela especialidades está vazia</p>\n";
                    echo "</div>\n";
                } else {
                    echo "<table>\n";
                    echo "<tr>";
                    foreach (array_keys($especialidades[0]) as $key) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>\n";
                    
                    foreach ($especialidades as $esp) {
                        echo "<tr>";
                        foreach ($esp as $value) {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>\n";
                    }
                    echo "</table>\n";
                }
            }
            
        } else {
            echo "<div class='error'>\n";
            echo "<p>❌ Tabela '{$table}' NÃO existe</p>\n";
            echo "</div>\n";
        }
        
        echo "<hr>\n";
    }
    
    // Verificar se há foreign keys relacionadas
    echo "<h2>🔗 Verificando Foreign Keys</h2>\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = DATABASE()
            AND (TABLE_NAME = 'profissionais' OR REFERENCED_TABLE_NAME = 'especialidades')
    ");
    $stmt->execute();
    $foreign_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($foreign_keys)) {
        echo "<div class='info'>\n";
        echo "<p>ℹ️ Nenhuma foreign key encontrada relacionada a profissionais/especialidades</p>\n";
        echo "</div>\n";
    } else {
        echo "<table>\n";
        echo "<tr><th>Tabela</th><th>Coluna</th><th>Constraint</th><th>Referencia Tabela</th><th>Referencia Coluna</th></tr>\n";
        
        foreach ($foreign_keys as $fk) {
            echo "<tr>\n";
            echo "<td>" . htmlspecialchars($fk['TABLE_NAME']) . "</td>\n";
            echo "<td>" . htmlspecialchars($fk['COLUMN_NAME']) . "</td>\n";
            echo "<td>" . htmlspecialchars($fk['CONSTRAINT_NAME']) . "</td>\n";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_TABLE_NAME'] ?? 'NULL') . "</td>\n";
            echo "<td>" . htmlspecialchars($fk['REFERENCED_COLUMN_NAME'] ?? 'NULL') . "</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Erro:</h3>\n";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
}

echo "</body>\n</html>";
?>