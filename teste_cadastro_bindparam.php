<?php
// Teste específico para verificar se o erro do bindParam foi corrigido
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste de Correção do bindParam() - Usuario.php</h2>";

try {
    // Incluir dependências
    require_once 'config/database.php';
    require_once 'models/usuario.php';
    
    echo "<p>✅ Dependências carregadas com sucesso</p>";
    
    // Criar conexão com banco
    $database = new Database();
    $conn = $database->connect();
    
    echo "<p>✅ Conexão com banco estabelecida</p>";
    
    // Criar instância do modelo Usuario
    $usuario = new Usuario($conn);
    
    echo "<p>✅ Modelo Usuario instanciado</p>";
    
    // Dados de teste para cadastro de cliente
    $dadosCliente = [
        'nome' => 'Teste Cliente bindParam',
        'email' => 'teste.bindparam@email.com',
        'senha' => '123456',
        'tipo_usuario' => 'cliente',
        'telefone' => '(11) 99999-9999',
        'cpf' => '123.456.789-00'
    ];
    
    echo "<p>📋 Dados de teste preparados:</p>";
    echo "<pre>" . print_r($dadosCliente, true) . "</pre>";
    
    // Verificar se email já existe antes do cadastro
    $emailExistente = $usuario->buscarPorEmail($dadosCliente['email']);
    if ($emailExistente) {
        echo "<p style='color: orange;'>⚠️ Email já existe no banco, removendo para teste...</p>";
        // Remover usuário existente para teste
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $dadosCliente['email']);
        $stmt->execute();
    }
    
    // Tentar cadastrar cliente
    echo "<p>🔄 Tentando cadastrar cliente...</p>";
    
    $resultado = $usuario->cadastrar($dadosCliente);
    
    if ($resultado) {
        echo "<p style='color: green;'>✅ SUCESSO: Cliente cadastrado sem erros de bindParam!</p>";
        
        // Verificar se foi inserido no banco
        $usuarioInserido = $usuario->buscarPorEmail($dadosCliente['email']);
        if ($usuarioInserido) {
            echo "<p style='color: green;'>✅ Cliente encontrado no banco de dados</p>";
            echo "<p>ID: " . $usuarioInserido['id'] . "</p>";
            echo "<p>Nome: " . $usuarioInserido['nome'] . "</p>";
            echo "<p>CPF: " . $usuarioInserido['cpf'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ ERRO: Falha no cadastro</p>";
        
        // Verificar logs de erro
        $errorLog = error_get_last();
        if ($errorLog) {
            echo "<p>Último erro PHP: " . $errorLog['message'] . "</p>";
        }
        
        // Verificar se a tabela existe
        try {
            $stmt = $conn->query("DESCRIBE usuarios");
            $columns = $stmt->fetchAll();
            echo "<p style='color: blue;'>📋 Estrutura da tabela usuarios:</p>";
            echo "<pre>";
            foreach ($columns as $column) {
                echo $column['Field'] . " - " . $column['Type'] . "\n";
            }
            echo "</pre>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro ao verificar tabela: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ EXCEÇÃO CAPTURADA: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ ERRO FATAL: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Teste concluído!</strong></p>";
?>