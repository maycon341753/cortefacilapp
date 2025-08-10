<?php
/**
 * Configurações específicas para Hostinger
 * Substitua os valores abaixo pelas informações do seu banco de dados no Hostinger
 */

// Configurações do banco de dados Hostinger
define('DB_HOST', 'srv486.hstgr.io'); // ou o host fornecido pela Hostinger
define('DB_NAME', 'u690889028_cortefacilapp'); // substitua pelo nome do seu banco
define('DB_USER', 'u690889028_mayconwender'); // substitua pelo seu usuário
define('DB_PASS', 'Brava1997@'); // substitua pela sua senha
define('DB_CHARSET', 'utf8mb4');

// URL base do site (sem barra no final)
define('BASE_URL', 'https://cortefacil.app'); // substitua pelo seu domínio

// Nome do site
define('SITE_NAME', 'CorteFácil');

// Configurações de segurança
define('JWT_SECRET', 'sua_chave_secreta_jwt_muito_segura_aqui_123456789'); // mude esta chave
define('HASH_COST', 12);

// Configurações de sessão
define('SESSION_LIFETIME', 3600); // 1 hora em segundos
define('SESSION_NAME', 'cortefacil_session');

// Configurações de email (se necessário)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@seudominio.com'); // substitua pelo seu email
define('SMTP_PASS', 'SuaSenhaEmail123!'); // substitua pela senha do email

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro (para produção)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');
?>