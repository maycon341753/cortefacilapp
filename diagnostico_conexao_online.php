<?php
/**
 * Diagnóstico de Conexão Online - CorteFácil
 * Script para identificar problemas de conexão com banco Hostinger
 */

echo "<h2>🔍 Diagnóstico de Conexão Online</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";

// 1. Verificar variáveis de servidor
echo "<h3>1. Informações do Servidor</h3>";
echo "<ul>";
echo "<li><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'não definido') . "</li>";
echo "<li><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'não definido') . "</li>";
echo "<li><strong>DOCUMENT_ROOT:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não definido') . "</li>";
echo "<li><strong>SERVER_SOFTWARE:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'não definido') . "</li>";
echo "<li><strong>SERVER_PORT:</strong> " . ($_SERVER['SERVER_PORT'] ?? 'não definido') . "</li>";
echo "<li><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? 'Sim' : 'Não') . "</li>";
echo "</ul>";

// 2. Testar detecção de ambiente
require_once 'config/database.php';
$db = new Database();

// Usar reflexão para acessar método privado
$reflection = new ReflectionClass($db);
$isLocalMethod = $reflection->getMethod('isLocalEnvironment');
$isLocalMethod->setAccessible(true);
$isLocal = $isLocalMethod->invoke($db);

echo "<h3>2. Detecção de Ambiente</h3>";
echo "<p class='" . ($isLocal ? 'info' : 'warning') . "'><strong>Ambiente detectado:</strong> " . ($isLocal ? 'LOCAL' : 'ONLINE') . "</p>";

// 3. Verificar configurações de conexão
echo "<h3>3. Configurações de Conexão</h3>";
$hostProperty = $reflection->getProperty('host');
$hostProperty->setAccessible(true);
$host = $hostProperty->getValue($db);

$dbNameProperty = $reflection->getProperty('db_name');
$dbNameProperty->setAccessible(true);
$dbName = $dbNameProperty->getValue($db);

$usernameProperty = $reflection->getProperty('username');
$usernameProperty->setAccessible(true);
$username = $usernameProperty->getValue($db);

$passwordProperty = $reflection->getProperty('password');
$passwordProperty->setAccessible(true);
$password = $passwordProperty->getValue($db);

echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Database:</strong> $dbName</li>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Password:</strong> " . (empty($password) ? 'VAZIO' : str_repeat('*', strlen($password))) . "</li>";
echo "</ul>";

// 4. Testar conexão básica
echo "<h3>4. Teste de Conexão</h3>";
try {
    $conn = $db->connect();
    if ($conn) {
        echo "<p class='success'>✅ Conexão estabelecida com sucesso!</p>";
        
        // Testar uma query simples
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            echo "<p class='success'>✅ Query de teste executada com sucesso!</p>";
        }
        
        // Verificar tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='info'><strong>Tabelas encontradas:</strong> " . count($tables) . "</p>";
        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p class='error'>❌ Falha na conexão - objeto de conexão é null</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
    echo "<p class='error'><strong>Código do erro:</strong> " . $e->getCode() . "</p>";
    
    // Análise específica de erros comuns
    $errorMsg = $e->getMessage();
    if (strpos($errorMsg, 'Access denied') !== false) {
        echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:10px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🔐 Problema de Autenticação</h4>";
        echo "<p>As credenciais (usuário/senha) estão incorretas ou o usuário não tem permissão.</p>";
        echo "<p><strong>Soluções:</strong></p>";
        echo "<ul>";
        echo "<li>Verificar usuário e senha no painel da Hostinger</li>";
        echo "<li>Confirmar se o usuário tem permissões no banco</li>";
        echo "<li>Verificar se o IP está liberado para conexão remota</li>";
        echo "</ul>";
        echo "</div>";
    } elseif (strpos($errorMsg, 'Unknown database') !== false) {
        echo "<div style='background:#f8d7da;border:1px solid #dc3545;padding:10px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🗄️ Banco de Dados Não Encontrado</h4>";
        echo "<p>O banco de dados '$dbName' não existe.</p>";
        echo "<p><strong>Soluções:</strong></p>";
        echo "<ul>";
        echo "<li>Criar o banco no painel da Hostinger</li>";
        echo "<li>Verificar o nome correto do banco</li>";
        echo "</ul>";
        echo "</div>";
    } elseif (strpos($errorMsg, 'Connection refused') !== false || strpos($errorMsg, 'timed out') !== false) {
        echo "<div style='background:#d1ecf1;border:1px solid #17a2b8;padding:10px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🌐 Problema de Conectividade</h4>";
        echo "<p>Não foi possível conectar ao servidor de banco.</p>";
        echo "<p><strong>Soluções:</strong></p>";
        echo "<ul>";
        echo "<li>Verificar se o host está correto: $host</li>";
        echo "<li>Verificar se o IP está liberado para conexão remota</li>";
        echo "<li>Aguardar alguns minutos e tentar novamente</li>";
        echo "</ul>";
        echo "</div>";
    }
}

// 5. Teste de conectividade de rede
echo "<h3>5. Teste de Conectividade de Rede</h3>";
$hostToTest = $host;
$port = 3306;

echo "<p>Testando conectividade com $hostToTest:$port...</p>";

$connection = @fsockopen($hostToTest, $port, $errno, $errstr, 10);
if ($connection) {
    echo "<p class='success'>✅ Conectividade de rede OK - Porta 3306 acessível</p>";
    fclose($connection);
} else {
    echo "<p class='error'>❌ Falha na conectividade de rede</p>";
    echo "<p class='error'><strong>Erro:</strong> $errstr ($errno)</p>";
    echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:10px;margin:10px 0;border-radius:4px;'>";
    echo "<h4>🔧 Possíveis Soluções</h4>";
    echo "<ul>";
    echo "<li>Verificar se o IP está liberado no painel da Hostinger</li>";
    echo "<li>Aguardar 15-30 minutos após liberar o IP</li>";
    echo "<li>Tentar usar '%' como IP liberado (todos os IPs)</li>";
    echo "<li>Contatar suporte da Hostinger</li>";
    echo "</ul>";
    echo "</div>";
}

// 6. Informações adicionais
echo "<h3>6. Informações Adicionais</h3>";
echo "<ul>";
echo "<li><strong>IP do servidor atual:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'não disponível') . "</li>";
echo "<li><strong>IP público (estimado):</strong> " . (file_get_contents('https://api.ipify.org') ?: 'não disponível') . "</li>";
echo "<li><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</li>";
echo "<li><strong>Versão PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>Extensão PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? 'Disponível' : 'NÃO DISPONÍVEL') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='login.php'>← Voltar para Login</a> | <a href='javascript:location.reload()'>🔄 Testar Novamente</a></p>";
?>