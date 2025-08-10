<?php
// Configurações do banco de dados para desenvolvimento local
define('DB_HOST', 'localhost');
define('DB_NAME', 'cortefacil_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações gerais
define('BASE_URL', 'http://localhost:8000');
define('SITE_NAME', 'CorteFácil');

// Configurações de segurança
define('JWT_SECRET', 'sua_chave_secreta_jwt_aqui_123456789');
define('HASH_COST', 12);

// Configurações de sessão
define('SESSION_LIFETIME', 3600); // 1 hora

// Configurações de email (para desenvolvimento)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('FROM_EMAIL', 'noreply@cortefacil.local');
define('FROM_NAME', 'CorteFácil');

// Configurações de upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de erro (desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>