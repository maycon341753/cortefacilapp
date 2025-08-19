<?php
/**
 * Configuração do banco de dados
 * Arquivo de configuração para conexão com MySQL
 * Detecta automaticamente o ambiente (local vs online)
 * VERSÃO ATUALIZADA - Agosto 2025
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Detectar ambiente automaticamente
        if ($this->isLocalEnvironment()) {
            // Configurações para ambiente local
            $this->host = 'localhost';
            $this->db_name = 'u690889028_cortefacil';
            $this->username = 'root';
            $this->password = '';
        } else {
            // ✅ CONFIGURAÇÕES PARA AMBIENTE ONLINE (HOSTINGER)
            // ✅ CREDENCIAIS ATUALIZADAS CONFORME PAINEL HOSTINGER:
            $this->host = 'srv486.hstgr.io';                    // ✅ Host correto do painel
            $this->db_name = 'u690889028_cortefacil';           // ✅ Confirmado no painel
            $this->username = 'u690889028_mayconwender';        // ✅ Confirmado no painel
            $this->password = 'Maycon341753';                   // ✅ Nova senha sem caracteres especiais
        }
    }
    
    /**
     * Detecta se está rodando em ambiente local
     * @return bool
     */
    private function isLocalEnvironment() {
        // Verificar múltiplas condições para ambiente local
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // 1. Verifica se está rodando localmente por hostname
        if (in_array($serverName, $localHosts)) {
            return true;
        }
        
        // 2. Verifica se está rodando no XAMPP/WAMP
        if (strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false || 
            strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'wamp') !== false) {
            return true;
        }
        
        // 3. Verifica se está usando servidor PHP built-in (php -S)
        if (isset($_SERVER['SERVER_SOFTWARE']) && 
            strpos($_SERVER['SERVER_SOFTWARE'], 'PHP') !== false) {
            return true;
        }
        
        // 4. Verifica porta de desenvolvimento
        $port = $_SERVER['SERVER_PORT'] ?? 80;
        if (in_array($port, [8000, 8080, 3000, 4000, 5000])) {
            return true;
        }
        
        // 5. Verifica se não tem HTTPS (desenvolvimento local geralmente não usa)
        if (!isset($_SERVER['HTTPS']) && $serverName === 'localhost') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Conecta ao banco de dados MySQL
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;
        
        try {
            // String de conexão DSN melhorada
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // Opções de conexão otimizadas
            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_PERSISTENT => false
            );
            
            // Estabelecer conexão
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Configurações adicionais de charset para garantir UTF-8
            $this->conn->exec("SET NAMES utf8mb4");
            $this->conn->exec("SET CHARACTER SET utf8mb4");
            $this->conn->exec("SET character_set_connection=utf8mb4");
            $this->conn->exec("SET collation_connection=utf8mb4_unicode_ci");
            
        } catch(PDOException $e) {
            // Log do erro para análise
            error_log("Erro de conexão BD: " . $e->getMessage());
            
            // Exibir erro baseado no ambiente
            if ($this->isLocalEnvironment()) {
                // Em desenvolvimento, mostrar erro detalhado
                echo "<div style='background:#ffebee;border:1px solid #f44336;padding:15px;margin:10px;border-radius:4px;'>";
                echo "<h3 style='color:#d32f2f;margin:0 0 10px 0;'>🚨 Erro de Conexão com o Banco</h3>";
                echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
                echo "<strong>Código:</strong> " . $e->getCode() . "<br>";
                echo "<strong>Host:</strong> " . $this->host . "<br>";
                echo "<strong>Database:</strong> " . $this->db_name . "<br>";
                echo "<strong>Username:</strong> " . $this->username . "<br>";
                echo "<strong>Ambiente:</strong> " . ($this->isLocalEnvironment() ? "Local" : "Online");
                echo "</div>";
            } else {
                // Em produção, erro mais genérico
                echo "<div style='background:#ffebee;padding:15px;margin:10px;border-radius:4px;text-align:center;'>";
                echo "<h3 style='color:#d32f2f;'>⚠️ Erro de Conexão</h3>";
                echo "<p>Não foi possível conectar ao banco de dados. Tente novamente em alguns minutos.</p>";
                echo "<small>Se o problema persistir, entre em contato com o suporte.</small>";
                echo "</div>";
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Fecha a conexão com o banco
     */
    public function disconnect() {
        $this->conn = null;
    }
    
    /**
     * Verifica se a conexão está ativa
     * @return bool
     */
    public function isConnected() {
        return $this->conn !== null;
    }
    
    /**
     * Obtém informações da conexão atual
     * @return array
     */
    public function getConnectionInfo() {
        return [
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'environment' => $this->isLocalEnvironment() ? 'local' : 'production',
            'connected' => $this->isConnected()
        ];
    }
    
    /**
     * Método para testar conexão (apenas para debug - remover em produção)
     * @return void
     */
    public function testConnection() {
        echo "<div style='background:#f5f5f5;padding:20px;margin:20px;border-radius:8px;font-family:Arial,sans-serif;'>";
        echo "<h2 style='color:#333;margin:0 0 15px 0;'>🔍 Teste de Conexão - CorteFácil</h2>";
        
        $info = $this->getConnectionInfo();
        echo "<div style='background:white;padding:15px;border-radius:4px;margin-bottom:15px;'>";
        echo "<h3 style='margin:0 0 10px 0;color:#666;'>📋 Informações da Conexão</h3>";
        echo "<strong>Host:</strong> " . $info['host'] . "<br>";
        echo "<strong>Database:</strong> " . $info['database'] . "<br>";
        echo "<strong>Username:</strong> " . $info['username'] . "<br>";
        echo "<strong>Password:</strong> " . str_repeat("*", strlen($this->password)) . "<br>";
        echo "<strong>Ambiente:</strong> " . ucfirst($info['environment']) . "<br>";
        echo "</div>";
        
        echo "<div style='background:white;padding:15px;border-radius:4px;'>";
        echo "<h3 style='margin:0 0 10px 0;color:#666;'>🧪 Resultado do Teste</h3>";
        
        $startTime = microtime(true);
        $conn = $this->connect();
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        if ($conn) {
            echo "<div style='color:#4caf50;font-size:18px;font-weight:bold;margin-bottom:10px;'>";
            echo "✅ CONEXÃO BEM-SUCEDIDA!";
            echo "</div>";
            echo "<p style='margin:5px 0;'>⏱️ Tempo de conexão: {$duration}ms</p>";
            
            try {
                // Testar uma query simples
                $stmt = $conn->query("SELECT VERSION() as version, NOW() as current_time");
                $result = $stmt->fetch();
                echo "<p style='margin:5px 0;'>🗄️ Versão MySQL: " . $result['version'] . "</p>";
                echo "<p style='margin:5px 0;'>🕐 Hora do servidor: " . $result['current_time'] . "</p>";
            } catch(Exception $e) {
                echo "<p style='color:#ff9800;'>⚠️ Conexão OK, mas erro ao executar query: " . $e->getMessage() . "</p>";
            }
            
            $this->disconnect();
            echo "<p style='color:#4caf50;margin:10px 0 0 0;'>🔌 Conexão fechada com sucesso!</p>";
        } else {
            echo "<div style='color:#f44336;font-size:18px;font-weight:bold;'>";
            echo "❌ FALHA NA CONEXÃO";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }
}

/**
 * Função auxiliar para obter conexão com o banco
 * @return PDO|null
 */
function getConnection() {
    $database = new Database();
    return $database->connect();
}

/**
 * Função auxiliar para testar conexão rapidamente
 * @return bool
 */
function testDatabaseConnection() {
    $database = new Database();
    $conn = $database->connect();
    $isConnected = $conn !== null;
    $database->disconnect();
    return $isConnected;
}

// 🧪 CÓDIGO DE TESTE (REMOVER EM PRODUÇÃO)
// Descomente a linha abaixo para testar a conexão:
// $db = new Database();
// $db->testConnection();

?>