<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $tipo_usuario;
    public $telefone;
    public $data_cadastro;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar usuário
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nome=:nome, email=:email, senha=:senha, tipo_usuario=:tipo_usuario, telefone=:telefone";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->senha = password_hash($this->senha, PASSWORD_DEFAULT);
        $this->tipo_usuario = htmlspecialchars(strip_tags($this->tipo_usuario));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));

        // Bind dos parâmetros
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":senha", $this->senha);
        $stmt->bindParam(":tipo_usuario", $this->tipo_usuario);
        $stmt->bindParam(":telefone", $this->telefone);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login do usuário
    public function login() {
        $query = "SELECT id, nome, email, senha, tipo_usuario, telefone 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND ativo = 1 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->senha, $row['senha'])) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->tipo_usuario = $row['tipo_usuario'];
            $this->telefone = $row['telefone'];
            return true;
        }
        return false;
    }

    // Verificar se email já existe
    public function emailExiste() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    // Buscar usuário por ID
    public function buscarPorId() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->tipo_usuario = $row['tipo_usuario'];
            $this->telefone = $row['telefone'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    // Atualizar perfil
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome=:nome, telefone=:telefone 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>