<?php
// Teste do Dashboard com usuário logado
session_start();

// Simular usuário logado para teste (chaves corretas)
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'cliente';
$_SESSION['usuario_nome'] = 'Cliente Teste';
$_SESSION['usuario_email'] = 'cliente@teste.com';
$_SESSION['usuario_telefone'] = '(11) 99999-9999';

echo "<h2>Teste Dashboard com Usuário Logado</h2>";
echo "<p>Simulando login do usuário: " . $_SESSION['user_name'] . "</p>";
echo "<p><a href='cliente/dashboard.php' target='_blank'>Abrir Dashboard</a></p>";
echo "<hr>";

// Incluir o dashboard diretamente
echo "<h3>Carregando Dashboard:</h3>";
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";

try {
    // Capturar output do dashboard
    ob_start();
    include 'cliente/dashboard.php';
    $dashboard_content = ob_get_clean();
    
    if (empty(trim($dashboard_content))) {
        echo "❌ Dashboard retornou conteúdo vazio!";
    } else {
        echo "✓ Dashboard carregou com sucesso (" . strlen($dashboard_content) . " caracteres)";
        echo "<br><small>Primeiros 200 caracteres:</small><br>";
        echo "<code>" . htmlspecialchars(substr($dashboard_content, 0, 200)) . "...</code>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar dashboard: " . $e->getMessage();
}

echo "</div>";

echo "<p><a href='logout.php'>Fazer Logout</a></p>";
?>