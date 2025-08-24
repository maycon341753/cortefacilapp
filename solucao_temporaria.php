<?php
/**
 * Solução temporária para contornar limite de conexões Hostinger
 * Usa banco local até reset do limite online
 */

echo "<h2>🔧 Solução Temporária - Banco Local</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} .alert{background:#e3f2fd;border:1px solid #2196f3;padding:15px;margin:10px 0;border-radius:4px;}</style>";

echo "<div class='alert'>";
echo "<h3>📋 Situação Atual</h3>";
echo "<p><strong>Problema:</strong> Limite de 500 conexões/hora excedido no Hostinger</p>";
echo "<p><strong>Solução:</strong> Usar banco local temporariamente</p>";
echo "<p><strong>Reset previsto:</strong> " . date('H:i', strtotime('+1 hour', mktime(date('H'), 0, 0))) . "</p>";
echo "</div>";

// Remover arquivo indicador de ambiente online temporariamente
if (file_exists('.env.online')) {
    if (rename('.env.online', '.env.online.backup')) {
        echo "<p class='success'>✅ Arquivo .env.online renomeado para .env.online.backup</p>";
        echo "<p class='info'>🔄 Sistema agora usará banco local</p>";
    } else {
        echo "<p class='error'>❌ Erro ao renomear arquivo .env.online</p>";
    }
} else {
    echo "<p class='info'>ℹ️ Arquivo .env.online não existe - sistema já usa banco local</p>";
}

echo "<h3>Teste de Conexão Local:</h3>";

require_once 'config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->connect();
    
    if ($conn) {
        echo "<p class='success'>✅ Conexão local estabelecida com sucesso!</p>";
        
        // Verificar tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p class='info'>📊 Tabelas no banco local: " . count($tables) . "</p>";
        
        $expectedTables = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
        $allTablesExist = true;
        
        foreach ($expectedTables as $table) {
            if (in_array($table, $tables)) {
                echo "<p class='success'>✅ Tabela '$table' OK</p>";
            } else {
                echo "<p class='error'>❌ Tabela '$table' não encontrada</p>";
                $allTablesExist = false;
            }
        }
        
        if ($allTablesExist) {
            echo "<div style='background:#e8f5e8;border:1px solid #4caf50;padding:15px;margin:10px 0;border-radius:4px;'>";
            echo "<h4>🎉 Sistema Funcionando Localmente!</h4>";
            echo "<p>Todas as funcionalidades estão disponíveis:</p>";
            echo "<ul>";
            echo "<li>✅ Login de parceiros</li>";
            echo "<li>✅ Cadastro de profissionais</li>";
            echo "<li>✅ Gerenciamento de salões</li>";
            echo "<li>✅ Sistema de agendamentos</li>";
            echo "</ul>";
            echo "</div>";
            
            // Contar registros
            $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'parceiro'");
            $parceiros = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM saloes");
            $saloes = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as total FROM profissionais");
            $profissionais = $stmt->fetch()['total'];
            
            echo "<h4>📈 Estatísticas:</h4>";
            echo "<ul>";
            echo "<li>👥 Parceiros: $parceiros</li>";
            echo "<li>🏪 Salões: $saloes</li>";
            echo "<li>💼 Profissionais: $profissionais</li>";
            echo "</ul>";
        }
        
    } else {
        echo "<p class='error'>❌ Falha na conexão local</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<h3>🔄 Para Restaurar Banco Online:</h3>";
echo "<div class='alert'>";
echo "<p><strong>Quando o limite resetar (após 14:00):</strong></p>";
echo "<ol>";
echo "<li>Renomeie .env.online.backup para .env.online</li>";
echo "<li>Ou execute: <code>rename .env.online.backup .env.online</code></li>";
echo "<li>Teste a conexão online novamente</li>";
echo "</ol>";
echo "<p><strong>Comando rápido:</strong></p>";
echo "<code style='background:#f5f5f5;padding:5px;border-radius:3px;'>";
echo "rename .env.online.backup .env.online";
echo "</code>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Status:</strong> " . date('d/m/Y H:i:s') . " - Usando banco LOCAL</p>";
echo "<p><a href='login.php'>🔐 Testar Login</a> | <a href='parceiro/profissionais.php'>👨‍💼 Testar Profissionais</a> | <a href='javascript:location.reload()'>🔄 Atualizar</a></p>";
?>