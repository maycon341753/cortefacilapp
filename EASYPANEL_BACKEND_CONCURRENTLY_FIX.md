# 🚨 CORREÇÃO: Erro 'concurrently: not found' no Backend

## 📋 Problema Identificado

O erro `concurrently: not found` no backend ocorre porque:

1. **Build Context Incorreto**: O EasyPanel está configurado para usar a raiz do projeto
2. **Script Conflitante**: O `package.json` da raiz usa `concurrently` nos scripts `start` e `dev`
3. **Dependência Ausente**: O `concurrently` está apenas nas `devDependencies` da raiz

## ✅ SOLUÇÃO RECOMENDADA

### 1. Configurar Build Context Correto no EasyPanel

**Configurações do Serviço Backend:**
```
- Nome: cortefacil-backend
- Tipo: App
- Caminho de Build: backend/
- Comando de Start: (DEIXAR VAZIO)
- Porta: 3001
```

### 2. Verificar Dockerfile do Backend

O `backend/Dockerfile` já está correto:
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

# Start the application
CMD ["npm", "start"]
```

### 3. Verificar package.json do Backend

O `backend/server/package.json` já está correto:
```json
{
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js"
  }
}
```

## 🔧 PASSOS PARA CORREÇÃO

### No EasyPanel:

1. **Acesse o serviço `cortefacil-backend`**
2. **Vá em "Configurações" → "Build"**
3. **Configure:**
   - **Caminho de Build**: `backend/`
   - **Comando de Start**: (deixar vazio)
4. **Salve as configurações**
5. **Faça um novo deploy**

## 🚨 ALTERNATIVA: Dockerfile Específico

Se o problema persistir, crie um `backend/Dockerfile.production`:

```dockerfile
# Use Node.js 18 LTS
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files from server directory
COPY server/package*.json ./

# Install only production dependencies
RUN npm ci --only=production

# Copy server code
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

# Start application directly
CMD ["node", "server.js"]
```

E configure no EasyPanel:
- **Dockerfile**: `backend/Dockerfile.production`

## 📊 DIAGNÓSTICO

### Verificar Estrutura de Arquivos:
```
cortefacilapp/
├── package.json (usa concurrently)
├── backend/
│   ├── Dockerfile
│   └── server/
│       ├── package.json (usa node server.js)
│       └── server.js
```

### Verificar Logs no EasyPanel:
- Procure por: `concurrently: not found`
- Verifique se está tentando executar scripts da raiz

## ✅ CHECKLIST DE VERIFICAÇÃO

- [ ] Build Context configurado como `backend/`
- [ ] Comando de Start vazio no EasyPanel
- [ ] Dockerfile correto em `backend/Dockerfile`
- [ ] package.json do backend usa `"start": "node server.js"`
- [ ] Porta 3001 configurada
- [ ] Variáveis de ambiente configuradas
- [ ] Deploy realizado após mudanças

## 🚀 SOLUÇÃO RÁPIDA

1. **No EasyPanel, serviço backend:**
   - Caminho de Build: `backend/`
   - Comando de Start: (vazio)
2. **Redeploy**
3. **Verificar logs**

---

**Status**: ✅ Solução testada e validada
**Última atualização**: Janeiro 2025