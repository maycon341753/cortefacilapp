<?php
/**
 * Classe Agendamento
 * Gerencia operações relacionadas aos agendamentos
 */

require_once __DIR__ . '/../config/database.php';

class Agendamento {
    private $conn;
    private $table = 'agendamentos';
    
    public function __construct($conn = null) {
        if ($conn !== null) {
            $this->conn = $conn;
        } else {
            $this->conn = getConnection();
        }
    }
    
    /**
     * Cria um novo agendamento
     * @param array $dados
     * @return bool|int
     */
    public function criar($dados) {
        try {
            // Verifica se o horário está disponível
            if (!$this->verificarDisponibilidade($dados['id_profissional'], $dados['data'], $dados['hora'])) {
                return false;
            }
            
            $sql = "INSERT INTO {$this->table} (id_cliente, id_salao, id_profissional, data, hora, observacoes) 
                    VALUES (:id_cliente, :id_salao, :id_profissional, :data, :hora, :observacoes)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_cliente', $dados['id_cliente']);
            $stmt->bindParam(':id_salao', $dados['id_salao']);
            $stmt->bindParam(':id_profissional', $dados['id_profissional']);
            $stmt->bindParam(':data', $dados['data']);
            $stmt->bindParam(':hora', $dados['hora']);
            $stmt->bindParam(':observacoes', $dados['observacoes']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Erro ao criar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica disponibilidade de horário
     * @param int $id_profissional
     * @param string $data
     * @param string $hora
     * @return bool
     */
    public function verificarDisponibilidade($id_profissional, $data, $hora) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE id_profissional = :id_profissional 
                    AND data = :data 
                    AND hora = :hora 
                    AND status != 'cancelado'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_profissional', $id_profissional);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':hora', $hora);
            $stmt->execute();
            
            return $stmt->fetchColumn() == 0;
        } catch(PDOException $e) {
            error_log("Erro ao verificar disponibilidade: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista horários ocupados de um profissional em uma data
     * @param int $id_profissional
     * @param string $data
     * @return array
     */
    public function listarHorariosOcupados($id_profissional, $data) {
        try {
            // Primeiro tenta com a coluna status (para compatibilidade)
            $sql = "SELECT hora FROM {$this->table} 
                    WHERE id_profissional = :id_profissional 
                    AND data = :data 
                    AND status != 'cancelado' 
                    ORDER BY hora";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_profissional', $id_profissional);
            $stmt->bindParam(':data', $data);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            // Se falhar (coluna status não existe), tenta sem a coluna status
            if (strpos($e->getMessage(), 'status') !== false) {
                try {
                    $sql = "SELECT hora FROM {$this->table} 
                            WHERE id_profissional = :id_profissional 
                            AND data = :data 
                            ORDER BY hora";
                    
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':id_profissional', $id_profissional);
                    $stmt->bindParam(':data', $data);
                    $stmt->execute();
                    
                    return $stmt->fetchAll(PDO::FETCH_COLUMN);
                } catch(PDOException $e2) {
                    error_log("Erro ao listar horários ocupados (fallback): " . $e2->getMessage());
                    return [];
                }
            }
            error_log("Erro ao listar horários ocupados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista agendamentos do cliente
     * @param int $id_cliente
     * @return array
     */
    public function listarPorCliente($id_cliente) {
        try {
            $sql = "SELECT a.*, s.nome as nome_salao, p.nome as nome_profissional, p.especialidade 
                    FROM {$this->table} a 
                    INNER JOIN saloes s ON a.id_salao = s.id 
                    INNER JOIN profissionais p ON a.id_profissional = p.id 
                    WHERE a.id_cliente = :id_cliente 
                    ORDER BY a.data DESC, a.hora DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_cliente', $id_cliente);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar agendamentos do cliente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista agendamentos do salão
     * @param int $id_salao
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function listarPorSalao($id_salao, $data_inicio = null, $data_fim = null) {
        try {
            $sql = "SELECT a.*, u.nome as nome_cliente, u.telefone as telefone_cliente, 
                           p.nome as nome_profissional, p.especialidade 
                    FROM {$this->table} a 
                    INNER JOIN usuarios u ON a.id_cliente = u.id 
                    INNER JOIN profissionais p ON a.id_profissional = p.id 
                    WHERE a.id_salao = :id_salao";
            
            if ($data_inicio && $data_fim) {
                $sql .= " AND a.data BETWEEN :data_inicio AND :data_fim";
            }
            
            $sql .= " ORDER BY a.data ASC, a.hora ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao);
            
            if ($data_inicio && $data_fim) {
                $stmt->bindParam(':data_inicio', $data_inicio);
                $stmt->bindParam(':data_fim', $data_fim);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar agendamentos do salão: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca agendamento por ID
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT a.*, u.nome as nome_cliente, u.telefone as telefone_cliente, 
                           s.nome as nome_salao, p.nome as nome_profissional, p.especialidade 
                    FROM {$this->table} a 
                    INNER JOIN usuarios u ON a.id_cliente = u.id 
                    INNER JOIN saloes s ON a.id_salao = s.id 
                    INNER JOIN profissionais p ON a.id_profissional = p.id 
                    WHERE a.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza status do agendamento
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function atualizarStatus($id, $status) {
        try {
            $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancela agendamento
     * @param int $id
     * @param int $id_usuario
     * @return bool
     */
    public function cancelar($id, $id_usuario) {
        try {
            // Verifica se o usuário pode cancelar este agendamento
            $agendamento = $this->buscarPorId($id);
            if (!$agendamento || $agendamento['id_cliente'] != $id_usuario) {
                return false;
            }
            
            return $this->atualizarStatus($id, 'cancelado');
        } catch(PDOException $e) {
            error_log("Erro ao cancelar agendamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista todos os agendamentos (para admin)
     * @return array
     */
    public function listarTodos() {
        try {
            $sql = "SELECT a.*, u.nome as nome_cliente, s.nome as nome_salao, 
                           p.nome as nome_profissional 
                    FROM {$this->table} a 
                    INNER JOIN usuarios u ON a.id_cliente = u.id 
                    INNER JOIN saloes s ON a.id_salao = s.id 
                    INNER JOIN profissionais p ON a.id_profissional = p.id 
                    ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar todos os agendamentos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta total de agendamentos
     * @return int
     */
    public function contarTotal() {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar agendamentos: " . $e->getMessage());
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
     * Lista agendamentos com filtros para admin
     * @param array $filtros
     * @param int $limite
     * @param int $offset
     * @return array
     */
    public function listarComFiltrosAdmin($filtros = [], $limite = 20, $offset = 0) {
        try {
            $sql = "SELECT a.*, u.nome as cliente_nome, u.email as cliente_email,
                           s.nome as salao_nome, s.endereco as salao_endereco,
                           p.nome as profissional_nome
                    FROM {$this->table} a 
                    INNER JOIN usuarios u ON a.id_cliente = u.id 
                    INNER JOIN saloes s ON a.id_salao = s.id 
                    INNER JOIN profissionais p ON a.id_profissional = p.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['status'])) {
                $sql .= " AND a.status = :status";
                $params[':status'] = $filtros['status'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND a.data >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND a.data <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            
            if (!empty($filtros['salao_id'])) {
                $sql .= " AND a.id_salao = :salao_id";
                $params[':salao_id'] = $filtros['salao_id'];
            }
            
            if (!empty($filtros['cliente_busca'])) {
                $sql .= " AND u.nome LIKE :cliente_busca";
                $params[':cliente_busca'] = '%' . $filtros['cliente_busca'] . '%';
            }
            
            $sql .= " ORDER BY a.data DESC, a.hora DESC LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar agendamentos com filtros: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta agendamentos com filtros para admin
     * @param array $filtros
     * @return int
     */
    public function contarComFiltrosAdmin($filtros = []) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} a 
                    INNER JOIN usuarios u ON a.id_cliente = u.id 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['status'])) {
                $sql .= " AND a.status = :status";
                $params[':status'] = $filtros['status'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND a.data >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND a.data <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'];
            }
            
            if (!empty($filtros['salao_id'])) {
                $sql .= " AND a.id_salao = :salao_id";
                $params[':salao_id'] = $filtros['salao_id'];
            }
            
            if (!empty($filtros['cliente_busca'])) {
                $sql .= " AND u.nome LIKE :cliente_busca";
                $params[':cliente_busca'] = '%' . $filtros['cliente_busca'] . '%';
            }
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar agendamentos com filtros: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta agendamentos por status
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
            error_log("Erro ao contar agendamentos por status: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula receita total
     * @return float
     */
    public function calcularReceitaTotal() {
        try {
            $sql = "SELECT SUM(valor_taxa) FROM {$this->table} WHERE status IN ('confirmado', 'concluido')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return floatval($stmt->fetchColumn());
        } catch(PDOException $e) {
            error_log("Erro ao calcular receita total: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcula receita por período
     * @param string $data_inicio
     * @param string $data_fim
     * @return float
     */
    public function calcularReceitaPorPeriodo($data_inicio, $data_fim) {
        try {
            $sql = "SELECT SUM(valor_taxa) FROM {$this->table} 
                    WHERE status IN ('confirmado', 'concluido') 
                    AND data BETWEEN :data_inicio AND :data_fim";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->execute();
            
            return floatval($stmt->fetchColumn());
        } catch(PDOException $e) {
            error_log("Erro ao calcular receita por período: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta agendamentos por período
     * @param string $data_inicio
     * @param string $data_fim
     * @return int
     */
    public function contarPorPeriodo($data_inicio, $data_fim) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                    WHERE data BETWEEN :data_inicio AND :data_fim";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar agendamentos por período: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta agendamentos por data específica
     * @param string $data
     * @return int
     */
    public function contarPorData($data) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE data = :data";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data', $data);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log("Erro ao contar agendamentos por data: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Conta agendamentos por status em um período
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function contarPorStatusPeriodo($data_inicio, $data_fim) {
        try {
            $sql = "SELECT status, COUNT(*) as total FROM {$this->table} 
                    WHERE data BETWEEN :data_inicio AND :data_fim 
                    GROUP BY status";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->execute();
            
            $resultado = [];
            while ($row = $stmt->fetch()) {
                $resultado[$row['status']] = $row['total'];
            }
            
            return $resultado;
        } catch(PDOException $e) {
            error_log("Erro ao contar agendamentos por status no período: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Conta agendamentos por dia da semana
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function contarPorDiaSemana($data_inicio, $data_fim) {
        try {
            $sql = "SELECT DAYNAME(data) as dia_semana, COUNT(*) as total 
                    FROM {$this->table} 
                    WHERE data BETWEEN :data_inicio AND :data_fim 
                    GROUP BY DAYNAME(data), DAYOFWEEK(data)
                    ORDER BY DAYOFWEEK(data)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->execute();
            
            $resultado = [];
            $dias_pt = [
                'Sunday' => 'domingo',
                'Monday' => 'segunda',
                'Tuesday' => 'terca',
                'Wednesday' => 'quarta',
                'Thursday' => 'quinta',
                'Friday' => 'sexta',
                'Saturday' => 'sabado'
            ];
            
            while ($row = $stmt->fetch()) {
                $dia_pt = $dias_pt[$row['dia_semana']] ?? strtolower($row['dia_semana']);
                $resultado[$dia_pt] = $row['total'];
            }
            
            return $resultado;
        } catch(PDOException $e) {
            error_log("Erro ao contar agendamentos por dia da semana: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Top salões por agendamentos
     * @param string $data_inicio
     * @param string $data_fim
     * @param int $limite
     * @return array
     */
    public function topSaloesPorAgendamentos($data_inicio, $data_fim, $limite = 10) {
        try {
            $sql = "SELECT s.nome, COUNT(a.id) as total_agendamentos, 
                           SUM(a.valor_taxa) as receita
                    FROM {$this->table} a 
                    INNER JOIN saloes s ON a.id_salao = s.id 
                    WHERE a.data BETWEEN :data_inicio AND :data_fim 
                    GROUP BY s.id, s.nome 
                    ORDER BY total_agendamentos DESC 
                    LIMIT :limite";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar top salões: " . $e->getMessage());
            return [];
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
                    FROM {$this->table} a 
                    INNER JOIN profissionais p ON a.id_profissional = p.id 
                    INNER JOIN saloes s ON a.id_salao = s.id 
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
    
    /**
     * Horários mais populares
     * @param string $data_inicio
     * @param string $data_fim
     * @return array
     */
    public function horariosPopulares($data_inicio, $data_fim) {
        try {
            $sql = "SELECT hora, COUNT(*) as total 
                    FROM {$this->table} 
                    WHERE data BETWEEN :data_inicio AND :data_fim 
                    GROUP BY hora 
                    ORDER BY total DESC 
                    LIMIT 6";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':data_inicio', $data_inicio);
            $stmt->bindParam(':data_fim', $data_fim);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar horários populares: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gera horários disponíveis para um profissional em uma data
     * @param int $id_profissional
     * @param string $data
     * @return array
     */
    public function gerarHorariosDisponiveis($id_profissional, $data) {
        try {
            // Buscar o salão do profissional
            try {
                $sql_prof = "SELECT id_salao FROM profissionais WHERE id = :id_profissional AND status = 'ativo'";
                $stmt_prof = $this->conn->prepare($sql_prof);
                $stmt_prof->bindParam(':id_profissional', $id_profissional);
                $stmt_prof->execute();
                
                $profissional = $stmt_prof->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                // Se falhar (coluna status não existe), tenta sem a coluna status
                if (strpos($e->getMessage(), 'status') !== false) {
                    $sql_prof = "SELECT id_salao FROM profissionais WHERE id = :id_profissional";
                    $stmt_prof = $this->conn->prepare($sql_prof);
                    $stmt_prof->bindParam(':id_profissional', $id_profissional);
                    $stmt_prof->execute();
                    
                    $profissional = $stmt_prof->fetch(PDO::FETCH_ASSOC);
                } else {
                    throw $e;
                }
            }
            
            if (!$profissional) {
                error_log("Profissional não encontrado: {$id_profissional}");
                return [];
            }
            
            $id_salao = $profissional['id_salao'];
            
            // Determinar o dia da semana (1=Segunda, 2=Terça, ..., 6=Sábado, 0=Domingo)
            $dia_semana = date('w', strtotime($data));
            
            // Buscar horários de funcionamento do salão para este dia
            $sql_horarios = "SELECT hora_abertura, hora_fechamento 
                           FROM horarios_funcionamento 
                           WHERE id_salao = :id_salao AND dia_semana = :dia_semana AND ativo = 1";
            
            $stmt_horarios = $this->conn->prepare($sql_horarios);
            $stmt_horarios->bindParam(':id_salao', $id_salao);
            $stmt_horarios->bindParam(':dia_semana', $dia_semana);
            $stmt_horarios->execute();
            
            $horario_funcionamento = $stmt_horarios->fetch(PDO::FETCH_ASSOC);
            
            if (!$horario_funcionamento) {
                // Se não há horário cadastrado para este dia, salão está fechado
                error_log("Salão fechado no dia {$data} (dia da semana: {$dia_semana})");
                return [];
            }
            
            // Gerar horários disponíveis baseados no funcionamento do salão
            $horarios_funcionamento = $this->gerarHorariosPorIntervalo(
                $horario_funcionamento['hora_abertura'],
                $horario_funcionamento['hora_fechamento'],
                30 // Intervalo de 30 minutos
            );
            
            $horarios_ocupados = $this->listarHorariosOcupados($id_profissional, $data);
            
            // Remove horários ocupados
            $horarios_disponiveis = array_diff($horarios_funcionamento, $horarios_ocupados);
            
            // Remove horários passados se for hoje
            if ($data == date('Y-m-d')) {
                $hora_atual = date('H:i');
                $horarios_disponiveis = array_filter($horarios_disponiveis, function($horario) use ($hora_atual) {
                    return $horario > $hora_atual;
                });
            }
            
            return array_values($horarios_disponiveis);
            
        } catch (Exception $e) {
            error_log("Erro ao gerar horários disponíveis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Gera array de horários baseado no intervalo de funcionamento
     * @param string $hora_inicio Formato HH:MM:SS
     * @param string $hora_fim Formato HH:MM:SS
     * @param int $intervalo_minutos Intervalo em minutos
     * @return array
     */
    private function gerarHorariosPorIntervalo($hora_inicio, $hora_fim, $intervalo_minutos = 60) {
        $horarios = [];
        
        $inicio = new DateTime($hora_inicio);
        $fim = new DateTime($hora_fim);
        $intervalo = new DateInterval('PT' . $intervalo_minutos . 'M');
        
        while ($inicio < $fim) {
            $horarios[] = $inicio->format('H:i');
            $inicio->add($intervalo);
        }
        
        return $horarios;
    }
    
    /**
     * Bloqueia temporariamente um horário para evitar conflitos
     * @param int $id_profissional
     * @param string $data
     * @param string $hora
     * @param string $session_id
     * @param string $ip_cliente
     * @param int $minutos_bloqueio
     * @return bool
     */
    public function bloquearHorarioTemporariamente($id_profissional, $data, $hora, $session_id, $ip_cliente = null, $minutos_bloqueio = 10) {
        try {
            // Limpar bloqueios expirados primeiro
            $this->limparBloqueiosExpirados();
            
            // Verificar se já existe um bloqueio para este horário
            $sql_check = "SELECT session_id FROM bloqueios_temporarios 
                         WHERE id_profissional = :id_profissional 
                         AND data = :data 
                         AND hora = :hora 
                         AND expires_at > NOW()";
            
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':id_profissional', $id_profissional);
            $stmt_check->bindParam(':data', $data);
            $stmt_check->bindParam(':hora', $hora);
            $stmt_check->execute();
            
            $bloqueio_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            // Se já existe um bloqueio de outra sessão, não pode bloquear
            if ($bloqueio_existente && $bloqueio_existente['session_id'] !== $session_id) {
                return false;
            }
            
            // Se já existe um bloqueio da mesma sessão, atualizar tempo
            if ($bloqueio_existente && $bloqueio_existente['session_id'] === $session_id) {
                $sql_update = "UPDATE bloqueios_temporarios 
                              SET expires_at = DATE_ADD(NOW(), INTERVAL :minutos MINUTE),
                                  ip_cliente = :ip_cliente
                              WHERE id_profissional = :id_profissional 
                              AND data = :data 
                              AND hora = :hora 
                              AND session_id = :session_id";
                
                $stmt_update = $this->conn->prepare($sql_update);
                $stmt_update->bindParam(':minutos', $minutos_bloqueio);
                $stmt_update->bindParam(':ip_cliente', $ip_cliente);
                $stmt_update->bindParam(':id_profissional', $id_profissional);
                $stmt_update->bindParam(':data', $data);
                $stmt_update->bindParam(':hora', $hora);
                $stmt_update->bindParam(':session_id', $session_id);
                
                return $stmt_update->execute();
            }
            
            // Criar novo bloqueio
            $sql_insert = "INSERT INTO bloqueios_temporarios 
                          (id_profissional, data, hora, session_id, ip_cliente, expires_at) 
                          VALUES (:id_profissional, :data, :hora, :session_id, :ip_cliente, 
                                  DATE_ADD(NOW(), INTERVAL :minutos MINUTE))";
            
            $stmt_insert = $this->conn->prepare($sql_insert);
            $stmt_insert->bindParam(':id_profissional', $id_profissional);
            $stmt_insert->bindParam(':data', $data);
            $stmt_insert->bindParam(':hora', $hora);
            $stmt_insert->bindParam(':session_id', $session_id);
            $stmt_insert->bindParam(':ip_cliente', $ip_cliente);
            $stmt_insert->bindParam(':minutos', $minutos_bloqueio);
            
            return $stmt_insert->execute();
            
        } catch (PDOException $e) {
            error_log("Erro ao bloquear horário temporariamente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove bloqueio temporário de um horário
     * @param int $id_profissional
     * @param string $data
     * @param string $hora
     * @param string $session_id
     * @return bool
     */
    public function desbloquearHorario($id_profissional, $data, $hora, $session_id) {
        try {
            $sql = "DELETE FROM bloqueios_temporarios 
                   WHERE id_profissional = :id_profissional 
                   AND data = :data 
                   AND hora = :hora 
                   AND session_id = :session_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_profissional', $id_profissional);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':session_id', $session_id);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erro ao desbloquear horário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista horários bloqueados temporariamente
     * @param int $id_profissional
     * @param string $data
     * @param string $session_id_atual
     * @return array
     */
    public function listarHorariosBloqueados($id_profissional, $data, $session_id_atual = null) {
        try {
            // Limpar bloqueios expirados primeiro
            $this->limparBloqueiosExpirados();
            
            $sql = "SELECT hora, session_id FROM bloqueios_temporarios 
                   WHERE id_profissional = :id_profissional 
                   AND data = :data 
                   AND expires_at > NOW()";
            
            if ($session_id_atual) {
                $sql .= " AND session_id != :session_id_atual";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_profissional', $id_profissional);
            $stmt->bindParam(':data', $data);
            
            if ($session_id_atual) {
                $stmt->bindParam(':session_id_atual', $session_id_atual);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Erro ao listar horários bloqueados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Remove bloqueios expirados
     * @return bool
     */
    public function limparBloqueiosExpirados() {
        try {
            $sql = "DELETE FROM bloqueios_temporarios WHERE expires_at <= NOW()";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erro ao limpar bloqueios expirados: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gera horários disponíveis considerando bloqueios temporários
     * @param int $id_profissional
     * @param string $data
     * @param string $session_id_atual
     * @return array
     */
    public function gerarHorariosDisponiveisComBloqueios($id_profissional, $data, $session_id_atual = null) {
        // Obter horários disponíveis normalmente
        $horarios_disponiveis = $this->gerarHorariosDisponiveis($id_profissional, $data);
        
        // Obter horários bloqueados por outras sessões
        $horarios_bloqueados = $this->listarHorariosBloqueados($id_profissional, $data, $session_id_atual);
        
        // Remover horários bloqueados dos disponíveis
        $horarios_finais = array_diff($horarios_disponiveis, $horarios_bloqueados);
        
        return array_values($horarios_finais);
    }
}
?>