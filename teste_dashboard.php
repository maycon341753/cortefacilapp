<?php
/**
 * Teste específico para o Dashboard do Parceiro
 * Simula acesso com sessão válida
 */

// Iniciar sessão
session_start();

// Simular usuário logado como parceiro
$_SESSION['usuario_id'] = 1;
$_SESSION['tipo_usuario'] = 'parceiro';
$_SESSION['usuario_nome'] = 'Teste Parceiro';
$_SESSION['usuario_email'] = 'parceiro@teste.com';
$_SESSION['usuario_telefone'] = '11999999999';

echo "<h2>🧪 Teste do Dashboard do Parceiro</h2>";
echo "<p><strong>Sessão simulada:</strong></p>";
echo "<ul>";
echo "<li>ID: " . $_SESSION['usuario_id'] . "</li>";
echo "<li>Tipo: " . $_SESSION['tipo_usuario'] . "</li>";
echo "<li>Nome: " . $_SESSION['usuario_nome'] . "</li>";
echo "<li>Email: " . $_SESSION['usuario_email'] . "</li>";
echo "</ul>";

echo "<h3>Testando acesso ao dashboard...</h3>";

// Capturar output do dashboard
ob_start();

try {
    // Incluir o dashboard
    include 'parceiro/dashboard.php';
    
    $output = ob_get_contents();
    
    if (strpos($output, 'Sistema em Manutenção') !== false) {
        echo "❌ <strong>Dashboard redirecionou para manutenção</strong><br>";
        echo "<div style='background:#ffebee;padding:10px;border-radius:5px;margin:10px 0;'>";
        echo "<strong>Possíveis causas:</strong><br>";
        echo "• Health check detectou problema<br>";
        echo "• Arquivo de autenticação não encontrado<br>";
        echo "• Erro na verificação de sessão<br>";
        echo "</div>";
    } else if (strpos($output, 'Dashboard') !== false || strpos($output, 'Painel') !== false) {
        echo "✅ <strong>Dashboard carregou com sucesso!</strong><br>";
        echo "<div style='background:#e8f5e8;padding:10px;border-radius:5px;margin:10px 0;'>";
        echo "Dashboard está funcionando normalmente.";
        echo "</div>";
    } else {
        echo "⚠️ <strong>Resultado inesperado</strong><br>";
        echo "<div style='background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0;'>";
        echo "Output: " . htmlspecialchars(substr($output, 0, 200)) . "...";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Erro ao carregar dashboard:</strong> " . $e->getMessage() . "<br>";
} finally {
    ob_end_clean();
}

// Testar health check diretamente
echo "<h3>Testando Health Check diretamente...</h3>";

try {
    require_once 'includes/health_check.php';
    
    $health = getSystemHealthStatus();
    
    if ($health['healthy']) {
        echo "✅ <strong>Health Check: Sistema Saudável</strong><br>";
        echo "<div style='background:#e8f5e8;padding:10px;border-radius:5px;margin:10px 0;'>";
        echo "Mensagem: " . $health['message'] . "<br>";
        echo "Conexão: " . $health['details']['connection_type'] . "<br>";
        echo "Banco: " . $health['details']['database'] . "<br>";
        echo "</div>";
    } else {
        echo "❌ <strong>Health Check: Sistema com Problemas</strong><br>";
        echo "<div style='background:#ffebee;padding:10px;border-radius:5px;margin:10px 0;'>";
        echo "Mensagem: " . $health['message'] . "<br>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Erro no Health Check:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<p><strong>Data/Hora do Teste:</strong> " . date('d/m/Y H:i:s') . "</p>";
?>