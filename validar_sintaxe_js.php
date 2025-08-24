<?php
/**
 * Script para encontrar erro de sintaxe JavaScript específico
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

echo "=== BUSCA POR ERRO DE SINTAXE ===\n\n";

$jsCompleto = '';
foreach ($matches[1] as $js) {
    $jsCompleto .= trim($js) . "\n";
}

// Dividir em linhas para análise
$linhas = explode("\n", $jsCompleto);
$totalLinhas = count($linhas);

echo "Total de linhas JavaScript: $totalLinhas\n\n";

// Procurar por padrões específicos que causam 'Unexpected token }'
$problemasEncontrados = [];

for ($i = 0; $i < $totalLinhas; $i++) {
    $linha = trim($linhas[$i]);
    $numeroLinha = $i + 1;
    
    // Verificar chaves isoladas ou mal posicionadas
    if (preg_match('/^\s*\}\s*$/', $linha)) {
        // Chave isolada - verificar contexto
        $contextoAntes = '';
        $contextoDepois = '';
        
        for ($j = max(0, $i - 3); $j < $i; $j++) {
            $contextoAntes .= ($j + 1) . ": " . trim($linhas[$j]) . "\n";
        }
        
        for ($j = $i + 1; $j < min($totalLinhas, $i + 4); $j++) {
            $contextoDepois .= ($j + 1) . ": " . trim($linhas[$j]) . "\n";
        }
        
        echo "CHAVE ISOLADA na linha $numeroLinha:\n";
        echo "Contexto antes:\n$contextoAntes";
        echo ">>> $numeroLinha: $linha <<<\n";
        echo "Contexto depois:\n$contextoDepois\n";
    }
    
    // Verificar chaves duplas
    if (strpos($linha, '}}') !== false) {
        echo "CHAVES DUPLAS na linha $numeroLinha: $linha\n";
        
        // Mostrar contexto
        for ($j = max(0, $i - 2); $j <= min($totalLinhas - 1, $i + 2); $j++) {
            $marcador = ($j == $i) ? '>>> ' : '    ';
            echo "$marcador" . ($j + 1) . ": " . trim($linhas[$j]) . "\n";
        }
        echo "\n";
    }
    
    // Verificar estruturas incompletas
    if (preg_match('/\{\s*$/', $linha) && $i + 1 < $totalLinhas) {
        $proximaLinha = trim($linhas[$i + 1]);
        if ($proximaLinha === '}') {
            echo "BLOCO VAZIO nas linhas $numeroLinha-" . ($numeroLinha + 1) . ":\n";
            echo "$numeroLinha: $linha\n";
            echo ($numeroLinha + 1) . ": $proximaLinha\n\n";
        }
    }
    
    // Verificar vírgulas ou pontos-e-vírgulas antes de chaves
    if (preg_match('/[,;]\s*\}/', $linha)) {
        echo "VÍRGULA/PONTO-E-VÍRGULA ANTES DE CHAVE na linha $numeroLinha: $linha\n\n";
    }
}

// Análise de balanceamento por seções
echo "=== ANÁLISE DE BALANCEAMENTO POR SEÇÕES ===\n\n";

$nivelChaves = 0;
$secaoAtual = 1;
$inicioSecao = 0;

for ($i = 0; $i < $totalLinhas; $i++) {
    $linha = $linhas[$i];
    $numeroLinha = $i + 1;
    
    $abreChaves = substr_count($linha, '{');
    $fechaChaves = substr_count($linha, '}');
    
    $nivelChaves += $abreChaves - $fechaChaves;
    
    if ($nivelChaves < 0) {
        echo "❌ ERRO: Chave de fechamento sem abertura na linha $numeroLinha\n";
        echo "Linha: " . trim($linha) . "\n";
        echo "Nível de chaves: $nivelChaves\n\n";
        break;
    }
    
    if ($nivelChaves == 0 && ($abreChaves > 0 || $fechaChaves > 0)) {
        echo "Seção $secaoAtual completa (linhas " . ($inicioSecao + 1) . "-$numeroLinha)\n";
        $secaoAtual++;
        $inicioSecao = $i;
    }
}

if ($nivelChaves != 0) {
    echo "❌ ERRO FINAL: Chaves desbalanceadas. Nível final: $nivelChaves\n";
    if ($nivelChaves > 0) {
        echo "Faltam $nivelChaves chaves de fechamento\n";
    } else {
        echo "Há " . abs($nivelChaves) . " chaves de fechamento em excesso\n";
    }
} else {
    echo "✅ Chaves balanceadas globalmente\n";
}

echo "\n=== ANÁLISE CONCLUÍDA ===\n";
?>