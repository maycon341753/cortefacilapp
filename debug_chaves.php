<?php
/**
 * Debug preciso para encontrar chaves desbalanceadas
 */

$arquivo = __DIR__ . '/cliente/agendar.php';
$conteudo = file_get_contents($arquivo);

// Extrair apenas o JavaScript
preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $conteudo, $matches);

echo "<h2>🔍 Debug de Chaves JavaScript</h2>";

foreach ($matches[1] as $index => $js) {
    echo "<h3>Bloco JavaScript #" . ($index + 1) . ":</h3>";
    
    $linhas = explode("\n", $js);
    $contador_chaves = 0;
    $contador_parenteses = 0;
    $problemas = [];
    
    foreach ($linhas as $num_linha => $linha) {
        $linha_limpa = trim($linha);
        if (empty($linha_limpa) || strpos($linha_limpa, '//') === 0) continue;
        
        // Contar chaves
        $abre_chaves = substr_count($linha, '{');
        $fecha_chaves = substr_count($linha, '}');
        $contador_chaves += ($abre_chaves - $fecha_chaves);
        
        // Contar parênteses
        $abre_parenteses = substr_count($linha, '(');
        $fecha_parenteses = substr_count($linha, ')');
        $contador_parenteses += ($abre_parenteses - $fecha_parenteses);
        
        // Detectar problemas
        if ($contador_chaves < 0) {
            $problemas[] = "Linha " . ($num_linha + 1) . ": Chave de fechamento sem abertura - '{$linha_limpa}'";
        }
        if ($contador_parenteses < 0) {
            $problemas[] = "Linha " . ($num_linha + 1) . ": Parêntese de fechamento sem abertura - '{$linha_limpa}'";
        }
        
        // Mostrar linhas com mudanças significativas
        if ($abre_chaves > 0 || $fecha_chaves > 0) {
            $status_chaves = $contador_chaves >= 0 ? "✅" : "❌";
            echo "<div style='font-family: monospace; font-size: 12px; margin: 2px 0;'>";
            echo "Linha " . ($num_linha + 1) . " {$status_chaves} [Saldo: {$contador_chaves}]: " . htmlspecialchars($linha_limpa);
            echo "</div>";
        }
    }
    
    echo "<div style='background: " . ($contador_chaves === 0 ? '#d4edda' : '#f8d7da') . "; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>Resultado Final:</strong><br>";
    echo "Chaves: {$contador_chaves} (" . ($contador_chaves === 0 ? '✅ Balanceadas' : '❌ Desbalanceadas') . ")<br>";
    echo "Parênteses: {$contador_parenteses} (" . ($contador_parenteses === 0 ? '✅ Balanceados' : '❌ Desbalanceados') . ")";
    echo "</div>";
    
    if (!empty($problemas)) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>⚠️ Problemas Detectados:</strong><ul>";
        foreach ($problemas as $problema) {
            echo "<li>{$problema}</li>";
        }
        echo "</ul></div>";
    }
    
    echo "<hr>";
}

echo "<p><strong>🔧 Debug concluído!</strong></p>";
?>