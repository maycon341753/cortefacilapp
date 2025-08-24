<?php
/**
 * Verificar dados no banco de dados
 */

require_once 'config/database.php';

try {
    $conn = connectWithFallback();
    
    echo "<h1>Verificação dos Dados no Banco</h1>";
    echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";
    
    // Verificar salões
    echo "<h2>📍 Salões Cadastrados</h2>";
    $stmt = $conn->prepare("SELECT * FROM saloes ORDER BY nome");
    $stmt->execute();
    $saloes = $stmt->fetchAll();
    
    if ($saloes) {
        echo "<table><tr><th>ID</th><th>Nome</th><th>Endereço</th><th>Telefone</th><th>Status</th></tr>";
        foreach ($saloes as $salao) {
            $status = isset($salao['status']) ? $salao['status'] : 'N/A';
            echo "<tr><td>{$salao['id']}</td><td>{$salao['nome']}</td><td>{$salao['endereco']}</td><td>{$salao['telefone']}</td><td>{$status}</td></tr>";
        }
        echo "</table>";
        echo "<p><strong>Total: " . count($saloes) . " salões</strong></p>";
    } else {
        echo "<p style='color:red;'>❌ Nenhum salão encontrado!</p>";
    }
    
    // Verificar profissionais
    echo "<h2>👤 Profissionais Cadastrados</h2>";
    $stmt = $conn->prepare("
        SELECT p.*, s.nome as nome_salao 
        FROM profissionais p 
        LEFT JOIN saloes s ON p.id_salao = s.id 
        ORDER BY s.nome, p.nome
    ");
    $stmt->execute();
    $profissionais = $stmt->fetchAll();
    
    if ($profissionais) {
        echo "<table><tr><th>ID</th><th>Nome</th><th>Especialidade</th><th>Salão</th><th>Status</th><th>Telefone</th><th>Email</th></tr>";
        foreach ($profissionais as $prof) {
            $status = isset($prof['status']) ? $prof['status'] : 'N/A';
            $especialidade = $prof['especialidade'] ?? 'N/A';
            $telefone = $prof['telefone'] ?? 'N/A';
            $email = $prof['email'] ?? 'N/A';
            echo "<tr><td>{$prof['id']}</td><td>{$prof['nome']}</td><td>{$especialidade}</td><td>{$prof['nome_salao']}</td><td>{$status}</td><td>{$telefone}</td><td>{$email}</td></tr>";
        }
        echo "</table>";
        echo "<p><strong>Total: " . count($profissionais) . " profissionais</strong></p>";
    } else {
        echo "<p style='color:red;'>❌ Nenhum profissional encontrado!</p>";
    }
    
    // Verificar profissionais por salão
    echo "<h2>📊 Profissionais por Salão</h2>";
    foreach ($saloes as $salao) {
        $stmt = $conn->prepare("
            SELECT p.* 
            FROM profissionais p 
            WHERE p.id_salao = ? 
            ORDER BY p.nome
        ");
        $stmt->execute([$salao['id']]);
        $profs_salao = $stmt->fetchAll();
        
        echo "<h3>🏢 {$salao['nome']} (ID: {$salao['id']})</h3>";
        if ($profs_salao) {
            echo "<ul>";
            foreach ($profs_salao as $prof) {
                $status = isset($prof['status']) ? $prof['status'] : 'N/A';
                $especialidade = $prof['especialidade'] ?? 'Sem especialidade';
                echo "<li>👤 <strong>{$prof['nome']}</strong> - {$especialidade} - Status: {$status}</li>";
            }
            echo "</ul>";
            echo "<p>Total: " . count($profs_salao) . " profissionais</p>";
        } else {
            echo "<p style='color:orange;'>⚠️ Nenhum profissional cadastrado neste salão</p>";
        }
    }
    
    // Verificar estrutura das tabelas
    echo "<h2>🔧 Estrutura das Tabelas</h2>";
    
    echo "<h3>Tabela: saloes</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $colunas_saloes = $stmt->fetchAll();
    echo "<table><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas_saloes as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    echo "<h3>Tabela: profissionais</h3>";
    $stmt = $conn->prepare("DESCRIBE profissionais");
    $stmt->execute();
    $colunas_prof = $stmt->fetchAll();
    echo "<table><tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas_prof as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Teste da consulta da API
    echo "<h2>🧪 Teste da Consulta da API</h2>";
    foreach ($saloes as $salao) {
        echo "<h3>Testando salão: {$salao['nome']} (ID: {$salao['id']})</h3>";
        
        // Consulta exata da API
        $sql = "SELECT p.*, s.nome as nome_salao 
                FROM profissionais p 
                INNER JOIN saloes s ON p.id_salao = s.id 
                WHERE p.id_salao = :id_salao AND p.status = 'ativo' 
                ORDER BY p.nome";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_salao', $salao['id']);
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        
        echo "<p><strong>SQL:</strong> <code>" . htmlspecialchars($sql) . "</code></p>";
        echo "<p><strong>Parâmetro:</strong> id_salao = {$salao['id']}</p>";
        echo "<p><strong>Resultado:</strong> " . count($resultado) . " registros</p>";
        
        if ($resultado) {
            echo "<ul>";
            foreach ($resultado as $prof) {
                echo "<li>✅ {$prof['nome']} - {$prof['especialidade']} - Status: {$prof['status']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>❌ Nenhum resultado para este salão</p>";
            
            // Verificar sem filtro de status
            $sql2 = "SELECT p.*, s.nome as nome_salao 
                     FROM profissionais p 
                     INNER JOIN saloes s ON p.id_salao = s.id 
                     WHERE p.id_salao = :id_salao 
                     ORDER BY p.nome";
            
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':id_salao', $salao['id']);
            $stmt2->execute();
            $resultado2 = $stmt2->fetchAll();
            
            echo "<p><strong>Sem filtro de status:</strong> " . count($resultado2) . " registros</p>";
            if ($resultado2) {
                echo "<ul>";
                foreach ($resultado2 as $prof) {
                    $status = $prof['status'] ?? 'NULL';
                    echo "<li>⚠️ {$prof['nome']} - Status: {$status}</li>";
                }
                echo "</ul>";
            }
        }
        
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>