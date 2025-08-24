<?php
$arquivo = 'cliente/agendar.php';
$conteudo = file_get_contents($arquivo);

echo "<h2>🔍 Verificação de Promises</h2>";
echo "<p><strong>Tamanho do arquivo:</strong> " . strlen($conteudo) . " bytes</p>";

// Buscar por fetch() no arquivo inteiro
$fetchCount = preg_match_all('/fetch\s*\(/i', $conteudo);
echo "<p><strong>Fetch encontrados:</strong> $fetchCount</p>";

// Buscar por .then( e .catch(
$thenCount = preg_match_all('/\.then\s*\(/i', $conteudo);
$catchCount = preg_match_all('/\.catch\s*\(/i', $conteudo);

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📊 Contagem Total:</h3>";
echo "<p><strong>.then() encontrados:</strong> $thenCount</p>";
echo "<p><strong>.catch() encontrados:</strong> $catchCount</p>";
echo "<p><strong>Diferença:</strong> " . ($thenCount - $catchCount) . "</p>";
echo "</div>";

if ($thenCount === $catchCount) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #155724;'>✅ Todas as Promises Fechadas!</h3>";
    echo "<p>Cada .then() tem um .catch() correspondente.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ " . ($thenCount - $catchCount) . " Promises Não Fechadas!</h3>";
    echo "<p>Algumas promises precisam de .catch()</p>";
    echo "</div>";
}

// Mostrar onde estão os .then() e .catch()
preg_match_all('/\.then\s*\(/i', $conteudo, $thenMatches, PREG_OFFSET_CAPTURE);
preg_match_all('/\.catch\s*\(/i', $conteudo, $catchMatches, PREG_OFFSET_CAPTURE);

function getLineNumber($content, $offset) {
    return substr_count(substr($content, 0, $offset), "\n") + 1;
}

echo "<h3>📋 Localização:</h3>";
echo "<div style='display: flex; gap: 20px;'>";

echo "<div style='flex: 1;'>";
echo "<h4>.then() encontrados:</h4>";
echo "<ul>";
foreach ($thenMatches[0] as $match) {
    $linha = getLineNumber($conteudo, $match[1]);
    echo "<li>Linha $linha</li>";
}
echo "</ul>";
echo "</div>";

echo "<div style='flex: 1;'>";
echo "<h4>.catch() encontrados:</h4>";
echo "<ul>";
foreach ($catchMatches[0] as $match) {
    $linha = getLineNumber($conteudo, $match[1]);
    echo "<li>Linha $linha</li>";
}
echo "</ul>";
echo "</div>";

echo "</div>";
echo "<hr><p><strong>🔧 Verificação concluída!</strong></p>";
?>