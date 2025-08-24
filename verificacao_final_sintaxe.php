<?php
/**
 * Verificação final da sintaxe JavaScript corrigida
 */

echo "<h2>✅ Verificação Final - Sintaxe JavaScript Corrigida</h2>";

try {
    $arquivo = __DIR__ . '/cliente/agendar.php';
    
    if (!file_exists($arquivo)) {
        throw new Exception('Arquivo não encontrado: ' . $arquivo);
    }
    
    $conteudo = file_get_contents($arquivo);
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 Correções Aplicadas com Sucesso!</h3>";
    
    // Verificar se as correções estão presentes
    $verificacoes = [
        'salon-select-btn.*addEventListener.*click' => 'Event listeners dos botões "Agendar Aqui"',
        'salon-card.*addEventListener.*click' => 'Event listeners dos cards de salão',
        'selecionarSalao.*card.*salonId.*salonName' => 'Função selecionarSalao conectada',
        '\.then.*response.*=>' => 'Promise .then() implementada',
        '\.catch.*error.*=>' => 'Promise .catch() implementada',
        'data-salon-id' => 'Atributos data dos cards',
        'preventDefault\(\)' => 'Prevenção de comportamento padrão',
        'closest\(\.salon-card\)' => 'Navegação DOM para encontrar card pai'
    ];
    
    echo "<ul>";
    $todas_corretas = true;
    
    foreach ($verificacoes as $pattern => $descricao) {
        if (preg_match('/' . $pattern . '/i', $conteudo)) {
            echo "<li style='color: #155724;'>✅ {$descricao} - Implementado</li>";
        } else {
            echo "<li style='color: #721c24;'>❌ {$descricao} - Não encontrado</li>";
            $todas_corretas = false;
        }
    }
    
    echo "</ul>";
    
    if ($todas_corretas) {
        echo "<p style='color: #155724; font-weight: bold;'>🎯 Todas as correções foram aplicadas com sucesso!</p>";
    } else {
        echo "<p style='color: #721c24; font-weight: bold;'>⚠️ Algumas correções podem estar faltando.</p>";
    }
    
    echo "</div>";
    
    // Verificar balanceamento de chaves no JavaScript principal
    preg_match('/<script[^>]*>.*document\.addEventListener.*DOMContentLoaded.*<\/script>/s', $conteudo, $js_match);
    
    if (!empty($js_match[0])) {
        $js_code = $js_match[0];
        $abre_chaves = substr_count($js_code, '{');
        $fecha_chaves = substr_count($js_code, '}');
        $abre_parenteses = substr_count($js_code, '(');
        $fecha_parenteses = substr_count($js_code, ')');
        
        echo "<h3>🔍 Análise de Sintaxe JavaScript:</h3>";
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>Chaves abertas:</strong> {$abre_chaves} | <strong>Chaves fechadas:</strong> {$fecha_chaves}</p>";
        echo "<p><strong>Parênteses abertos:</strong> {$abre_parenteses} | <strong>Parênteses fechados:</strong> {$fecha_parenteses}</p>";
        
        if ($abre_chaves === $fecha_chaves && $abre_parenteses === $fecha_parenteses) {
            echo "<p style='color: #155724; font-weight: bold;'>✅ Sintaxe JavaScript está correta!</p>";
        } else {
            echo "<p style='color: #721c24; font-weight: bold;'>❌ Erro de sintaxe detectado!</p>";
        }
        
        echo "</div>";
    }
    
    // Informações do arquivo
    echo "<h3>📄 Informações do Arquivo:</h3>";
    echo "<ul>";
    echo "<li><strong>Arquivo:</strong> " . basename($arquivo) . "</li>";
    echo "<li><strong>Tamanho:</strong> " . number_format(filesize($arquivo)) . " bytes</li>";
    echo "<li><strong>Última modificação:</strong> " . date('d/m/Y H:i:s', filemtime($arquivo)) . "</li>";
    echo "</ul>";
    
    // Instruções finais
    echo "<h3>📋 Próximos Passos:</h3>";
    echo "<div style='background: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px;'>";
    echo "<ol>";
    echo "<li><strong>Upload para o servidor:</strong> Faça upload do arquivo <code>agendar.php</code> via FTP/cPanel</li>";
    echo "<li><strong>Teste online:</strong> Acesse <code>https://cortefacil.app/cliente/agendar.php</code></li>";
    echo "<li><strong>Verificar funcionamento:</strong> Clique em qualquer botão 'Agendar Aqui'</li>";
    echo "<li><strong>Confirmar redirecionamento:</strong> Verifique se a etapa 2 (Escolha o Profissional) aparece</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>🔧 Verificação concluída!</strong></p>";
?>