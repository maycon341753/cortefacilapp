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
     * @param mixed $dados_ou_id_salao Array de dados ou ID do salão
     * @param string $nome Nome do profissional (se usando parâmetros individuais)
     * @param string $especialidade Especialidade (se usando parâmetros individuais)
     * @param string $telefone Telefone (se usando parâmetros individuais)
     * @param string $email Email (se usando parâmetros individuais)
     * @return int|bool ID do profissional cadastrado ou false em caso de erro
     */
    public function cadastrar($dados_ou_id_salao, $nome = null, $especialidade = null, $telefone = null, $email = null) {
        try {
            // Verificar se foi passado array ou parâmetros individuais
            if (is_array($dados_ou_id_salao)) {
                // Modo array (compatibilidade com a página)
                $dados = $dados_ou_id_salao;
                $id_salao = $dados['salao_id'] ?? $dados['id_salao'];
                $nome = $dados['nome'];
                $especialidade = $dados['especialidade'];
                $telefone = $dados['telefone'] ?? null;
                $email = $dados['email'] ?? null;
                $status = ($dados['ativo'] ?? 1) ? 'ativo' : 'inativo';
            } else {
                // Modo parâmetros individuais
                $id_salao = $dados_ou_id_salao;
                $status = 'ativo'; // Padrão para novos profissionais
            }
            
            $sql = "INSERT INTO {$this->table} (id_salao, nome, especialidade, telefone, email, status) 
                    VALUES (:id_salao, :nome, :especialidade, :telefone, :email, :status)";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':id_salao', $id_salao, PDO::PARAM_INT);
            $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindParam(':especialidade', $especialidade, PDO::PARAM_STR);
            $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
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
            // Consulta simplificada sem JOIN para evitar problemas
            $sql = "SELECT * FROM {$this->table} WHERE id_salao = :id_salao ORDER BY nome";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao, PDO::PARAM_INT);
            $stmt->execute();
            
            $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adicionar nome do salão se necessário
            if (!empty($profissionais)) {
                $sql_salao = "SELECT nome FROM saloes WHERE id = :id_salao";
                $stmt_salao = $this->conn->prepare($sql_salao);
                $stmt_salao->bindParam(':id_salao', $id_salao, PDO::PARAM_INT);
                $stmt_salao->execute();
                $salao = $stmt_salao->fetch(PDO::FETCH_ASSOC);
                
                foreach ($profissionais as &$prof) {
                    $prof['nome_salao'] = $salao ? $salao['nome'] : '';
                }
            }
            
            return $profissionais;
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
                    WHERE s.id_dono = :id_dono AND p.status = 'ativo' 
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
                    SET nome = :nome, especialidade = :especialidade, telefone = :telefone, email = :email, status = :status 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':especialidade', $dados['especialidade']);
            $stmt->bindParam(':telefone', $dados['telefone'] ?? null);
            $stmt->bindParam(':email', $dados['email'] ?? null);
            $status = ($dados['ativo'] ?? 1) ? 'ativo' : 'inativo';
            $stmt->bindParam(':status', $status);
            
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
            $status = $ativo ? 'ativo' : 'inativo';
            $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao alterar status do profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exclui profissional completamente do banco de dados
     * @param int $id
     * @return bool
     */
    public function excluir($id) {
        try {
            // Verificar se há agendamentos futuros
            $sql_check = "SELECT COUNT(*) as total FROM agendamentos WHERE id_profissional = :id AND data >= CURDATE() AND status IN ('agendado', 'confirmado')";
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':id', $id);
            $stmt_check->execute();
            $result = $stmt_check->fetch();
            
            if ($result['total'] > 0) {
                throw new Exception('Não é possível excluir profissional com agendamentos futuros. Desative-o ao invés de excluir.');
            }
            
            // Iniciar transação
            $this->conn->beginTransaction();
            
            // Excluir agendamentos antigos do profissional
            $sql_agendamentos = "DELETE FROM agendamentos WHERE id_profissional = :id";
            $stmt_agendamentos = $this->conn->prepare($sql_agendamentos);
            $stmt_agendamentos->bindParam(':id', $id);
            $stmt_agendamentos->execute();
            
            // Excluir o profissional
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();
            
            // Confirmar transação
            $this->conn->commit();
            
            return $result;
        } catch(Exception $e) {
            // Reverter transação em caso de erro
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Erro ao excluir profissional: " . $e->getMessage());
            throw $e;
        } catch(PDOException $e) {
            // Reverter transação em caso de erro
            if ($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Erro ao excluir profissional: " . $e->getMessage());
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
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status = :status";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status', $status);
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