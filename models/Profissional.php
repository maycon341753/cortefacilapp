<?php
require_once '../config/Database.php';

class Profissional {
    private $conn;
    private $table_name = "profissionais";

    public $id;
    public $id_salao;
    public $nome;
    public $especialidade;
    public $telefone;
    public $horario_trabalho;
    public $data_cadastro;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar profissional
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_salao=:id_salao, nome=:nome, especialidade=:especialidade, 
                      telefone=:telefone, horario_trabalho=:horario_trabalho";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->especialidade = htmlspecialchars(strip_tags($this->especialidade));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->horario_trabalho = htmlspecialchars(strip_tags($this->horario_trabalho));

        // Bind dos parâmetros
        $stmt->bindParam(":id_salao", $this->id_salao);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":especialidade", $this->especialidade);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":horario_trabalho", $this->horario_trabalho);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar profissionais por salão
    public function listarPorSalao() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_salao = :id_salao AND ativo = 1
                  ORDER BY nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_salao", $this->id_salao);
        $stmt->execute();

        return $stmt;
    }

    // Buscar por ID
    public function buscarPorId() {
        $query = "SELECT p.*, s.nome as nome_salao
                  FROM " . $this->table_name . " p
                  LEFT JOIN saloes s ON p.id_salao = s.id
                  WHERE p.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id_salao = $row['id_salao'];
            $this->nome = $row['nome'];
            $this->especialidade = $row['especialidade'];
            $this->telefone = $row['telefone'];
            $this->horario_trabalho = $row['horario_trabalho'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    // Atualizar profissional
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome=:nome, especialidade=:especialidade, telefone=:telefone, 
                      horario_trabalho=:horario_trabalho
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->especialidade = htmlspecialchars(strip_tags($this->especialidade));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->horario_trabalho = htmlspecialchars(strip_tags($this->horario_trabalho));

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":especialidade", $this->especialidade);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":horario_trabalho", $this->horario_trabalho);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Listar todos os profissionais ativos
    public function listarTodos() {
        $query = "SELECT p.*, s.nome as nome_salao
                  FROM " . $this->table_name . " p
                  LEFT JOIN saloes s ON p.id_salao = s.id
                  WHERE p.ativo = 1 AND s.ativo = 1
                  ORDER BY s.nome ASC, p.nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Verificar horários disponíveis
    public function verificarHorariosDisponiveis($data) {
        $query = "SELECT hora_agendamento 
                  FROM agendamentos 
                  WHERE id_profissional = :id_profissional 
                  AND data_agendamento = :data 
                  AND status != 'cancelado'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_profissional", $this->id);
        $stmt->bindParam(":data", $data);
        $stmt->execute();

        $horariosOcupados = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $horariosOcupados[] = $row['hora_agendamento'];
        }

        // Horários padrão de funcionamento (8h às 18h)
        $horariosDisponiveis = [];
        for($hora = 8; $hora <= 17; $hora++) {
            $horario = sprintf("%02d:00:00", $hora);
            if(!in_array($horario, $horariosOcupados)) {
                $horariosDisponiveis[] = $horario;
            }
        }

        return $horariosDisponiveis;
    }

    // Desativar profissional
    public function desativar() {
        $query = "UPDATE " . $this->table_name . " SET ativo = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>