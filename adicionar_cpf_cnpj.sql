-- ============================================
-- SCRIPT PARA ADICIONAR CPF E CNPJ
-- CorteFácil - Sistema de Agendamento
-- Prevenção de Fraudes
-- ============================================

-- Usar o banco de dados correto
USE u690889028_cortefacil;

-- ============================================
-- ADICIONAR CPF NA TABELA USUARIOS
-- ============================================

-- Adicionar coluna CPF para clientes e parceiros
ALTER TABLE usuarios 
ADD COLUMN cpf VARCHAR(14) NULL COMMENT 'CPF do usuário (apenas números ou formatado)',
ADD COLUMN cpf_verificado BOOLEAN DEFAULT FALSE COMMENT 'Se o CPF foi verificado';

-- Criar índice único para CPF (permitindo NULL para admin)
CREATE UNIQUE INDEX idx_usuarios_cpf ON usuarios(cpf);

-- ============================================
-- ADICIONAR CPF/CNPJ NA TABELA SALOES
-- ============================================

-- Adicionar colunas de documento para salões
ALTER TABLE saloes 
ADD COLUMN documento VARCHAR(18) NOT NULL COMMENT 'CPF ou CNPJ do salão',
ADD COLUMN tipo_documento ENUM('CPF', 'CNPJ') NOT NULL COMMENT 'Tipo do documento',
ADD COLUMN documento_verificado BOOLEAN DEFAULT FALSE COMMENT 'Se o documento foi verificado',
ADD COLUMN razao_social VARCHAR(200) NULL COMMENT 'Razão social (para CNPJ)',
ADD COLUMN inscricao_estadual VARCHAR(20) NULL COMMENT 'Inscrição estadual (opcional)';

-- Criar índice único para documento
CREATE UNIQUE INDEX idx_saloes_documento ON saloes(documento);

-- ============================================
-- ATUALIZAR DADOS EXISTENTES (TEMPORÁRIO)
-- ============================================

-- Atualizar usuários existentes com CPF temporário
-- IMPORTANTE: Remover após inserir CPFs reais
UPDATE usuarios 
SET cpf = CONCAT('000.000.00', LPAD(id, 1, '0'), '-', LPAD(id, 2, '0'))
WHERE tipo_usuario IN ('cliente', 'parceiro') AND cpf IS NULL;

-- Atualizar salões existentes com documento temporário
-- IMPORTANTE: Remover após inserir documentos reais
UPDATE saloes 
SET 
    documento = CONCAT('00.000.000/000', LPAD(id, 1, '0'), '-', LPAD(id, 2, '0')),
    tipo_documento = 'CNPJ',
    razao_social = CONCAT(nome, ' LTDA')
WHERE documento IS NULL OR documento = '';

-- ============================================
-- CRIAR TABELA DE LOG DE VERIFICAÇÕES
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
-- ATUALIZAR TABELA DE LOG DE ATIVIDADES
-- ============================================

-- Adicionar campos para rastreamento de alterações de documentos
ALTER TABLE log_atividades 
ADD COLUMN documento_alterado VARCHAR(18) NULL COMMENT 'Documento que foi alterado',
ADD COLUMN tipo_alteracao ENUM('cadastro', 'atualizacao', 'verificacao') NULL COMMENT 'Tipo de alteração no documento';

-- ============================================
-- VERIFICAÇÕES FINAIS
-- ============================================

-- Verificar estrutura da tabela usuarios
DESCRIBE usuarios;

-- Verificar estrutura da tabela saloes
DESCRIBE saloes;

-- Verificar usuários com CPF
SELECT id, nome, email, tipo_usuario, cpf, cpf_verificado 
FROM usuarios 
WHERE tipo_usuario IN ('cliente', 'parceiro')
ORDER BY tipo_usuario, nome;

-- Verificar salões com documento
SELECT s.id, s.nome, s.documento, s.tipo_documento, s.documento_verificado, s.razao_social, u.nome as dono
FROM saloes s 
JOIN usuarios u ON s.id_dono = u.id
ORDER BY s.nome;

-- Verificar índices criados
SHOW INDEX FROM usuarios WHERE Key_name LIKE '%cpf%';
SHOW INDEX FROM saloes WHERE Key_name LIKE '%documento%';

-- ============================================
-- INFORMAÇÕES IMPORTANTES
-- ============================================
/*
🛡️ SISTEMA DE PREVENÇÃO DE FRAUDES IMPLEMENTADO!

📋 ALTERAÇÕES REALIZADAS:

👤 TABELA USUARIOS:
✅ cpf - CPF do usuário (VARCHAR 14)
✅ cpf_verificado - Status de verificação (BOOLEAN)
✅ Índice único para CPF (previne duplicatas)

🏪 TABELA SALOES:
✅ documento - CPF ou CNPJ (VARCHAR 18)
✅ tipo_documento - Tipo do documento (ENUM)
✅ documento_verificado - Status de verificação (BOOLEAN)
✅ razao_social - Razão social para CNPJ (VARCHAR 200)
✅ inscricao_estadual - IE opcional (VARCHAR 20)
✅ Índice único para documento (previne duplicatas)

📊 NOVA TABELA:
✅ verificacoes_documento - Log de verificações de documentos

🔍 LOG APRIMORADO:
✅ Campos para rastrear alterações de documentos

⚠️ DADOS TEMPORÁRIOS:
- CPFs e CNPJs temporários foram inseridos
- IMPORTANTE: Substitua por documentos reais
- Formato CPF: 000.000.001-01, 000.000.002-02, etc.
- Formato CNPJ: 00.000.000/0001-01, 00.000.000/0002-02, etc.

🔐 SEGURANÇA:
✅ Índices únicos previnem duplicatas
✅ Campos de verificação para validação manual
✅ Log completo de alterações
✅ Rastreabilidade total

📝 PRÓXIMOS PASSOS:
1. Execute este script no phpMyAdmin
2. Substitua os documentos temporários por reais
3. Implemente validação de CPF/CNPJ no frontend
4. Configure processo de verificação manual
5. Teste o sistema de prevenção de fraudes

💡 DICAS DE IMPLEMENTAÇÃO:
- Use bibliotecas de validação de CPF/CNPJ
- Implemente verificação em tempo real
- Configure alertas para documentos duplicados
- Mantenha log de todas as alterações
*/