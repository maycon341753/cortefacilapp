<?php
/**
 * Configuração de conexão com o banco de dados MySQL
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'cortefacil_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe para gerenciar conexão com banco de dados
 */
class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    /**
     * Estabelece conexão com o banco de dados
     */
    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    /**
     * Retorna a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Executa uma query preparada
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erro na execução da query: " . $e->getMessage());
        }
    }
    
    /**
     * Retorna o último ID inserido
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->connection->rollback();
    }
}

// Instância global da conexão
$database = new Database();
$db = $database->getConnection();
?>