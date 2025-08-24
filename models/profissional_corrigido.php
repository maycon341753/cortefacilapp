<?php
/**
 * Classe Profissional - VERSÃO CORRIGIDA
 * Gerencia operações relacionadas aos profissionais dos salões
 */

require_once __DIR__ . "/../config/database.php";

class Profissional {
    private $conn;
    private $table = "profissionais";
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Cadastra um novo profissional
     * @param array $dados
     * @return bool
     */
    public function cadastrar($dados) {
        try {
            $sql = "INSERT INTO {$this->table} (id_salao, nome, especialidade, telefone, email, ativo) 
                    VALUES (:id_salao, :nome, :especialidade, :telefone, :email, :ativo)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_salao", $dados["id_salao"]);
            $stmt->bindParam(":nome", $dados["nome"]);
            $stmt->bindParam(":especialidade", $dados["especialidade"]);
            $stmt->bindParam(":telefone", $dados["telefone"]);
            $stmt->bindParam(":email", $dados["email"]);
            $stmt->bindParam(":ativo", $dados["ativo"]);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao cadastrar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista profissionais por salão (em tempo real)
     * @param int $id_salao
     * @return array
     */
    public function listarPorSalao($id_salao) {
        try {
            $sql = "SELECT p.*, s.nome as nome_salao 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    WHERE p.id_salao = :id_salao 
                    ORDER BY p.ativo DESC, p.nome";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_salao", $id_salao);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar profissionais: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca profissional por ID
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT p.*, s.nome as nome_salao 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    WHERE p.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza dados do profissional
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE {$this->table} SET 
                    nome = :nome, 
                    especialidade = :especialidade, 
                    telefone = :telefone, 
                    email = :email, 
                    ativo = :ativo 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":nome", $dados["nome"]);
            $stmt->bindParam(":especialidade", $dados["especialidade"]);
            $stmt->bindParam(":telefone", $dados["telefone"]);
            $stmt->bindParam(":email", $dados["email"]);
            $stmt->bindParam(":ativo", $dados["ativo"]);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exclui profissional
     * @param int $id
     * @return bool
     */
    public function excluir($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao excluir profissional: " . $e->getMessage());
            return false;
        }
    }
}