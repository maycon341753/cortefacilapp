<?php
/**
 * Arquivo de Teste - Sistema CorteFácil
 */

echo "<h1>🎉 Sistema CorteFácil - Teste de Funcionamento</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Testar conexão com banco de dados
try {
    require_once 'config/database.php';
    echo "<p><strong>✅ Conexão com banco:</strong> Sucesso!</p>";
    
    // Testar se as tabelas existem
    $tables = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
    echo "<h3>📋 Verificação de Tabelas:</h3>";
    echo "<ul>";
    
    foreach ($tables as $table) {
        try {
            $stmt = $database->query("SELECT COUNT(*) as total FROM $table");
            $result = $stmt->fetch();
            echo "<li><strong>$table:</strong> ✅ Existe ({$result['total']} registros)</li>";
        } catch (Exception $e) {
            echo "<li><strong>$table:</strong> ❌ Erro: " . $e->getMessage() . "</li>";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p><strong>❌ Erro na conexão:</strong> " . $e->getMessage() . "</p>";
}

echo "<h3>🔗 Links do Sistema:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>🏠 Página Inicial</a></li>";
echo "<li><a href='login.php'>🔐 Login</a></li>";
echo "<li><a href='register.php'>📝 Cadastro</a></li>";
echo "</ul>";

echo "<h3>👥 Usuários de Teste:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@cortefacil.com / admin123</li>";
echo "<li><strong>Parceiro:</strong> salao@teste.com / senha123</li>";
echo "<li><strong>Cliente:</strong> cliente@teste.com / senha123</li>";
echo "</ul>";

echo "<p><em>Sistema desenvolvido em PHP + JavaScript + MySQL</em></p>";
?>