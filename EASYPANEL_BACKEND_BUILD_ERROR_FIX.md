# 🚨 CORREÇÃO: Erro de Build do Backend (Exit Code 1)

## 📋 Erro Identificado

```bash
Command failed with exit code 1: pack build easypanel/cortefacil/cortefacil-backend 
--path /etc/easypanel/projects/cortefacil/cortefacil-backend/code/backend 
--default-process web --network easypanel 
--env 'Nome=BACKEND_URL' 
--env 'Valor= `https://cortefacil.app/api` ' 
--env 'NODE_ENV=production , PORT=3001 , DB_HOST=cortefacil_user' 
--env 'PORT=3001' 
--env 'DB_HOST=cortefacil_user' 
--env 'DB_USER=root' 
--env 'DB_PASSWORD=[Maycon341753]' 
--env 'DB_NAME=cortefacil' 
--builder 'heroku/builder:24'
```

## 🔍 Problemas Identificados

### 1. **Path Incorreto**
- **Problema**: `--path /etc/easypanel/projects/cortefacil/cortefacil-backend/code/backend`
- **Solução**: Deve ser `backend/` no Build Context

### 2. **Variáveis de Ambiente Malformadas**
- **Problema**: `NODE_ENV=production , PORT=3001 , DB_HOST=cortefacil_user` (tudo em uma variável)
- **Problema**: `PORT=3001` duplicado
- **Problema**: Variáveis com nomes estranhos (`Nome=BACKEND_URL`, `Valor=...`)

### 3. **Buildpack Incompatível**
- **Problema**: `heroku/builder:24` pode não ser compatível com Dockerfile
- **Solução**: Usar buildpack correto ou configurar Dockerfile adequadamente

## ✅ SOLUÇÕES

### 1. Configuração Correta no EasyPanel

**Configurações do Serviço Backend:**
```
- Nome: cortefacil-backend
- Tipo: App
- Caminho de Build: backend/
- Dockerfile: backend/Dockerfile (ou deixar vazio)
- Comando de Start: (DEIXAR VAZIO)
- Porta: 3001
```

### 2. Variáveis de Ambiente Corretas

**Configure cada variável separadamente:**
```
NODE_ENV=production
PORT=3001
DB_HOST=cortefacil_user
DB_USER=root
DB_PASSWORD=Maycon341753
DB_NAME=cortefacil
BACKEND_URL=https://api.cortefacil.app
```

### 3. Dockerfile Otimizado

Crie um `backend/Dockerfile.production`:

```dockerfile
# Use Node.js 18 LTS
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY server/package*.json ./

# Install dependencies
RUN npm ci --only=production

# Copy application code
COPY server/ .

# Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S nodejs -u 1001 && \
    chown -R nodejs:nodejs /app

USER nodejs

# Expose port
EXPOSE 3001

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js || exit 1

# Start application
CMD ["node", "server.js"]
```

### 4. Alternativa: Usar Buildpack Node.js

Se preferir usar buildpack em vez de Dockerfile:

**Configurações:**
```
- Buildpack: heroku/nodejs
- Caminho de Build: backend/server/
- Comando de Start: npm start
```

## 🔧 PASSOS PARA CORREÇÃO

### No EasyPanel:

1. **Acesse o serviço `cortefacil-backend`**
2. **Vá em "Configurações" → "Build"**
3. **Configure:**
   - **Caminho de Build**: `backend/`
   - **Dockerfile**: `backend/Dockerfile` (ou vazio)
   - **Comando de Start**: (deixar vazio)
4. **Vá em "Configurações" → "Ambiente"**
5. **Remova todas as variáveis malformadas**
6. **Adicione as variáveis corretas uma por uma:**
   ```
   NODE_ENV=production
   PORT=3001
   DB_HOST=cortefacil_user
   DB_USER=root
   DB_PASSWORD=Maycon341753
   DB_NAME=cortefacil
   ```
7. **Salve as configurações**
8. **Faça um novo deploy**

## 🚨 SOLUÇÃO RÁPIDA

### Opção 1: Dockerfile (Recomendado)
```
1. Caminho de Build: backend/
2. Dockerfile: backend/Dockerfile
3. Comando de Start: (vazio)
4. Variáveis de ambiente separadas
5. Redeploy
```

### Opção 2: Buildpack Node.js
```
1. Caminho de Build: backend/server/
2. Buildpack: heroku/nodejs
3. Comando de Start: npm start
4. Variáveis de ambiente separadas
5. Redeploy
```

## 📊 DIAGNÓSTICO

### Verificar Logs de Build:
- Procure por erros específicos do pack build
- Verifique se o Dockerfile está sendo encontrado
- Confirme se as dependências estão sendo instaladas

### Verificar Estrutura:
```
cortefacilapp/
├── backend/
│   ├── Dockerfile ✅
│   └── server/
│       ├── package.json ✅
│       └── server.js ✅
```

## ✅ CHECKLIST DE VERIFICAÇÃO

- [ ] Caminho de Build: `backend/`
- [ ] Dockerfile correto em `backend/Dockerfile`
- [ ] Comando de Start vazio
- [ ] Variáveis de ambiente separadas e corretas
- [ ] Porta 3001 configurada
- [ ] NODE_ENV=production
- [ ] Credenciais do banco corretas
- [ ] Deploy realizado após mudanças

---

**Status**: 🔧 Aguardando correção no EasyPanel
**Última atualização**: Janeiro 2025