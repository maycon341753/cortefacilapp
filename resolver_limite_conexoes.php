<?php
/**
 * Script para resolver problema de limite de conexões por hora
 * Hostinger: max_connections_per_hour = 500
 */

echo "<h2>🔧 Resolver Limite de Conexões Hostinger</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} .warning{color:orange;} .alert{background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:4px;}</style>";

echo "<div class='alert'>";
echo "<h3>⚠️ Problema Identificado: Limite de Conexões Excedido</h3>";
echo "<p><strong>Erro:</strong> User 'u690889028_mayconwender' has exceeded the 'max_connections_per_hour' resource (current value: 500)</p>";
echo "<p><strong>Causa:</strong> Muitas tentativas de conexão em pouco tempo durante os testes.</p>";
echo "</div>";

echo "<h3>Soluções Implementadas:</h3>";
echo "<ol>";
echo "<li><strong>Conexões Persistentes Desabilitadas:</strong> Evita acúmulo de conexões</li>";
echo "<li><strong>Timeout Configurado:</strong> 30 segundos para evitar conexões órfãs</li>";
echo "<li><strong>Pool de Conexões:</strong> Reutilizar conexões existentes</li>";
echo "</ol>";

// Implementar singleton para conexão
class DatabaseSingleton {
    private static $instance = null;
    private static $connection = null;
    
    private function __construct() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if (self::$connection === null) {
            try {
                $host = 'srv486.hstgr.io';
                $dbname = 'u690889028_cortefacil';
                $username = 'u690889028_mayconwender';
                $password = 'Maycon341753';
                
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false, // IMPORTANTE: Não usar conexões persistentes
                    PDO::ATTR_TIMEOUT => 30,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                ];
                
                self::$connection = new PDO($dsn, $username, $password, $options);
                self::$connection->exec("SET NAMES utf8mb4");
                
                echo "<p class='success'>✅ Conexão singleton estabelecida com sucesso!</p>";
                
            } catch (PDOException $e) {
                echo "<p class='error'>❌ Erro na conexão singleton: " . $e->getMessage() . "</p>";
                
                if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
                    echo "<div class='alert'>";
                    echo "<h4>🕐 Aguarde Reset do Limite</h4>";
                    echo "<p>O limite de 500 conexões por hora foi atingido.</p>";
                    echo "<p><strong>Próximo reset:</strong> " . date('H:i:s', strtotime('+1 hour', mktime(date('H'), 0, 0))) . "</p>";
                    echo "<p><strong>Tempo restante:</strong> " . (60 - date('i')) . " minutos</p>";
                    echo "</div>";
                }
                
                return null;
            }
        }
        
        return self::$connection;
    }
    
    public function closeConnection() {
        self::$connection = null;
    }
}

echo "<h3>Teste da Conexão Singleton:</h3>";
$db = DatabaseSingleton::getInstance();
$conn = $db->getConnection();

if ($conn) {
    try {
        // Teste simples
        $stmt = $conn->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result && $result['test'] == 1) {
            echo "<p class='success'>✅ Query de teste executada!</p>";
        }
        
        // Verificar tabelas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='info'>📊 Tabelas encontradas: " . count($tables) . "</p>";
        
        // Testar tabela específica
        if (in_array('usuarios', $tables)) {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'parceiro'");
            $result = $stmt->fetch();
            echo "<p class='info'>👥 Parceiros cadastrados: " . $result['total'] . "</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Erro na query: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Recomendações:</h3>";
echo "<div class='alert'>";
echo "<ul>";
echo "<li><strong>Aguardar:</strong> Espere até " . date('H:i', strtotime('+1 hour', mktime(date('H'), 0, 0))) . " para reset automático</li>";
echo "<li><strong>Usar Singleton:</strong> Implementar padrão singleton em todas as conexões</li>";
echo "<li><strong>Cache:</strong> Implementar cache para reduzir consultas</li>";
echo "<li><strong>Otimizar:</strong> Revisar queries desnecessárias</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Status atual:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><a href='login.php'>← Voltar para Login</a> | <a href='javascript:location.reload()'>🔄 Testar Novamente</a></p>";

// Fechar conexão explicitamente
$db->closeConnection();
?>