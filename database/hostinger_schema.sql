-- =====================================================
-- SCRIPT SQL PARA HOSTINGER - CORTEFÁCIL APP
-- =====================================================
-- Execute este script no phpMyAdmin da Hostinger
-- Banco: u690889028_cortefacilapp
-- =====================================================

-- Configurar charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- TABELA: usuarios
-- =====================================================
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` enum('cliente','parceiro','admin') NOT NULL DEFAULT 'cliente',
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: saloes
-- =====================================================
DROP TABLE IF EXISTS `saloes`;
CREATE TABLE `saloes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `endereco` varchar(255) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_saloes_usuario` (`usuario_id`),
  CONSTRAINT `fk_saloes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: profissionais
-- =====================================================
DROP TABLE IF EXISTS `profissionais`;
CREATE TABLE `profissionais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `especialidade` varchar(100) NOT NULL,
  `salao_id` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_profissionais_salao` (`salao_id`),
  CONSTRAINT `fk_profissionais_salao` FOREIGN KEY (`salao_id`) REFERENCES `saloes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: servicos
-- =====================================================
DROP TABLE IF EXISTS `servicos`;
CREATE TABLE `servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `preco` decimal(10,2) NOT NULL,
  `duracao` int(11) NOT NULL COMMENT 'Duração em minutos',
  `salao_id` int(11) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_servicos_salao` (`salao_id`),
  CONSTRAINT `fk_servicos_salao` FOREIGN KEY (`salao_id`) REFERENCES `saloes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: agendamentos
-- =====================================================
DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `salao_id` int(11) NOT NULL,
  `profissional_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `data_agendamento` date NOT NULL,
  `hora_agendamento` time NOT NULL,
  `status` enum('pendente','confirmado','cancelado','concluido') DEFAULT 'pendente',
  `observacoes` text,
  `valor_total` decimal(10,2) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_agendamentos_cliente` (`cliente_id`),
  KEY `fk_agendamentos_salao` (`salao_id`),
  KEY `fk_agendamentos_profissional` (`profissional_id`),
  KEY `fk_agendamentos_servico` (`servico_id`),
  CONSTRAINT `fk_agendamentos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agendamentos_salao` FOREIGN KEY (`salao_id`) REFERENCES `saloes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agendamentos_profissional` FOREIGN KEY (`profissional_id`) REFERENCES `profissionais` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agendamentos_servico` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Inserir usuário administrador
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo_usuario`) VALUES 
('Administrador', 'admin@cortefacil.app', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir usuários de teste
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `tipo_usuario`, `telefone`) VALUES 
('Maria Silva', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 99999-9999'),
('João Santos', 'joao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 88888-8888'),
('Ana Costa', 'ana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 77777-7777');

-- Inserir salão de exemplo
INSERT INTO `saloes` (`nome`, `descricao`, `endereco`, `telefone`, `email`, `usuario_id`) VALUES 
('Salão Beleza & Estilo', 'Salão completo com serviços de cabelo, barba e estética', 'Rua das Flores, 123 - Centro', '(11) 3333-4444', 'contato@belezaestilo.com', 3);

-- Inserir profissionais de exemplo
INSERT INTO `profissionais` (`nome`, `especialidade`, `salao_id`) VALUES 
('Carlos Barbeiro', 'Corte Masculino e Barba', 1),
('Fernanda Cabeleireira', 'Corte e Coloração Feminina', 1),
('Roberto Estilista', 'Penteados e Tratamentos', 1);

-- Inserir serviços de exemplo
INSERT INTO `servicos` (`nome`, `descricao`, `preco`, `duracao`, `salao_id`) VALUES 
('Corte Masculino', 'Corte de cabelo masculino tradicional', 25.00, 30, 1),
('Barba Completa', 'Aparar e modelar barba', 20.00, 20, 1),
('Corte Feminino', 'Corte de cabelo feminino', 45.00, 60, 1),
('Escova', 'Escova modeladora', 30.00, 45, 1),
('Coloração', 'Pintura completa do cabelo', 80.00, 120, 1),
('Luzes', 'Mechas e reflexos', 120.00, 180, 1);

-- Inserir agendamentos de exemplo
INSERT INTO `agendamentos` (`cliente_id`, `salao_id`, `profissional_id`, `servico_id`, `data_agendamento`, `hora_agendamento`, `status`, `valor_total`) VALUES 
(2, 1, 1, 1, '2024-08-15', '10:00:00', 'confirmado', 25.00),
(4, 1, 2, 3, '2024-08-15', '14:00:00', 'pendente', 45.00),
(2, 1, 1, 2, '2024-08-16', '09:30:00', 'pendente', 20.00);

-- =====================================================
-- FINALIZAR
-- =====================================================
SET FOREIGN_KEY_CHECKS = 1;

-- Verificar se as tabelas foram criadas
SELECT 'Tabelas criadas com sucesso!' as status;
SELECT COUNT(*) as total_usuarios FROM usuarios;
SELECT COUNT(*) as total_saloes FROM saloes;
SELECT COUNT(*) as total_profissionais FROM profissionais;
SELECT COUNT(*) as total_servicos FROM servicos;
SELECT COUNT(*) as total_agendamentos FROM agendamentos;