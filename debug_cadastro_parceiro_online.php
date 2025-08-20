<?php
/**
 * Debug específico para cadastro de parceiros online
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/usuario.php';

echo "<h2>Debug - Cadastro Parceiro Online</h2>";

try {
    // Dados de teste únicos
    $timestamp = time();
    $dadosUsuario = [
        'nome' => 'Parceiro Debug ' . $timestamp,
        'email' => 'debug_' . $timestamp . '@teste.com',
        'telefone' => '11999887766',
        'senha' => '123456',
        'tipo_usuario' => 'parceiro'
    ];
    
    $dadosSalao = [
        'nome' => 'Salão Debug ' . $timestamp,
        'endereco' => 'Rua Debug, 123',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'cep' => '01234567',
        'telefone' => '11999887766',
        'documento' => '12345678' . substr($timestamp, -3), // CPF único
        'tipo_documento' => 'cpf',
        'razao_social' => '',
        'inscricao_estadual' => '',
        'descricao' => 'Salão de teste debug'
    ];
    
    echo "<h3>Dados do Usuário:</h3>";
    echo "<pre>" . print_r($dadosUsuario, true) . "</pre>";
    
    echo "<h3>Dados do Salão:</h3>";
    echo "<pre>" . print_r($dadosSalao, true) . "</pre>";
    
    $usuario = new Usuario();
    
    echo "<h3>Testando cadastro...</h3>";
    
    // Verificar se email já existe
    if ($usuario->emailExiste($dadosUsuario['email'])) {
        echo "<p style='color: orange;'>⚠️ Email já existe, gerando novo...</p>";
        $dadosUsuario['email'] = 'debug_' . (time() + rand(1, 1000)) . '@teste.com';
    }
    
    // Verificar se documento já existe
    if ($usuario->documentoSalaoExiste($dadosSalao['documento'])) {
        echo "<p style='color: orange;'>⚠️ Documento já existe, gerando novo...</p>";
        $dadosSalao['documento'] = '12345678' . rand(100, 999);
    }
    
    echo "<p>Iniciando cadastro de parceiro...</p>";
    
    if ($usuario->cadastrarParceiro($dadosUsuario, $dadosSalao)) {
        echo "<p style='color: green;'>✅ SUCESSO! Parceiro cadastrado com sucesso!</p>";
        echo "<p>Email: " . $dadosUsuario['email'] . "</p>";
        echo "<p>Nome do Salão: " . $dadosSalao['nome'] . "</p>";
        echo "<p>Documento: " . $dadosSalao['documento'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ ERRO! Falha no cadastro do parceiro</p>";
        
        // Tentar identificar onde está o erro
        echo "<h3>Testando cadastro de usuário separadamente:</h3>";
        if ($usuario->cadastrar($dadosUsuario)) {
            echo "<p style='color: green;'>✅ Usuário cadastrado OK</p>";
            $usuario_id = $usuario->conn->lastInsertId();
            
            echo "<h3>Testando cadastro de salão separadamente:</h3>";
            if ($usuario->cadastrarSalao($usuario_id, $dadosSalao)) {
                echo "<p style='color: green;'>✅ Salão cadastrado OK</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro no cadastro do salão</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro no cadastro do usuário</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exceção: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar para cadastro</a></p>";
?>