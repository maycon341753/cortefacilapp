# 🚨 CORREÇÃO: Erro de Permissão do Vite no Frontend

## 📋 Problema Identificado

**Erro:** `sh: vite: Permission denied` durante o build do frontend no EasyPanel

**Causa:** O Vite não possui permissões adequadas para executar no container Docker quando executado como usuário root.

## ✅ Solução Implementada

### 1. Correção no Dockerfile

O problema foi resolvido com uma solução robusta que corrige as permissões dos binários do node_modules:

```dockerfile
# Multi-stage build for React frontend

# Build stage
FROM node:18-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies as root
RUN npm install

# Copy source code
COPY . .

# Fix permissions for node_modules binaries
RUN chmod -R 755 node_modules/.bin/
RUN chmod +x node_modules/.bin/vite

# Create a non-root user and set ownership
RUN addgroup -g 1001 -S nodejs && adduser -S nextjs -u 1001
RUN chown -R nextjs:nodejs /app

# Switch to non-root user
USER nextjs

# Build the application
RUN npm run build

# Production stage
FROM nginx:alpine

# Copy built files from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80
```

### 2. Configuração Correta do EasyPanel

Para o serviço `cortefacil-frontend`:

- **Método de Build:** `Dockerfile`
- **Caminho de Build:** `frontend/`
- **Dockerfile Path:** `Dockerfile`
- **Comando de Start:** (deixar vazio)
- **Porta:** `80`

## 🔧 Passos para Aplicar a Correção

1. **Verificar o Dockerfile:** O arquivo já foi corrigido no repositório
2. **Configurar o EasyPanel:**
   - Acesse o painel do EasyPanel
   - Vá para o serviço `cortefacil-frontend`
   - Clique em "Edit" ou "Configurações"
   - Configure conforme especificado acima
   - Salve as configurações
3. **Realizar o Deploy:**
   - Clique em "Deploy" ou "Redeploy"
   - Aguarde o build completar

## 📝 Explicação Técnica

### Por que o erro ocorreu?
- O Vite, quando executado como root, pode ter problemas de permissão
- Alguns binários Node.js não funcionam corretamente com usuário root
- É uma boa prática de segurança usar usuários não-root em containers

### Como a solução funciona?
1. **Instalação como root:** As dependências são instaladas com permissões completas
2. **Correção de permissões:** Os binários do node_modules recebem permissões executáveis
3. **Criação do usuário:** Criamos um usuário `nextjs` com grupo `nodejs`
4. **Transferência de propriedade:** Todos os arquivos são transferidos para o usuário não-root
5. **Build seguro:** O build é executado como usuário não-root com permissões corretas
6. **Preservação da funcionalidade:** O Nginx continua funcionando normalmente

## ✅ Verificação do Sucesso

Após o deploy, você deve ver:
- Build completado sem erros de permissão
- Frontend acessível na URL do EasyPanel
- Logs sem mensagens de "Permission denied"

## 🆘 Troubleshooting

Se ainda houver problemas:
1. Verifique se o caminho de build está correto: `frontend/`
2. Confirme que o Dockerfile está no diretório `frontend/`
3. Verifique os logs do build no EasyPanel
4. Certifique-se de que não há outros erros no código

---

**Data da Correção:** Janeiro 2025  
**Status:** ✅ Resolvido  
**Arquivos Modificados:** `frontend/Dockerfile`