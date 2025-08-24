<?php
/**
 * Script para corrigir a estrutura da tabela saloes
 * Adiciona colunas separadas para bairro, cidade e CEP
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>Correção da Estrutura da Tabela 'saloes'</h2>";
    
    // Verificar se as colunas já existem
    echo "<h3>1. Verificando estrutura atual...</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tem_bairro = false;
    $tem_cidade = false;
    $tem_cep = false;
    
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'bairro') $tem_bairro = true;
        if ($coluna['Field'] === 'cidade') $tem_cidade = true;
        if ($coluna['Field'] === 'cep') $tem_cep = true;
    }
    
    echo "<ul>";
    echo "<li>Coluna 'bairro': " . ($tem_bairro ? "<span style='color: green;'>JÁ EXISTE</span>" : "<span style='color: red;'>NÃO EXISTE</span>") . "</li>";
    echo "<li>Coluna 'cidade': " . ($tem_cidade ? "<span style='color: green;'>JÁ EXISTE</span>" : "<span style='color: red;'>NÃO EXISTE</span>") . "</li>";
    echo "<li>Coluna 'cep': " . ($tem_cep ? "<span style='color: green;'>JÁ EXISTE</span>" : "<span style='color: red;'>NÃO EXISTE</span>") . "</li>";
    echo "</ul>";
    
    // Adicionar colunas se não existirem
    $alteracoes_feitas = [];
    
    if (!$tem_bairro) {
        echo "<h3>2. Adicionando coluna 'bairro'...</h3>";
        $sql = "ALTER TABLE saloes ADD COLUMN bairro VARCHAR(100) NULL AFTER endereco";
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Coluna 'bairro' adicionada com sucesso!</p>";
        $alteracoes_feitas[] = 'bairro';
    }
    
    if (!$tem_cidade) {
        echo "<h3>3. Adicionando coluna 'cidade'...</h3>";
        $sql = "ALTER TABLE saloes ADD COLUMN cidade VARCHAR(100) NULL AFTER " . ($tem_bairro ? 'bairro' : 'endereco');
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Coluna 'cidade' adicionada com sucesso!</p>";
        $alteracoes_feitas[] = 'cidade';
    }
    
    if (!$tem_cep) {
        echo "<h3>4. Adicionando coluna 'cep'...</h3>";
        $sql = "ALTER TABLE saloes ADD COLUMN cep VARCHAR(10) NULL AFTER cidade";
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Coluna 'cep' adicionada com sucesso!</p>";
        $alteracoes_feitas[] = 'cep';
    }
    
    if (empty($alteracoes_feitas)) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Estrutura já está correta!</h4>";
        echo "<p>Todas as colunas necessárias já existem na tabela 'saloes'.</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Correções aplicadas com sucesso!</h4>";
        echo "<p>Colunas adicionadas: " . implode(', ', $alteracoes_feitas) . "</p>";
        echo "</div>";
    }
    
    // Mostrar estrutura final
    echo "<h3>5. Estrutura final da tabela 'saloes':</h3>";
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $colunas_finais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    foreach ($colunas_finais as $coluna) {
        $destaque = in_array($coluna['Field'], ['bairro', 'cidade', 'cep']) ? "style='background-color: #d4edda;'" : "";
        echo "<tr {$destaque}>";
        echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Default'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<div style='background-color: #cce5ff; border: 1px solid #99ccff; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h4>📋 Próximos Passos:</h4>";
    echo "<ol>";
    echo "<li>Atualizar o método <code>cadastrarSalao</code> na classe <code>Usuario</code> para salvar os dados nas colunas separadas</li>";
    echo "<li>Testar o cadastro de novos parceiros para verificar se os dados estão sendo salvos corretamente</li>";
    echo "<li>Considerar migrar dados existentes (se houver endereços concatenados que possam ser separados)</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Erro:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Detalhes técnicos:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>