<?php
/**
 * Classe Usuario
 * Gerencia operações relacionadas aos usuários (clientes, parceiros, admin)
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;
    private $table = 'usuarios';
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Cadastra um novo usuário
     * @param array $dados
     * @return bool
     */
    public function cadastrar($dados) {
        try {
            // Para clientes, incluir CPF
            if ($dados['tipo_usuario'] === 'cliente') {
                $sql = "INSERT INTO {$this->table} (nome, email, senha, tipo_usuario, telefone, cpf) 
                        VALUES (:nome, :email, :senha, :tipo_usuario, :telefone, :cpf)";
                
                $stmt = $this->conn->prepare($sql);
                $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
                $cpf_limpo = preg_replace('/\D/', '', $dados['cpf']); // Remove formatação
                $stmt->bindParam(':nome', $dados['nome']);
                $stmt->bindParam(':email', $dados['email']);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':tipo_usuario', $dados['tipo_usuario']);
                $stmt->bindParam(':telefone', $dados['telefone']);
                $stmt->bindParam(':cpf', $cpf_limpo);
            } else {
                // Para parceiros, usar campos básicos (documento será tratado na tabela saloes)
                $sql = "INSERT INTO {$this->table} (nome, email, senha, tipo_usuario, telefone) 
                        VALUES (:nome, :email, :senha, :tipo_usuario, :telefone)";
                
                $stmt = $this->conn->prepare($sql);
                $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
                $stmt->bindParam(':nome', $dados['nome']);
                $stmt->bindParam(':email', $dados['email']);
                $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':tipo_usuario', $dados['tipo_usuario']);
                $stmt->bindParam(':telefone', $dados['telefone']);
            }
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao cadastrar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Realiza login do usuário
     * @param string $email
     * @param string $senha
     * @return array|false
     */
    public function login($email, $senha) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Remove a senha do retorno por segurança
                unset($usuario['senha']);
                return $usuario;
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca usuário por ID
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT id, nome, email, tipo_usuario, telefone, created_at FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca usuário por email
     * @param string $email
     * @return array|false
     */
    public function buscarPorEmail($email) {
        try {
            $sql = "SELECT id, nome, email, tipo_usuario, telefone, created_at FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar usuário por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se email já existe
     * @param string $email
     * @return bool
     */
    public function emailExiste($email) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Erro ao verificar email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se CPF já existe
     * @param string $cpf
     * @return bool
     */
    public function cpfExiste($cpf) {
        try {
            $cpfLimpo = preg_replace('/\D/', '', $cpf);
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE cpf = :cpf";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cpf', $cpfLimpo);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Erro ao verificar CPF: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cadastra dados do salão para parceiros
     * @param int $usuario_id
     * @param array $dados
     * @return bool
     */
    public function cadastrarSalao($usuario_id, $dados) {
        try {
            // SQL adaptado para a estrutura do banco online (usando id_dono)
            $sql = "INSERT INTO saloes (id_dono, nome, endereco, telefone, documento, tipo_documento, razao_social, inscricao_estadual, descricao) 
                    VALUES (:id_dono, :nome, :endereco, :telefone, :documento, :tipo_documento, :razao_social, :inscricao_estadual, :descricao)";
            
            $stmt = $this->conn->prepare($sql);
            
            // Preparar variáveis para bind
            $documento_limpo = preg_replace('/\D/', '', $dados['documento']);
            $razao_social = $dados['razao_social'] ?? null;
            $inscricao_estadual = $dados['inscricao_estadual'] ?? null;
            $telefone = $dados['telefone'] ?? '';
            $descricao = $dados['descricao'] ?? 'Salão cadastrado via sistema';
            
            // Combinar endereço completo (incluindo bairro, cidade e CEP se disponíveis)
            $endereco_completo = $dados['endereco'] ?? 'Endereço não informado';
            if (!empty($dados['bairro']) || !empty($dados['cidade']) || !empty($dados['cep'])) {
                $endereco_completo .= ', ' . ($dados['bairro'] ?? '') . ', ' . ($dados['cidade'] ?? '') . ' - CEP: ' . ($dados['cep'] ?? '');
            }
            
            $stmt->bindParam(':id_dono', $usuario_id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':endereco', $endereco_completo);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':documento', $documento_limpo);
            $stmt->bindParam(':tipo_documento', $dados['tipo_documento']);
            $stmt->bindParam(':razao_social', $razao_social);
            $stmt->bindParam(':inscricao_estadual', $inscricao_estadual);
            $stmt->bindParam(':descricao', $descricao);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao cadastrar salão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cadastra parceiro com transação (usuário + salão)
     * @param array $dadosUsuario
     * @param array $dadosSalao
     * @return bool
     */
    public function cadastrarParceiro($dadosUsuario, $dadosSalao) {
        try {
            $this->conn->beginTransaction();
            
            // Cadastrar usuário
            $resultadoUsuario = $this->cadastrar($dadosUsuario);
            if ($resultadoUsuario) {
                $usuario_id = $this->conn->lastInsertId();
                error_log("Usuário cadastrado com ID: " . $usuario_id);
                
                // Verificar se o ID foi obtido corretamente
                if ($usuario_id == 0 || empty($usuario_id)) {
                    // Tentar obter o ID de outra forma
                    $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ? ORDER BY id DESC LIMIT 1");
                    $stmt->execute([$dadosUsuario['email']]);
                    $user = $stmt->fetch();
                    $usuario_id = $user ? $user['id'] : 0;
                    error_log("ID obtido via SELECT: " . $usuario_id);
                }
                
                if ($usuario_id > 0) {
                    // Cadastrar salão
                    if ($this->cadastrarSalao($usuario_id, $dadosSalao)) {
                        $this->conn->commit();
                        error_log("Parceiro cadastrado com sucesso - Usuario ID: " . $usuario_id);
                        return true;
                    } else {
                        $this->conn->rollback();
                        error_log("Erro ao cadastrar salão - rollback executado");
                        return false;
                    }
                } else {
                    $this->conn->rollback();
                    error_log("Erro: ID do usuário não foi obtido corretamente");
                    return false;
                }
            } else {
                $this->conn->rollback();
                error_log("Erro ao cadastrar usuário - rollback executado");
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Erro ao cadastrar parceiro: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Verifica se documento já existe na tabela saloes
     * @param string $documento
     * @return bool
     */
    public function documentoSalaoExiste($documento) {
        try {
            $documentoLimpo = preg_replace('/\D/', '', $documento);
            $sql = "SELECT COUNT(*) FROM saloes WHERE documento = :documento";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':documento', $documentoLimpo);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Erro ao verificar documento do salão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todos os usuários (para admin)
     * @param string $tipo_usuario
     * @return array
     */
    public function listarTodos($tipo_usuario = null) {
        try {
            $sql = "SELECT id, nome, email, tipo_usuario, telefone, created_at FROM {$this->table}";
            
            if ($tipo_usuario) {
                $sql .= " WHERE tipo_usuario = :tipo_usuario";
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            
            if ($tipo_usuario) {
                $stmt->bindParam(':tipo_usuario', $tipo_usuario);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Atualiza dados do usuário
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE {$this->table} SET nome = :nome, telefone = :telefone WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Conta total de usuários
     * @return int
     */
    public function contarTotal() {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar usuários: " . $e->getMessage());
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
     * Conta usuários por tipo
     * @param string $tipo
     * @return int
     */
    public function contarPorTipo($tipo) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE tipo_usuario = :tipo";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar usuários por tipo: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta usuários por status (ativo/inativo baseado em data de acesso)
     * @param string $status
     * @return int
     */
    public function contarPorStatus($status) {
        try {
            // Como não há campo status na tabela, vamos considerar ativo se logou nos últimos 30 dias
            if ($status === 'ativo') {
                $sql = "SELECT COUNT(*) FROM {$this->table} WHERE ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            } else {
                $sql = "SELECT COUNT(*) FROM {$this->table} WHERE ultimo_acesso < DATE_SUB(NOW(), INTERVAL 30 DAY) OR ultimo_acesso IS NULL";
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar usuários por status: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta novos usuários por período
     * @param string $data_inicio
     * @param string $data_fim
     * @return int
     */
    public function contarNovosPorPeriodo($data_inicio, $data_fim) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE DATE(created_at) BETWEEN :data_inicio AND :data_fim";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar novos usuários por período: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lista usuários com filtros para admin
     * @param array $filtros
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function listarComFiltrosAdmin($filtros = [], $limite = 20, $offset = 0) {
        try {
            $sql = "SELECT id, nome, email, tipo_usuario, telefone, created_at, ultimo_acesso FROM {$this->table} WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo_usuario = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }
            
            if (!empty($filtros['status'])) {
                if ($filtros['status'] === 'ativo') {
                    $sql .= " AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                } else {
                    $sql .= " AND (ultimo_acesso < DATE_SUB(NOW(), INTERVAL 30 DAY) OR ultimo_acesso IS NULL)";
                }
            }
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (nome LIKE :busca OR email LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND DATE(created_at) >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND DATE(created_at) <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar usuários com filtros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta usuários com filtros para admin
     * @param array $filtros
     * @return int
     */
    public function contarComFiltrosAdmin($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
            $params = [];
            
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo_usuario = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }
            
            if (!empty($filtros['status'])) {
                if ($filtros['status'] === 'ativo') {
                    $sql .= " AND ultimo_acesso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                } else {
                    $sql .= " AND (ultimo_acesso < DATE_SUB(NOW(), INTERVAL 30 DAY) OR ultimo_acesso IS NULL)";
                }
            }
            
            if (!empty($filtros['busca'])) {
                $sql .= " AND (nome LIKE :busca OR email LIKE :busca)";
                $params[':busca'] = '%' . $filtros['busca'] . '%';
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND DATE(created_at) >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND DATE(created_at) <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar usuários com filtros: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Lista usuários recentes
     * @param int $limite
     * @return array
     */
    public function listarRecentes($limite = 10) {
        try {
            $sql = "SELECT id, nome, email, tipo_usuario, telefone, created_at, ultimo_acesso FROM {$this->table} 
                    ORDER BY created_at DESC 
                    LIMIT :limite";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar usuários recentes: " . $e->getMessage());
            return [];
        }
    }
}
?>