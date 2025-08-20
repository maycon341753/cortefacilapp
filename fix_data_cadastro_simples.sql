-- ============================================
-- SCRIPT SIMPLES PARA ADICIONAR data_cadastro
-- CorteFácil - Sistema de Agendamento
-- ============================================

-- Usar o banco de dados correto
USE u690889028_cortefacil;

-- Adicionar a coluna data_cadastro na tabela usuarios
ALTER TABLE usuarios ADD COLUMN data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Atualizar registros existentes com a data atual
UPDATE usuarios SET data_cadastro = NOW() WHERE data_cadastro IS NULL;

-- Verificar se funcionou
SELECT id, nome, email, tipo_usuario, data_cadastro FROM usuarios;

/*
✅ SCRIPT SIMPLES PARA ADICIONAR COLUNA data_cadastro

📋 INSTRUÇÕES:
1. Execute este script no phpMyAdmin
2. Se der erro "column already exists", ignore - significa que já existe
3. Verifique os dados na consulta final

⚠️ NOTA:
- Se a coluna já existir, o ALTER TABLE dará erro, mas é normal
- O UPDATE funcionará mesmo assim
- Use este script se o anterior der problemas
*/