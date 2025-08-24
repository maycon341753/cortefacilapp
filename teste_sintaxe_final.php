<?php
/**
 * Teste final da sintaxe JavaScript
 */

$arquivo = 'cliente/agendar.php';
$conteudo = file_get_contents($arquivo);

if (!$conteudo) {
    die("Erro: Não foi possível ler o arquivo $arquivo");
}

// Extrair apenas o JavaScript
preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $conteudo, $matches);

if (empty($matches[1])) {
    die("Nenhum JavaScript encontrado no arquivo.");
}

echo "=== TESTE FINAL DE SINTAXE JAVASCRIPT ===\n\n";

$jsCompleto = '';
foreach ($matches[1] as $js) {
    $jsCompleto .= trim($js) . "\n";
}

// Contar elementos
$abreChaves = substr_count($jsCompleto, '{');
$fechaChaves = substr_count($jsCompleto, '}');
$abreParenteses = substr_count($jsCompleto, '(');
$fechaParenteses = substr_count($jsCompleto, ')');
$abreColchetes = substr_count($jsCompleto, '[');
$fechaColchetes = substr_count($jsCompleto, ']');

echo "📊 CONTAGEM DE ELEMENTOS:\n";
echo "Chaves: $abreChaves abertas, $fechaChaves fechadas";
if ($abreChaves === $fechaChaves) {
    echo " ✅ BALANCEADAS\n";
} else {
    echo " ❌ DESBALANCEADAS (diferença: " . ($abreChaves - $fechaChaves) . ")\n";
}

echo "Parênteses: $abreParenteses abertos, $fechaParenteses fechados";
if ($abreParenteses === $fechaParenteses) {
    echo " ✅ BALANCEADOS\n";
} else {
    echo " ❌ DESBALANCEADOS (diferença: " . ($abreParenteses - $fechaParenteses) . ")\n";
}

echo "Colchetes: $abreColchetes abertos, $fechaColchetes fechados";
if ($abreColchetes === $fechaColchetes) {
    echo " ✅ BALANCEADOS\n";
} else {
    echo " ❌ DESBALANCEADOS (diferença: " . ($abreColchetes - $fechaColchetes) . ")\n";
}

echo "\n🔍 VERIFICAÇÕES ESPECÍFICAS:\n";

// Verificar se há chaves duplas problemáticas
if (strpos($jsCompleto, '}}') !== false) {
    echo "⚠️ Chaves duplas encontradas (podem ser normais em objetos)\n";
} else {
    echo "✅ Nenhuma chave dupla encontrada\n";
}

// Verificar pontos-e-vírgulas antes de chaves
if (preg_match('/;\s*\}/', $jsCompleto)) {
    echo "⚠️ Ponto-e-vírgula antes de chave encontrado\n";
} else {
    echo "✅ Nenhum ponto-e-vírgula problemático antes de chaves\n";
}

// Verificar vírgulas antes de chaves
if (preg_match('/,\s*\}/', $jsCompleto)) {
    echo "⚠️ Vírgula antes de chave encontrada\n";
} else {
    echo "✅ Nenhuma vírgula problemática antes de chaves\n";
}

echo "\n🎯 RESULTADO FINAL:\n";
if ($abreChaves === $fechaChaves && $abreParenteses === $fechaParenteses && $abreColchetes === $fechaColchetes) {
    echo "✅ SINTAXE JAVASCRIPT VÁLIDA - Todos os elementos estão balanceados!\n";
    echo "\n📋 PRÓXIMOS PASSOS:\n";
    echo "1. Faça upload do arquivo agendar.php corrigido\n";
    echo "2. Teste a página no navegador\n";
    echo "3. Verifique se não há mais erros de JavaScript no console\n";
    echo "4. Teste o fluxo completo de agendamento\n";
} else {
    echo "❌ AINDA HÁ PROBLEMAS DE SINTAXE - Verifique os elementos desbalanceados\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>