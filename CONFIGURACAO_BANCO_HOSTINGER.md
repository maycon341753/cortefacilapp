# 🗄️ Configuração do Banco de Dados - Hostinger

## 📋 Status Atual

❌ **Problema Identificado:** Acesso negado ao banco de dados
- Usuário: `u690889028_cortefacil`
- Host: `srv973908.hstgr.cloud`
- Erro: `Access denied for user 'u690889028_cortefacil'@'IP' (using password: YES)`

## 🔧 Passos para Resolver

### 1. Criar o Serviço MySQL no Hostinger

1. **Acesse o painel do Hostinger**
2. **Vá para "Banco de Dados" → "MySQL"**
3. **Clique em "Criar Banco de Dados"**
4. **Configure:**
   - Nome do Banco: `u690889028_cortefacil`
   - Usuário: `u690889028_cortefacil` (ou outro nome)
   - Senha: `Maycon341753@`
   - Host: Será fornecido pelo Hostinger

### 2. Obter as Credenciais Corretas

Após criar o banco, o Hostinger fornecerá:
- ✅ **Host/Servidor:** (ex: `localhost` ou `srv973908.hstgr.cloud`)
- ✅ **Nome do Banco:** `u690889028_cortefacil`
- ✅ **Usuário:** (pode ser diferente do nome do banco)
- ✅ **Senha:** A que você definiu
- ✅ **Porta:** Geralmente `3306`

### 3. Configurar Permissões de IP

**IMPORTANTE:** O Hostinger pode restringir acesso por IP.

1. **No painel MySQL do Hostinger:**
   - Procure por "Hosts Remotos" ou "Remote Hosts"
   - Adicione seu IP atual: `177.50.70.124`
   - Ou use `%` para permitir qualquer IP (menos seguro)

### 4. Testar Conexão via phpMyAdmin

1. **Acesse o phpMyAdmin do Hostinger**
2. **Faça login com as credenciais**
3. **Verifique se consegue acessar o banco**
4. **Execute o script das tabelas:**

```sql
-- Use o arquivo: todas_tabelas_u690889028_cortefacil.sql
-- Que já foi criado com todas as tabelas necessárias
```

### 5. Atualizar Configurações do Projeto

Quando obtiver as credenciais corretas, atualize:

**Arquivo: `backend/server/.env`**
```env
DB_HOST=SEU_HOST_CORRETO
DB_PORT=3306
DB_USER=SEU_USUARIO_CORRETO
DB_PASSWORD=SUA_SENHA_CORRETA
DB_NAME=u690889028_cortefacil
```

### 6. Verificar Conexão

Após configurar:
```bash
node test-database-connection.js
```

## 🚨 Problemas Comuns

### Erro: Access Denied
- ✅ Verifique usuário e senha
- ✅ Confirme se o banco foi criado
- ✅ Verifique permissões de IP
- ✅ Teste via phpMyAdmin primeiro

### Erro: Can't Connect
- ✅ Verifique o host/servidor
- ✅ Confirme a porta (3306)
- ✅ Verifique firewall

### Erro: Database doesn't exist
- ✅ Crie o banco no painel Hostinger
- ✅ Execute o script das tabelas

## 📞 Próximos Passos

1. **Crie o serviço MySQL no Hostinger**
2. **Anote as credenciais corretas**
3. **Configure permissões de IP**
4. **Teste via phpMyAdmin**
5. **Atualize os arquivos .env**
6. **Execute o script das tabelas**
7. **Teste a conexão**

## 📁 Arquivos Importantes

- ✅ `todas_tabelas_u690889028_cortefacil.sql` - Script completo das tabelas
- ✅ `test-database-connection.js` - Teste de conexão
- ✅ `backend/server/.env` - Configurações do backend
- ✅ `.env` - Configurações gerais

---

**💡 Dica:** Sempre teste a conexão via phpMyAdmin antes de configurar a aplicação!