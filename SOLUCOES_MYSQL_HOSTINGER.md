# 🔧 Soluções para Erro MySQL Hostinger

## 📋 Problema Identificado

**Erro:** `Access denied for user 'u690889028_mayconwender'@'45.181.72.123' (using password: YES)`

**Causa:** O usuário MySQL não tem permissões para conexões remotas do IP externo.

## 🎯 Soluções Disponíveis

### 🚀 Solução 1: SSH Tunnel (RECOMENDADA - Mais Rápida)

Esta é a solução mais confiável e rápida para resolver o problema imediatamente.

#### Passos:

1. **Execute o SSH Tunnel:**
   ```bash
   node ssh-tunnel-solution.js
   ```

2. **Quando solicitado, insira a senha SSH do root do servidor**

3. **O script criará automaticamente o arquivo `.env.tunnel` com as configurações corretas**

4. **Copie as configurações do tunnel para o arquivo principal:**
   ```bash
   # No Windows (PowerShell)
   Copy-Item "backend\server\.env.tunnel" "backend\server\.env.easypanel"
   ```

5. **Reinicie o backend:**
   ```bash
   # Pare o backend atual (Ctrl+C no terminal do backend)
   # Depois execute:
   cd backend/server
   npm start
   ```

6. **Mantenha o terminal do SSH tunnel aberto** - ele deve permanecer ativo para a conexão funcionar

#### Vantagens:
- ✅ Funciona imediatamente
- ✅ Não requer alterações no servidor Hostinger
- ✅ Mais seguro (conexão criptografada)
- ✅ Bypass completo de problemas de firewall

#### Desvantagens:
- ⚠️ Requer manter o tunnel ativo
- ⚠️ Dependente da conexão SSH

---

### 🛠️ Solução 2: Corrigir Permissões MySQL (Permanente)

Esta solução corrige o problema na origem, mas requer acesso SSH ao servidor.

#### Passos:

1. **Execute o script de correção:**
   ```bash
   bash fix-hostinger-mysql.sh
   ```

2. **OU execute manualmente via SSH:**
   ```bash
   ssh root@srv973908.hstgr.cloud
   ```

3. **No servidor, execute os comandos SQL:**
   ```bash
   mysql -u root -p
   ```

4. **Execute os comandos do arquivo `fix-mysql-user-permissions.sql`:**
   ```sql
   CREATE USER IF NOT EXISTS 'u690889028_mayconwender'@'%' IDENTIFIED BY 'Maycon341753';
   GRANT ALL PRIVILEGES ON u690889028_cortefacil.* TO 'u690889028_mayconwender'@'%';
   FLUSH PRIVILEGES;
   ```

5. **Verifique se o MySQL está configurado para conexões remotas:**
   ```bash
   sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
   # Altere: bind-address = 0.0.0.0
   sudo systemctl restart mysql
   ```

6. **Teste a conexão:**
   ```bash
   node test-database-connection.js
   ```

#### Vantagens:
- ✅ Solução permanente
- ✅ Não requer tunnel ativo
- ✅ Performance melhor (conexão direta)

#### Desvantagens:
- ⚠️ Requer acesso SSH root
- ⚠️ Pode ser bloqueado pelo firewall do Hostinger
- ⚠️ Mais complexo de implementar

---

## 🎯 Recomendação Imediata

**Use a Solução 1 (SSH Tunnel)** para resolver o problema imediatamente e manter o desenvolvimento ativo.

## 📁 Arquivos Criados

- ✅ `ssh-tunnel-solution.js` - Script completo do SSH tunnel
- ✅ `fix-mysql-user-permissions.sql` - Comandos SQL para corrigir permissões
- ✅ `fix-hostinger-mysql.sh` - Script bash automatizado
- ✅ `test-database-connection.js` - Teste de conexão (atualizado)

## 🔍 Próximos Passos

1. **Execute:** `node ssh-tunnel-solution.js`
2. **Aguarde** a mensagem "SSH Tunnel funcionando perfeitamente!"
3. **Copie** as configurações do `.env.tunnel` para `.env.easypanel`
4. **Reinicie** o backend
5. **Teste** a aplicação