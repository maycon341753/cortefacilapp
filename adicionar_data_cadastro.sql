-- ============================================
-- SCRIPT PARA ADICIONAR COLUNA data_cadastro
-- CorteFácil - Sistema de Agendamento
-- ============================================

-- Usar o banco de dados correto
USE u690889028_cortefacil;

-- ============================================
-- ADICIONAR COLUNA data_cadastro NA TABELA usuarios
-- ============================================

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u690889028_cortefacil' 
AND TABLE_NAME = 'usuarios' 
AND COLUMN_NAME = 'data_cadastro';

-- Adicionar a coluna apenas se ela não existir
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE usuarios ADD COLUMN data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER telefone', 
    'SELECT "Coluna data_cadastro já existe" as status');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- ATUALIZAR REGISTROS EXISTENTES
-- ============================================

-- Atualizar registros que não têm data_cadastro definida
-- (usar created_at como referência se existir, senão usar data atual)
UPDATE usuarios 
SET data_cadastro = COALESCE(created_at, NOW()) 
WHERE data_cadastro IS NULL OR data_cadastro = '0000-00-00 00:00:00';

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================

-- Verificar a estrutura da tabela usuarios
DESCRIBE usuarios;

-- Verificar os dados dos usuários
SELECT id, nome, email, tipo_usuario, telefone, data_cadastro, created_at 
FROM usuarios 
ORDER BY id;

-- ============================================
-- INFORMAÇÕES
-- ============================================
/*
✅ SCRIPT PARA ADICIONAR COLUNA data_cadastro

🔧 O QUE ESTE SCRIPT FAZ:
1. Verifica se a coluna data_cadastro já existe
2. Adiciona a coluna apenas se ela não existir
3. Define valor padrão como CURRENT_TIMESTAMP
4. Atualiza registros existentes com data atual
5. Mostra a estrutura final da tabela

📋 INSTRUÇÕES:
1. Execute este script no phpMyAdmin
2. Verifique se a coluna foi adicionada corretamente
3. Confirme que os dados existentes foram atualizados

⚠️ IMPORTANTE:
- Este script é seguro para executar múltiplas vezes
- Não afeta dados existentes
- Apenas adiciona a coluna se ela não existir
*/