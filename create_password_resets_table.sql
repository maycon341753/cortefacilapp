-- Script para criar a tabela password_resets no banco de dados online
-- Execute este script no phpMyAdmin do seu servidor

USE u690889028_cortefacil;

-- Criar tabela para armazenar tokens de redefinição de senha
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- Verificar se a tabela foi criada
SHOW TABLES LIKE 'password_resets';

-- Verificar estrutura da tabela
DESCRIBE password_resets;

-- Comentário: Esta tabela é necessária para a funcionalidade "Esqueci minha senha"
-- Ela armazena tokens temporários para redefinição de senha com expiração de 1 hora