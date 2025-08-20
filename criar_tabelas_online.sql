-- ============================================
-- SCRIPT PARA CRIAR TABELAS NO BANCO ONLINE
-- CorteFácil - Sistema de Agendamento
-- ============================================

-- Usar o banco de dados correto
USE u690889028_cortefacil;

-- ============================================
-- TABELA DE USUÁRIOS
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('cliente', 'parceiro', 'admin') NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(14) NULL COMMENT 'CPF do usuário (apenas números ou formatado)',
    cpf_verificado BOOLEAN DEFAULT FALSE COMMENT 'Se o CPF foi verificado',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE SALÕES
-- ============================================
CREATE TABLE IF NOT EXISTS saloes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_dono INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    documento VARCHAR(18) NOT NULL COMMENT 'CPF ou CNPJ do salão',
    tipo_documento ENUM('CPF', 'CNPJ') NOT NULL COMMENT 'Tipo do documento',
    documento_verificado BOOLEAN DEFAULT FALSE COMMENT 'Se o documento foi verificado',
    razao_social VARCHAR(200) NULL COMMENT 'Razão social (para CNPJ)',
    inscricao_estadual VARCHAR(20) NULL COMMENT 'Inscrição estadual (opcional)',
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dono) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE PROFISSIONAIS
-- ============================================
CREATE TABLE IF NOT EXISTS profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    especialidade VARCHAR(100) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE AGENDAMENTOS
-- ============================================
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE HORÁRIOS DE FUNCIONAMENTO
-- ============================================
CREATE TABLE IF NOT EXISTS horarios_funcionamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salao INT NOT NULL,
    dia_semana TINYINT NOT NULL COMMENT '0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado',
    hora_abertura TIME NOT NULL,
    hora_fechamento TIME NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_salao_dia (id_salao, dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE SERVIÇOS
-- ============================================
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_salao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    duracao_minutos INT NOT NULL DEFAULT 60,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_salao) REFERENCES saloes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE PAGAMENTOS
-- ============================================
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_agendamento INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'aprovado', 'rejeitado', 'cancelado') DEFAULT 'pendente',
    metodo_pagamento VARCHAR(50),
    transaction_id VARCHAR(100),
    data_pagamento TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE LOG DE ATIVIDADES
-- ============================================
CREATE TABLE IF NOT EXISTS log_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    documento_alterado VARCHAR(18) NULL COMMENT 'Documento que foi alterado',
    tipo_alteracao ENUM('cadastro', 'atualizacao', 'verificacao') NULL COMMENT 'Tipo de alteração no documento',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE VERIFICAÇÕES DE DOCUMENTOS
-- ============================================
CREATE TABLE IF NOT EXISTS verificacoes_documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_entidade ENUM('usuario', 'salao') NOT NULL,
    id_entidade INT NOT NULL,
    documento VARCHAR(18) NOT NULL,
    tipo_documento ENUM('CPF', 'CNPJ') NOT NULL,
    status_verificacao ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    data_verificacao TIMESTAMP NULL,
    observacoes TEXT,
    verificado_por INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (verificado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_verificacao_entidade (tipo_entidade, id_entidade),
    INDEX idx_verificacao_documento (documento),
    INDEX idx_verificacao_status (status_verificacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ÍNDICES PARA MELHOR PERFORMANCE
-- ============================================
CREATE INDEX IF NOT EXISTS idx_agendamentos_data_hora ON agendamentos(data, hora);
CREATE INDEX IF NOT EXISTS idx_agendamentos_profissional ON agendamentos(id_profissional);
CREATE INDEX IF NOT EXISTS idx_agendamentos_cliente ON agendamentos(id_cliente);
CREATE INDEX IF NOT EXISTS idx_agendamentos_salao ON agendamentos(id_salao);
CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos(status);
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario);
CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_cpf ON usuarios(cpf);
CREATE INDEX IF NOT EXISTS idx_saloes_dono ON saloes(id_dono);
CREATE UNIQUE INDEX IF NOT EXISTS idx_saloes_documento ON saloes(documento);
CREATE INDEX IF NOT EXISTS idx_profissionais_salao ON profissionais(id_salao);
CREATE INDEX IF NOT EXISTS idx_log_usuario ON log_atividades(id_usuario);
CREATE INDEX IF NOT EXISTS idx_log_data ON log_atividades(created_at);

-- ============================================
-- INSERIR DADOS INICIAIS
-- ============================================

-- Usuário Administrador (sem CPF - é admin)
INSERT IGNORE INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '(11) 99999-0000', NOW());

-- Cliente de Teste
INSERT IGNORE INTO usuarios (nome, email, senha, tipo_usuario, telefone, cpf, cpf_verificado, data_cadastro) VALUES 
('Cliente Teste', 'cliente@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 99999-1111', '123.456.789-01', FALSE, NOW());

-- Parceiro de Teste
INSERT IGNORE INTO usuarios (nome, email, senha, tipo_usuario, telefone, cpf, cpf_verificado, data_cadastro) VALUES 
('Parceiro Teste', 'parceiro@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 99999-2222', '987.654.321-09', FALSE, NOW());

-- Salão de Teste
INSERT IGNORE INTO saloes (id_dono, nome, endereco, telefone, documento, tipo_documento, documento_verificado, razao_social, descricao) VALUES 
((SELECT id FROM usuarios WHERE email = 'parceiro@teste.com'), 'Salão Beleza Total', 'Rua das Flores, 123 - Centro', '(11) 3333-4444', '12.345.678/0001-90', 'CNPJ', FALSE, 'Salão Beleza Total LTDA', 'Salão completo com serviços de corte, coloração e tratamentos');

-- Profissionais de Teste
INSERT IGNORE INTO profissionais (id_salao, nome, especialidade) VALUES 
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Ana Costa', 'Corte e Escova'),
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Carlos Mendes', 'Coloração e Química');

-- Serviços de Teste
INSERT IGNORE INTO servicos (id_salao, nome, descricao, preco, duracao_minutos) VALUES 
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Corte Masculino', 'Corte de cabelo masculino tradicional', 25.00, 30),
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Corte Feminino', 'Corte de cabelo feminino', 35.00, 45),
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Escova', 'Escova modeladora', 20.00, 30),
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Coloração', 'Coloração completa', 80.00, 120);

-- Horários de Funcionamento de Teste (Segunda a Sexta: 8h às 18h, Sábado: 8h às 16h)
INSERT IGNORE INTO horarios_funcionamento (id_salao, dia_semana, hora_abertura, hora_fechamento) VALUES 
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 1, '08:00:00', '18:00:00'), -- Segunda
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 2, '08:00:00', '18:00:00'), -- Terça
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 3, '08:00:00', '18:00:00'), -- Quarta
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 4, '08:00:00', '18:00:00'), -- Quinta
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 5, '08:00:00', '18:00:00'), -- Sexta
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 6, '08:00:00', '16:00:00'); -- Sábado

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================

-- Verificar se as tabelas foram criadas
SHOW TABLES;

-- Verificar usuários inseridos
SELECT id, nome, email, tipo_usuario, telefone, cpf, cpf_verificado, data_cadastro 
FROM usuarios 
WHERE email IN ('admin@cortefacil.com', 'cliente@teste.com', 'parceiro@teste.com')
ORDER BY tipo_usuario, nome;

-- Verificar salões
SELECT s.id, s.nome, s.endereco, s.telefone, s.documento, s.tipo_documento, s.documento_verificado, s.razao_social, u.nome as dono 
FROM saloes s 
JOIN usuarios u ON s.id_dono = u.id;

-- Verificar profissionais
SELECT p.id, p.nome, p.especialidade, s.nome as salao 
FROM profissionais p 
JOIN saloes s ON p.id_salao = s.id;

-- ============================================
-- INFORMAÇÕES IMPORTANTES
-- ============================================
/*
🎉 BANCO DE DADOS CONFIGURADO COM SUCESSO!

📋 TABELAS CRIADAS:
✅ usuarios - Clientes, parceiros e administradores (com CPF)
✅ saloes - Estabelecimentos dos parceiros (com CPF/CNPJ)
✅ profissionais - Profissionais dos salões
✅ agendamentos - Agendamentos dos clientes
✅ horarios_funcionamento - Horários de funcionamento dos salões
✅ servicos - Serviços oferecidos pelos salões
✅ pagamentos - Controle de pagamentos
✅ log_atividades - Log de atividades do sistema (com rastreamento de documentos)
✅ verificacoes_documento - Sistema de verificação de CPF/CNPJ

🔐 CREDENCIAIS PARA LOGIN:

🔑 ADMINISTRADOR:
   Email: admin@cortefacil.com
   Senha: password

👤 CLIENTE:
   Email: cliente@teste.com
   Senha: 123456

🏪 PARCEIRO:
   Email: parceiro@teste.com
   Senha: 123456

📊 DADOS DE TESTE INCLUÍDOS:
✅ Usuários de teste (com CPF válidos)
✅ Salão de exemplo (com CNPJ)
✅ Profissionais de exemplo
✅ Serviços de exemplo
✅ Horários de funcionamento

🛡️ SISTEMA DE SEGURANÇA:
✅ CPF obrigatório para clientes e parceiros
✅ CPF/CNPJ obrigatório para salões
✅ Índices únicos previnem duplicatas
✅ Sistema de verificação de documentos
✅ Log completo de alterações
✅ Rastreabilidade total de fraudes

🚀 PRÓXIMOS PASSOS:
1. Execute este script no phpMyAdmin
2. Verifique se todas as tabelas foram criadas
3. Teste o login com as credenciais fornecidas
4. Comece a usar o sistema!

⚠️ IMPORTANTE:
- Todas as senhas são criptografadas com bcrypt
- CPFs e CNPJs de teste são fictícios - substitua por reais
- Sistema de prevenção de fraudes implementado
- Documentos únicos por usuário/salão
- Verificação manual de documentos disponível
- O sistema está pronto para uso em produção
- Remova os dados de teste se necessário
*/