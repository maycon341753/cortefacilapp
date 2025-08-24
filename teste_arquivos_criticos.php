<?php
/**
 * Teste de Arquivos Críticos do Dashboard
 * Verifica se todos os arquivos necessários existem
 */

echo "<h2>🔍 Verificação de Arquivos Críticos</h2>";

// Lista de arquivos críticos
$arquivos_criticos = [
    '../includes/auth.php',
    '../includes/functions.php', 
    '../models/salao.php',
    '../models/profissional.php',
    '../models/agendamento.php'
];

echo "<h3>Verificando existência dos arquivos:</h3>";
echo "<ul>";

$todos_existem = true;
foreach ($arquivos_criticos as $arquivo) {
    $caminho_absoluto = realpath(__DIR__ . '/parceiro/' . $arquivo);
    $existe = file_exists(__DIR__ . '/parceiro/' . $arquivo);
    
    echo "<li>";
    echo "<strong>$arquivo</strong>: ";
    
    if ($existe) {
        echo "<span style='color: green'>✅ EXISTE</span>";
        echo " (Caminho: $caminho_absoluto)";
    } else {
        echo "<span style='color: red'>❌ NÃO ENCONTRADO</span>";
        $todos_existem = false;
    }
    
    echo "</li>";
}

echo "</ul>";

echo "<h3>Resultado:</h3>";
if ($todos_existem) {
    echo "<p style='color: green'><strong>✅ Todos os arquivos críticos existem!</strong></p>";
    
    // Tentar incluir cada arquivo
    echo "<h3>Testando inclusão dos arquivos:</h3>";
    echo "<ul>";
    
    foreach ($arquivos_criticos as $arquivo) {
        echo "<li>";
        echo "<strong>$arquivo</strong>: ";
        
        try {
            $caminho_completo = __DIR__ . '/parceiro/' . $arquivo;
            require_once $caminho_completo;
            echo "<span style='color: green'>✅ INCLUÍDO COM SUCESSO</span>";
        } catch (Exception $e) {
            echo "<span style='color: red'>❌ ERRO: " . $e->getMessage() . "</span>";
        } catch (Error $e) {
            echo "<span style='color: red'>❌ ERRO FATAL: " . $e->getMessage() . "</span>";
        }
        
        echo "</li>";
    }
    
    echo "</ul>";
    
} else {
    echo "<p style='color: red'><strong>❌ Alguns arquivos críticos estão faltando!</strong></p>";
}

echo "<h3>Informações adicionais:</h3>";
echo "<ul>";
echo "<li><strong>Diretório atual:</strong> " . __DIR__ . "</li>";
echo "<li><strong>Diretório parceiro:</strong> " . __DIR__ . "/parceiro</li>";
echo "<li><strong>Diretório includes:</strong> " . __DIR__ . "/includes</li>";
echo "<li><strong>Diretório models:</strong> " . __DIR__ . "/models</li>";
echo "</ul>";
?>