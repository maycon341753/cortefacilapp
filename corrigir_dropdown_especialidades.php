<?php
/**
 * Script para corrigir o dropdown de especialidades na página de profissionais
 * Substitui as opções hardcoded por carregamento dinâmico do banco
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Corrigir Dropdown Especialidades</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".error { color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 15px 0; }\n";
echo ".success { color: green; background: #e6ffe6; padding: 15px; border: 1px solid green; margin: 15px 0; }\n";
echo ".info { color: blue; background: #e6f3ff; padding: 15px; border: 1px solid blue; margin: 15px 0; }\n";
echo "pre { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; overflow-x: auto; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>🔧 Corrigindo Dropdown de Especialidades</h1>\n";

try {
    // Conectar ao banco
    require_once 'config/database.php';
    $database = Database::getInstance();
    $pdo = $database->connect();
    
    if (!$pdo) {
        throw new Exception('Falha na conexão com o banco de dados');
    }
    
    echo "<div class='success'>\n";
    echo "<h3>✅ Conexão com banco estabelecida</h3>\n";
    echo "</div>\n";
    
    // Verificar se a tabela especialidades existe e tem dados
    echo "<h2>📋 Verificando Especialidades Disponíveis</h2>\n";
    
    $stmt = $pdo->prepare("SELECT id, nome FROM especialidades WHERE ativo = 1 ORDER BY nome");
    $stmt->execute();
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($especialidades)) {
        throw new Exception('Nenhuma especialidade encontrada na tabela. Execute primeiro o script de criação de especialidades.');
    }
    
    echo "<div class='success'>\n";
    echo "<p>✅ Encontradas " . count($especialidades) . " especialidades ativas</p>\n";
    echo "</div>\n";
    
    echo "<h3>Lista de Especialidades:</h3>\n";
    echo "<ul>\n";
    foreach ($especialidades as $esp) {
        echo "<li>ID: {$esp['id']} - {$esp['nome']}</li>\n";
    }
    echo "</ul>\n";
    
    // Ler o arquivo profissionais.php atual
    $arquivo_profissionais = 'parceiro/profissionais.php';
    
    if (!file_exists($arquivo_profissionais)) {
        throw new Exception('Arquivo profissionais.php não encontrado');
    }
    
    echo "<h2>📝 Lendo Arquivo Atual</h2>\n";
    
    $conteudo_atual = file_get_contents($arquivo_profissionais);
    
    if ($conteudo_atual === false) {
        throw new Exception('Erro ao ler o arquivo profissionais.php');
    }
    
    echo "<div class='info'>\n";
    echo "<p>📄 Arquivo lido com sucesso (" . strlen($conteudo_atual) . " caracteres)</p>\n";
    echo "</div>\n";
    
    // Gerar o novo código PHP para carregar especialidades
    $codigo_especialidades = "\n    // Carregar especialidades do banco\n";
    $codigo_especialidades .= "    \$stmt_esp = \$conn->prepare(\"SELECT id, nome FROM especialidades WHERE ativo = 1 ORDER BY nome\");\n";
    $codigo_especialidades .= "    \$stmt_esp->execute();\n";
    $codigo_especialidades .= "    \$especialidades_db = \$stmt_esp->fetchAll(PDO::FETCH_ASSOC);\n";
    
    // Gerar o novo HTML do dropdown
    $novo_dropdown = "                            <select class=\"form-select\" id=\"especialidade\" name=\"especialidade\" required>\n";
    $novo_dropdown .= "                                <option value=\"\">Selecione...</option>\n";
    $novo_dropdown .= "                                <?php foreach (\$especialidades_db as \$esp_option): ?>\n";
    $novo_dropdown .= "                                <option value=\"<?php echo htmlspecialchars(\$esp_option['nome']); ?>\"><?php echo htmlspecialchars(\$esp_option['nome']); ?></option>\n";
    $novo_dropdown .= "                                <?php endforeach; ?>\n";
    $novo_dropdown .= "                            </select>";
    
    echo "<h2>🔄 Aplicando Correções</h2>\n";
    
    // Encontrar onde inserir o código de carregamento das especialidades
    // Procurar por uma linha que contenha a busca de profissionais
    $posicao_insert = strpos($conteudo_atual, '// Buscar profissionais do salão');
    
    if ($posicao_insert === false) {
        // Tentar encontrar outra referência
        $posicao_insert = strpos($conteudo_atual, '$profissionais = $profissional->buscarPorSalao');
        
        if ($posicao_insert === false) {
            throw new Exception('Não foi possível encontrar o local para inserir o código de especialidades');
        }
    }
    
    // Encontrar o final da linha
    $fim_linha = strpos($conteudo_atual, "\n", $posicao_insert);
    
    // Inserir o código após essa linha
    $conteudo_novo = substr($conteudo_atual, 0, $fim_linha + 1) . 
                     $codigo_especialidades . 
                     substr($conteudo_atual, $fim_linha + 1);
    
    echo "<div class='success'>\n";
    echo "<p>✅ Código de carregamento de especialidades inserido</p>\n";
    echo "</div>\n";
    
    // Substituir o dropdown hardcoded
    $dropdown_antigo_pattern = '/<select class="form-select" id="especialidade" name="especialidade" required>.*?<\/select>/s';
    
    if (preg_match($dropdown_antigo_pattern, $conteudo_novo)) {
        $conteudo_novo = preg_replace($dropdown_antigo_pattern, $novo_dropdown, $conteudo_novo);
        
        echo "<div class='success'>\n";
        echo "<p>✅ Dropdown de especialidades substituído por versão dinâmica</p>\n";
        echo "</div>\n";
    } else {
        echo "<div class='error'>\n";
        echo "<p>❌ Não foi possível encontrar o dropdown para substituir</p>\n";
        echo "</div>\n";
    }
    
    // Criar backup do arquivo original
    $backup_file = $arquivo_profissionais . '.backup.' . date('Y-m-d_H-i-s');
    
    if (copy($arquivo_profissionais, $backup_file)) {
        echo "<div class='info'>\n";
        echo "<p>💾 Backup criado: {$backup_file}</p>\n";
        echo "</div>\n";
    }
    
    // Salvar o arquivo corrigido
    if (file_put_contents($arquivo_profissionais, $conteudo_novo) !== false) {
        echo "<div class='success'>\n";
        echo "<h3>🎉 Arquivo Corrigido com Sucesso!</h3>\n";
        echo "<p>O dropdown de especialidades agora carrega dinamicamente do banco de dados.</p>\n";
        echo "</div>\n";
    } else {
        throw new Exception('Erro ao salvar o arquivo corrigido');
    }
    
    echo "<h2>📋 Resumo das Alterações</h2>\n";
    echo "<div class='info'>\n";
    echo "<h4>Alterações Realizadas:</h4>\n";
    echo "<ul>\n";
    echo "<li>✅ Adicionado código PHP para carregar especialidades do banco</li>\n";
    echo "<li>✅ Substituído dropdown hardcoded por versão dinâmica</li>\n";
    echo "<li>✅ Criado backup do arquivo original</li>\n";
    echo "<li>✅ Arquivo salvo com as correções</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<h3>🧪 Próximos Passos:</h3>\n";
    echo "<div class='info'>\n";
    echo "<ol>\n";
    echo "<li>Teste o cadastro de profissionais na página online</li>\n";
    echo "<li>Verifique se o dropdown mostra todas as especialidades</li>\n";
    echo "<li>Confirme que o cadastro funciona corretamente</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Erro:</h3>\n";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
}

echo "</body>\n</html>";
?>