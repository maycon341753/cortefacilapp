<?php
/**
 * Classe Profissional
 * Gerencia operações relacionadas aos profissionais dos salões
 */

require_once __DIR__ . '/../config/database.php';

class Profissional {
    private $conn;
    private $table = 'profissionais';
    
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
            $sql = "INSERT INTO {$this->table} (id_salao, nome, especialidade) 
                    VALUES (:id_salao, :nome, :especialidade)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $dados['id_salao']);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':especialidade', $dados['especialidade']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao cadastrar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista profissionais por salão
     * @param int $id_salao
     * @return array
     */
    public function listarPorSalao($id_salao) {
        try {
            $sql = "SELECT p.*, s.nome as nome_salao 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    WHERE p.id_salao = :id_salao AND p.ativo = 1 
                    ORDER BY p.nome";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao);
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
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista profissionais dos salões do parceiro
     * @param int $id_dono
     * @return array
     */
    public function listarPorDono($id_dono) {
        try {
            $sql = "SELECT p.*, s.nome as nome_salao 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    WHERE s.id_dono = :id_dono AND p.ativo = 1 
                    ORDER BY s.nome, p.nome";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_dono', $id_dono);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar profissionais do parceiro: " . $e->getMessage());
            return [];
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
            $sql = "UPDATE {$this->table} 
                    SET nome = :nome, especialidade = :especialidade 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':especialidade', $dados['especialidade']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ativa/desativa profissional
     * @param int $id
     * @param bool $ativo
     * @return bool
     */
    public function alterarStatus($id, $ativo) {
        try {
            $sql = "UPDATE {$this->table} SET ativo = :ativo WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':ativo', $ativo, PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao alterar status do profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se profissional pertence ao salão do parceiro
     * @param int $id_profissional
     * @param int $id_dono
     * @return bool
     */
    public function pertenceAoParceiro($id_profissional, $id_dono) {
        try {
            $sql = "SELECT COUNT(*) 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    WHERE p.id = :id_profissional AND s.id_dono = :id_dono";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_profissional', $id_profissional);
            $stmt->bindParam(':id_dono', $id_dono);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Erro ao verificar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todos os profissionais (para admin)
     * @return array
     */
    public function listarTodos() {
        try {
            $sql = "SELECT p.*, s.nome as nome_salao, u.nome as nome_dono 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    INNER JOIN usuarios u ON s.id_dono = u.id 
                    ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar todos os profissionais: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta total de profissionais
     * @return int
     */
    public function contarTotal() {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar profissionais: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Alias para contarTotal para compatibilidade
     * @return int
     */
    public function contar() {
        return $this->contarTotal();
    }
    
    /**
     * Conta profissionais por status
     * @param string $status
     * @return int
     */
    public function contarPorStatus($status) {
        try {
            $ativo = ($status === 'ativo') ? 1 : 0;
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE ativo = :ativo";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar profissionais por status: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Top profissionais por agendamentos
     * @param string $data_inicio
     * @param string $data_fim
     * @param int $limite
     * @return array
     */
    public function topProfissionaisPorAgendamentos($data_inicio, $data_fim, $limite = 10) {
        try {
            $sql = "SELECT p.nome, s.nome as salao_nome, COUNT(a.id) as total_agendamentos
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    INNER JOIN agendamentos a ON p.id = a.id_profissional 
                    WHERE a.data BETWEEN :data_inicio AND :data_fim 
                    GROUP BY p.id, p.nome, s.nome 
                    ORDER BY total_agendamentos DESC 
                    LIMIT :limite";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar top profissionais: " . $e->getMessage());
            return [];
        }
    }
}
?>