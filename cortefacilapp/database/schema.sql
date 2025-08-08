-- Script de criação do banco de dados para o sistema SaaS de agendamentos
-- Criado para funcionar com XAMPP/MySQL

CREATE DATABASE IF NOT EXISTS cortefacil_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cortefacil_db;

-- Tabela de usuários (clientes, parceiros e administradores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'parceiro', 'admin') NOT NULL,
    telefone VARCHAR(20),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de salões
CREATE TABLE saloes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dono INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT NOT NULL,
    telefone VARCHAR(20),
    descricao TEXT,
    horario_funcionamento VARCHAR(100),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de profissionais
CREATE TABLE profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    especialidade VARCHAR(100),
    telefone VARCHAR(20),
    horario_trabalho VARCHAR(100),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
);

-- Tabela de agendamentos
CREATE TABLE agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_salao INT NOT NULL,
    id_profissional INT NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    servico VARCHAR(255),
    observacoes TEXT,
    status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
    valor_taxa DECIMAL(10,2) DEFAULT 1.29,
    transacao_id VARCHAR(100),
    data_pagamento TIMESTAMP NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_profissional) REFERENCES profissionais(id) ON DELETE CASCADE
);

-- Inserir usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Índice único para evitar conflitos de horário
CREATE UNIQUE INDEX unique_agendamento ON agendamentos (id_profissional, data_agendamento, hora_agendamento);

-- Inserir dados de exemplo para teste
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES 
('João Silva', 'joao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 99999-9999'),
('Maria Santos', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 88888-8888');

INSERT INTO saloes (id_dono, nome, endereco, telefone, descricao, horario_funcionamento) VALUES 
(2, 'Salão Beleza Total', 'Rua das Flores, 123 - Centro', '(11) 3333-3333', 'Salão especializado em cortes e tratamentos', 'Segunda a Sábado: 8h às 18h');

INSERT INTO profissionais (id_salao, nome, especialidade, telefone, horario_trabalho) VALUES 
(1, 'Ana Costa', 'Cabeleireira', '(11) 77777-7777', 'Segunda a Sexta: 8h às 17h'),
(1, 'Carlos Mendes', 'Barbeiro', '(11) 66666-6666', 'Terça a Sábado: 9h às 18h');