<?php
/**
 * Script para verificar e criar tabelas necessárias
 * CorteFácil - Sistema de Agendamento
 */

require_once 'config/database.php';

echo "<h2>Verificação das Tabelas do Banco de Dados</h2>";

try {
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        die("<p style='color: red;'>❌ Erro: Não foi possível conectar ao banco de dados.</p>");
    }
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    // Lista de tabelas necessárias
    $tabelas_necessarias = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
    
    echo "<h3>Verificando tabelas existentes:</h3>";
    
    // Verificar quais tabelas existem
    $stmt = $conn->query("SHOW TABLES");
    $tabelas_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tabelas_necessarias as $tabela) {
        if (in_array($tabela, $tabelas_existentes)) {
            echo "<li style='color: green;'>✅ Tabela '$tabela' existe</li>";
        } else {
            echo "<li style='color: red;'>❌ Tabela '$tabela' NÃO existe</li>";
        }
    }
    echo "</ul>";
    
    // Verificar estrutura da tabela profissionais se existir
    if (in_array('profissionais', $tabelas_existentes)) {
        echo "<h3>Estrutura da tabela 'profissionais':</h3>";
        $stmt = $conn->query("DESCRIBE profissionais");
        $colunas = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>{$coluna['Field']}</td>";
            echo "<td>{$coluna['Type']}</td>";
            echo "<td>{$coluna['Null']}</td>";
            echo "<td>{$coluna['Key']}</td>";
            echo "<td>" . ($coluna['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Contar profissionais cadastrados
        $stmt = $conn->query("SELECT COUNT(*) as total FROM profissionais");
        $total = $stmt->fetch()['total'];
        echo "<p><strong>Total de profissionais cadastrados:</strong> $total</p>";
    }
    
    // Verificar se há salões cadastrados
    if (in_array('saloes', $tabelas_existentes)) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes");
        $total_saloes = $stmt->fetch()['total'];
        echo "<p><strong>Total de salões cadastrados:</strong> $total_saloes</p>";
    }
    
    // Verificar usuários parceiros
    if (in_array('usuarios', $tabelas_existentes)) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'parceiro'");
        $total_parceiros = $stmt->fetch()['total'];
        echo "<p><strong>Total de parceiros cadastrados:</strong> $total_parceiros</p>";
    }
    
    echo "<hr>";
    echo "<h3>Status do Sistema:</h3>";
    
    $todas_existem = true;
    foreach ($tabelas_necessarias as $tabela) {
        if (!in_array($tabela, $tabelas_existentes)) {
            $todas_existem = false;
            break;
        }
    }
    
    if ($todas_existem) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Todas as tabelas necessárias estão presentes!</p>";
        echo "<p>O sistema está pronto para funcionar corretamente.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Algumas tabelas estão faltando!</p>";
        echo "<p>Execute o script de criação de tabelas para corrigir.</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Erro geral: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='profissionais.php'>← Voltar para Profissionais</a></p>";
echo "<p><a href='dashboard.php'>← Voltar para Dashboard</a></p>";
?>