# 🔧 Guia SSH Tunnel para EasyPanel - Solução MySQL

## 📋 Problema Identificado

**Erro:** `Access denied for user 'u690889028_mayconwender'@'45.181.72.123' (using password: YES)`

**Causa:** O usuário MySQL não tem permissões para o IP atual (45.181.72.123)

**Solução:** SSH Tunnel para conectar via localhost no servidor

## 🚀 Implementação no EasyPanel

### 1. 📦 Atualizar Dockerfile

Adicione as dependências SSH no seu Dockerfile:

```dockerfile
# Use Node.js 18 LTS
FROM node:18-alpine

# Install SSH client and sshpass
RUN apk add --no-cache openssh-client sshpass bash

# Set working directory
WORKDIR /app

# Copy package files
COPY server/package*.json ./

# Install dependencies
RUN npm install --production

# Copy application code
COPY server/ .

# Copy SSH tunnel script
COPY solucao-ssh-tunnel-easypanel.js ./

# Create non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nodejs -u 1001

# Change ownership
RUN chown -R nodejs:nodejs /app
USER nodejs

# Expose port
EXPOSE 3001

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js || exit 1

# Start with SSH tunnel
CMD ["sh", "-c", "node solucao-ssh-tunnel-easypanel.js & sleep 10 && npm start"]
```

### 2. 🔧 Configurar Variáveis de Ambiente

No EasyPanel, vá em **Settings** → **Environment Variables** e adicione:

```bash
# SSH Configuration
SSH_HOST=srv973908.hstgr.cloud
SSH_USER=u973908341
SSH_PASSWORD=[SUA_SENHA_HOSTINGER]

# Database Configuration (via tunnel)
DB_HOST=localhost
DB_PORT=3307
DB_USER=u690889028_mayconwender
DB_PASSWORD=Maycon341753
DB_NAME=u690889028_cortefacil
DATABASE_URL=mysql://u690889028_mayconwender:Maycon341753@localhost:3307/u690889028_cortefacil

# Outras configurações
NODE_ENV=production
JWT_SECRET=3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53
FRONTEND_URL=https://cortefacil.app
BACKEND_URL=https://cortefacil.app/api
```

### 3. 🔑 Obter Senha SSH do Hostinger

#### Opção A: Usar Senha do Painel
1. Acesse o painel do Hostinger
2. Vá em **Hosting** → **Gerenciar**
3. Procure por **SSH Access** ou **Acesso SSH**
4. Use a mesma senha do painel ou crie uma senha SSH específica

#### Opção B: Resetar Senha SSH
1. No painel Hostinger, vá em **SSH Access**
2. Clique em **Reset Password** ou **Alterar Senha**
3. Defina uma nova senha
4. Use essa senha na variável `SSH_PASSWORD`

### 4. 📁 Arquivos Necessários

Certifique-se de que estes arquivos estão no seu projeto:

- ✅ `solucao-ssh-tunnel-easypanel.js` (script principal)
- ✅ `diagnostico-mysql-remoto.js` (para testes)
- ✅ `Dockerfile` (atualizado com dependências SSH)

### 5. 🚀 Deploy no EasyPanel

1. **Commit e Push** dos arquivos:
   ```bash
   git add .
   git commit -m "feat: Adicionar SSH tunnel para MySQL"
   git push
   ```

2. **Deploy no EasyPanel**:
   - Vá para seu projeto no EasyPanel
   - Clique em **Deploy**
   - Aguarde o build completar

3. **Verificar Logs**:
   - Acesse **Logs** no EasyPanel
   - Procure por mensagens como:
     ```
     🔧 CONFIGURAÇÃO SSH TUNNEL - CORTEFACIL
     ✅ Configuração SSH completa
     🚇 Estabelecendo túnel SSH...
     ✅ Túnel SSH iniciado com sucesso
     ✅ Conexão MySQL via tunnel: SUCESSO
     🎉 SSH TUNNEL CONFIGURADO COM SUCESSO!
     ```

## 🔍 Troubleshooting

### ❌ Erro: "Variáveis SSH não configuradas"
**Solução:** Verifique se todas as variáveis SSH estão definidas no EasyPanel:
- `SSH_HOST`
- `SSH_USER` 
- `SSH_PASSWORD`

### ❌ Erro: "sshpass: command not found"
**Solução:** Verifique se o Dockerfile inclui:
```dockerfile
RUN apk add --no-cache openssh-client sshpass bash
```

### ❌ Erro: "Permission denied (publickey,password)"
**Soluções:**
1. Verificar se a senha SSH está correta
2. Resetar senha SSH no painel Hostinger
3. Testar conexão SSH manualmente:
   ```bash
   ssh u973908341@srv973908.hstgr.cloud
   ```

### ❌ Erro: "Connection refused on port 3307"
**Solução:** O túnel não foi estabelecido. Verifique:
1. Logs do SSH tunnel
2. Se o processo SSH está rodando
3. Se a porta 3307 não está sendo usada por outro processo

### ❌ Erro: "MySQL connection timeout"
**Soluções:**
1. Aguardar mais tempo para o túnel estabilizar
2. Verificar se o MySQL está rodando no servidor Hostinger
3. Testar conexão direta ao MySQL via SSH

## 🧪 Teste Local

Para testar localmente antes do deploy:

```bash
# 1. Configurar variáveis de ambiente
export SSH_HOST=srv973908.hstgr.cloud
export SSH_USER=u973908341
export SSH_PASSWORD=[sua_senha]
export DB_USER=u690889028_mayconwender
export DB_PASSWORD=Maycon341753
export DB_NAME=u690889028_cortefacil

# 2. Executar diagnóstico
node diagnostico-mysql-remoto.js

# 3. Testar SSH tunnel (se tiver sshpass instalado)
node solucao-ssh-tunnel-easypanel.js
```

## 📊 Monitoramento

### Logs Importantes
Fique atento a estas mensagens nos logs:

✅ **Sucesso:**
```
✅ Configuração SSH completa
✅ Túnel SSH iniciado com sucesso
✅ Conexão MySQL via tunnel: SUCESSO
⏰ Túnel ativo - [timestamp]
```

❌ **Problemas:**
```
❌ Variáveis SSH não configuradas
❌ Erro ao iniciar processo SSH
❌ SSH tunnel falhou com código [X]
❌ Erro na conexão MySQL via tunnel
```

### Health Check
O health check do container deve passar após o túnel ser estabelecido.

## 🎯 Alternativas

Se o SSH tunnel não funcionar:

### 1. 🔧 Corrigir Permissões MySQL
Siga o guia `SOLUCAO_DOCKER_IP.md` para configurar permissões via phpMyAdmin.

### 2. 📞 Contatar Suporte Hostinger
Solicite:
- Liberação da porta 3306 para conexões externas
- Adicionar IP do EasyPanel à whitelist
- Verificar configurações de firewall

### 3. 🗄️ Migrar para Banco EasyPanel
- Exportar dados do Hostinger
- Criar banco MySQL no EasyPanel
- Importar dados
- Atualizar configurações

## 📋 Checklist de Implementação

- [ ] Dockerfile atualizado com dependências SSH
- [ ] Variáveis de ambiente configuradas no EasyPanel
- [ ] Senha SSH obtida do painel Hostinger
- [ ] Arquivos `solucao-ssh-tunnel-easypanel.js` adicionados
- [ ] Commit e push realizados
- [ ] Deploy executado no EasyPanel
- [ ] Logs verificados para confirmar sucesso
- [ ] Aplicação testada e funcionando

## 🎉 Resultado Esperado

Após a implementação bem-sucedida:

1. ✅ SSH tunnel estabelecido automaticamente
2. ✅ Conexão MySQL funcionando via localhost:3307
3. ✅ Backend conectando ao banco sem erros
4. ✅ Aplicação funcionando normalmente
5. ✅ Logs mostrando túnel ativo

---

**💡 Dica:** Mantenha este guia como referência e monitore os logs regularmente para garantir que o túnel permaneça ativo.