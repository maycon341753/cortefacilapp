<?php
/**
 * Debug específico para verificar o campo ativo na tabela saloes
 */

require_once 'includes/config.php';

echo "<h2>🐛 Debug - Campo 'ativo' na Tabela Salões</h2>";
echo "<hr>";

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>1. Teste de Existência do Campo 'ativo'</h3>";
    
    // Tentar fazer uma consulta que usa o campo ativo
    try {
        $stmt = $conn->query("SELECT ativo FROM saloes LIMIT 1");
        echo "<p style='color: green;'>✅ Campo 'ativo' existe e é acessível!</p>";
        
        // Verificar se há dados
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            echo "<p>Valor do campo 'ativo' no primeiro registro: <strong>" . ($resultado['ativo'] ? 'TRUE (1)' : 'FALSE (0)') . "</strong></p>";
        } else {
            echo "<p>Tabela saloes está vazia.</p>";
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Unknown column 'ativo'") !== false) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<p><strong>❌ PROBLEMA IDENTIFICADO!</strong></p>";
            echo "<p>O campo 'ativo' NÃO existe na tabela saloes.</p>";
            echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
            
            echo "<h3>2. Adicionando o Campo 'ativo'</h3>";
            try {
                $conn->exec("ALTER TABLE saloes ADD COLUMN ativo TINYINT(1) DEFAULT 1");
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>✅ SUCESSO!</strong></p>";
                echo "<p>Campo 'ativo' adicionado com sucesso à tabela saloes!</p>";
                echo "<p>Valor padrão: 1 (ativo)</p>";
                echo "</div>";
                
                // Testar novamente
                echo "<h3>3. Teste Após Adição do Campo</h3>";
                $stmt = $conn->query("SELECT COUNT(*) as total, SUM(ativo) as ativos FROM saloes");
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Total de salões: <strong>" . $stats['total'] . "</strong></p>";
                echo "<p>Salões ativos: <strong style='color: green;'>" . $stats['ativos'] . "</strong></p>";
                
            } catch (PDOException $e2) {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>❌ ERRO ao adicionar campo!</strong></p>";
                echo "<p>" . $e2->getMessage() . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro inesperado: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>4. Estrutura Atual da Tabela</h3>";
    $stmt = $conn->query("SHOW COLUMNS FROM saloes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    foreach ($colunas as $coluna) {
        $destaque = ($coluna['Field'] === 'ativo') ? "style='background: #d4edda;'" : "";
        echo "<tr $destaque>";
        echo "<td>" . htmlspecialchars($coluna['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>5. Teste do Método cadastrarSalao</h3>";
    
    // Agora testar se o método funciona
    require_once 'models/usuario.php';
    $usuario = new Usuario();
    
    // Criar usuário de teste
    $dadosUsuario = [
        'nome' => 'Teste Debug Campo Ativo',
        'email' => 'debug_ativo_' . time() . '@teste.com',
        'senha' => 'senha123',
        'telefone' => '(11) 99999-9999',
        'tipo_usuario' => 'parceiro'
    ];
    
    $resultadoUsuario = $usuario->cadastrar($dadosUsuario);
    
    if ($resultadoUsuario) {
        $usuario_id = $conn->lastInsertId();
        echo "<p style='color: green;'>✅ Usuário de teste criado (ID: $usuario_id)</p>";
        
        $dadosSalao = [
            'nome' => 'Salão Debug Ativo',
            'endereco' => 'Rua Debug, 123',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'cep' => '01234-567',
            'telefone' => '(11) 3333-3333',
            'documento' => '12345678901',
            'tipo_documento' => 'CPF',
            'descricao' => 'Salão de teste para debug'
        ];
        
        $resultadoSalao = $usuario->cadastrarSalao($usuario_id, $dadosSalao);
        
        if ($resultadoSalao) {
            $salao_id = $conn->lastInsertId();
            echo "<p style='color: green;'>✅ Salão criado (ID: $salao_id)</p>";
            
            // Verificar se está ativo
            $stmt = $conn->prepare("SELECT ativo FROM saloes WHERE id = :id");
            $stmt->bindParam(':id', $salao_id);
            $stmt->execute();
            $salao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($salao && $salao['ativo']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>🎉 TESTE PASSOU!</strong></p>";
                echo "<p>O salão foi criado como ATIVO automaticamente!</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>❌ TESTE FALHOU!</strong></p>";
                echo "<p>O salão não foi criado como ativo.</p>";
                echo "</div>";
            }
            
            // Limpar dados de teste
            $conn->exec("DELETE FROM saloes WHERE id = $salao_id");
            $conn->exec("DELETE FROM usuarios WHERE id = $usuario_id");
            echo "<p style='color: blue;'>🧹 Dados de teste removidos.</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar salão.</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Erro ao criar usuário de teste.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro geral: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='cadastro.php?tipo=parceiro'>🔗 Testar Cadastro Real</a></p>";
?>