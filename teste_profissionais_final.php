<?php
/**
 * Teste Final da Página de Profissionais com Simulação de Sessão
 * Para verificar se o erro 500 é causado por falta de autenticação
 */

// Simular sessão de parceiro logado
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'teste@exemplo.com';
$_SESSION['usuario_telefone'] = '11999999999';
$_SESSION['salao_id'] = 1;

// Configurar cabeçalhos para UTF-8
header('Content-Type: text/html; charset=utf-8');

// Iniciar buffer de saída para capturar erros
ob_start();

// Incluir o arquivo original para testar
try {
    echo "<!DOCTYPE html>\n<html lang='pt-BR'>\n<head>\n";
    echo "<meta charset='UTF-8'>\n";
    echo "<title>Teste de Profissionais</title>\n";
    echo "<style>\n";
    echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
    echo ".error { color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 15px 0; }\n";
    echo ".success { color: green; background: #e6ffe6; padding: 15px; border: 1px solid green; margin: 15px 0; }\n";
    echo "</style>\n";
    echo "</head>\n<body>\n";
    
    echo "<h1>🧪 Teste Final de Profissionais com Sessão Simulada</h1>\n";
    echo "<div class='success'>\n";
    echo "<p><strong>Sessão simulada com sucesso:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Usuário ID: " . $_SESSION['usuario_id'] . "</li>\n";
    echo "<li>Tipo: " . $_SESSION['tipo_usuario'] . "</li>\n";
    echo "<li>Nome: " . $_SESSION['usuario_nome'] . "</li>\n";
    echo "<li>Email: " . $_SESSION['usuario_email'] . "</li>\n";
    echo "<li>Salão ID: " . $_SESSION['salao_id'] . "</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<hr>\n";
    echo "<h2>Resultado do Teste:</h2>\n";
    
    // Capturar a saída da página profissionais.php
    ob_start();
    include 'parceiro/profissionais.php';
    $output = ob_get_clean();
    
    // Verificar se houve erro 500
    if (strpos($output, 'HTTP 500') !== false || strpos($output, 'erro interno') !== false) {
        echo "<div class='error'>\n";
        echo "<h3>❌ Erro 500 detectado!</h3>\n";
        echo "<p>A página profissionais.php retornou um erro 500.</p>\n";
        echo "</div>\n";
    } else {
        echo "<div class='success'>\n";
        echo "<h3>✅ Página carregada com sucesso!</h3>\n";
        echo "<p>A página profissionais.php foi carregada sem erros 500.</p>\n";
        echo "</div>\n";
    }
    
    echo "<h3>Saída da página:</h3>\n";
    echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 15px 0; max-height: 400px; overflow: auto;'>\n";
    echo htmlspecialchars($output);
    echo "</div>\n";
    
    echo "</body>\n</html>";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Erro Capturado:</h3>\n";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>\n";
    echo "<p><strong>Stack Trace:</strong></p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    echo "</div>\n";
} catch (Error $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Erro Fatal Capturado:</h3>\n";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>\n";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>\n";
    echo "<p><strong>Stack Trace:</strong></p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    echo "</div>\n";
}

// Capturar qualquer erro ou aviso do PHP
$output = ob_get_clean();
echo $output;
?>