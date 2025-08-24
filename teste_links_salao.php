<?php
/**
 * Teste de Links do Salão
 * Verifica se os links estão funcionando corretamente
 */

// Simular sessão de parceiro para teste
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'teste@teste.com';
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_telefone'] = '11999999999';

echo "<h1>Teste de Links do Salão</h1>";
echo "<p>Testando redirecionamentos...</p>";

// Testar links principais
$links = [
    'profissionais.php' => 'Gerenciar Profissionais',
    'agenda.php' => 'Ver Agenda', 
    'agendamentos.php' => 'Ver Agendamentos',
    'dashboard.php' => 'Dashboard'
];

echo "<ul>";
foreach ($links as $link => $titulo) {
    $url = "http://localhost/cortefacil/cortefacilapp/parceiro/$link";
    echo "<li><a href='$url' target='_blank'>$titulo</a> - <code>$link</code></li>";
}
echo "</ul>";

// Verificar se os arquivos existem
echo "<h2>Verificação de Arquivos</h2>";
echo "<ul>";
foreach ($links as $link => $titulo) {
    $caminho = __DIR__ . "/parceiro/$link";
    $existe = file_exists($caminho);
    $status = $existe ? "✅ Existe" : "❌ Não existe";
    echo "<li>$titulo: $status - <code>$caminho</code></li>";
}
echo "</ul>";

// Verificar permissões
echo "<h2>Informações de Sessão</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Informações do Servidor</h2>";
echo "<p>SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "</p>";
echo "<p>HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</p>";
echo "<p>REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
?>