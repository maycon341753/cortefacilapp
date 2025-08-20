<?php
/**
 * Script de teste para verificar conexão com banco de dados online
 * e testar o cadastro de parceiros
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/usuario.php';

// Forçar ambiente online para teste
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

echo "<h2>Teste de Conexão com Banco Online</h2>";

try {
    // Testar conexão
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão com banco online estabelecida com sucesso!</p>";
        
        // Verificar se as tabelas existem
        $tables = ['usuarios', 'saloes'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                echo "<p style='color: green;'>✅ Tabela '$table' existe no banco online</p>";
            } else {
                echo "<p style='color: red;'>❌ Tabela '$table' NÃO existe no banco online</p>";
            }
        }
        
        // Testar cadastro de parceiro
        echo "<h3>Teste de Cadastro de Parceiro</h3>";
        
        $usuario = new Usuario();
        
        // Dados de teste
        $dadosUsuario = [
            'nome' => 'Salão Teste Online',
            'email' => 'teste_online_' . time() . '@exemplo.com',
            'telefone' => '11999887766',
            'senha' => '123456',
            'tipo_usuario' => 'parceiro'
        ];
        
        $dadosSalao = [
            'nome' => 'Salão Teste Online',
            'endereco' => 'Rua Teste, 123',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'cep' => '01234567',
            'telefone' => '11999887766',
            'documento' => '12345678901',
            'tipo_documento' => 'cpf',
            'razao_social' => '',
            'inscricao_estadual' => '',
            'descricao' => 'Salão de teste para verificar banco online'
        ];
        
        if ($usuario->cadastrarParceiro($dadosUsuario, $dadosSalao)) {
            echo "<p style='color: green;'>✅ Parceiro cadastrado com sucesso no banco online!</p>";
            
            // Verificar se foi salvo
            $stmt = $conn->prepare("SELECT u.*, s.* FROM usuarios u LEFT JOIN saloes s ON u.id = s.id_dono WHERE u.email = ? ORDER BY u.id DESC LIMIT 1");
            $stmt->execute([$dadosUsuario['email']]);
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                echo "<h4>Dados salvos no banco online:</h4>";
                echo "<pre>";
                print_r($resultado);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro ao cadastrar parceiro no banco online</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão com banco online</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='cadastro.php?tipo=parceiro'>← Voltar para cadastro</a></p>";
?>