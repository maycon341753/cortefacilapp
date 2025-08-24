<?php
/**
 * Script para testar a sintaxe JavaScript do arquivo agendar.php
 */

echo "<h2>🔍 Teste de Sintaxe JavaScript - agendar.php</h2>";

try {
    $arquivo = __DIR__ . '/cliente/agendar.php';
    
    if (!file_exists($arquivo)) {
        throw new Exception('Arquivo não encontrado: ' . $arquivo);
    }
    
    $conteudo = file_get_contents($arquivo);
    
    // Extrair apenas o JavaScript
    preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $conteudo, $matches);
    
    if (empty($matches[1])) {
        echo "<p style='color: red;'>❌ Nenhum script JavaScript encontrado</p>";
        exit;
    }
    
    echo "<p>✅ Encontrados " . count($matches[1]) . " blocos de JavaScript</p>";
    
    foreach ($matches[1] as $index => $js) {
        echo "<h3>📄 Bloco JavaScript #" . ($index + 1) . ":</h3>";
        
        // Verificar balanceamento de chaves
        $abre_chaves = substr_count($js, '{');
        $fecha_chaves = substr_count($js, '}');
        
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p><strong>Chaves abertas:</strong> {$abre_chaves}</p>";
        echo "<p><strong>Chaves fechadas:</strong> {$fecha_chaves}</p>";
        
        if ($abre_chaves === $fecha_chaves) {
            echo "<p style='color: green;'>✅ Chaves balanceadas</p>";
        } else {
            echo "<p style='color: red;'>❌ Chaves desbalanceadas! Diferença: " . abs($abre_chaves - $fecha_chaves) . "</p>";
        }
        
        // Verificar parênteses
        $abre_parenteses = substr_count($js, '(');
        $fecha_parenteses = substr_count($js, ')');
        
        echo "<p><strong>Parênteses abertos:</strong> {$abre_parenteses}</p>";
        echo "<p><strong>Parênteses fechados:</strong> {$fecha_parenteses}</p>";
        
        if ($abre_parenteses === $fecha_parenteses) {
            echo "<p style='color: green;'>✅ Parênteses balanceados</p>";
        } else {
            echo "<p style='color: red;'>❌ Parênteses desbalanceados! Diferença: " . abs($abre_parenteses - $fecha_parenteses) . "</p>";
        }
        
        echo "</div>";
        
        // Mostrar as últimas 20 linhas do JavaScript para debug
        $linhas = explode("\n", $js);
        $total_linhas = count($linhas);
        $inicio = max(0, $total_linhas - 20);
        
        echo "<h4>📝 Últimas 20 linhas do JavaScript:</h4>";
        echo "<pre style='background: #f1f3f4; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px;'>";
        
        for ($i = $inicio; $i < $total_linhas; $i++) {
            $linha_num = $i + 1;
            $linha = htmlspecialchars($linhas[$i]);
            echo sprintf("%3d: %s\n", $linha_num, $linha);
        }
        
        echo "</pre>";
    }
    
    // Verificar padrões problemáticos
    echo "<h3>🔍 Verificações Específicas:</h3>";
    echo "<ul>";
    
    $verificacoes = [
        'addEventListener.*function.*{[^}]*$' => 'Event listeners não fechados',
        'function.*{[^}]*$' => 'Funções não fechadas',
        '\.then\([^)]*{[^}]*$' => 'Promises não fechadas',
        'if.*{[^}]*$' => 'Condicionais não fechadas'
    ];
    
    foreach ($verificacoes as $pattern => $descricao) {
        $matches_pattern = [];
        preg_match_all('/' . $pattern . '/m', $conteudo, $matches_pattern, PREG_OFFSET_CAPTURE);
        
        if (!empty($matches_pattern[0])) {
            echo "<li style='color: orange;'>⚠️ {$descricao}: " . count($matches_pattern[0]) . " ocorrências</li>";
            
            foreach ($matches_pattern[0] as $match) {
                $linha = substr_count(substr($conteudo, 0, $match[1]), "\n") + 1;
                echo "<ul><li>Linha {$linha}: " . htmlspecialchars(trim($match[0])) . "</li></ul>";
            }
        } else {
            echo "<li style='color: green;'>✅ {$descricao}: OK</li>";
        }
    }
    
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>🔧 Análise de sintaxe concluída!</strong></p>";
?>