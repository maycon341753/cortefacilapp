<?php
/**
 * Modelo para Serviços
 * Gerencia operações CRUD para serviços dos salões
 */

require_once __DIR__ . '/../config/database.php';

class Servico {
    private $conn;
    private $table = 'servicos';
    
    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->connect();
    }
    
    /**
     * Listar serviços por salão
     */
    public function listarPorSalao($id_salao) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id_salao = :id_salao ORDER BY nome ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_salao', $id_salao, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar serviços: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar serviço por ID
     */
    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao buscar serviço: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar novo serviço
     */
    public function criar($dados) {
        try {
            $query = "INSERT INTO {$this->table} (id_salao, nome, descricao, preco, duracao_minutos, ativo) 
                     VALUES (:id_salao, :nome, :descricao, :preco, :duracao_minutos, :ativo)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_salao', $dados['id_salao'], PDO::PARAM_INT);
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->bindParam(':descricao', $dados['descricao'], PDO::PARAM_STR);
            $stmt->bindParam(':preco', $dados['preco'], PDO::PARAM_STR);
            $stmt->bindParam(':duracao_minutos', $dados['duracao_minutos'], PDO::PARAM_INT);
            $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao criar serviço: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar serviço
     */
    public function atualizar($id, $dados) {
        try {
            $query = "UPDATE {$this->table} SET 
                     nome = :nome, 
                     descricao = :descricao, 
                     preco = :preco, 
                     duracao_minutos = :duracao_minutos, 
                     ativo = :ativo,
                     updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->bindParam(':descricao', $dados['descricao'], PDO::PARAM_STR);
            $stmt->bindParam(':preco', $dados['preco'], PDO::PARAM_STR);
            $stmt->bindParam(':duracao_minutos', $dados['duracao_minutos'], PDO::PARAM_INT);
            $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao atualizar serviço: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Excluir serviço
     */
    public function excluir($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao excluir serviço: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ativar/Desativar serviço
     */
    public function alterarStatus($id, $ativo) {
        try {
            $query = "UPDATE {$this->table} SET ativo = :ativo, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao alterar status do serviço: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar serviços ativos por salão
     */
    public function contarAtivosPorSalao($id_salao) {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE id_salao = :id_salao AND ativo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_salao', $id_salao, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Erro ao contar serviços ativos: " . $e->getMessage());
            return 0;
        }
    }
}
?>