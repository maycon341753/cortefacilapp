<?php
// Configuração automática de ambiente
// Este arquivo detecta se está rodando localmente ou na Hostinger

// Detectar ambiente
$isHostinger = strpos($_SERVER['HTTP_HOST'] ?? '', 'cortefacil.app') !== false || 
               strpos($_SERVER['SERVER_NAME'] ?? '', 'cortefacil.app') !== false ||
               strpos(__DIR__, '/home/u690889028/') !== false;

if ($isHostinger) {
    // Carregar configurações da Hostinger
    require_once __DIR__ . '/database.php';
} else {
    // Carregar configurações locais
    require_once __DIR__ . '/database_local.php';
}

// Função para verificar se está na Hostinger
function isHostingerEnvironment() {
    global $isHostinger;
    return $isHostinger;
}

// Função para obter configuração do banco
function getDatabaseConfig() {
    return [
        'host' => DB_HOST,
        'database' => DB_NAME,
        'username' => DB_USER,
        'password' => DB_PASS,
        'charset' => DB_CHARSET
    ];
}

// Função para obter URL base
function getBaseUrl() {
    return BASE_URL;
}
?>