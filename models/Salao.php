<?php
require_once '../config/Database.php';

class Salao {
    private $conn;
    private $table_name = "saloes";

    public $id;
    public $id_dono;
    public $nome;
    public $endereco;
    public $telefone;
    public $descricao;
    public $horario_funcionamento;
    public $data_cadastro;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar salão
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_dono=:id_dono, nome=:nome, endereco=:endereco, 
                      telefone=:telefone, descricao=:descricao, horario_funcionamento=:horario_funcionamento";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->horario_funcionamento = htmlspecialchars(strip_tags($this->horario_funcionamento));

        // Bind dos parâmetros
        $stmt->bindParam(":id_dono", $this->id_dono);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":endereco", $this->endereco);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":horario_funcionamento", $this->horario_funcionamento);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Listar todos os salões ativos
    public function listarTodos() {
        $query = "SELECT s.*, u.nome as nome_dono, u.telefone as telefone_dono
                  FROM " . $this->table_name . " s
                  LEFT JOIN usuarios u ON s.id_dono = u.id
                  WHERE s.ativo = 1
                  ORDER BY s.nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Buscar salão por dono
    public function buscarPorDono() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_dono = :id_dono AND ativo = 1 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_dono", $this->id_dono);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->endereco = $row['endereco'];
            $this->telefone = $row['telefone'];
            $this->descricao = $row['descricao'];
            $this->horario_funcionamento = $row['horario_funcionamento'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    // Buscar por ID
    public function buscarPorId() {
        $query = "SELECT s.*, u.nome as nome_dono, u.telefone as telefone_dono, u.email as email_dono
                  FROM " . $this->table_name . " s
                  LEFT JOIN usuarios u ON s.id_dono = u.id
                  WHERE s.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id_dono = $row['id_dono'];
            $this->nome = $row['nome'];
            $this->endereco = $row['endereco'];
            $this->telefone = $row['telefone'];
            $this->descricao = $row['descricao'];
            $this->horario_funcionamento = $row['horario_funcionamento'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    // Atualizar salão
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome=:nome, endereco=:endereco, telefone=:telefone, 
                      descricao=:descricao, horario_funcionamento=:horario_funcionamento
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->horario_funcionamento = htmlspecialchars(strip_tags($this->horario_funcionamento));

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":endereco", $this->endereco);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":horario_funcionamento", $this->horario_funcionamento);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Buscar salões com profissionais
    public function listarComProfissionais() {
        $query = "SELECT s.*, 
                         COUNT(p.id) as total_profissionais,
                         GROUP_CONCAT(p.nome SEPARATOR ', ') as profissionais
                  FROM " . $this->table_name . " s
                  LEFT JOIN profissionais p ON s.id = p.id_salao AND p.ativo = 1
                  WHERE s.ativo = 1
                  GROUP BY s.id
                  ORDER BY s.nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>