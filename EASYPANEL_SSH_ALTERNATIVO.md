# 🔧 Configuração SSH Alternativa - EasyPanel

## ⚠️ SOLUÇÃO SIMPLIFICADA PARA ERRO ECONNREFUSED

Como o acesso SSH com chaves está apresentando dificuldades, vamos usar uma abordagem alternativa.

## 🎯 Opção 1: Usar Senha SSH (Mais Simples)

### Modificar o Script SSH

Edite o arquivo `backend/server/ssh-tunnel-setup.sh` para usar senha:

```bash
#!/bin/bash

echo "🔧 Configurando túnel SSH para MySQL..."

# Verificar variáveis de ambiente
if [ -z "$SSH_HOST" ] || [ -z "$SSH_USER" ] || [ -z "$SSH_PASSWORD" ]; then
    echo "❌ Erro: Variáveis SSH não configuradas"
    echo "Configure: SSH_HOST, SSH_USER, SSH_PASSWORD"
    exit 1
fi

# Instalar sshpass se não existir
which sshpass > /dev/null || apk add --no-cache sshpass

# Configurar túnel SSH com senha
echo "🚇 Iniciando túnel SSH com senha..."
sshpass -p "$SSH_PASSWORD" ssh -f -N -L 3306:localhost:3306 -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST

if [ $? -eq 0 ]; then
    echo "✅ Túnel SSH configurado com sucesso"
    echo "📍 MySQL acessível via localhost:3306"
else
    echo "❌ Erro ao configurar túnel SSH"
    exit 1
fi

sleep 2
echo "🚀 Túnel SSH pronto - iniciando servidor Node.js..."
```

### Variáveis de Ambiente no EasyPanel

```bash
# SSH Configuration (com senha)
SSH_HOST=srv973908.hstgr.cloud
SSH_USER=u973908341
SSH_PASSWORD=[SUA_SENHA_HOSTINGER]

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_USER=u973908341_cortefacil
DB_PASSWORD=Maycon341753
DB_NAME=u973908341_cortefacil
DATABASE_URL=mysql://u973908341_cortefacil:Maycon341753@localhost:3306/u973908341_cortefacil
```

### Atualizar Dockerfile

```dockerfile
# Use Node.js 18 LTS
FROM node:18-alpine

# Install SSH client, bash and sshpass
RUN apk add --no-cache openssh-client bash sshpass

# Set working directory
WORKDIR /app

# Copy package files
COPY server/package*.json ./

# Install dependencies
RUN npm install --production

# Copy application code
COPY server/ .

# Create non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nodejs -u 1001

# Change ownership of the app directory
RUN chown -R nodejs:nodejs /app
USER nodejs

# Expose port
EXPOSE 3001

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js || exit 1

# Copy SSH tunnel setup script
COPY ssh-tunnel-setup.sh ./
RUN chmod +x ssh-tunnel-setup.sh

# Start the application with SSH tunnel
CMD ["sh", "-c", "./ssh-tunnel-setup.sh && npm start"]
```

## 🎯 Opção 2: Usar Banco de Dados EasyPanel (Recomendado)

### Vantagens:
- Sem necessidade de túnel SSH
- Melhor performance
- Mais seguro
- Sem dependência externa

### Passos:

1. **Criar Banco MySQL no EasyPanel:**
   - Acesse EasyPanel → Services → Add Service → MySQL
   - Configure nome: `cortefacil-db`
   - Anote as credenciais geradas

2. **Exportar Dados do Hostinger:**
   ```bash
   mysqldump -h srv973908.hstgr.cloud -u u973908341_cortefacil -p u973908341_cortefacil > backup.sql
   ```

3. **Importar no EasyPanel:**
   ```bash
   mysql -h [EASYPANEL_DB_HOST] -u [EASYPANEL_DB_USER] -p [EASYPANEL_DB_NAME] < backup.sql
   ```

4. **Atualizar Variáveis de Ambiente:**
   ```bash
   DB_HOST=[EASYPANEL_DB_HOST]
   DB_PORT=3306
   DB_USER=[EASYPANEL_DB_USER]
   DB_PASSWORD=[EASYPANEL_DB_PASSWORD]
   DB_NAME=[EASYPANEL_DB_NAME]
   DATABASE_URL=mysql://[USER]:[PASS]@[HOST]:3306/[DB_NAME]
   ```

## 🎯 Opção 3: Contatar Suporte Hostinger

### O que solicitar:

1. **Liberar porta 3306** para conexões externas
2. **Adicionar IP do EasyPanel** à whitelist MySQL
3. **Verificar configurações** do usuário MySQL

### Template de Solicitação:

```
Assunto: Liberação de Acesso Remoto MySQL - Conta u973908341

Olá,

Preciso habilitar o acesso remoto ao banco de dados MySQL da minha conta.

Detalhes:
- Usuário: u973908341_cortefacil
- Banco: u973908341_cortefacil
- Servidor: srv973908.hstgr.cloud
- Porta: 3306

Atualmente recebo erro "ECONNREFUSED" ao tentar conectar externamente.

Por favor, podem:
1. Habilitar conexões remotas na porta 3306
2. Adicionar meu IP à whitelist MySQL
3. Verificar permissões do usuário para acesso remoto

Obrigado!
```

## 🚀 Recomendação Final

**Para resolver rapidamente:** Use a Opção 2 (Banco EasyPanel)
**Para manter Hostinger:** Use a Opção 1 (SSH com senha) ou Opção 3 (Suporte)

A migração para o banco EasyPanel é a solução mais robusta e elimina completamente o problema de conectividade.