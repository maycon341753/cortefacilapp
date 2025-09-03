# 🔥 Configuração Firewall Hostinger - Porta 3306

## 📋 Comando do Suporte Hostinger

O suporte Hostinger forneceu este comando para liberar a porta 3306:

```bash
sudo ufw allow 3306/tcp
```

## 🚀 Passos Completos para Configuração

### 1. Acessar VPS via SSH

```bash
ssh u973908341@srv973908.hstgr.cloud
```

### 2. Verificar Status do Firewall

```bash
sudo ufw status
```

### 3. Liberar Porta 3306 (MySQL)

```bash
# Comando fornecido pelo suporte
sudo ufw allow 3306/tcp

# Verificar se a regra foi adicionada
sudo ufw status numbered
```

### 4. Verificar Configuração MySQL

```bash
# Verificar se MySQL está rodando
sudo systemctl status mysql

# Verificar configuração de bind-address
sudo grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf
```

### 5. Configurar MySQL para Conexões Remotas

```bash
# Editar configuração MySQL
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Alterar ou comentar a linha:
# bind-address = 127.0.0.1
# Para:
bind-address = 0.0.0.0

# Reiniciar MySQL
sudo systemctl restart mysql
```

### 6. Configurar Usuário MySQL para Acesso Remoto

```sql
# Conectar ao MySQL
mysql -u root -p

# Criar/atualizar usuário para acesso remoto
CREATE USER 'u973908341_cortefacil'@'%' IDENTIFIED BY 'Maycon341753';
GRANT ALL PRIVILEGES ON u973908341_cortefacil.* TO 'u973908341_cortefacil'@'%';
FLUSH PRIVILEGES;

# Verificar usuários
SELECT user, host FROM mysql.user WHERE user = 'u973908341_cortefacil';

# Sair
EXIT;
```

## 🔍 Testes de Conectividade

### Teste Local (no VPS)

```bash
# Testar conexão local
mysql -u u973908341_cortefacil -p -h localhost u973908341_cortefacil

# Testar porta
netstat -tlnp | grep :3306
```

### Teste Remoto (do seu computador)

```bash
# Testar conectividade da porta
telnet srv973908.hstgr.cloud 3306

# Ou usar nmap
nmap -p 3306 srv973908.hstgr.cloud

# Testar conexão MySQL
mysql -u u973908341_cortefacil -p -h srv973908.hstgr.cloud u973908341_cortefacil
```

## 📞 Informações para o Suporte Hostinger

### Template de Solicitação

```
Assunto: Liberação Porta 3306 MySQL - Conta u973908341

Olá equipe Hostinger,

Preciso de ajuda para liberar o acesso remoto ao MySQL na minha VPS.

Detalhes da conta:
- Usuário: u973908341
- Servidor: srv973908.hstgr.cloud
- Banco: u973908341_cortefacil
- Usuário MySQL: u973908341_cortefacil

Problema:
- Erro ECONNREFUSED ao conectar na porta 3306 externamente
- Aplicação hospedada no EasyPanel precisa acessar o banco

Já executei:
- sudo ufw allow 3306/tcp (conforme orientação)
- Configurei bind-address = 0.0.0.0
- Criei usuário com permissões remotas

Preciso que verifiquem:
1. Se a porta 3306 está liberada no firewall do servidor
2. Se há alguma restrição adicional de rede
3. Se o IP do EasyPanel precisa ser whitelistado

Obrigado!
```

### Informações Técnicas

- **Servidor**: srv973908.hstgr.cloud
- **IP**: 31.97.171.104
- **Porta**: 3306
- **Usuário SSH**: u973908341
- **Usuário MySQL**: u973908341_cortefacil
- **Banco**: u973908341_cortefacil
- **Aplicação**: EasyPanel (IP pode variar)

## ⚠️ Troubleshooting

### Se ainda não funcionar após liberar a porta:

1. **Verificar logs do MySQL:**
```bash
sudo tail -f /var/log/mysql/error.log
```

2. **Verificar se a porta está realmente aberta:**
```bash
sudo ss -tlnp | grep :3306
```

3. **Testar com diferentes IPs:**
```bash
# Testar com IP específico
mysql -u u973908341_cortefacil -p -h 31.97.171.104 u973908341_cortefacil
```

4. **Verificar configurações de rede do Hostinger:**
   - Pode haver firewall adicional no painel
   - Verificar se há whitelist de IPs
   - Confirmar se VPS permite conexões externas

## 🎯 Próximos Passos

1. **Executar comando do suporte**: `sudo ufw allow 3306/tcp`
2. **Seguir passos de configuração MySQL**
3. **Testar conectividade**
4. **Se não funcionar, contatar suporte novamente** com logs específicos
5. **Alternativa**: Usar solução SSH tunnel já implementada

## 📋 Checklist

- [ ] Executar `sudo ufw allow 3306/tcp`
- [ ] Configurar `bind-address = 0.0.0.0`
- [ ] Criar usuário MySQL remoto
- [ ] Reiniciar MySQL
- [ ] Testar conectividade local
- [ ] Testar conectividade remota
- [ ] Atualizar aplicação no EasyPanel
- [ ] Verificar logs de erro

Se todos os passos forem executados e ainda houver problemas, a solução SSH tunnel permanece como backup confiável.