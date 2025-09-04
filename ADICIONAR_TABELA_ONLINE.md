# 🌐 Como Adicionar a Tabela Faltante no Banco Online

## ❌ Problema Identificado

Durante a verificação do banco de dados, foi identificado que a tabela `password_resets` está **faltando no banco online** do EasyPanel.

### 📊 Status das Tabelas:
- ✅ **Local (XAMPP):** 6 tabelas (incluindo password_resets)
- ❌ **Online (EasyPanel):** 6 tabelas (SEM password_resets)

## 🔧 Solução Criada

Foi criado o script **`add-missing-table-online.js`** que:
- ✅ Conecta ao banco do EasyPanel
- ✅ Verifica tabelas existentes
- ✅ Cria a tabela `password_resets` se não existir
- ✅ Testa a nova tabela
- ✅ Confirma que tudo está funcionando

## 🚀 Como Executar no EasyPanel

### Opção 1: Via Terminal do EasyPanel

1. **Acesse o painel EasyPanel**
2. **Vá para o serviço do backend** (`cortefacil-backend`)
3. **Clique em "Terminal"**
4. **Execute os comandos:**

```bash
# Navegar para o diretório do projeto
cd /app

# Executar o script para adicionar a tabela
node add-missing-table-online.js
```

### Opção 2: Via Deploy

1. **Faça upload do arquivo** `add-missing-table-online.js` para o servidor
2. **Execute via SSH ou terminal do painel**
3. **Remova o arquivo após a execução**

### Opção 3: Via SQL Direto

1. **Acesse o terminal do MySQL no EasyPanel**
2. **Execute o SQL diretamente:**

```sql
USE u690889028_cortefacil;

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

SHOW TABLES;
DESCRIBE password_resets;
```

## 📋 O que o Script Faz

### 1. 🔌 Conexão
- Conecta ao MySQL do EasyPanel
- Host: `cortefacil_cortefacil_user:3306`
- User: `mayconwender`
- Database: `u690889028_cortefacil`

### 2. 📊 Verificação
- Lista todas as tabelas existentes
- Verifica se `password_resets` já existe
- Se existir, mostra a estrutura atual

### 3. 🔧 Criação
- Cria a tabela `password_resets` com:
  - **Campos:** id, email, token, expires_at, used, created_at, updated_at
  - **Índices:** email, token, expires_at
  - **Constraints:** token único, campos obrigatórios

### 4. 🧪 Teste
- Insere um registro de teste
- Verifica se a consulta funciona
- Remove o registro de teste
- Confirma que tudo está OK

## ✅ Resultado Esperado

Após executar o script, você deve ver:

```
🎉 TABELA ADICIONADA COM SUCESSO!
✅ password_resets criada no banco online
🔧 Sistema de redefinição de senha pronto
🌐 Banco de dados online completo
```

## 🔍 Verificação Pós-Execução

### Comandos para verificar:

```sql
-- Verificar se a tabela foi criada
SHOW TABLES;

-- Ver estrutura da tabela
DESCRIBE password_resets;

-- Ver índices
SHOW INDEX FROM password_resets;

-- Contar registros (deve ser 0)
SELECT COUNT(*) FROM password_resets;
```

### Status final esperado:
- ✅ **7 tabelas** no total
- ✅ **password_resets** presente
- ✅ **Índices** criados corretamente
- ✅ **Sistema de redefinição** funcionando

## 🚨 Troubleshooting

### Se der erro de conexão:
- ✅ Verifique se o MySQL está rodando no EasyPanel
- ✅ Execute dentro do terminal do EasyPanel
- ✅ Confirme as credenciais do banco

### Se der erro de permissão:
- ✅ Verifique privilégios do usuário `mayconwender`
- ✅ Use usuário com privilégios de CREATE TABLE

### Se a tabela já existir:
- ✅ O script detectará e não fará nada
- ✅ Mostrará a estrutura atual
- ✅ Isso é normal e esperado

## 📝 Arquivos Relacionados

- **Script principal:** `add-missing-table-online.js`
- **SQL da tabela:** `database/password_resets.sql`
- **Verificação:** `check-easypanel-database.js`

## 🎯 Próximos Passos

1. ✅ **Executar o script no EasyPanel**
2. ✅ **Verificar se a tabela foi criada**
3. ✅ **Testar sistema de redefinição de senha**
4. ✅ **Fazer deploy completo da aplicação**
5. ✅ **Testar funcionalidade end-to-end**

---

**💡 Importante:** Execute este script apenas uma vez. Se executar novamente, ele detectará que a tabela já existe e não fará alterações.