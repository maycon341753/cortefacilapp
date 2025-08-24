<?php
/**
 * Teste para verificar se o salão é ativado automaticamente no cadastro do parceiro
 */

require_once 'includes/config.php';
require_once 'models/usuario.php';

echo "<h2>🧪 Teste de Ativação Automática do Salão</h2>";
echo "<p>Verificando se o salão é criado como ativo quando um parceiro se cadastra.</p>";
echo "<hr>";

try {
    $usuario = new Usuario();
    
    // Dados de teste para o usuário parceiro
    $dadosUsuario = [
        'nome' => 'Parceiro Teste Ativação',
        'email' => 'teste_ativacao_' . time() . '@teste.com',
        'senha' => 'senha123',
        'telefone' => '(11) 99999-9999',
        'tipo_usuario' => 'parceiro'
    ];
    
    // Dados de teste para o salão
    $dadosSalao = [
        'nome' => 'Salão Teste Ativação',
        'endereco' => 'Rua Teste, 123',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'cep' => '01234-567',
        'telefone' => '(11) 3333-3333',
        'documento' => '12.345.678/0001-90',
        'tipo_documento' => 'CNPJ',
        'razao_social' => 'Salão Teste Ativação LTDA',
        'inscricao_estadual' => '123456789',
        'descricao' => 'Salão de teste para verificar ativação automática'
    ];
    
    echo "<h3>1. Testando Cadastro do Parceiro</h3>";
    
    // Tentar cadastrar o parceiro
    $resultado = $usuario->cadastrarParceiro($dadosUsuario, $dadosSalao);
    
    if ($resultado) {
        echo "<p style='color: green;'>✅ Parceiro cadastrado com sucesso!</p>";
        
        // Buscar o salão recém-criado para verificar se está ativo
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT * FROM saloes WHERE nome = :nome ORDER BY id DESC LIMIT 1");
        $stmt->bindParam(':nome', $dadosSalao['nome']);
        $stmt->execute();
        $salao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($salao) {
            echo "<h3>2. Verificação do Status do Salão</h3>";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<p><strong>ID do Salão:</strong> " . $salao['id'] . "</p>";
            echo "<p><strong>Nome:</strong> " . htmlspecialchars($salao['nome']) . "</p>";
            echo "<p><strong>Status Ativo:</strong> " . ($salao['ativo'] ? '<span style="color: green; font-weight: bold;">✅ SIM (Ativo)</span>' : '<span style="color: red; font-weight: bold;">❌ NÃO (Inativo)</span>') . "</p>";
            echo "<p><strong>Data de Criação:</strong> " . $salao['created_at'] . "</p>";
            echo "</div>";
            
            if ($salao['ativo']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4>🎉 TESTE PASSOU!</h4>";
                echo "<p>O salão foi criado automaticamente como <strong>ATIVO</strong> quando o parceiro se cadastrou.</p>";
                echo "<p>A funcionalidade está funcionando corretamente!</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4>❌ TESTE FALHOU!</h4>";
                echo "<p>O salão foi criado como <strong>INATIVO</strong>. Há um problema na implementação.</p>";
                echo "</div>";
            }
            
            // Limpar dados de teste
            echo "<h3>3. Limpeza dos Dados de Teste</h3>";
            $stmt_delete_salao = $conn->prepare("DELETE FROM saloes WHERE id = :id");
            $stmt_delete_salao->bindParam(':id', $salao['id']);
            $stmt_delete_salao->execute();
            
            $stmt_delete_usuario = $conn->prepare("DELETE FROM usuarios WHERE email = :email");
            $stmt_delete_usuario->bindParam(':email', $dadosUsuario['email']);
            $stmt_delete_usuario->execute();
            
            echo "<p style='color: blue;'>🧹 Dados de teste removidos com sucesso.</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Erro: Salão não encontrado após o cadastro.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Erro ao cadastrar parceiro: " . $usuario->getError() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro durante o teste: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='cadastro.php?tipo=parceiro'>🔗 Ir para Cadastro de Parceiro</a></p>";
echo "<p><a href='parceiro/dashboard.php'>🔗 Ir para Dashboard do Parceiro</a></p>";
?>