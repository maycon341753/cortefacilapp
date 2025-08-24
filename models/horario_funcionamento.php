<?php
/**
 * Classe HorarioFuncionamento
 * Gerencia os horários de funcionamento dos salões
 */

require_once __DIR__ . '/../config/database.php';

class HorarioFuncionamento {
    private $conn;
    private $table = 'horarios_funcionamento';
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Cadastra horários de funcionamento para um salão
     * @param int $id_salao
     * @param array $horarios Array com os horários por dia da semana
     * @return bool
     */
    public function cadastrarHorarios($id_salao, $horarios) {
        try {
            $this->conn->beginTransaction();
            
            // Primeiro, remover horários existentes do salão
            $sql = "DELETE FROM {$this->table} WHERE id_salao = :id_salao";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao);
            $stmt->execute();
            
            // Inserir novos horários
            $sql = "INSERT INTO {$this->table} (id_salao, dia_semana, hora_abertura, hora_fechamento, ativo) 
                    VALUES (:id_salao, :dia_semana, :hora_abertura, :hora_fechamento, :ativo)";
            $stmt = $this->conn->prepare($sql);
            
            foreach ($horarios as $dia => $horario) {
                if (!empty($horario['ativo']) && !empty($horario['abertura']) && !empty($horario['fechamento'])) {
                    $stmt->bindParam(':id_salao', $id_salao);
                    $stmt->bindParam(':dia_semana', $dia);
                    $stmt->bindParam(':hora_abertura', $horario['abertura']);
                    $stmt->bindParam(':hora_fechamento', $horario['fechamento']);
                    $ativo = true;
                    $stmt->bindParam(':ativo', $ativo);
                    $stmt->execute();
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Erro ao cadastrar horários: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca horários de funcionamento de um salão
     * @param int $id_salao
     * @return array
     */
    public function buscarPorSalao($id_salao) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_salao = :id_salao AND ativo = 1 ORDER BY dia_semana";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Erro ao buscar horários: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca horários formatados para exibição
     * @param int $id_salao
     * @return array
     */
    public function buscarHorariosFormatados($id_salao) {
        $horarios = $this->buscarPorSalao($id_salao);
        $dias_semana = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado'
        ];
        
        $resultado = [];
        foreach ($horarios as $horario) {
            $resultado[] = [
                'dia' => $dias_semana[$horario['dia_semana']],
                'dia_numero' => $horario['dia_semana'],
                'abertura' => substr($horario['hora_abertura'], 0, 5), // Remove segundos
                'fechamento' => substr($horario['hora_fechamento'], 0, 5),
                'ativo' => $horario['ativo']
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Verifica se o salão está aberto em um determinado dia e horário
     * @param int $id_salao
     * @param int $dia_semana (0=Domingo, 1=Segunda, etc.)
     * @param string $hora Formato HH:MM
     * @return bool
     */
    public function estaAberto($id_salao, $dia_semana, $hora) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE id_salao = :id_salao 
                    AND dia_semana = :dia_semana 
                    AND ativo = 1 
                    AND :hora BETWEEN hora_abertura AND hora_fechamento";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao);
            $stmt->bindParam(':dia_semana', $dia_semana);
            $stmt->bindParam(':hora', $hora . ':00'); // Adiciona segundos
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Erro ao verificar se está aberto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza horários de funcionamento
     * @param int $id_salao
     * @param array $horarios
     * @return bool
     */
    public function atualizarHorarios($id_salao, $horarios) {
        return $this->cadastrarHorarios($id_salao, $horarios);
    }
    
    /**
     * Remove todos os horários de um salão
     * @param int $id_salao
     * @return bool
     */
    public function removerHorariosSalao($id_salao) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_salao = :id_salao";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_salao', $id_salao);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erro ao remover horários: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cadastra horários padrão para um salão (Segunda a Sexta: 8h às 18h, Sábado: 8h às 16h)
     * @param int $id_salao
     * @return bool
     */
    public function cadastrarHorariosPadrao($id_salao) {
        $horarios_padrao = [
            1 => ['ativo' => true, 'abertura' => '08:00', 'fechamento' => '18:00'], // Segunda
            2 => ['ativo' => true, 'abertura' => '08:00', 'fechamento' => '18:00'], // Terça
            3 => ['ativo' => true, 'abertura' => '08:00', 'fechamento' => '18:00'], // Quarta
            4 => ['ativo' => true, 'abertura' => '08:00', 'fechamento' => '18:00'], // Quinta
            5 => ['ativo' => true, 'abertura' => '08:00', 'fechamento' => '18:00'], // Sexta
            6 => ['ativo' => true, 'abertura' => '08:00', 'fechamento' => '16:00'], // Sábado
        ];
        
        return $this->cadastrarHorarios($id_salao, $horarios_padrao);
    }
}
?>