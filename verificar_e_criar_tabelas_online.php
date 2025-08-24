<?php
/**
 * Script para verificar e criar tabelas faltantes no banco online
 * CorteFácil - Sistema de Agendamento
 */

require_once 'config/database.php';

try {
    echo "<h2>🔍 Verificando estrutura do banco de dados online...</h2>";
    
    // Conectar ao banco usando Singleton
    $database = Database::getInstance();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception("Erro ao conectar com o banco de dados");
    }
    
    echo "<p>✅ Conexão estabelecida com sucesso!</p>";
    
    // Lista de tabelas esperadas
    $tabelasEsperadas = [
        'usuarios',
        'saloes', 
        'profissionais',
        'agendamentos',
        'horarios_funcionamento',
        'servicos',
        'pagamentos',
        'log_atividades',
        'verificacoes_documento'
    ];
    
    // Verificar tabelas existentes
    echo "<h3>📋 Verificando tabelas existentes:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tabelasExistentes = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tabelasExistentes[] = $row[0];
    }
    
    echo "<ul>";
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelasExistentes)) {
            echo "<li>✅ <strong>$tabela</strong> - Existe</li>";
        } else {
            echo "<li>❌ <strong>$tabela</strong> - Não existe</li>";
        }
    }
    echo "</ul>";
    
    // Identificar tabelas faltantes
    $tabelasFaltantes = array_diff($tabelasEsperadas, $tabelasExistentes);
    
    if (empty($tabelasFaltantes)) {
        echo "<p>🎉 <strong>Todas as tabelas já existem!</strong></p>";
    } else {
        echo "<h3>⚠️ Tabelas faltantes encontradas:</h3>";
        echo "<ul>";
        foreach ($tabelasFaltantes as $tabela) {
            echo "<li>❌ $tabela</li>";
        }
        echo "</ul>";
        
        echo "<h3>🔧 Criando tabelas faltantes...</h3>";
        
        // Ler e executar o script SQL
        $sqlFile = __DIR__ . '/criar_tabelas_online.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("Arquivo SQL não encontrado: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remover comentários e dividir em comandos
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove comentários de linha
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove comentários de bloco
        
        // Dividir em comandos individuais
        $comandos = array_filter(array_map('trim', explode(';', $sql)));
        
        $sucessos = 0;
        $erros = 0;
        
        foreach ($comandos as $comando) {
            if (empty($comando)) continue;
            
            try {
                $pdo->exec($comando);
                $sucessos++;
                
                // Verificar se é um comando CREATE TABLE
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $comando, $matches)) {
                    echo "<p>✅ Tabela <strong>{$matches[1]}</strong> criada com sucesso</p>";
                }
            } catch (PDOException $e) {
                // Ignorar erros de tabela já existente
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "<p>❌ Erro ao executar comando: " . htmlspecialchars($e->getMessage()) . "</p>";
                    $erros++;
                }
            }
        }
        
        echo "<h3>📊 Resultado:</h3>";
        echo "<p>✅ Comandos executados com sucesso: <strong>$sucessos</strong></p>";
        if ($erros > 0) {
            echo "<p>❌ Comandos com erro: <strong>$erros</strong></p>";
        }
    }
    
    // Verificação final
    echo "<h3>🔍 Verificação final das tabelas:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tabelasFinais = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tabelasFinais[] = $row[0];
    }
    
    echo "<ul>";
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelasFinais)) {
            echo "<li>✅ <strong>$tabela</strong> - OK</li>";
        } else {
            echo "<li>❌ <strong>$tabela</strong> - FALTANDO</li>";
        }
    }
    echo "</ul>";
    
    // Verificar estrutura das tabelas principais
    echo "<h3>🔍 Verificando estrutura das tabelas principais:</h3>";
    
    $tabelasVerificar = ['usuarios', 'saloes', 'agendamentos'];
    
    foreach ($tabelasVerificar as $tabela) {
        if (in_array($tabela, $tabelasFinais)) {
            echo "<h4>📋 Estrutura da tabela '$tabela':</h4>";
            $stmt = $pdo->query("DESCRIBE $tabela");
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>{$row['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h2>🎉 Processo concluído!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro:</h2>";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Detalhes:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h2, h3, h4 {
    color: #333;
}

ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

li {
    margin: 5px 0;
    padding: 5px;
}

table {
    background: white;
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

th, td {
    padding: 8px 12px;
    text-align: left;
}

th {
    background-color: #007bff;
    color: white;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}
</style>