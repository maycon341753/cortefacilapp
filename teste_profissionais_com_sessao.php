<?php
/**
 * Teste da Página de Profissionais com Simulação de Sessão
 * Para verificar se o erro 500 é causado por falta de autenticação
 */

// Simular sessão de parceiro logado
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'parceiro'; // Corrigido: tipo_usuario em vez de usuario_tipo
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'teste@exemplo.com';
$_SESSION['usuario_telefone'] = '11999999999';
$_SESSION['salao_id'] = 1;

// Incluir o arquivo original para testar
try {
    echo "<h1>🧪 Teste de Profissionais com Sessão Simulada</h1>";
    echo "<p><strong>Sessão simulada:</strong></p>";
    echo "<ul>";
    echo "<li>Usuário ID: " . $_SESSION['usuario_id'] . "</li>";
    echo "<li>Tipo: " . $_SESSION['tipo_usuario'] . "</li>";
    echo "<li>Nome: " . $_SESSION['usuario_nome'] . "</li>";
    echo "<li>Salão ID: " . $_SESSION['salao_id'] . "</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<h2>Carregando página de profissionais...</h2>";
    
    // Incluir a página original
    include 'parceiro/profissionais.php';
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 20px; border: 1px solid red; margin: 20px;'>";
    echo "<h2>❌ Erro Capturado:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 20px; border: 1px solid red; margin: 20px;'>";
    echo "<h2>❌ Erro Fatal Capturado:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>