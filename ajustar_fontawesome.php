<?php
/**
 * SCRIPT PARA AJUSTAR LINKS DO FONT AWESOME
 * Atualiza todos os links do Font Awesome para uma versão mais recente e confiável
 */

echo "<h1>🔧 AJUSTE DOS LINKS FONT AWESOME</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .success { background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; color: #155724; }
    .error { background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; color: #721c24; }
    .warning { background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; color: #856404; }
    .info { background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; color: #0c5460; }
    .highlight { background: #e7f3ff; padding: 20px; border-left: 4px solid #007bff; margin: 20px 0; }
    h2 { color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .file-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
    code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
</style>";

echo "<div class='container'>";

// URLs antigas e nova
$url_antiga_6_0 = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
$url_antiga_6_4 = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
$url_nova = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';

// Lista de arquivos encontrados na busca
$arquivos = [
    'parceiro/dashboard.php',
    'parceiro/profissionais.php',
    'cliente/saloes.php',
    'cliente/pagamento.php',
    'admin/usuarios.php',
    'admin/dashboard.php',
    'cliente/perfil.php',
    'cliente/agendar.php',
    'cadastro.php',
    'admin/saloes.php',
    'parceiro/agendamentos.php',
    'cliente/dashboard.php',
    'index.php',
    'parceiro/salao.php',
    'admin/relatorios.php',
    'ajuda_cadastro.php',
    'parceiro/agenda.php',
    'admin/agendamentos.php',
    'cadastro_cliente_simples.php',
    'parceiro/relatorios.php',
    'cliente/pagamento-sucesso.php',
    'cadastro_corrigido.php',
    'login.php',
    'cliente/agendamentos.php',
    // Arquivos de correção/teste (opcional)
    'fix_csrf_production_final.php',
    'fix_modal_profissionais.php',
    'fix_csrf_profissionais_final.php',
    'teste_responsivo.html'
];

echo "<h2>📋 1. Informações do Ajuste</h2>";
echo "<div class='info'>";
echo "<h4>🔄 Substituições que serão feitas:</h4>";
echo "<p><strong>De:</strong> <code>{$url_antiga_6_0}</code></p>";
echo "<p><strong>De:</strong> <code>{$url_antiga_6_4}</code></p>";
echo "<p><strong>Para:</strong> <code>{$url_nova}</code></p>";
echo "<p><strong>Versão:</strong> Font Awesome 6.5.1 (mais recente e estável)</p>";
echo "</div>";

echo "<h2>🔧 2. Processando Arquivos</h2>";

$arquivos_processados = 0;
$arquivos_atualizados = 0;
$erros = [];

foreach ($arquivos as $arquivo) {
    $caminho_completo = __DIR__ . '/' . $arquivo;
    
    if (!file_exists($caminho_completo)) {
        echo "<div class='warning'>⚠️ Arquivo não encontrado: {$arquivo}</div>";
        continue;
    }
    
    $arquivos_processados++;
    
    try {
        // Ler conteúdo do arquivo
        $conteudo = file_get_contents($caminho_completo);
        $conteudo_original = $conteudo;
        
        // Fazer as substituições
        $conteudo = str_replace($url_antiga_6_0, $url_nova, $conteudo);
        $conteudo = str_replace($url_antiga_6_4, $url_nova, $conteudo);
        
        // Verificar se houve mudanças
        if ($conteudo !== $conteudo_original) {
            // Salvar arquivo atualizado
            if (file_put_contents($caminho_completo, $conteudo)) {
                echo "<div class='success'>✅ Atualizado: {$arquivo}</div>";
                $arquivos_atualizados++;
            } else {
                echo "<div class='error'>❌ Erro ao salvar: {$arquivo}</div>";
                $erros[] = $arquivo;
            }
        } else {
            echo "<div class='info'>ℹ️ Sem alterações necessárias: {$arquivo}</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro ao processar {$arquivo}: " . $e->getMessage() . "</div>";
        $erros[] = $arquivo;
    }
}

echo "<h2>📊 3. Resumo do Processamento</h2>";

echo "<div class='highlight'>";
echo "<h4>📈 Estatísticas:</h4>";
echo "<ul>";
echo "<li><strong>Arquivos processados:</strong> {$arquivos_processados}</li>";
echo "<li><strong>Arquivos atualizados:</strong> {$arquivos_atualizados}</li>";
echo "<li><strong>Erros encontrados:</strong> " . count($erros) . "</li>";
echo "</ul>";
echo "</div>";

if (count($erros) > 0) {
    echo "<div class='error'>";
    echo "<h4>❌ Arquivos com erro:</h4>";
    echo "<ul>";
    foreach ($erros as $erro) {
        echo "<li>{$erro}</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if ($arquivos_atualizados > 0) {
    echo "<div class='success'>";
    echo "<h4>🎉 AJUSTE CONCLUÍDO COM SUCESSO!</h4>";
    echo "<p>✅ {$arquivos_atualizados} arquivos foram atualizados com a nova versão do Font Awesome.</p>";
    echo "<p>✅ A versão 6.5.1 é mais estável e deve resolver problemas de carregamento.</p>";
    echo "<p>✅ Todos os ícones devem funcionar normalmente.</p>";
    echo "</div>";
}

echo "<h2>🧪 4. Teste da Nova URL</h2>";

echo "<div class='info'>";
echo "<h4>🔗 Testando conectividade com a nova URL:</h4>";
echo "<p><strong>URL:</strong> <code>{$url_nova}</code></p>";

// Testar se a URL está acessível
$headers = @get_headers($url_nova);
if ($headers && strpos($headers[0], '200') !== false) {
    echo "<div class='success'>✅ URL acessível - Font Awesome carregará corretamente</div>";
} else {
    echo "<div class='warning'>⚠️ Não foi possível verificar a URL (pode ser devido a configurações do servidor)</div>";
}
echo "</div>";

// Exemplo visual
echo "<h2>🎨 5. Teste Visual</h2>";
echo "<div class='info'>";
echo "<h4>👁️ Teste dos ícones Font Awesome:</h4>";
echo "<link href='{$url_nova}' rel='stylesheet'>";
echo "<div style='font-size: 24px; margin: 20px 0;'>";
echo "<i class='fas fa-home' style='margin: 10px; color: #007bff;'></i>";
echo "<i class='fas fa-user' style='margin: 10px; color: #28a745;'></i>";
echo "<i class='fas fa-calendar' style='margin: 10px; color: #ffc107;'></i>";
echo "<i class='fas fa-cog' style='margin: 10px; color: #6c757d;'></i>";
echo "<i class='fas fa-heart' style='margin: 10px; color: #dc3545;'></i>";
echo "</div>";
echo "<p><small>Se você vê os ícones acima, o Font Awesome está funcionando corretamente!</small></p>";
echo "</div>";

echo "<h2>📋 6. Próximos Passos</h2>";

echo "<div class='highlight'>";
echo "<h4>🚀 Recomendações:</h4>";
echo "<ol>";
echo "<li><strong>Teste as páginas:</strong> Verifique se os ícones estão aparecendo corretamente</li>";
echo "<li><strong>Limpe o cache:</strong> Limpe o cache do navegador para ver as mudanças</li>";
echo "<li><strong>Monitore:</strong> Observe se não há mais erros de carregamento do Font Awesome</li>";
echo "<li><strong>Backup:</strong> Considere fazer backup dos arquivos antes de fazer upload para produção</li>";
echo "</ol>";
echo "</div>";

if ($arquivos_atualizados > 0) {
    echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; margin: 30px 0; text-align: center;'>";
    echo "<h3 style='margin-top: 0; color: white;'>✅ FONT AWESOME ATUALIZADO!</h3>";
    echo "<p style='margin: 10px 0; opacity: 0.9;'>Todos os links foram atualizados para a versão 6.5.1</p>";
    echo "<p style='margin: 10px 0; opacity: 0.9;'>Os erros de carregamento devem estar resolvidos</p>";
    echo "<p style='margin-bottom: 0; font-size: 14px; opacity: 0.8;'>Atualização realizada em: " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";
}

echo "</div>"; // Fechar container
?>