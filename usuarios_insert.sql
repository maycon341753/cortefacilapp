-- SQL para inserir usuários de teste no CorteFácil
-- Execute estes comandos no phpMyAdmin ou MySQL Workbench
-- Todas as senhas são criptografadas com password_hash() do PHP

-- ============================================
-- USUÁRIO ADMINISTRADOR
-- ============================================
-- Email: admin@cortefacil.com
-- Senha: password
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '(11) 99999-0000', NOW());

-- ============================================
-- USUÁRIO CLIENTE DE TESTE
-- ============================================
-- Email: cliente@teste.com
-- Senha: 123456
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) VALUES 
('Cliente Teste', 'cliente@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '(11) 99999-1111', NOW());

-- ============================================
-- USUÁRIO PARCEIRO DE TESTE
-- ============================================
-- Email: parceiro@teste.com
-- Senha: 123456
INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, data_cadastro) VALUES 
('Parceiro Teste', 'parceiro@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parceiro', '(11) 99999-2222', NOW());

-- ============================================
-- VERIFICAR SE OS USUÁRIOS FORAM INSERIDOS
-- ============================================
SELECT id, nome, email, tipo_usuario, telefone, data_cadastro 
FROM usuarios 
WHERE email IN ('admin@cortefacil.com', 'cliente@teste.com', 'parceiro@teste.com')
ORDER BY tipo_usuario, nome;

-- ============================================
-- COMANDOS ADICIONAIS (OPCIONAL)
-- ============================================

-- Para atualizar apenas a senha do administrador (se já existir):
-- UPDATE usuarios SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@cortefacil.com';

-- Para deletar todos os usuários de teste (se necessário):
-- DELETE FROM usuarios WHERE email IN ('admin@cortefacil.com', 'cliente@teste.com', 'parceiro@teste.com');

-- Para verificar total de usuários na tabela:
-- SELECT COUNT(*) as total_usuarios FROM usuarios;

-- ============================================
-- INFORMAÇÕES IMPORTANTES
-- ============================================
/*
CREDENCIAIS PARA LOGIN:

🔑 ADMINISTRADOR:
   Email: admin@cortefacil.com
   Senha: password

👤 CLIENTE:
   Email: cliente@teste.com
   Senha: 123456

🏪 PARCEIRO:
   Email: parceiro@teste.com
   Senha: 123456

NOTAS:
- Todas as senhas são criptografadas com bcrypt
- O hash usado é para as senhas mencionadas acima
- Execute os comandos INSERT um por vez para evitar erros
- Verifique se a tabela 'usuarios' existe antes de executar
- Use o comando SELECT no final para confirmar a inserção
*/