<?php
require_once '../config/Database.php';

class Agendamento {
    private $conn;
    private $table_name = "agendamentos";

    public $id;
    public $id_cliente;
    public $id_salao;
    public $id_profissional;
    public $data_agendamento;
    public $hora_agendamento;
    public $servico;
    public $observacoes;
    public $status;
    public $valor_taxa;
    public $transacao_id;
    public $data_pagamento;
    public $data_criacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar agendamento
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_cliente=:id_cliente, id_salao=:id_salao, id_profissional=:id_profissional,
                      data_agendamento=:data_agendamento, hora_agendamento=:hora_agendamento,
                      servico=:servico, observacoes=:observacoes, valor_taxa=:valor_taxa";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->servico = htmlspecialchars(strip_tags($this->servico));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos parâmetros
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->bindParam(":id_salao", $this->id_salao);
        $stmt->bindParam(":id_profissional", $this->id_profissional);
        $stmt->bindParam(":data_agendamento", $this->data_agendamento);
        $stmt->bindParam(":hora_agendamento", $this->hora_agendamento);
        $stmt->bindParam(":servico", $this->servico);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":valor_taxa", $this->valor_taxa);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar agendamentos do cliente
    public function listarPorCliente() {
        $query = "SELECT a.*, s.nome as nome_salao, p.nome as nome_profissional, u.nome as nome_cliente
                  FROM " . $this->table_name . " a
                  LEFT JOIN saloes s ON a.id_salao = s.id
                  LEFT JOIN profissionais p ON a.id_profissional = p.id
                  LEFT JOIN usuarios u ON a.id_cliente = u.id
                  WHERE a.id_cliente = :id_cliente
                  ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->execute();

        return $stmt;
    }

    // Listar agendamentos do salão
    public function listarPorSalao() {
        $query = "SELECT a.*, s.nome as nome_salao, p.nome as nome_profissional, u.nome as nome_cliente
                  FROM " . $this->table_name . " a
                  LEFT JOIN saloes s ON a.id_salao = s.id
                  LEFT JOIN profissionais p ON a.id_profissional = p.id
                  LEFT JOIN usuarios u ON a.id_cliente = u.id
                  WHERE a.id_salao = :id_salao
                  ORDER BY a.data_agendamento ASC, a.hora_agendamento ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_salao", $this->id_salao);
        $stmt->execute();

        return $stmt;
    }

    // Verificar disponibilidade
    public function verificarDisponibilidade() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE id_profissional = :id_profissional 
                  AND data_agendamento = :data_agendamento 
                  AND hora_agendamento = :hora_agendamento
                  AND status != 'cancelado'
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_profissional", $this->id_profissional);
        $stmt->bindParam(":data_agendamento", $this->data_agendamento);
        $stmt->bindParam(":hora_agendamento", $this->hora_agendamento);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return false; // Não disponível
        }
        return true; // Disponível
    }

    // Atualizar status
    public function atualizarStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Confirmar pagamento
    public function confirmarPagamento() {
        $query = "UPDATE " . $this->table_name . " 
                  SET transacao_id=:transacao_id, data_pagamento=NOW(), status='confirmado'
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":transacao_id", $this->transacao_id);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar por ID
    public function buscarPorId() {
        $query = "SELECT a.*, s.nome as nome_salao, p.nome as nome_profissional, u.nome as nome_cliente
                  FROM " . $this->table_name . " a
                  LEFT JOIN saloes s ON a.id_salao = s.id
                  LEFT JOIN profissionais p ON a.id_profissional = p.id
                  LEFT JOIN usuarios u ON a.id_cliente = u.id
                  WHERE a.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id_cliente = $row['id_cliente'];
            $this->id_salao = $row['id_salao'];
            $this->id_profissional = $row['id_profissional'];
            $this->data_agendamento = $row['data_agendamento'];
            $this->hora_agendamento = $row['hora_agendamento'];
            $this->servico = $row['servico'];
            $this->observacoes = $row['observacoes'];
            $this->status = $row['status'];
            $this->valor_taxa = $row['valor_taxa'];
            $this->transacao_id = $row['transacao_id'];
            $this->data_pagamento = $row['data_pagamento'];
            $this->data_criacao = $row['data_criacao'];
            return true;
        }
        return false;
    }
}
?>