<?php
$arquivo = 'cliente/agendar.php';
$conteudo = file_get_contents($arquivo);

echo "<h2>🔍 Análise Detalhada de Promises</h2>";

// Função para obter número da linha
function getLineNumber($content, $offset) {
    return substr_count(substr($content, 0, $offset), "\n") + 1;
}

// Buscar por todas as ocorrências de .then( e .catch(
preg_match_all('/\.then\s*\(/i', $conteudo, $thenMatches, PREG_OFFSET_CAPTURE);
preg_match_all('/\.catch\s*\(/i', $conteudo, $catchMatches, PREG_OFFSET_CAPTURE);

echo "<h3>📋 Todas as ocorrências:</h3>";
echo "<div style='display: flex; gap: 20px;'>";

// Listar todos os .then()
echo "<div style='flex: 1; background: #fff3cd; padding: 15px; border-radius: 5px;'>";
echo "<h4>.then() encontrados (" . count($thenMatches[0]) . "):</h4>";
echo "<ul>";
foreach ($thenMatches[0] as $match) {
    $linha = getLineNumber($conteudo, $match[1]);
    // Pegar contexto da linha
    $linhas = explode("\n", $conteudo);
    $contexto = isset($linhas[$linha-1]) ? trim($linhas[$linha-1]) : '';
    echo "<li><strong>Linha $linha:</strong> " . htmlspecialchars(substr($contexto, 0, 80)) . "...</li>";
}
echo "</ul>";
echo "</div>";

// Listar todos os .catch()
echo "<div style='flex: 1; background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
echo "<h4>.catch() encontrados (" . count($catchMatches[0]) . "):</h4>";
echo "<ul>";
foreach ($catchMatches[0] as $match) {
    $linha = getLineNumber($conteudo, $match[1]);
    // Pegar contexto da linha
    $linhas = explode("\n", $conteudo);
    $contexto = isset($linhas[$linha-1]) ? trim($linhas[$linha-1]) : '';
    echo "<li><strong>Linha $linha:</strong> " . htmlspecialchars(substr($contexto, 0, 80)) . "...</li>";
}
echo "</ul>";
echo "</div>";

echo "</div>";

// Análise de promises específicas
echo "<h3>🔍 Análise de Promises por Bloco:</h3>";

// Buscar por blocos de fetch() ou outras promises
preg_match_all('/(fetch\s*\([^)]+\)|bloquearHorario\s*\([^)]+\)|CorteFacil\.ajax\.get\s*\([^)]+\))[\s\S]*?(?=\n\s*\n|\n\s*}|$)/m', $conteudo, $promiseBlocks, PREG_OFFSET_CAPTURE);

echo "<p><strong>Blocos de Promise encontrados:</strong> " . count($promiseBlocks[0]) . "</p>";

foreach ($promiseBlocks[0] as $index => $block) {
    $blockContent = $block[0];
    $blockOffset = $block[1];
    $blockLine = getLineNumber($conteudo, $blockOffset);
    
    $thenCount = substr_count($blockContent, '.then(');
    $catchCount = substr_count($blockContent, '.catch(');
    
    echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>Promise #" . ($index + 1) . " (Linha $blockLine):</h4>";
    echo "<p><strong>.then():</strong> $thenCount | <strong>.catch():</strong> $catchCount</p>";
    
    if ($thenCount > $catchCount) {
        echo "<p style='color: #721c24; font-weight: bold;'>❌ " . ($thenCount - $catchCount) . " .then() sem .catch()</p>";
    } else {
        echo "<p style='color: #155724;'>✅ Promise fechada corretamente</p>";
    }
    
    // Mostrar preview do bloco
    $preview = substr($blockContent, 0, 200) . (strlen($blockContent) > 200 ? '...' : '');
    echo "<pre style='background: #f8f9fa; padding: 10px; font-size: 0.8em; overflow-x: auto;'>" . htmlspecialchars($preview) . "</pre>";
    echo "</div>";
}

echo "<hr><p><strong>🔧 Análise detalhada concluída!</strong></p>";
?>