<?php
/**
 * Teste específico de conexão com Hostinger
 * Script para diagnosticar problemas de autenticação
 */

echo "<h2>🔧 Teste de Conexão Hostinger</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;}</style>";

// Configurações diretas do Hostinger
$host = 'srv486.hstgr.io';
$dbname = 'u690889028_cortefacil';
$username = 'u690889028_mayconwender';
$password = 'Maycon341753';

echo "<h3>Configurações de Teste:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Database:</strong> $dbname</li>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Password:</strong> " . str_repeat('*', strlen($password)) . "</li>";
echo "</ul>";

echo "<h3>Teste 1: Conectividade de Rede</h3>";
$connection = @fsockopen($host, 3306, $errno, $errstr, 10);
if ($connection) {
    echo "<p class='success'>✅ Conectividade OK - Porta 3306 acessível</p>";
    fclose($connection);
} else {
    echo "<p class='error'>❌ Falha na conectividade: $errstr ($errno)</p>";
}

echo "<h3>Teste 2: Conexão PDO</h3>";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    echo "<p class='info'>DSN: $dsn</p>";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<p class='success'>✅ Conexão PDO estabelecida com sucesso!</p>";
    
    // Teste de query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result && $result['test'] == 1) {
        echo "<p class='success'>✅ Query de teste executada com sucesso!</p>";
    }
    
    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p class='info'><strong>Tabelas encontradas:</strong> " . count($tables) . "</p>";
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Erro PDO: " . $e->getMessage() . "</p>";
    echo "<p class='error'><strong>Código:</strong> " . $e->getCode() . "</p>";
    
    // Análise do erro
    $errorMsg = $e->getMessage();
    
    if (strpos($errorMsg, 'Access denied') !== false) {
        echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🔐 Problema de Autenticação</h4>";
        echo "<p><strong>Possíveis causas:</strong></p>";
        echo "<ul>";
        echo "<li>Usuário ou senha incorretos</li>";
        echo "<li>IP não liberado para conexão remota</li>";
        echo "<li>Usuário não tem permissão no banco</li>";
        echo "</ul>";
        echo "<p><strong>Soluções:</strong></p>";
        echo "<ul>";
        echo "<li>Verificar credenciais no painel Hostinger</li>";
        echo "<li>Liberar IP 189.40.77.108 ou usar '%' (todos)</li>";
        echo "<li>Aguardar 15-30 minutos após liberar IP</li>";
        echo "</ul>";
        echo "</div>";
    } elseif (strpos($errorMsg, 'Unknown database') !== false) {
        echo "<div style='background:#f8d7da;border:1px solid #dc3545;padding:15px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🗄️ Banco Não Encontrado</h4>";
        echo "<p>O banco '$dbname' não existe ou não está acessível.</p>";
        echo "</div>";
    } elseif (strpos($errorMsg, 'Connection refused') !== false || strpos($errorMsg, 'timed out') !== false) {
        echo "<div style='background:#d1ecf1;border:1px solid #17a2b8;padding:15px;margin:10px 0;border-radius:4px;'>";
        echo "<h4>🌐 Problema de Conectividade</h4>";
        echo "<p>Servidor não acessível ou bloqueado.</p>";
        echo "</div>";
    }
}

echo "<h3>Teste 3: Usando Classe Database</h3>";
require_once 'config/database.php';

// Forçar configuração online
$db = new Database();
$db->forceOnlineConfig();

try {
    $conn = $db->connect();
    if ($conn) {
        echo "<p class='success'>✅ Conexão via classe Database OK!</p>";
    } else {
        echo "<p class='error'>❌ Falha na conexão via classe Database</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na classe Database: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Informações do Sistema</h3>";
echo "<ul>";
echo "<li><strong>IP Público:</strong> " . (file_get_contents('https://api.ipify.org') ?: 'não disponível') . "</li>";
echo "<li><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</li>";
echo "<li><strong>Versão PHP:</strong> " . PHP_VERSION . "</li>";
echo "<li><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? 'Disponível' : 'NÃO DISPONÍVEL') . "</li>";
echo "</ul>";

echo "<p><a href='login.php'>← Voltar para Login</a> | <a href='javascript:location.reload()'>🔄 Testar Novamente</a></p>";
?>