# 🚨 CORREÇÃO: Erro 403 Proibido no Frontend

## ❌ Problema Identificado

O site `https://cortefacil.app` está retornando:
```
403 Proibido
O acesso a este recurso no servidor foi negado!
```

## 🔍 Possíveis Causas

### 1. **Serviço Frontend Não Está Rodando**
- O serviço `cortefacil-frontend` pode estar parado
- Build falhou ou não foi concluído
- Container não iniciou corretamente

### 2. **Configuração de Domínio Incorreta**
- DNS não está apontando corretamente
- Certificado SSL não configurado
- Proxy reverso mal configurado

### 3. **Problema no Nginx/Servidor Web**
- Configuração do nginx.conf incorreta
- Permissões de arquivo incorretas
- Diretório de build não encontrado

## ✅ SOLUÇÕES IMPLEMENTADAS

### ⚠️ CORREÇÃO CRÍTICA: Configuração Inválida do Nginx

**Problema:** O valor `must-revalidate` é inválido na diretiva `gzip_proxied` do Nginx.

**Erro nos logs:**
```
nginx: [emerg] invalid value "must-revalidate" in /etc/nginx/conf.d/default.conf:11
```

**Correção aplicada:**
```nginx
# ANTES (INCORRETO)
gzip_proxied expired no-cache no-store private must-revalidate auth;

# DEPOIS (CORRETO)
gzip_proxied expired no-cache no-store private auth;
```

### Passo 1: Verificar Status do Serviço

1. **Acesse o EasyPanel**
2. **Vá para o serviço `cortefacil-frontend`**
3. **Verifique se está com status VERDE (rodando)**
4. **Se estiver vermelho/amarelo:**
   - Clique em "Logs" para ver erros
   - Faça um redeploy

### Passo 2: Verificar Configurações do Frontend

**Configuração Correta:**
```
📋 Frontend Configuration
├── Tipo: App
├── Source: GitHub
├── URL: https://github.com/maycon341753/cortefacilapp.git
├── Ramo: main
├── Método de Build: Dockerfile
├── Caminho de Build: frontend        ← IMPORTANTE
├── Dockerfile Path: Dockerfile       ← RELATIVO AO CAMINHO
└── Porta: 80
```

### Passo 3: Verificar Dockerfile do Frontend

O arquivo `frontend/Dockerfile` deve estar assim:

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

# Remove default nginx config
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy built files from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Set proper permissions
RUN chmod -R 755 /usr/share/nginx/html
RUN chown -R nginx:nginx /usr/share/nginx/html

# Create nginx user if not exists
RUN addgroup -g 101 -S nginx || true
RUN adduser -S -D -H -u 101 -h /var/cache/nginx -s /sbin/nologin -G nginx -g nginx nginx || true

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost/ || exit 1

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
```

### Passo 4: Verificar nginx.conf

O arquivo `frontend/nginx.conf` deve conter:

```nginx
server {
    listen 80;
    server_name _;
    root /usr/share/nginx/html;
    index index.html index.htm;

    # Enable gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # Handle client routing, return all requests to index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline' 'unsafe-eval'" always;

    # Hide nginx version
    server_tokens off;

    # Error pages
    error_page 404 /index.html;
}
```

### Passo 5: Verificar Domínio e DNS

1. **No EasyPanel, vá para "Domínios"**
2. **Verifique se `cortefacil.app` está configurado:**
   - Apontando para o serviço `cortefacil-frontend`
   - Com certificado SSL ativo
   - Status verde

### Passo 6: Rebuild Completo

**Se nada funcionar:**

1. **Delete o serviço frontend completamente**
2. **Aguarde 2-3 minutos**
3. **Recrie o serviço com as configurações do Passo 2**
4. **Configure o domínio novamente**
5. **Aguarde o deploy completar**

## 🔍 DIAGNÓSTICO AVANÇADO

### Verificar Logs do Frontend

1. **Acesse o serviço frontend no EasyPanel**
2. **Clique em "Logs"**
3. **Procure por erros como:**
   ```
   ❌ nginx: [error] open() failed
   ❌ 403 Forbidden
   ❌ No such file or directory
   ❌ Permission denied
   ```

### Testar Conectividade

**Teste direto pela porta:**
- Se o serviço estiver na porta 80, teste: `http://[ip-do-servidor]:80`
- Verifique se responde sem o domínio

## 🎯 CHECKLIST DE VERIFICAÇÃO

- [ ] Serviço frontend está verde/rodando
- [ ] Build Context configurado como `frontend`
- [ ] Dockerfile Path configurado como `Dockerfile`
- [ ] Porta configurada como 80
- [ ] Domínio `cortefacil.app` apontando para o serviço
- [ ] Certificado SSL ativo
- [ ] Logs não mostram erros críticos
- [ ] Variáveis de ambiente configuradas

## 🚨 SOLUÇÃO RÁPIDA

**Se tiver pressa:**

1. **Vá para o serviço frontend**
2. **Clique em "Redeploy"**
3. **Aguarde 5-10 minutos**
4. **Teste novamente `https://cortefacil.app`**

---

**💡 O erro 403 geralmente indica que o servidor web (nginx) não consegue servir os arquivos. Isso pode ser por build falho, configuração incorreta ou serviço parado.**