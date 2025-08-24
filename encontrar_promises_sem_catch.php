<?php
$arquivo = 'cliente/agendar.php';
$conteudo = file_get_contents($arquivo);

echo "<h2>🔍 Busca por Promises sem .catch()</h2>";

// Dividir o conteúdo em linhas
$linhas = explode("\n", $conteudo);

echo "<h3>📋 Análise linha por linha:</h3>";

$promisesSemCatch = [];
$dentroDePromise = false;
$inicioPromise = 0;
$nivelChaves = 0;
$temThen = false;
$temCatch = false;

for ($i = 0; $i < count($linhas); $i++) {
    $linha = trim($linhas[$i]);
    $numeroLinha = $i + 1;
    
    // Detectar início de promise (fetch, bloquearHorario, etc.)
    if (preg_match('/(fetch\s*\(|bloquearHorario\s*\(|CorteFacil\.ajax\.get\s*\()/', $linha)) {
        $dentroDePromise = true;
        $inicioPromise = $numeroLinha;
        $nivelChaves = 0;
        $temThen = false;
        $temCatch = false;
        echo "<p>🚀 <strong>Início de Promise na linha $numeroLinha:</strong> " . htmlspecialchars(substr($linha, 0, 80)) . "...</p>";
    }
    
    if ($dentroDePromise) {
        // Contar chaves para saber quando a promise termina
        $nivelChaves += substr_count($linha, '{') - substr_count($linha, '}');
        
        // Verificar se tem .then(
        if (preg_match('/\.then\s*\(/', $linha)) {
            $temThen = true;
            echo "<p>➡️ .then() encontrado na linha $numeroLinha</p>";
        }
        
        // Verificar se tem .catch(
        if (preg_match('/\.catch\s*\(/', $linha)) {
            $temCatch = true;
            echo "<p>🛡️ .catch() encontrado na linha $numeroLinha</p>";
        }
        
        // Se chegou ao final da promise (ponto e vírgula ou chaves zeradas)
        if ((preg_match('/;\s*$/', $linha) && $nivelChaves <= 0) || 
            ($nivelChaves <= 0 && preg_match('/}\s*\)\s*;?\s*$/', $linha))) {
            
            echo "<p>🏁 <strong>Fim de Promise na linha $numeroLinha</strong></p>";
            
            if ($temThen && !$temCatch) {
                $promisesSemCatch[] = [
                    'inicio' => $inicioPromise,
                    'fim' => $numeroLinha,
                    'codigo' => array_slice($linhas, $inicioPromise-1, $numeroLinha-$inicioPromise+1)
                ];
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "<p style='color: #721c24; font-weight: bold;'>❌ Promise sem .catch() encontrada (linhas $inicioPromise-$numeroLinha)</p>";
                echo "</div>";
            } else if ($temThen && $temCatch) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "<p style='color: #155724;'>✅ Promise com .catch() (linhas $inicioPromise-$numeroLinha)</p>";
                echo "</div>";
            }
            
            $dentroDePromise = false;
            echo "<hr>";
        }
    }
}

echo "<h3>🎯 Resultado Final:</h3>";

if (empty($promisesSemCatch)) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #155724;'>✅ Todas as Promises estão fechadas!</h4>";
    echo "<p>Não foram encontradas promises sem .catch()</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: #721c24;'>❌ " . count($promisesSemCatch) . " Promise(s) sem .catch() encontrada(s)!</h4>";
    
    foreach ($promisesSemCatch as $index => $promise) {
        echo "<div style='margin: 15px 0; padding: 10px; background: white; border-radius: 5px;'>";
        echo "<h5>Promise #" . ($index + 1) . " (Linhas {$promise['inicio']}-{$promise['fim']}):</h5>";
        echo "<pre style='background: #f8f9fa; padding: 10px; font-size: 0.8em; overflow-x: auto;'>";
        echo htmlspecialchars(implode("\n", $promise['codigo']));
        echo "</pre>";
        echo "</div>";
    }
    echo "</div>";
}

echo "<hr><p><strong>🔧 Análise concluída!</strong></p>";
?>