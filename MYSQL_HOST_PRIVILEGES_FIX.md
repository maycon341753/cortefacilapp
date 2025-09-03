# 🔧 Correção de Privilégios MySQL - Host Not Allowed

## ✅ Status Atual
- ✅ Porta 3306 liberada no firewall (confirmado)
- ✅ Progresso: Erro mudou de "Host not allowed" para "Access denied"
- ❌ Erro atual: `Access denied for user 'cortefacil_user'@'45.181.72.123' (using password: YES)`

## 🎯 Problema
O usuário MySQL `cortefacil_user` existe mas:
1. A senha pode estar incorreta
2. O usuário pode não ter privilégios para o IP `45.181.72.123`
3. O usuário pode não existir para este host específico

## 🛠️ Solução: Configurar Privilégios MySQL

### 1. Acessar o VPS via SSH
```bash
ssh root@srv973908.hstgr.cloud
```

### 2. Conectar ao MySQL como root
```bash
mysql -u root -p
```

### 3. Verificar usuários existentes
```sql
SELECT User, Host FROM mysql.user WHERE User = 'cortefacil_user';
```

### 4. Criar/Atualizar usuário para aceitar conexões externas

#### Opção A: Permitir qualquer IP (mais simples)
```sql
-- Remover usuário existente se necessário
DROP USER IF EXISTS 'cortefacil_user'@'localhost';
DROP USER IF EXISTS 'cortefacil_user'@'%';

-- Criar usuário que aceita conexões de qualquer IP
CREATE USER 'cortefacil_user'@'%' IDENTIFIED BY 'SUA_SENHA_AQUI';
GRANT ALL PRIVILEGES ON cortefacil.* TO 'cortefacil_user'@'%';
FLUSH PRIVILEGES;
```

#### Opção B: Permitir apenas IP específico (mais seguro)
```sql
-- Criar usuário para IP específico do EasyPanel
CREATE USER 'cortefacil_user'@'45.181.72.123' IDENTIFIED BY 'SUA_SENHA_AQUI';
GRANT ALL PRIVILEGES ON cortefacil.* TO 'cortefacil_user'@'45.181.72.123';
FLUSH PRIVILEGES;
```

### 5. Verificar se foi criado corretamente
```sql
SELECT User, Host FROM mysql.user WHERE User = 'cortefacil_user';
SHOW GRANTS FOR 'cortefacil_user'@'%'; -- ou @'45.181.72.123'
```

### 6. Sair do MySQL
```sql
EXIT;
```

## 🧪 Teste Local no VPS

### Testar conexão local
```bash
mysql -u cortefacil_user -p -h localhost cortefacil
```

### Testar conexão externa (do próprio VPS)
```bash
mysql -u cortefacil_user -p -h srv973908.hstgr.cloud cortefacil
```

## 📋 Comandos Rápidos (Copy/Paste)

```bash
# 1. SSH no VPS
ssh root@srv973908.hstgr.cloud

# 2. MySQL como root
mysql -u root -p
```

```sql
-- 3. Configurar usuário (escolha uma opção)
-- OPÇÃO A: Qualquer IP
DROP USER IF EXISTS 'cortefacil_user'@'localhost';
DROP USER IF EXISTS 'cortefacil_user'@'%';
CREATE USER 'cortefacil_user'@'%' IDENTIFIED BY 'SUA_SENHA_MYSQL';
GRANT ALL PRIVILEGES ON cortefacil.* TO 'cortefacil_user'@'%';
FLUSH PRIVILEGES;
EXIT;
```

## 🔍 Verificação Final

Após configurar, teste a conexão do seu ambiente local:

```bash
node test-mysql-connection.js
```

## 📞 Se Ainda Não Funcionar

### Verificar bind-address
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Procurar por bind-address e garantir que seja:
bind-address = 0.0.0.0

# Reiniciar MySQL
sudo systemctl restart mysql
```

### Verificar se MySQL está escutando
```bash
sudo netstat -tlnp | grep :3306
# Deve mostrar: 0.0.0.0:3306
```

## 🚀 Alternativa: Solução SSH Tunnel

Se ainda houver problemas, use a solução SSH tunnel já implementada:

1. Configure as variáveis no EasyPanel:
   - `SSH_HOST=srv973908.hstgr.cloud`
   - `SSH_USER=root`
   - `SSH_PASSWORD=sua_senha_ssh`
   - `DB_HOST=localhost` (via tunnel)

2. Redeploy do backend

## 📝 Notas Importantes

- ⚠️ **Segurança**: A opção `@'%'` permite conexões de qualquer IP. Para produção, prefira IPs específicos.
- 🔄 **FLUSH PRIVILEGES**: Sempre execute após alterar usuários.
- 🧪 **Teste**: Sempre teste a conexão após as alterações.
- 📱 **Backup**: A solução SSH tunnel está pronta como alternativa.