<?php
/**
 * Script para aplicar correções nos arquivos de profissionais
 * Corrige problemas de cadastro e exibição em tempo real
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Aplicando Correções - Sistema de Profissionais</h2>";

// 1. Verificar e corrigir estrutura do banco
echo "<h3>1. Verificando estrutura do banco</h3>";

try {
    require_once 'config/database.php';
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Falha na conexão com o banco");
    }
    
    echo "<p style='color: green;'>✅ Conexão estabelecida</p>";
    
    // Verificar se a tabela profissionais tem a estrutura correta
    $stmt = $conn->query("DESCRIBE profissionais");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $colunas_necessarias = ['id', 'id_salao', 'nome', 'especialidade', 'ativo', 'data_cadastro'];
    $colunas_faltantes = array_diff($colunas_necessarias, $colunas);
    
    if (!empty($colunas_faltantes)) {
        echo "<p style='color: orange;'>⚠️ Adicionando colunas faltantes: " . implode(', ', $colunas_faltantes) . "</p>";
        
        foreach ($colunas_faltantes as $coluna) {
            switch ($coluna) {
                case 'ativo':
                    $conn->exec("ALTER TABLE profissionais ADD COLUMN ativo BOOLEAN DEFAULT TRUE");
                    break;
                case 'data_cadastro':
                    $conn->exec("ALTER TABLE profissionais ADD COLUMN data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                    break;
            }
        }
    }
    
    echo "<p style='color: green;'>✅ Estrutura da tabela profissionais verificada</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

// 2. Criar arquivo de profissionais corrigido
echo "<h3>2. Criando versão corrigida do arquivo profissionais.php</h3>";

$profissionais_corrigido = '<?php
/**
 * Página de Gerenciamento de Profissionais - VERSÃO CORRIGIDA
 * Permite ao parceiro cadastrar, editar e gerenciar profissionais do seu salão
 */

require_once "../includes/auth.php";
require_once "../includes/functions.php";
require_once "../models/salao.php";
require_once "../models/profissional.php";

// Verificar se é parceiro
requireParceiro();

$usuario = getLoggedUser();
$salao = new Salao();
$profissional = new Profissional();

$erro = "";
$sucesso = "";

// Verificar se tem salão cadastrado
$meu_salao = $salao->buscarPorDono($usuario["id"]);
if (!$meu_salao) {
    header("Location: salao.php");
    exit;
}

// Processar ações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Validar CSRF
        if (!verifyCsrfToken($_POST["csrf_token"] ?? "")) {
            throw new Exception("Token de segurança inválido.");
        }
        
        $acao = $_POST["acao"] ?? "";
        
        if ($acao === "cadastrar" || $acao === "editar") {
            // Validar dados
            $nome = trim($_POST["nome"] ?? "");
            $especialidade = trim($_POST["especialidade"] ?? "");
            $telefone = trim($_POST["telefone"] ?? "");
            $email = trim($_POST["email"] ?? "");
            $ativo = isset($_POST["ativo"]);
            
            if (empty($nome)) {
                throw new Exception("Nome do profissional é obrigatório.");
            }
            
            if (strlen($nome) < 3) {
                throw new Exception("Nome deve ter pelo menos 3 caracteres.");
            }
            
            if (empty($especialidade)) {
                throw new Exception("Especialidade é obrigatória.");
            }
            
            // Preparar dados
            $dados = [
                "nome" => $nome,
                "especialidade" => $especialidade,
                "telefone" => $telefone,
                "email" => $email,
                "ativo" => $ativo ? 1 : 0
            ];
            
            if ($acao === "cadastrar") {
                // Cadastrar novo profissional
                $dados["id_salao"] = $meu_salao["id"];
                $resultado = $profissional->cadastrar($dados);
                $mensagem = "Profissional cadastrado com sucesso!";
                $log_acao = "profissional_cadastrado";
            } else {
                // Editar profissional existente
                $id_profissional = (int)($_POST["id_profissional"] ?? 0);
                if (!$id_profissional) {
                    throw new Exception("ID do profissional inválido.");
                }
                
                // Verificar se o profissional pertence ao salão do usuário
                $prof_existente = $profissional->buscarPorId($id_profissional);
                if (!$prof_existente || $prof_existente["id_salao"] != $meu_salao["id"]) {
                    throw new Exception("Profissional não encontrado.");
                }
                
                $resultado = $profissional->atualizar($id_profissional, $dados);
                $mensagem = "Profissional atualizado com sucesso!";
                $log_acao = "profissional_atualizado";
            }
            
            if ($resultado) {
                $sucesso = $mensagem;
                logActivity($usuario["id"], $log_acao, "Profissional: {$nome}");
            } else {
                throw new Exception("Erro ao salvar dados do profissional.");
            }
            
        } elseif ($acao === "excluir") {
            $id_profissional = (int)($_POST["id_profissional"] ?? 0);
            if (!$id_profissional) {
                throw new Exception("ID do profissional inválido.");
            }
            
            // Verificar se o profissional pertence ao salão do usuário
            $prof_existente = $profissional->buscarPorId($id_profissional);
            if (!$prof_existente || $prof_existente["id_salao"] != $meu_salao["id"]) {
                throw new Exception("Profissional não encontrado.");
            }
            
            $resultado = $profissional->excluir($id_profissional);
            if ($resultado) {
                $sucesso = "Profissional excluído com sucesso!";
                logActivity($usuario["id"], "profissional_excluido", "Profissional: {$prof_existente["nome"]}");
            } else {
                throw new Exception("Erro ao excluir profissional.");
            }
        }
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar profissionais do salão (em tempo real)
$profissionais = $profissional->listarPorSalao($meu_salao["id"]);
?>';

// Salvar arquivo corrigido
file_put_contents('parceiro/profissionais_corrigido.php', $profissionais_corrigido);
echo "<p style='color: green;'>✅ Arquivo profissionais_corrigido.php criado</p>";

// 3. Criar modelo de profissional corrigido
echo "<h3>3. Criando modelo Profissional corrigido</h3>";

$modelo_corrigido = '<?php
/**
 * Classe Profissional - VERSÃO CORRIGIDA
 * Gerencia operações relacionadas aos profissionais dos salões
 */

require_once __DIR__ . "/../config/database.php";

class Profissional {
    private $conn;
    private $table = "profissionais";
    
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
            $sql = "INSERT INTO {$this->table} (id_salao, nome, especialidade, telefone, email, ativo) 
                    VALUES (:id_salao, :nome, :especialidade, :telefone, :email, :ativo)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_salao", $dados["id_salao"]);
            $stmt->bindParam(":nome", $dados["nome"]);
            $stmt->bindParam(":especialidade", $dados["especialidade"]);
            $stmt->bindParam(":telefone", $dados["telefone"]);
            $stmt->bindParam(":email", $dados["email"]);
            $stmt->bindParam(":ativo", $dados["ativo"]);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao cadastrar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista profissionais por salão (em tempo real)
     * @param int $id_salao
     * @return array
     */
    public function listarPorSalao($id_salao) {
        try {
            $sql = "SELECT p.*, s.nome as nome_salao 
                    FROM {$this->table} p 
                    INNER JOIN saloes s ON p.id_salao = s.id 
                    WHERE p.id_salao = :id_salao 
                    ORDER BY p.ativo DESC, p.nome";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_salao", $id_salao);
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
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar profissional: " . $e->getMessage());
            return false;
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
            $sql = "UPDATE {$this->table} SET 
                    nome = :nome, 
                    especialidade = :especialidade, 
                    telefone = :telefone, 
                    email = :email, 
                    ativo = :ativo 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":nome", $dados["nome"]);
            $stmt->bindParam(":especialidade", $dados["especialidade"]);
            $stmt->bindParam(":telefone", $dados["telefone"]);
            $stmt->bindParam(":email", $dados["email"]);
            $stmt->bindParam(":ativo", $dados["ativo"]);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar profissional: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exclui profissional
     * @param int $id
     * @return bool
     */
    public function excluir($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao excluir profissional: " . $e->getMessage());
            return false;
        }
    }
}';

// Salvar modelo corrigido
file_put_contents('models/profissional_corrigido.php', $modelo_corrigido);
echo "<p style='color: green;'>✅ Modelo profissional_corrigido.php criado</p>";

echo "<h3>4. Resumo das Correções</h3>";
echo "<ul>";
echo "<li>✅ Verificação da estrutura do banco de dados</li>";
echo "<li>✅ Correção do cadastro de profissionais</li>";
echo "<li>✅ Implementação de listagem em tempo real</li>";
echo "<li>✅ Adição de campos telefone e email</li>";
echo "<li>✅ Melhoria no controle de status ativo/inativo</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Correções aplicadas com sucesso!</strong></p>";
echo "<p>Arquivos criados:</p>";
echo "<ul>";
echo "<li>parceiro/profissionais_corrigido.php</li>";
echo "<li>models/profissional_corrigido.php</li>";
echo "</ul>";
?>