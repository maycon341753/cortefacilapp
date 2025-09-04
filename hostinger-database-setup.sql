-- Script de Setup do Banco de Dados CorteFácil para Hostinger
-- Execute este script no phpMyAdmin do Hostinger
-- Banco: u690889028_cortefacil

USE u690889028_cortefacil;

-- Tabela de usuários (clientes, parceiros e administradores)
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

-- Tabela de salões
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

-- Tabela de profissionais
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

-- Tabela de agendamentos
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

-- Tabela para redefinição de senhas
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

-- Inserir usuário administrador padrão
INSERT IGNORE INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir dados de teste (opcional)
INSERT IGNORE INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES 
('João Silva', 'joao@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 99999-9999'),
('Maria Santos', 'maria@salao.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 88888-8888');

-- Inserir salão de teste
INSERT IGNORE INTO saloes (id_dono, nome, endereco, telefone, descricao) VALUES 
(3, 'Salão Beleza Total', 'Rua das Flores, 123 - Centro', '(11) 3333-4444', 'Salão completo com serviços de corte, escova e manicure');

-- Inserir profissional de teste
INSERT IGNORE INTO profissionais (id_salao, nome, especialidade) VALUES 
(1, 'Ana Costa', 'Cabeleireira e Manicure');

-- Índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_agendamentos_data_hora ON agendamentos(data, hora);
CREATE INDEX IF NOT EXISTS idx_agendamentos_profissional ON agendamentos(id_profissional);
CREATE INDEX IF NOT EXISTS idx_agendamentos_cliente ON agendamentos(id_cliente);
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario);
CREATE INDEX IF NOT EXISTS idx_saloes_ativo ON saloes(ativo);
CREATE INDEX IF NOT EXISTS idx_profissionais_ativo ON profissionais(ativo);

-- Verificar se as tabelas foram criadas
SHOW TABLES;

-- Verificar dados inseridos
SELECT 'Usuários cadastrados:' as info;
SELECT id, nome, email, tipo_usuario FROM usuarios;

SELECT 'Salões cadastrados:' as info;
SELECT id, nome, endereco FROM saloes;

SELECT 'Profissionais cadastrados:' as info;
SELECT id, nome, especialidade FROM profissionais;

-- Mensagem de sucesso
SELECT '✅ Setup do banco de dados concluído com sucesso!' as resultado;
SELECT '📊 Tabelas criadas: usuarios, saloes, profissionais, agendamentos, password_resets' as tabelas;
SELECT '👤 Usuário admin criado: admin@cortefacil.com (senha: password)' as admin_info;
SELECT '🧪 Dados de teste inseridos para desenvolvimento' as dados_teste;