<?php
/**
 * Classe Salao
 * Gerencia operações relacionadas aos salões de beleza
 */

require_once __DIR__ . '/../config/database.php';

class Salao {
    private $conn;
    private $table = 'saloes';
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Cadastra um novo salão
     * @param array $dados
     * @return bool
     */
    public function cadastrar($dados) {
        try {
            $sql = "INSERT INTO {$this->table} (id_dono, nome, endereco, telefone, descricao) 
                    VALUES (:id_dono, :nome, :endereco, :telefone, :descricao)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_dono', $dados['id_dono']);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':endereco', $dados['endereco']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':descricao', $dados['descricao']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao cadastrar salão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todos os salões ativos
     * @return array
     */
    public function listarAtivos() {
        try {
            $sql = "SELECT s.*, u.nome as nome_dono 
                    FROM {$this->table} s 
                    INNER JOIN usuarios u ON s.id_dono = u.id 
                    WHERE s.ativo = 1 
                    ORDER BY s.nome";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar salões: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca salão por ID
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT s.*, u.nome as nome_dono 
                    FROM {$this->table} s 
                    INNER JOIN usuarios u ON s.id_dono = u.id 
                    WHERE s.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar salão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca salões do parceiro
     * @param int $id_dono
     * @return array
     */
    public function buscarPorDono($id_dono) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_dono = :id_dono LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_dono', $id_dono);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar salão do parceiro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza dados do salão
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE {$this->table} 
                    SET nome = :nome, endereco = :endereco, telefone = :telefone, descricao = :descricao 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':endereco', $dados['endereco']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':descricao', $dados['descricao']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar salão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ativa/desativa salão
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
            error_log("Erro ao alterar status do salão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Conta total de salões
     * @return int
     */
    public function contarTotal() {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar salões: " . $e->getMessage());
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
     * Conta salões por status
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
            error_log("Erro ao contar salões por status: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lista salões com filtros para admin
     * @param array $filtros
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function listarComFiltrosAdmin($filtros = [], $limite = 20, $offset = 0) {
        try {
            $sql = "SELECT s.*, u.nome as proprietario_nome, u.email as proprietario_email,
                           (SELECT COUNT(*) FROM profissionais p WHERE p.id_salao = s.id) as total_profissionais,
                           (SELECT COUNT(*) FROM agendamentos a WHERE a.id_salao = s.id) as total_agendamentos,
                           (SELECT SUM(a.valor_taxa) FROM agendamentos a WHERE a.id_salao = s.id AND a.status IN ('confirmado', 'concluido')) as receita_total
                    FROM {$this->table} s 
                    INNER JOIN usuarios u ON s.id_dono = u.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['status'])) {
                $ativo = ($filtros['status'] === 'ativo') ? 1 : 0;
                $sql .= " AND s.ativo = :ativo";
                $params[':ativo'] = $ativo;
            }
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (s.nome LIKE :busca OR s.endereco LIKE :busca OR u.nome LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            
            if (!empty($filtros['cidade'])) {
                $sql .= " AND s.endereco LIKE :cidade";
                $params[':cidade'] = '%' . $filtros['cidade'] . '%';
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND DATE(s.created_at) >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND DATE(s.created_at) <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            
            $sql .= " ORDER BY s.created_at DESC LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar salões com filtros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta salões com filtros para admin
     * @param array $filtros
     * @return int
     */
    public function contarComFiltrosAdmin($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} s 
                    INNER JOIN usuarios u ON s.id_dono = u.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['status'])) {
                $ativo = ($filtros['status'] === 'ativo') ? 1 : 0;
                $sql .= " AND s.ativo = :ativo";
                $params[':ativo'] = $ativo;
            }
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (s.nome LIKE :busca OR s.endereco LIKE :busca OR u.nome LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            
            if (!empty($filtros['cidade'])) {
                $sql .= " AND s.endereco LIKE :cidade";
                $params[':cidade'] = '%' . $filtros['cidade'] . '%';
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND DATE(s.created_at) >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND DATE(s.created_at) <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar salões com filtros: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lista todos os salões para seleção
     * @return array
     */
    public function listarTodos() {
        try {
            $sql = "SELECT id, nome, endereco FROM {$this->table} 
                    WHERE ativo = 1 
                    ORDER BY nome";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar todos os salões: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista todos os salões (para admin)
     * @return array
     */
    public function listarTodosAdmin() {
        try {
            $sql = "SELECT s.*, u.nome as nome_dono 
                    FROM {$this->table} s 
                    INNER JOIN usuarios u ON s.id_dono = u.id 
                    ORDER BY s.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar todos os salões: " . $e->getMessage());
            return [];
        }
    }
}
?>