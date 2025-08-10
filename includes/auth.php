<?php
require_once __DIR__ . '/../config/database.php';

// Verificar se usuário está logado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

// Verificar se é cliente
function verificarCliente() {
    verificarLogin();
    if ($_SESSION['tipo_usuario'] !== 'cliente') {
        header('Location: ../index.php');
        exit();
    }
}

// Verificar se é parceiro
function verificarParceiro() {
    verificarLogin();
    if ($_SESSION['tipo_usuario'] !== 'parceiro') {
        header('Location: ../index.php');
        exit();
    }
}

// Verificar se é admin
function verificarAdmin() {
    verificarLogin();
    if ($_SESSION['tipo_usuario'] !== 'admin') {
        header('Location: ../index.php');
        exit();
    }
}

// Fazer logout
function logout() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Gerar token CSRF
function gerarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitizar entrada
function sanitizar($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Sanitizar string (alias para sanitizar)
function sanitizarString($data) {
    return sanitizar($data);
}

// Validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validar telefone brasileiro
function validarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    return strlen($telefone) >= 10 && strlen($telefone) <= 11;
}

// Gerar senha aleatória
function gerarSenhaAleatoria($tamanho = 8) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $senha = '';
    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $senha;
}

// Formatar telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) == 11) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    } elseif (strlen($telefone) == 10) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
    }
    return $telefone;
}

// Formatar data brasileira
function formatarDataBR($data) {
    return date('d/m/Y', strtotime($data));
}

// Formatar data e hora brasileira
function formatarDataHoraBR($dataHora) {
    return date('d/m/Y H:i', strtotime($dataHora));
}

// Verificar se data é válida
function validarData($data) {
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

// Verificar se hora é válida
function validarHora($hora) {
    $h = DateTime::createFromFormat('H:i', $hora);
    return $h && $h->format('H:i') === $hora;
}

// Enviar resposta JSON
function enviarJSON($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Registrar log de atividade
function registrarLog($acao, $detalhes = '') {
    $log = date('Y-m-d H:i:s') . " - ";
    if (isset($_SESSION['usuario_id'])) {
        $log .= "Usuário ID: " . $_SESSION['usuario_id'] . " - ";
    }
    $log .= $acao;
    if ($detalhes) {
        $log .= " - " . $detalhes;
    }
    $log .= "\n";
    
    file_put_contents(__DIR__ . '/../logs/atividades.log', $log, FILE_APPEND | LOCK_EX);
}
?>