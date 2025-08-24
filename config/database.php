<?php
/**
 * Configuração do banco de dados
 * Arquivo de configuração para conexão com MySQL
 * Detecta automaticamente o ambiente (local vs online)
 * VERSÃO CORRIGIDA - Agosto 2025
 */

class Database {
    private static $instance = null;
    private static $connection = null;
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    private function __construct() {
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
     * Implementação do padrão Singleton
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Detecta se está rodando em ambiente local
     * @return bool
     */
    private function isLocalEnvironment() {
        // 0. PRIORIDADE ABSOLUTA: Verificar arquivo .env.online
        if (file_exists(__DIR__ . '/../.env.online')) {
            return false; // FORÇAR ONLINE
        }
        
        // 1. PRIORIDADE MÁXIMA: Verificar se está sendo acessado via domínio online
        $serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
        
        // Se está sendo acessado via cortefacil.app, é SEMPRE online
        if (strpos($serverName, 'cortefacil.app') !== false) {
            return false; // FORÇAR ONLINE
        }
        
        // 1.1. VERIFICAÇÃO ADICIONAL: Se está sendo executado via web e não é localhost
        if (!empty($serverName) && !in_array($serverName, ['localhost', '127.0.0.1', '::1'])) {
            // Se tem um domínio real (não localhost), provavelmente é online
            return false;
        }
        
        // 2. Verificar se está sendo executado via CLI (linha de comando)
        if (php_sapi_name() === 'cli') {
            // No CLI, verificar se existe arquivo indicador de ambiente
            if (file_exists(__DIR__ . '/../.env.online')) {
                return false; // Arquivo indica ambiente online
            }
            // Por padrão, CLI é considerado local para desenvolvimento
            return true;
        }
        
        // 3. Verificar hosts locais tradicionais
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        if (in_array($serverName, $localHosts)) {
            return true;
        }
        
        // 4. Verificar se está rodando no XAMPP/WAMP
        if (strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'xampp') !== false || 
            strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'wamp') !== false) {
            return true;
        }
        
        // 5. Verificar se está usando servidor PHP built-in (php -S)
        if (isset($_SERVER['SERVER_SOFTWARE']) && 
            strpos($_SERVER['SERVER_SOFTWARE'], 'PHP') !== false) {
            return true;
        }
        
        // 6. Verificar porta de desenvolvimento
        $port = $_SERVER['SERVER_PORT'] ?? 80;
        if (in_array($port, [8000, 8080, 3000, 4000, 5000])) {
            return true;
        }
        
        // 7. Se chegou até aqui e não é nenhum caso local, é online
        return false;
    }
    
    /**
     * Conecta ao banco de dados MySQL usando Singleton com fallback automático
     * @return PDO|null
     */
    public function connect() {
        // Se já existe uma conexão singleton, reutilizar
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        // Tentar conexão com fallback automático
        return $this->connectWithFallback();
    }
    
    /**
     * Conecta com fallback automático: tenta online primeiro, depois local
     * @return PDO|null
     */
    private function connectWithFallback() {
        $originalIsLocal = $this->isLocalEnvironment();
        
        // Se não é ambiente local, tentar conexão online primeiro
        if (!$originalIsLocal) {
            if ($this->tryConnection('online')) {
                return self::$connection;
            }
            
            // Se falhou online, tentar local como fallback
            error_log('Fallback: Tentando conexão local após falha online');
            if ($this->tryConnection('local')) {
                return self::$connection;
            }
        } else {
            // Se é ambiente local, tentar local primeiro
            if ($this->tryConnection('local')) {
                return self::$connection;
            }
        }
        
        // Se chegou aqui, ambas as conexões falharam
        $this->handleConnectionFailure();
        return null;
    }
    
    /**
     * Tenta estabelecer conexão com configuração específica
     * @param string $type 'online' ou 'local'
     * @return bool
     */
    private function tryConnection($type) {
        try {
            // Configurar credenciais baseado no tipo
            if ($type === 'online') {
                $host = 'srv486.hstgr.io';
                $db_name = 'u690889028_cortefacil';
                $username = 'u690889028_mayconwender';
                $password = 'Maycon341753';
            } else {
                $host = 'localhost';
                $db_name = 'u690889028_cortefacil';
                $username = 'root';
                $password = '';
            }
            
            // String de conexão DSN
            $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
            
            // Opções de conexão otimizadas
            $options = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => ($type === 'online' ? 10 : 5), // Timeout menor para fallback
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            );
            
            // Estabelecer conexão
            self::$connection = new PDO($dsn, $username, $password, $options);
            $this->conn = self::$connection;
            
            // Configurações de charset
            self::$connection->exec("SET NAMES utf8mb4");
            self::$connection->exec("SET CHARACTER SET utf8mb4");
            self::$connection->exec("SET character_set_connection=utf8mb4");
            self::$connection->exec("SET collation_connection=utf8mb4_unicode_ci");
            
            // Atualizar propriedades da instância
            $this->host = $host;
            $this->db_name = $db_name;
            $this->username = $username;
            $this->password = $password;
            
            // Log de sucesso
            error_log("Conexão {$type} estabelecida com sucesso");
            
            return true;
            
        } catch(PDOException $e) {
            // Log do erro sem exibir para o usuário
            error_log("Falha na conexão {$type}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Trata falha de conexão de forma amigável
     */
    private function handleConnectionFailure() {
        // Log do erro crítico
        error_log('CRÍTICO: Todas as tentativas de conexão falharam');
        
        // Não exibir erro diretamente - será tratado pela aplicação
        // A aplicação deve verificar se a conexão é null e tratar adequadamente
    }
    
    /**
     * Fecha a conexão singleton (usar com cuidado)
     */
    public static function closeConnection() {
        self::$connection = null;
    }
    
    /**
     * Retorna informações de debug (apenas em ambiente local)
     * @return array
     */
    public function getDebugInfo() {
        if (!$this->isLocalEnvironment()) {
            return ['error' => 'Debug info only available in local environment'];
        }
        
        return [
            'host' => $this->host,
            'database' => $this->db_name,
            'username' => $this->username,
            'password_set' => !empty($this->password),
            'environment' => $this->isLocalEnvironment() ? 'local' : 'online',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'not set',
            'http_host' => $_SERVER['HTTP_HOST'] ?? 'not set',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set'
        ];
    }
    
    /**
     * Força o uso de configurações online (para testes)
     */
    public function forceOnlineConfig() {
        $this->host = 'srv486.hstgr.io';
        $this->db_name = 'u690889028_cortefacil';
        $this->username = 'u690889028_mayconwender';
        $this->password = 'Maycon341753';
    }
}

/**
 * Função auxiliar para obter conexão usando Singleton (compatibilidade)
 * @return PDO|null
 */
function getConnection() {
    $db = Database::getInstance();
    return $db->connect();
}

/**
 * Função auxiliar para obter instância singleton da classe Database
 * @return Database
 */
function getDatabaseInstance() {
    return Database::getInstance();
}
?>