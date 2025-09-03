# 🔍 Verificação MySQL VPS - Porta Ainda Bloqueada

## ❌ Status Atual (Após Suporte "Liberar")

**Teste de Conectividade:**
- ❌ `TcpTestSucceeded: False` para srv973908.hstgr.cloud:3306
- ❌ `TcpTestSucceeded: False` para 31.97.171.104:3306
- ⚠️ **Suporte disse que liberou, mas porta ainda inacessível**

## 🚨 Verificações Urgentes no VPS

### 1. Conectar ao VPS e Verificar MySQL

```bash
# Conectar ao VPS
ssh u973908341@srv973908.hstgr.cloud

# Verificar se MySQL está rodando
sudo systemctl status mysql

# Se não estiver rodando:
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 2. Verificar se MySQL Está Escutando na Porta 3306

```bash
# Verificar portas abertas
sudo netstat -tlnp | grep :3306
# OU
sudo ss -tlnp | grep :3306

# Deve mostrar algo como:
# tcp 0 0 0.0.0.0:3306 0.0.0.0:* LISTEN 1234/mysqld
```

### 3. Verificar Configuração bind-address

```bash
# Verificar configuração atual
sudo grep -n bind-address /etc/mysql/mysql.conf.d/mysqld.cnf

# Se mostrar:
# bind-address = 127.0.0.1
# Precisa alterar para:
# bind-address = 0.0.0.0

# Editar arquivo
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Alterar linha:
# DE: bind-address = 127.0.0.1
# PARA: bind-address = 0.0.0.0

# Salvar: Ctrl+X, Y, Enter
```

### 4. Reiniciar MySQL Após Configuração

```bash
# Reiniciar MySQL
sudo systemctl restart mysql

# Verificar status
sudo systemctl status mysql

# Verificar se agora está escutando em todas as interfaces
sudo ss -tlnp | grep :3306
# Deve mostrar: 0.0.0.0:3306 (não 127.0.0.1:3306)
```

### 5. Verificar Usuário MySQL Remoto

```sql
# Conectar ao MySQL
mysql -u root -p

# Verificar usuários existentes
SELECT user, host FROM mysql.user WHERE user = 'u973908341_cortefacil';

# Se não existir usuário remoto, criar:
CREATE USER 'u973908341_cortefacil'@'%' IDENTIFIED BY 'Maycon341753';
GRANT ALL PRIVILEGES ON u973908341_cortefacil.* TO 'u973908341_cortefacil'@'%';

# Se existir apenas localhost, atualizar:
UPDATE mysql.user SET host = '%' WHERE user = 'u973908341_cortefacil' AND host = 'localhost';

# Aplicar mudanças
FLUSH PRIVILEGES;

# Verificar novamente
SELECT user, host FROM mysql.user WHERE user = 'u973908341_cortefacil';

# Sair
EXIT;
```

### 6. Testar Conexão Local no VPS

```bash
# Testar conexão local
mysql -u u973908341_cortefacil -p -h localhost u973908341_cortefacil

# Testar conexão com IP local
mysql -u u973908341_cortefacil -p -h 127.0.0.1 u973908341_cortefacil

# Se funcionar localmente, problema é de firewall/rede
```

### 7. Verificar Firewall UFW

```bash
# Verificar status UFW
sudo ufw status numbered

# Deve mostrar regra para 3306:
# [X] 3306/tcp ALLOW IN Anywhere

# Se não mostrar, adicionar:
sudo ufw allow 3306/tcp

# Verificar iptables também
sudo iptables -L -n | grep 3306
```

## 🔧 Possíveis Problemas

### Problema 1: MySQL Não Configurado para Conexões Remotas
- **Sintoma**: MySQL roda mas só escuta em 127.0.0.1:3306
- **Solução**: Alterar `bind-address = 0.0.0.0`

### Problema 2: Usuário MySQL Só Permite Localhost
- **Sintoma**: Usuário existe mas host = 'localhost'
- **Solução**: Criar/atualizar usuário com host = '%'

### Problema 3: Firewall de Rede Hostinger
- **Sintoma**: UFW permite mas porta ainda bloqueada
- **Solução**: Contatar suporte novamente com logs específicos

### Problema 4: MySQL Não Está Rodando
- **Sintoma**: `systemctl status mysql` mostra inativo
- **Solução**: `sudo systemctl start mysql`

## 📞 Template para Suporte Hostinger

```
Assunto: URGENTE - Porta 3306 ainda bloqueada após liberação

Olá equipe,

Vocês informaram que a porta 3306 foi liberada, mas ainda não consigo conectar externamente.

Testes realizados:
❌ Test-NetConnection srv973908.hstgr.cloud:3306 - FALHA
❌ Test-NetConnection 31.97.171.104:3306 - FALHA

Verificações no VPS:
✅ MySQL rodando: sudo systemctl status mysql
✅ MySQL escutando: sudo ss -tlnp | grep :3306
✅ bind-address = 0.0.0.0 configurado
✅ Usuário remoto criado: u973908341_cortefacil@'%'
✅ UFW permite: sudo ufw status | grep 3306
✅ Conexão local funciona no VPS

O problema parece ser firewall de rede do datacenter.

Detalhes:
- Conta: u973908341
- Servidor: srv973908.hstgr.cloud (31.97.171.104)
- Porta: 3306
- Aplicação: EasyPanel precisa conectar externamente

Por favor, verifiquem:
1. Firewall de rede do datacenter
2. Políticas de segurança do servidor
3. Se há whitelist de IPs necessária

Este bloqueio está impedindo minha aplicação de funcionar.

Obrigado!
```

## ⚡ Ações Imediatas

### Opção 1: Verificar Configurações VPS
1. **Executar TODOS os comandos** das seções 1-7
2. **Documentar resultados** de cada verificação
3. **Se tudo estiver correto**, problema é de infraestrutura Hostinger

### Opção 2: Usar Solução SSH Tunnel (Recomendado)
```bash
# No EasyPanel, configurar:
SSH_HOST=srv973908.hstgr.cloud
SSH_USER=u973908341
SSH_PASSWORD=[sua_senha_ssh]
MYSQL_HOST=localhost  # Via tunnel

# Fazer redeploy do backend
```

### Opção 3: Migrar para Banco EasyPanel
- Exportar dados Hostinger
- Criar banco EasyPanel
- Importar dados
- Atualizar configurações

## 📋 Checklist de Verificação

- [ ] MySQL rodando: `sudo systemctl status mysql`
- [ ] MySQL escutando em 0.0.0.0:3306: `sudo ss -tlnp | grep :3306`
- [ ] bind-address = 0.0.0.0: `grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf`
- [ ] Usuário remoto existe: `SELECT user, host FROM mysql.user WHERE user = 'u973908341_cortefacil';`
- [ ] UFW permite 3306: `sudo ufw status | grep 3306`
- [ ] Conexão local funciona: `mysql -u u973908341_cortefacil -p -h localhost`
- [ ] Teste externo: `Test-NetConnection srv973908.hstgr.cloud 3306`

## 🎯 Resultado Esperado

Após executar todas as verificações:
- Se **conexão local funciona** mas **externa falha** = Problema de firewall Hostinger
- Se **conexão local falha** = Problema de configuração MySQL
- Se **MySQL não roda** = Problema de serviço

**Recomendação**: Use a solução SSH tunnel que já está implementada enquanto resolve com o suporte!