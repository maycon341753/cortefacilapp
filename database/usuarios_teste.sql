-- Script para inserir usuários de teste adicionais
-- Execute este script se precisar de mais dados de teste

USE cortefacil_db;

-- Inserir usuários de teste adicionais (senhas: senha123)
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone) VALUES 
('Salão Teste', 'salao@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 99999-1111'),
('Cliente Teste', 'cliente@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 88888-2222'),
('Ana Oliveira', 'ana@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 77777-3333'),
('Barbearia Central', 'barbearia@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 66666-4444');

-- Inserir salões adicionais
INSERT INTO saloes (id_dono, nome, endereco, telefone, descricao, horario_funcionamento) VALUES 
(4, 'Salão Elegante', 'Av. Principal, 456 - Jardins', '(11) 4444-4444', 'Salão premium com serviços exclusivos', 'Segunda a Sábado: 9h às 19h'),
(6, 'Barbearia Central', 'Rua do Comércio, 789 - Centro', '(11) 5555-5555', 'Barbearia tradicional masculina', 'Terça a Domingo: 8h às 18h');

-- Inserir profissionais adicionais
INSERT INTO profissionais (id_salao, nome, especialidade, telefone, horario_trabalho) VALUES 
(2, 'Fernanda Lima', 'Manicure', '(11) 99999-7777', 'Segunda a Sexta: 9h às 18h'),
(2, 'Roberto Silva', 'Cabeleireiro', '(11) 88888-6666', 'Terça a Sábado: 8h às 17h'),
(3, 'Paulo Barbeiro', 'Barbeiro', '(11) 77777-5555', 'Terça a Domingo: 9h às 18h'),
(3, 'Diego Cortes', 'Barbeiro', '(11) 66666-4444', 'Segunda a Sábado: 8h às 17h');

-- Inserir alguns agendamentos de exemplo
INSERT INTO agendamentos (id_cliente, id_salao, id_profissional, data_agendamento, hora_agendamento, servico, status, valor_taxa) VALUES 
(3, 1, 1, '2025-08-10', '10:00:00', 'Corte e escova', 'confirmado', 1.29),
(3, 1, 2, '2025-08-12', '14:00:00', 'Corte masculino', 'pendente', 1.29),
(5, 2, 3, '2025-08-11', '15:30:00', 'Manicure', 'confirmado', 1.29),
(5, 1, 1, '2025-08-15', '09:00:00', 'Tratamento capilar', 'pendente', 1.29);

SELECT 'Usuários de teste inseridos com sucesso!' as status;