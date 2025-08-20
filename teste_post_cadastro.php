<?php
/**
 * Teste simulando POST do formulário de cadastro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Forçar ambiente online
$_SERVER['SERVER_NAME'] = 'cortefacil.app';
$_SERVER['HTTP_HOST'] = 'cortefacil.app';
$_SERVER['HTTPS'] = 'on';
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "<h2>Teste POST - Cadastro Parceiro</h2>";

// Simular dados POST como se viessem do formulário
$timestamp = time();

// Gerar CPF único válido
function gerarCPFValido() {
    $cpf = '';
    for ($i = 0; $i < 9; $i++) {
        $cpf .= rand(0, 9);
    }
    
    // Calcular dígitos verificadores
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }
    $soma += $dv1 * 2;
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . $dv1 . $dv2;
}

$_POST = [
    'nome' => 'Teste POST ' . $timestamp,
    'email' => 'post_' . $timestamp . '@teste.com',
    'telefone' => '(11) 99988-7766',
    'senha' => '123456',
    'confirmar_senha' => '123456',
    'tipo_usuario' => 'parceiro',
    'documento' => gerarCPFValido(),
    'tipo_documento' => 'cpf',
    'razao_social' => '',
    'inscricao_estadual' => '',
    'endereco' => 'Rua Teste POST, 123',
    'bairro' => 'Centro',
    'cidade' => 'São Paulo',
    'cep' => '01234-567'
];

echo "<h3>Dados POST simulados:</h3>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

try {
    // Incluir o processamento do cadastro.php
    ob_start();
    include 'cadastro.php';
    $output = ob_get_clean();
    
    echo "<h3>Resultado do processamento:</h3>";
    
    // Verificar se houve redirecionamento (sucesso)
    if (headers_sent()) {
        echo "<p style='color: green;'>✅ Headers enviados - provavelmente redirecionou (sucesso)</p>";
    }
    
    // Mostrar parte do output (sem todo o HTML)
    if (strpos($output, 'Erro ao realizar cadastro') !== false) {
        echo "<p style='color: red;'>❌ Erro encontrado no output</p>";
    } elseif (strpos($output, 'sucesso') !== false) {
        echo "<p style='color: green;'>✅ Sucesso encontrado no output</p>";
    }
    
    // Mostrar apenas mensagens de erro se houver
    if (preg_match('/<div class="alert alert-danger"[^>]*>(.*?)<\/div>/s', $output, $matches)) {
        echo "<h3>Mensagem de erro:</h3>";
        echo "<div style='color: red; border: 1px solid red; padding: 10px;'>" . $matches[1] . "</div>";
    }
    
    // Mostrar apenas mensagens de sucesso se houver
    if (preg_match('/<div class="alert alert-success"[^>]*>(.*?)<\/div>/s', $output, $matches)) {
        echo "<h3>Mensagem de sucesso:</h3>";
        echo "<div style='color: green; border: 1px solid green; padding: 10px;'>" . $matches[1] . "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exceção: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
}

echo "<hr><p><a href='cadastro.php?tipo=parceiro'>← Voltar para cadastro</a></p>";
?>