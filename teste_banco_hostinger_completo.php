<?php
/**
 * Teste completo para diagnosticar problemas com banco Hostinger
 * CorteFácil - Diagnóstico de Conexão Online
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Banco Hostinger - CorteFácil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border-left: 5px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .error { background: #f8d7da; border-left: 5px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .warning { background: #fff3cd; border-left: 5px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .info { background: #d1ecf1; border-left: 5px solid #17a2b8; padding: 15px; margin: 15px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background: #e9ecef; font-weight: bold; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .code-block { background: #f8f9fa; border: 1px solid #e9ecef; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        h1 { color: #495057; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #6c757d; margin-top: 30px; }
        .test-section { margin: 30px 0; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Completo - Banco Hostinger</h1>
        
        <div class="info">
            <strong>📅 Data/Hora:</strong> <?php echo date('d/m/Y H:i:s'); ?><br>
            <strong>🌐 Servidor:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost'; ?><br>
            <strong>📍 IP Cliente:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'N/A'; ?><br>
            <strong>🔧 PHP Version:</strong> <?php echo phpversion(); ?>
        </div>

        <?php
        // Detectar ambiente
        $isLocal = in_array($_SERVER['HTTP_HOST'] ?? 'localhost', ['localhost', '127.0.0.1', '::1']) || 
                   strpos($_SERVER['HTTP_HOST'] ?? '', ':8080') !== false;
        
        echo "<div class='" . ($isLocal ? 'warning' : 'info') . "'>";
        echo "<strong>🌍 Ambiente:</strong> " . ($isLocal ? 'LOCAL (XAMPP)' : 'ONLINE (Hostinger)') . "<br>";
        echo "<strong>📊 Modo:</strong> " . ($isLocal ? 'Desenvolvimento' : 'Produção');
        echo "</div>";
        ?>

        <div class="test-section">
            <h2>🔧 1. Verificação de Extensões PHP</h2>
            <table>
                <tr><th>Extensão</th><th>Status</th><th>Versão</th></tr>
                <?php
                $extensions = [
                    'PDO' => 'pdo',
                    'PDO MySQL' => 'pdo_mysql', 
                    'MySQLi' => 'mysqli',
                    'JSON' => 'json',
                    'MBString' => 'mbstring',
                    'OpenSSL' => 'openssl',
                    'cURL' => 'curl'
                ];
                
                foreach ($extensions as $name => $ext) {
                    $loaded = extension_loaded($ext);
                    $version = $loaded ? phpversion($ext) : 'N/A';
                    echo "<tr>";
                    echo "<td>{$name}</td>";
                    echo "<td class='" . ($loaded ? 'status-ok' : 'status-error') . "'>" . ($loaded ? '✅ OK' : '❌ Faltando') . "</td>";
                    echo "<td>{$version}</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <div class="test-section">
            <h2>🗄️ 2. Teste de Configurações do Banco</h2>
            
            <?php
            // Configurações para testar
            $configs = [];
            
            if ($isLocal) {
                $configs['Local XAMPP'] = [
                    'host' => 'localhost',
                    'db_name' => 'u690889028_cortefacil',
                    'username' => 'root',
                    'password' => ''
                ];
            } else {
                // Múltiplas configurações Hostinger para testar
                $configs = [
                    'Hostinger Atual' => [
                        'host' => 'srv486.hstgr.io',
                        'db_name' => 'u690889028_cortefacil',
                        'username' => 'u690889028_mayconwender',
                        'password' => 'Maycon341753'
                    ],
                    'Hostinger Alt 1' => [
                        'host' => 'srv488.hstgr.io',
                        'db_name' => 'u690889028_cortefacil',
                        'username' => 'u690889028_mayconwender',
                        'password' => 'Maycon341753'
                    ],
                    'Hostinger Alt 2' => [
                        'host' => '31.170.167.153',
                        'db_name' => 'u690889028_cortefacil',
                        'username' => 'u690889028_mayconwender',
                        'password' => 'Maycon341753'
                    ]
                ];
            }
            
            foreach ($configs as $configName => $config) {
                echo "<h3>📋 Testando: {$configName}</h3>";
                
                echo "<div class='code-block'>";
                echo "<strong>Configuração:</strong><br>";
                echo "Host: {$config['host']}<br>";
                echo "Database: {$config['db_name']}<br>";
                echo "Username: {$config['username']}<br>";
                echo "Password: " . str_repeat('*', strlen($config['password'])) . "";
                echo "</div>";
                
                $startTime = microtime(true);
                $testResults = [];
                
                // Teste 1: DNS Resolution
                echo "<p><strong>🔍 Teste DNS:</strong> ";
                $ip = gethostbyname($config['host']);
                if ($ip === $config['host'] && !filter_var($config['host'], FILTER_VALIDATE_IP)) {
                    echo "<span class='status-error'>❌ Falha na resolução DNS</span></p>";
                    $testResults['dns'] = false;
                } else {
                    echo "<span class='status-ok'>✅ OK (IP: {$ip})</span></p>";
                    $testResults['dns'] = true;
                }
                
                // Teste 2: Port Connection
                echo "<p><strong>🔌 Teste Porta 3306:</strong> ";
                $connection = @fsockopen($config['host'], 3306, $errno, $errstr, 10);
                if ($connection) {
                    fclose($connection);
                    echo "<span class='status-ok'>✅ Porta acessível</span></p>";
                    $testResults['port'] = true;
                } else {
                    echo "<span class='status-error'>❌ Porta inacessível (Erro: {$errno} - {$errstr})</span></p>";
                    $testResults['port'] = false;
                }
                
                // Teste 3: PDO Connection
                echo "<p><strong>🔗 Teste Conexão PDO:</strong> ";
                try {
                    $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
                    $options = [
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 15
                    ];
                    
                    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
                    $endTime = microtime(true);
                    $duration = round(($endTime - $startTime) * 1000, 2);
                    
                    echo "<span class='status-ok'>✅ Conexão OK ({$duration}ms)</span></p>";
                    $testResults['pdo'] = true;
                    
                    // Teste 4: Database Info
                    echo "<p><strong>📊 Informações do Servidor:</strong></p>";
                    $stmt = $pdo->query("SELECT VERSION() as version, NOW() as current_time, DATABASE() as database_name, USER() as current_user");
                    $info = $stmt->fetch();
                    
                    echo "<div class='success'>";
                    echo "<strong>Servidor MySQL:</strong><br>";
                    echo "• Versão: {$info['version']}<br>";
                    echo "• Hora: {$info['current_time']}<br>";
                    echo "• Banco: {$info['database_name']}<br>";
                    echo "• Usuário: {$info['current_user']}<br>";
                    echo "• Tempo de conexão: {$duration}ms";
                    echo "</div>";
                    
                    // Teste 5: Tables Check
                    echo "<p><strong>📋 Verificação de Tabelas:</strong></p>";
                    $stmt = $pdo->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (empty($tables)) {
                        echo "<div class='warning'>⚠️ Nenhuma tabela encontrada - Banco vazio</div>";
                    } else {
                        echo "<div class='success'>";
                        echo "<strong>✅ Tabelas encontradas (" . count($tables) . "):</strong><br>";
                        echo "• " . implode('<br>• ', $tables);
                        echo "</div>";
                        
                        // Verificar tabelas essenciais
                        $essentialTables = ['usuarios', 'saloes', 'profissionais', 'agendamentos'];
                        $missingTables = array_diff($essentialTables, $tables);
                        
                        if (!empty($missingTables)) {
                            echo "<div class='warning'>";
                            echo "<strong>⚠️ Tabelas essenciais faltando:</strong><br>";
                            echo "• " . implode('<br>• ', $missingTables);
                            echo "</div>";
                        }
                    }
                    
                    $pdo = null;
                    
                } catch (PDOException $e) {
                    $endTime = microtime(true);
                    $duration = round(($endTime - $startTime) * 1000, 2);
                    
                    echo "<span class='status-error'>❌ Falha na conexão</span></p>";
                    $testResults['pdo'] = false;
                    
                    echo "<div class='error'>";
                    echo "<strong>Erro PDO:</strong><br>";
                    echo "• Código: {$e->getCode()}<br>";
                    echo "• Mensagem: {$e->getMessage()}<br>";
                    echo "• Tempo até erro: {$duration}ms";
                    echo "</div>";
                }
                
                // Resumo dos testes
                $successCount = array_sum($testResults);
                $totalTests = count($testResults);
                
                if ($successCount === $totalTests) {
                    echo "<div class='success'><strong>🎉 Configuração {$configName}: FUNCIONANDO PERFEITAMENTE!</strong></div>";
                } else {
                    echo "<div class='error'><strong>❌ Configuração {$configName}: {$successCount}/{$totalTests} testes passaram</strong></div>";
                }
                
                echo "<hr>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>🏗️ 3. Teste da Classe Database Atual</h2>
            
            <?php
            try {
                if (file_exists('config/database.php')) {
                    require_once 'config/database.php';
                    
                    echo "<div class='success'>✅ Arquivo config/database.php encontrado</div>";
                    
                    $database = new Database();
                    $info = $database->getConnectionInfo();
                    
                    echo "<div class='code-block'>";
                    echo "<strong>Configuração da Classe Database:</strong><br>";
                    echo "• Host: {$info['host']}<br>";
                    echo "• Database: {$info['database']}<br>";
                    echo "• Username: {$info['username']}<br>";
                    echo "• Ambiente: {$info['environment']}<br>";
                    echo "• Status: " . ($info['connected'] ? 'Conectado' : 'Desconectado');
                    echo "</div>";
                    
                    echo "<p><strong>🧪 Testando conexão via classe:</strong></p>";
                    $startTime = microtime(true);
                    $conn = $database->connect();
                    $endTime = microtime(true);
                    $duration = round(($endTime - $startTime) * 1000, 2);
                    
                    if ($conn) {
                        echo "<div class='success'>✅ Conexão via classe Database bem-sucedida! ({$duration}ms)</div>";
                        
                        // Teste adicional com a conexão
                        try {
                            $stmt = $conn->query("SELECT 'Teste de query' as teste");
                            $result = $stmt->fetch();
                            echo "<div class='success'>✅ Query de teste executada: {$result['teste']}</div>";
                        } catch (Exception $e) {
                            echo "<div class='warning'>⚠️ Conexão OK, mas erro na query: {$e->getMessage()}</div>";
                        }
                        
                        $database->disconnect();
                    } else {
                        echo "<div class='error'>❌ Falha na conexão via classe Database</div>";
                    }
                    
                } else {
                    echo "<div class='error'>❌ Arquivo config/database.php não encontrado</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<strong>❌ Erro ao testar classe Database:</strong><br>";
                echo "• Mensagem: {$e->getMessage()}<br>";
                echo "• Arquivo: {$e->getFile()}<br>";
                echo "• Linha: {$e->getLine()}";
                echo "</div>";
            }
            ?>
        </div>

        <div class="test-section">
            <h2>💡 4. Recomendações e Próximos Passos</h2>
            
            <?php if ($isLocal): ?>
                <div class="info">
                    <strong>🏠 Ambiente Local (XAMPP):</strong><br>
                    • Certifique-se de que o MySQL está rodando no XAMPP<br>
                    • Crie o banco 'u690889028_cortefacil' no phpMyAdmin local<br>
                    • Importe o arquivo schema.sql no banco local<br>
                    • Use as configurações locais para desenvolvimento
                </div>
            <?php else: ?>
                <div class="warning">
                    <strong>🌐 Ambiente Online (Hostinger):</strong><br>
                    • Verifique as credenciais no painel do Hostinger<br>
                    • Confirme se o banco de dados foi criado corretamente<br>
                    • Importe o schema.sql via phpMyAdmin do Hostinger<br>
                    • Verifique se não há restrições de IP ou firewall
                </div>
            <?php endif; ?>
            
            <div class="info">
                <strong>🔧 Checklist Geral:</strong><br>
                1. ✅ Extensões PHP necessárias instaladas<br>
                2. ✅ Credenciais de banco corretas<br>
                3. ✅ Banco de dados criado<br>
                4. ✅ Tabelas importadas (schema.sql)<br>
                5. ✅ Conectividade de rede OK<br>
                6. ✅ Permissões de usuário adequadas
            </div>
            
            <div class="warning">
                <strong>⚠️ IMPORTANTE:</strong><br>
                • Remova este arquivo após resolver os problemas<br>
                • Não deixe arquivos de debug em produção<br>
                • Mantenha as credenciais seguras
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <p><strong>🔗 Links Úteis:</strong></p>
            <a href="index.php" style="margin: 0 10px;">← Voltar ao Início</a> |
            <a href="login.php" style="margin: 0 10px;">Login</a> |
            <a href="cadastro.php" style="margin: 0 10px;">Cadastro</a>
        </div>
    </div>
</body>
</html>