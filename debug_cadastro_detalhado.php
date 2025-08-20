<?php
/**
 * Debug detalhado do cadastro de parceiro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/usuario.php';
require_once __DIR__ . '/config/database.php';

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

echo "<h2>Debug Detalhado do Cadastro</h2>";

try {
    // Testar conexão primeiro
    echo "<h3>1. Testando Conexão</h3>";
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Conexão OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão</p>";
        exit;
    }
    
    // Testar se as tabelas existem
    echo "<h3>2. Verificando Tabelas</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'usuarios' existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'usuarios' não existe</p>";
    }
    
    $stmt = $conn->query("SHOW TABLES LIKE 'saloes'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela 'saloes' existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela 'saloes' não existe</p>";
    }
    
    // Testar cadastro simples de usuário primeiro
    echo "<h3>3. Testando Cadastro de Usuário</h3>";
    $usuario = new Usuario();
    
    $timestamp = time();
    $dadosUsuario = [
        'nome' => 'Teste ' . $timestamp,
        'email' => 'teste' . $timestamp . '@exemplo.com',
        'telefone' => '11999887766',
        'senha' => '123456',
        'tipo_usuario' => 'parceiro'
    ];
    
    echo "<p>Dados do usuário:</p>";
    echo "<pre>" . print_r($dadosUsuario, true) . "</pre>";
    
    if ($usuario->cadastrar($dadosUsuario)) {
        echo "<p style='color: green;'>✅ Usuário cadastrado com sucesso</p>";
        
        // Obter ID do usuário
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$dadosUsuario['email']]);
        $user = $stmt->fetch();
        $usuario_id = $user ? $user['id'] : 0;
        
        echo "<p>ID do usuário: $usuario_id</p>";
        
        if ($usuario_id > 0) {
            // Testar cadastro de salão
            echo "<h3>4. Testando Cadastro de Salão</h3>";
            
            $dadosSalao = [
                'nome' => 'Salão Teste ' . $timestamp,
                'endereco' => 'Rua Teste, 123',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'cep' => '01234567',
                'telefone' => '11999887766',
                'documento' => '123456789' . substr($timestamp, -2),
                'tipo_documento' => 'cpf',
                'razao_social' => '',
                'inscricao_estadual' => '',
                'descricao' => 'Teste de cadastro online'
            ];
            
            echo "<p>Dados do salão:</p>";
            echo "<pre>" . print_r($dadosSalao, true) . "</pre>";
            
            if ($usuario->cadastrarSalao($usuario_id, $dadosSalao)) {
                echo "<p style='color: green;'>✅ Salão cadastrado com sucesso!</p>";
                
                // Verificar dados salvos
                $stmt = $conn->prepare("SELECT * FROM saloes WHERE id_dono = ?");
                $stmt->execute([$usuario_id]);
                $salao = $stmt->fetch();
                
                if ($salao) {
                    echo "<h3>5. Dados Salvos no Banco Online:</h3>";
                    echo "<pre>" . print_r($salao, true) . "</pre>";
                } else {
                    echo "<p style='color: red;'>❌ Salão não encontrado no banco</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Erro ao cadastrar salão</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Erro ao cadastrar usuário</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar</a></p>";
?>