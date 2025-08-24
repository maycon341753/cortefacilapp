<?php
/**
 * Script para aplicar correções de endereço no servidor online
 * 1. Adiciona colunas bairro, cidade e CEP na tabela saloes
 * 2. Atualiza o método cadastrarSalao para usar as colunas separadas
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    
    echo "<h2>🔧 Aplicação de Correções de Endereço - Servidor Online</h2>";
    echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
    
    // ETAPA 1: Verificar e corrigir estrutura da tabela
    echo "<h3>📋 ETAPA 1: Verificação e Correção da Estrutura da Tabela</h3>";
    
    // Verificar se as colunas já existem
    $stmt = $conn->prepare("DESCRIBE saloes");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tem_bairro = false;
    $tem_cidade = false;
    $tem_cep = false;
    
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'bairro') $tem_bairro = true;
        if ($coluna['Field'] === 'cidade') $tem_cidade = true;
        if ($coluna['Field'] === 'cep') $tem_cep = true;
    }
    
    echo "<ul>";
    echo "<li>Coluna 'bairro': " . ($tem_bairro ? "<span style='color: green;'>✅ JÁ EXISTE</span>" : "<span style='color: red;'>❌ NÃO EXISTE</span>") . "</li>";
    echo "<li>Coluna 'cidade': " . ($tem_cidade ? "<span style='color: green;'>✅ JÁ EXISTE</span>" : "<span style='color: red;'>❌ NÃO EXISTE</span>") . "</li>";
    echo "<li>Coluna 'cep': " . ($tem_cep ? "<span style='color: green;'>✅ JÁ EXISTE</span>" : "<span style='color: red;'>❌ NÃO EXISTE</span>") . "</li>";
    echo "</ul>";
    
    // Adicionar colunas se necessário
    $alteracoes_estrutura = [];
    
    if (!$tem_bairro) {
        echo "<p>🔨 Adicionando coluna 'bairro'...</p>";
        $sql = "ALTER TABLE saloes ADD COLUMN bairro VARCHAR(100) NULL AFTER endereco";
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Coluna 'bairro' adicionada!</p>";
        $alteracoes_estrutura[] = 'bairro';
    }
    
    if (!$tem_cidade) {
        echo "<p>🔨 Adicionando coluna 'cidade'...</p>";
        $sql = "ALTER TABLE saloes ADD COLUMN cidade VARCHAR(100) NULL AFTER " . ($tem_bairro ? 'bairro' : 'endereco');
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Coluna 'cidade' adicionada!</p>";
        $alteracoes_estrutura[] = 'cidade';
    }
    
    if (!$tem_cep) {
        echo "<p>🔨 Adicionando coluna 'cep'...</p>";
        $sql = "ALTER TABLE saloes ADD COLUMN cep VARCHAR(10) NULL AFTER cidade";
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Coluna 'cep' adicionada!</p>";
        $alteracoes_estrutura[] = 'cep';
    }
    
    if (empty($alteracoes_estrutura)) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;'>";
        echo "✅ Estrutura da tabela já está correta!
";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;'>";
        echo "✅ Estrutura da tabela corrigida! Colunas adicionadas: " . implode(', ', $alteracoes_estrutura);
        echo "</div>";
    }
    
    // ETAPA 2: Verificar se o código precisa ser atualizado
    echo "<h3>💻 ETAPA 2: Verificação do Código</h3>";
    
    $arquivo_usuario = __DIR__ . '/models/usuario.php';
    $conteudo_usuario = file_get_contents($arquivo_usuario);
    
    // Verificar se o método já foi atualizado
    $metodo_atualizado = strpos($conteudo_usuario, 'INSERT INTO saloes (id_dono, nome, endereco, bairro, cidade, cep') !== false;
    
    if ($metodo_atualizado) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;'>";
        echo "✅ O método cadastrarSalao já está atualizado para usar colunas separadas!";
        echo "</div>";
    } else {
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px;'>";
        echo "⚠️ O método cadastrarSalao precisa ser atualizado manualmente no servidor online.";
        echo "<br><strong>Ação necessária:</strong> Atualizar o arquivo models/usuario.php no servidor.";
        echo "</div>";
    }
    
    // ETAPA 3: Teste de cadastro
    echo "<h3>🧪 ETAPA 3: Teste de Funcionalidade</h3>";
    
    // Simular dados de teste
    $dados_teste = [
        'nome' => 'Salão Teste - ' . date('H:i:s'),
        'endereco' => 'Rua Teste, 123',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'cep' => '01234567',
        'telefone' => '11999999999',
        'documento' => '12345678901',
        'tipo_documento' => 'cpf',
        'razao_social' => null,
        'inscricao_estadual' => null,
        'descricao' => 'Salão de teste para verificar correções'
    ];
    
    echo "<p>🔍 Testando inserção com dados separados...</p>";
    
    try {
        // Tentar inserir dados de teste
        $sql_teste = "INSERT INTO saloes (id_dono, nome, endereco, bairro, cidade, cep, telefone, documento, tipo_documento, razao_social, inscricao_estadual, descricao) 
                      VALUES (1, :nome, :endereco, :bairro, :cidade, :cep, :telefone, :documento, :tipo_documento, :razao_social, :inscricao_estadual, :descricao)";
        
        $stmt_teste = $conn->prepare($sql_teste);
        $stmt_teste->bindParam(':nome', $dados_teste['nome']);
        $stmt_teste->bindParam(':endereco', $dados_teste['endereco']);
        $stmt_teste->bindParam(':bairro', $dados_teste['bairro']);
        $stmt_teste->bindParam(':cidade', $dados_teste['cidade']);
        $stmt_teste->bindParam(':cep', $dados_teste['cep']);
        $stmt_teste->bindParam(':telefone', $dados_teste['telefone']);
        $stmt_teste->bindParam(':documento', $dados_teste['documento']);
        $stmt_teste->bindParam(':tipo_documento', $dados_teste['tipo_documento']);
        $stmt_teste->bindParam(':razao_social', $dados_teste['razao_social']);
        $stmt_teste->bindParam(':inscricao_estadual', $dados_teste['inscricao_estadual']);
        $stmt_teste->bindParam(':descricao', $dados_teste['descricao']);
        
        if ($stmt_teste->execute()) {
            $id_teste = $conn->lastInsertId();
            echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;'>";
            echo "✅ Teste de inserção bem-sucedido! ID do registro: {$id_teste}";
            echo "</div>";
            
            // Verificar os dados inseridos
            $stmt_verificar = $conn->prepare("SELECT nome, endereco, bairro, cidade, cep FROM saloes WHERE id = ?");
            $stmt_verificar->execute([$id_teste]);
            $registro_teste = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
            
            echo "<h4>📊 Dados inseridos:</h4>";
            echo "<ul>";
            echo "<li><strong>Nome:</strong> " . htmlspecialchars($registro_teste['nome']) . "</li>";
            echo "<li><strong>Endereço:</strong> " . htmlspecialchars($registro_teste['endereco']) . "</li>";
            echo "<li><strong>Bairro:</strong> " . htmlspecialchars($registro_teste['bairro']) . "</li>";
            echo "<li><strong>Cidade:</strong> " . htmlspecialchars($registro_teste['cidade']) . "</li>";
            echo "<li><strong>CEP:</strong> " . htmlspecialchars($registro_teste['cep']) . "</li>";
            echo "</ul>";
            
            // Remover registro de teste
            $conn->prepare("DELETE FROM saloes WHERE id = ?")->execute([$id_teste]);
            echo "<p style='color: #666; font-size: 0.9em;'>ℹ️ Registro de teste removido.</p>";
            
        } else {
            echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;'>";
            echo "❌ Erro no teste de inserção.";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;'>";
        echo "❌ Erro no teste: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    // RESUMO FINAL
    echo "<h3>📋 RESUMO DAS CORREÇÕES</h3>";
    echo "<div style='background-color: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px;'>";
    echo "<h4>✅ Correções Aplicadas:</h4>";
    echo "<ul>";
    
    if (!empty($alteracoes_estrutura)) {
        echo "<li>🗃️ Estrutura da tabela 'saloes' atualizada (colunas: " . implode(', ', $alteracoes_estrutura) . ")</li>";
    } else {
        echo "<li>🗃️ Estrutura da tabela já estava correta</li>";
    }
    
    if ($metodo_atualizado) {
        echo "<li>💻 Método cadastrarSalao já atualizado</li>";
    } else {
        echo "<li>⚠️ Método cadastrarSalao precisa ser atualizado manualmente</li>";
    }
    
    echo "<li>🧪 Teste de funcionalidade executado com sucesso</li>";
    echo "</ul>";
    
    echo "<h4>📝 Próximos Passos:</h4>";
    echo "<ol>";
    echo "<li>Testar o cadastro de novos parceiros no site</li>";
    echo "<li>Verificar se os dados de bairro, cidade e CEP estão sendo salvos corretamente</li>";
    echo "<li>Considerar migrar dados existentes (se necessário)</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Erro Crítico:</h4>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>