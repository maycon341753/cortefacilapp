<?php
/**
 * Teste simples de cadastro de parceiro no banco online
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/usuario.php';

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';

echo "<h2>Teste de Cadastro Online</h2>";

try {
    $usuario = new Usuario();
    
    // Dados de teste únicos
    $timestamp = time();
    $dadosUsuario = [
        'nome' => 'Salão Teste ' . $timestamp,
        'email' => 'teste' . $timestamp . '@exemplo.com',
        'telefone' => '11999887766',
        'senha' => '123456',
        'tipo_usuario' => 'parceiro'
    ];
    
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
    
    echo "<p>Tentando cadastrar parceiro...</p>";
    
    if ($usuario->cadastrarParceiro($dadosUsuario, $dadosSalao)) {
        echo "<p style='color: green;'>✅ SUCESSO! Parceiro cadastrado no banco online!</p>";
        echo "<p>Email: " . $dadosUsuario['email'] . "</p>";
        echo "<p>Nome: " . $dadosUsuario['nome'] . "</p>";
        echo "<p>Endereço: " . $dadosSalao['endereco'] . ", " . $dadosSalao['bairro'] . ", " . $dadosSalao['cidade'] . "</p>";
        echo "<p>CEP: " . $dadosSalao['cep'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao cadastrar parceiro</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar para cadastro</a></p>";
?>