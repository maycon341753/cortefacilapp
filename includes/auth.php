<?php
/**
 * Sistema de autenticação e autorização
 * Sistema SaaS de Agendamentos para Salões de Beleza
 */

session_start();
require_once '../config/database.php';

/**
 * Classe para gerenciar autenticação de usuários
 */
class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Realiza login do usuário
     */
    public function login($email, $senha) {
        try {
            $sql = "SELECT id, nome, email, tipo_usuario, ativo FROM usuarios WHERE email = ? AND ativo = 1";
            $stmt = $this->db->query($sql, [$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $this->getPasswordHash($email))) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_name'] = $usuario['nome'];
                $_SESSION['user_email'] = $usuario['email'];
                $_SESSION['user_type'] = $usuario['tipo_usuario'];
                $_SESSION['logged_in'] = true;
                
                return [
                    'success' => true,
                    'user' => $usuario,
                    'redirect' => $this->getRedirectUrl($usuario['tipo_usuario'])
                ];
            }
            
            return ['success' => false, 'message' => 'Email ou senha inválidos'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no sistema: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtém hash da senha do usuário
     */
    private function getPasswordHash($email) {
        $sql = "SELECT senha FROM usuarios WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        $result = $stmt->fetch();
        return $result ? $result['senha'] : false;
    }
    
    /**
     * Registra novo usuário
     */
    public function register($nome, $email, $senha, $tipo_usuario = 'cliente', $telefone = '') {
        try {
            // Verifica se email já existe
            $sql = "SELECT id FROM usuarios WHERE email = ?";
            $stmt = $this->db->query($sql, [$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email já cadastrado'];
            }
            
            // Cria novo usuário
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, [$nome, $email, $senhaHash, $tipo_usuario, $telefone]);
            
            return ['success' => true, 'message' => 'Usuário cadastrado com sucesso'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro no cadastro: ' . $e->getMessage()];
        }
    }
    
    /**
     * Realiza logout do usuário
     */
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    
    /**
     * Verifica se usuário está logado
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Verifica se usuário tem permissão para acessar determinado tipo
     */
    public function hasPermission($requiredType) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userType = $_SESSION['user_type'];
        
        // Admin tem acesso a tudo
        if ($userType === 'admin') {
            return true;
        }
        
        // Verifica permissão específica
        return $userType === $requiredType;
    }
    
    /**
     * Redireciona usuário não autorizado
     */
    public function requireAuth($requiredType = null) {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
        
        if ($requiredType && !$this->hasPermission($requiredType)) {
            header('Location: ../unauthorized.php');
            exit;
        }
    }
    
    /**
     * Obtém URL de redirecionamento baseada no tipo de usuário
     */
    private function getRedirectUrl($tipo_usuario) {
        switch ($tipo_usuario) {
            case 'admin':
                return 'admin/dashboard.php';
            case 'parceiro':
                return 'parceiro/dashboard.php';
            case 'cliente':
                return 'cliente/dashboard.php';
            default:
                return 'index.php';
        }
    }
    
    /**
     * Obtém dados do usuário logado
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'tipo' => $_SESSION['user_type']
        ];
    }
}

// Instância global da autenticação
$auth = new Auth($database);
?>