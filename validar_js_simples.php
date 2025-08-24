<?php
/**
 * Validação simples de sintaxe JavaScript
 */

$arquivo = __DIR__ . '/cliente/agendar.php';
$conteudo = file_get_contents($arquivo);

// Extrair todo o JavaScript
preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $conteudo, $matches);

echo "<h2>✅ Validação JavaScript Simples</h2>";

$js_completo = '';
foreach ($matches[1] as $js) {
    $js_completo .= $js . "\n";
}

// Contar chaves e parênteses no JavaScript completo
$abre_chaves = substr_count($js_completo, '{');
$fecha_chaves = substr_count($js_completo, '}');
$abre_parenteses = substr_count($js_completo, '(');
$fecha_parenteses = substr_count($js_completo, ')');

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📊 Contagem Total:</h3>";
echo "<p><strong>Chaves abertas:</strong> {$abre_chaves}</p>";
echo "<p><strong>Chaves fechadas:</strong> {$fecha_chaves}</p>";
echo "<p><strong>Diferença chaves:</strong> " . ($abre_chaves - $fecha_chaves) . "</p>";
echo "<hr>";
echo "<p><strong>Parênteses abertos:</strong> {$abre_parenteses}</p>";
echo "<p><strong>Parênteses fechados:</strong> {$fecha_parenteses}</p>";
echo "<p><strong>Diferença parênteses:</strong> " . ($abre_parenteses - $fecha_parenteses) . "</p>";
echo "</div>";

$chaves_ok = ($abre_chaves === $fecha_chaves);
$parenteses_ok = ($abre_parenteses === $fecha_parenteses);

if ($chaves_ok && $parenteses_ok) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #155724;'>🎉 JavaScript Válido!</h3>";
    echo "<p>Todas as chaves e parênteses estão balanceados.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Erro de Sintaxe!</h3>";
    
    if (!$chaves_ok) {
        $diff_chaves = $abre_chaves - $fecha_chaves;
        if ($diff_chaves > 0) {
            echo "<p>Faltam {$diff_chaves} chave(s) de fechamento '}'</p>";
        } else {
            echo "<p>Há " . abs($diff_chaves) . " chave(s) de fechamento '}' em excesso</p>";
        }
    }
    
    if (!$parenteses_ok) {
        $diff_parenteses = $abre_parenteses - $fecha_parenteses;
        if ($diff_parenteses > 0) {
            echo "<p>Faltam {$diff_parenteses} parêntese(s) de fechamento ')'</p>";
        } else {
            echo "<p>Há " . abs($diff_parenteses) . " parêntese(s) de fechamento ')' em excesso</p>";
        }
    }
    
    echo "</div>";
}

// Teste de sintaxe usando node.js se disponível
echo "<h3>🧪 Teste de Sintaxe (Node.js):</h3>";

$temp_file = tempnam(sys_get_temp_dir(), 'js_test_');
file_put_contents($temp_file, $js_completo);

$output = [];
$return_var = 0;
exec("node -c {$temp_file} 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
    echo "<p style='color: #155724;'>✅ Sintaxe JavaScript válida (Node.js)</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px;'>";
    echo "<p style='color: #721c24;'>❌ Erro de sintaxe detectado:</p>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 3px;'>" . implode("\n", $output) . "</pre>";
    echo "</div>";
}

unlink($temp_file);

echo "<hr>";
echo "<p><strong>🔧 Validação concluída!</strong></p>";
?>