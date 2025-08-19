-- Script para criar usuários de teste no CorteFácil
-- Execute este script no phpMyAdmin do Hostinger

-- Usuários de teste com senhas simples para facilitar os testes
-- Todas as senhas são: 123456

-- Cliente de teste
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES 
('João Silva', 'cliente@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 99999-1111');

-- Parceiro de teste
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES 
('Maria Santos', 'parceiro@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 99999-2222');

-- Salão de teste para o parceiro
INSERT INTO saloes (id_dono, nome, endereco, telefone, descricao) VALUES 
((SELECT id FROM usuarios WHERE email = 'parceiro@teste.com'), 'Salão Beleza Total', 'Rua das Flores, 123 - Centro', '(11) 3333-4444', 'Salão completo com serviços de corte, coloração e tratamentos');

-- Profissionais de teste para o salão
INSERT INTO profissionais (id_salao, nome, especialidade) VALUES 
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Ana Costa', 'Corte e Escova'),
((SELECT id FROM saloes WHERE nome = 'Salão Beleza Total'), 'Carlos Mendes', 'Coloração e Química');

-- Verificar se os usuários foram criados
SELECT id, nome, email, tipo_usuario, telefone FROM usuarios WHERE email IN ('admin@cortefacil.com', 'cliente@teste.com', 'parceiro@teste.com');