<?php
/**
 * Script para verificar a estrutura da tabela saloes no banco online
 * e identificar se existem colunas separadas para bairro, cidade e CEP
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Verificação da Estrutura da Tabela 'saloes'</h2>";
    
    // Verificar estrutura da tabela saloes
    echo "<h3>Estrutura da tabela 'saloes':</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    $tem_bairro = false;
    $tem_cidade = false;
    $tem_cep = false;
    
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Extra'] ?? '') . "</td>";
        echo "</tr>";
        
        // Verificar se existem colunas específicas
        if ($coluna['Field'] === 'bairro') $tem_bairro = true;
        if ($coluna['Field'] === 'cidade') $tem_cidade = true;
        if ($coluna['Field'] === 'cep') $tem_cep = true;
    }
    
    echo "</table>";
    
    // Resumo das colunas de endereço
    echo "<h3>Análise das Colunas de Endereço:</h3>";
    echo "<ul>";
    echo "<li>Coluna 'bairro': " . ($tem_bairro ? "<strong style='color: green;'>EXISTE</strong>" : "<strong style='color: red;'>NÃO EXISTE</strong>") . "</li>";
    echo "<li>Coluna 'cidade': " . ($tem_cidade ? "<strong style='color: green;'>EXISTE</strong>" : "<strong style='color: red;'>NÃO EXISTE</strong>") . "</li>";
    echo "<li>Coluna 'cep': " . ($tem_cep ? "<strong style='color: green;'>EXISTE</strong>" : "<strong style='color: red;'>NÃO EXISTE</strong>") . "</li>";
    echo "</ul>";
    
    // Verificar alguns registros existentes
    echo "<h3>Amostra de Dados Existentes (últimos 5 registros):</h3>";
    $stmt = $conn->prepare("SELECT id, nome, endereco, telefone, documento FROM saloes ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($saloes)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Endereço</th><th>Telefone</th><th>Documento</th></tr>";
        
        foreach ($saloes as $salao) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($salao['id']) . "</td>";
            echo "<td>" . htmlspecialchars($salao['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($salao['endereco']) . "</td>";
            echo "<td>" . htmlspecialchars($salao['telefone']) . "</td>";
            echo "<td>" . htmlspecialchars($salao['documento']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Nenhum salão encontrado na tabela.</p>";
    }
    
    // Recomendações
    echo "<h3>Recomendações:</h3>";
    if (!$tem_bairro || !$tem_cidade || !$tem_cep) {
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<h4>⚠️ Problema Identificado:</h4>";
        echo "<p>A tabela 'saloes' não possui colunas separadas para bairro, cidade e CEP. ";
        echo "Atualmente, essas informações estão sendo concatenadas no campo 'endereco'.</p>";
        
        echo "<h4>💡 Soluções Possíveis:</h4>";
        echo "<ol>";
        echo "<li><strong>Adicionar colunas separadas:</strong> Criar colunas 'bairro', 'cidade' e 'cep' na tabela 'saloes'</li>";
        echo "<li><strong>Modificar o código:</strong> Atualizar o método cadastrarSalao para salvar os dados nas colunas corretas</li>";
        echo "<li><strong>Migrar dados existentes:</strong> Extrair bairro, cidade e CEP dos endereços concatenados (se possível)</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Estrutura Adequada:</h4>";
        echo "<p>A tabela possui colunas separadas para bairro, cidade e CEP. ";
        echo "Verifique se o código está salvando os dados nas colunas corretas.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Erro:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>