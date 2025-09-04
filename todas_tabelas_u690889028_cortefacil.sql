-- Script completo com todas as tabelas do banco u690889028_cortefacil
-- Execute este script no phpMyAdmin do seu servidor Hostinger

USE u690889028_cortefacil;

-- ========================================
-- TABELA 1: USUÁRIOS
-- ========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'parceiro', 'admin') NOT NULL,
    telefone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- TABELA 2: SALÕES
-- ========================================
CREATE TABLE IF NOT EXISTS saloes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dono INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ========================================
-- TABELA 3: PROFISSIONAIS
-- ========================================
CREATE TABLE IF NOT EXISTS profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    especialidade VARCHAR(100) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
);

-- ========================================
-- TABELA 4: AGENDAMENTOS
-- ========================================
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_salao INT NOT NULL,
    id_profissional INT NOT NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado', 'concluido') DEFAULT 'pendente',
    valor_taxa DECIMAL(10,2) DEFAULT 1.29,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_profissional) REFERENCES profissionais(id) ON DELETE CASCADE,
    UNIQUE KEY unique_appointment (id_profissional, data, hora)
);

-- ========================================
-- TABELA 5: PASSWORD_RESETS (Redefinição de Senha)
-- ========================================
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

-- ========================================
-- ÍNDICES PARA MELHOR PERFORMANCE
-- ========================================
CREATE INDEX IF NOT EXISTS idx_agendamentos_data_hora ON agendamentos(data, hora);
CREATE INDEX IF NOT EXISTS idx_agendamentos_profissional ON agendamentos(id_profissional);
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario);

-- ========================================
-- USUÁRIO ADMINISTRADOR PADRÃO
-- ========================================
INSERT IGNORE INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ========================================
-- VERIFICAÇÕES
-- ========================================
-- Verificar todas as tabelas criadas
SHOW TABLES;

-- Verificar estrutura de cada tabela
DESCRIBE usuarios;
DESCRIBE saloes;
DESCRIBE profissionais;
DESCRIBE agendamentos;
DESCRIBE password_resets;

-- ========================================
-- RESUMO DAS TABELAS
-- ========================================
/*
1. USUARIOS - Armazena clientes, parceiros e administradores
   - Campos principais: id, nome, email, senha, tipo_usuario
   
2. SALOES - Informações dos salões de beleza
   - Campos principais: id, id_dono, nome, endereco, telefone
   
3. PROFISSIONAIS - Profissionais que trabalham nos salões
   - Campos principais: id, id_salao, nome, especialidade
   
4. AGENDAMENTOS - Agendamentos dos clientes
   - Campos principais: id, id_cliente, id_salao, id_profissional, data, hora, status
   
5. PASSWORD_RESETS - Tokens para redefinição de senha
   - Campos principais: id, email, token, expires_at, used
   
Usuário Admin Padrão:
- Email: admin@cortefacil.com
- Senha: password (altere após primeiro login)
*/