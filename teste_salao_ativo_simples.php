<?php
/**
 * Teste simples para verificar se o salão é criado como ativo
 */

require_once 'includes/config.php';
require_once 'models/usuario.php';

echo "<h2>🧪 Teste Simples - Salão Ativo</h2>";
echo "<p>Testando diretamente o método cadastrarSalao com campo ativo.</p>";
echo "<hr>";

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar um usuário de teste primeiro
    $usuario = new Usuario();
    $dadosUsuario = [
        'nome' => 'Teste Salão Ativo',
        'email' => 'teste_salao_' . time() . '@teste.com',
        'senha' => 'senha123',
        'telefone' => '(11) 99999-9999',
        'tipo_usuario' => 'parceiro'
    ];
    
    echo "<h3>1. Criando usuário de teste</h3>";
    $resultadoUsuario = $usuario->cadastrar($dadosUsuario);
    
    if ($resultadoUsuario) {
        $usuario_id = $conn->lastInsertId();
        echo "<p style='color: green;'>✅ Usuário criado com ID: $usuario_id</p>";
        
        // Dados do salão
        $dadosSalao = [
            'nome' => 'Salão Teste Ativo',
            'endereco' => 'Rua Teste, 123',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'cep' => '01234-567',
            'telefone' => '(11) 3333-3333',
            'documento' => '12345678901',
            'tipo_documento' => 'CPF',
            'razao_social' => null,
            'inscricao_estadual' => null,
            'descricao' => 'Salão de teste'
        ];
        
        echo "<h3>2. Criando salão</h3>";
        $resultadoSalao = $usuario->cadastrarSalao($usuario_id, $dadosSalao);
        
        if ($resultadoSalao) {
            $salao_id = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Salão criado com ID: $salao_id</p>";
            
            // Verificar se o salão está ativo
            $stmt = $conn->prepare("SELECT * FROM saloes WHERE id = :id");
            $stmt->bindParam(':id', $salao_id);
            $stmt->execute();
            $salao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($salao) {
                echo "<h3>3. Verificação do Status</h3>";
                echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>Nome:</strong> " . htmlspecialchars($salao['nome']) . "</p>";
                echo "<p><strong>Campo 'ativo' existe:</strong> " . (array_key_exists('ativo', $salao) ? '✅ SIM' : '❌ NÃO') . "</p>";
                
                if (array_key_exists('ativo', $salao)) {
                    echo "<p><strong>Valor do campo 'ativo':</strong> " . ($salao['ativo'] ? '<span style="color: green; font-weight: bold;">✅ 1 (Ativo)</span>' : '<span style="color: red; font-weight: bold;">❌ 0 (Inativo)</span>') . "</p>";
                    
                    if ($salao['ativo']) {
                        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<strong>🎉 SUCESSO!</strong> O salão foi criado como ATIVO automaticamente.";
                        echo "</div>";
                    } else {
                        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                        echo "<strong>❌ PROBLEMA!</strong> O salão foi criado como INATIVO.";
                        echo "</div>";
                    }
                } else {
                    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<strong>⚠️ ATENÇÃO!</strong> O campo 'ativo' não existe na tabela saloes.";
                    echo "</div>";
                }
                echo "</div>";
                
                // Mostrar todos os campos da tabela
                echo "<h4>Todos os campos do salão:</h4>";
                echo "<pre>" . print_r($salao, true) . "</pre>";
            }
            
            // Limpar dados de teste
            echo "<h3>4. Limpeza</h3>";
            $stmt_delete_salao = $conn->prepare("DELETE FROM saloes WHERE id = :id");
            $stmt_delete_salao->bindParam(':id', $salao_id);
            $stmt_delete_salao->execute();
            
            $stmt_delete_usuario = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt_delete_usuario->bindParam(':id', $usuario_id);
            $stmt_delete_usuario->execute();
            
            echo "<p style='color: blue;'>🧹 Dados de teste removidos.</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar salão.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Erro ao criar usuário: " . $usuario->getError() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='cadastro.php?tipo=parceiro'>🔗 Cadastro de Parceiro</a></p>";
?>