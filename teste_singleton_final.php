<?php
/**
 * Teste final da implementação Singleton
 * Verifica se o problema de limite de conexões foi resolvido
 */

echo "<h2>🎯 Teste Final - Implementação Singleton</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} .alert{background:#e8f5e8;border:1px solid #4caf50;padding:15px;margin:10px 0;border-radius:4px;}</style>";

echo "<h3>1. Teste da Nova Implementação</h3>";

require_once 'config/database.php';

echo "<p class='info'>📋 Testando padrão Singleton implementado...</p>";

try {
    // Teste 1: Obter instância singleton
    echo "<h4>Teste 1: Instância Singleton</h4>";
    $db1 = Database::getInstance();
    $db2 = Database::getInstance();
    
    if ($db1 === $db2) {
        echo "<p class='success'>✅ Singleton funcionando - mesma instância retornada</p>";
    } else {
        echo "<p class='error'>❌ Singleton falhou - instâncias diferentes</p>";
    }
    
    // Teste 2: Conexão singleton
    echo "<h4>Teste 2: Conexão Singleton</h4>";
    $conn1 = $db1->connect();
    $conn2 = $db2->connect();
    
    if ($conn1 && $conn2 && $conn1 === $conn2) {
        echo "<p class='success'>✅ Conexão singleton funcionando - mesma conexão reutilizada</p>";
        
        // Teste 3: Query de verificação
        echo "<h4>Teste 3: Query de Verificação</h4>";
        $stmt = $conn1->query("SELECT 1 as test, NOW() as timestamp");
        $result = $stmt->fetch();
        
        if ($result) {
            echo "<p class='success'>✅ Query executada com sucesso</p>";
            echo "<p class='info'>🕐 Timestamp: " . $result['timestamp'] . "</p>";
        }
        
        // Teste 4: Verificar tabelas
        echo "<h4>Teste 4: Verificação de Tabelas</h4>";
        $stmt = $conn1->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p class='info'>📊 Tabelas encontradas: " . count($tables) . "</p>";
        
        $expectedTables = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
        $missingTables = [];
        
        foreach ($expectedTables as $table) {
            if (in_array($table, $tables)) {
                echo "<p class='success'>✅ Tabela '$table' encontrada</p>";
            } else {
                echo "<p class='error'>❌ Tabela '$table' não encontrada</p>";
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            echo "<div class='alert'>";
            echo "<h4>🎉 Sistema Pronto!</h4>";
            echo "<p>Todas as tabelas necessárias estão presentes e a conexão está funcionando.</p>";
            echo "</div>";
        }
        
        // Teste 5: Dados de exemplo
        echo "<h4>Teste 5: Contagem de Registros</h4>";
        
        if (in_array('usuarios', $tables)) {
            $stmt = $conn1->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'parceiro'");
            $result = $stmt->fetch();
            echo "<p class='info'>👥 Parceiros: " . $result['total'] . "</p>";
        }
        
        if (in_array('saloes', $tables)) {
            $stmt = $conn1->query("SELECT COUNT(*) as total FROM saloes");
            $result = $stmt->fetch();
            echo "<p class='info'>🏪 Salões: " . $result['total'] . "</p>";
        }
        
        if (in_array('profissionais', $tables)) {
            $stmt = $conn1->query("SELECT COUNT(*) as total FROM profissionais");
            $result = $stmt->fetch();
            echo "<p class='info'>💼 Profissionais: " . $result['total'] . "</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Falha na conexão singleton</p>";
        
        if (!$conn1) {
            echo "<p class='error'>Primeira conexão falhou</p>";
        }
        if (!$conn2) {
            echo "<p class='error'>Segunda conexão falhou</p>";
        }
        if ($conn1 && $conn2 && $conn1 !== $conn2) {
            echo "<p class='warning'>⚠️ Conexões diferentes - singleton não está funcionando</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro durante o teste: " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🕐 Limite Ainda Ativo</h4>";
        echo "<p>O limite de conexões ainda está ativo. Aguarde mais alguns minutos.</p>";
        echo "<p><strong>Próxima tentativa:</strong> " . date('H:i:s', time() + 300) . "</p>";
        echo "</div>";
    }
}

echo "<h3>2. Informações do Sistema</h3>";
echo "<ul>";
echo "<li><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</li>";
echo "<li><strong>Versão PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Ambiente:</strong> " . (file_exists('.env.online') ? 'Online (forçado)' : 'Detectado automaticamente') . "</li>";
echo "</ul>";

echo "<h3>3. Próximos Passos</h3>";
echo "<div class='alert'>";
echo "<p><strong>Se o teste passou:</strong></p>";
echo "<ul>";
echo "<li>✅ O sistema está pronto para uso</li>";
echo "<li>✅ Login de parceiros funcionará</li>";
echo "<li>✅ Cadastro de profissionais funcionará</li>";
echo "</ul>";
echo "<p><strong>Se ainda há erros:</strong></p>";
echo "<ul>";
echo "<li>🕐 Aguarde mais tempo para reset do limite</li>";
echo "<li>📞 Entre em contato com suporte Hostinger se persistir</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><a href='login.php'>🔐 Ir para Login</a> | <a href='javascript:location.reload()'>🔄 Testar Novamente</a></p>";
?>