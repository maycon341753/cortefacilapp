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

## ✅ SOLUÇÕES PASSO A PASSO

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
# Build stage
FROM node:18-alpine as build

WORKDIR /app

# Copy package files
COPY package*.json ./
RUN npm ci

# Copy source code
COPY . .

# Build the app
RUN npm run build

# Production stage
FROM nginx:alpine

# Copy custom nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Copy built app
COPY --from=build /app/dist /usr/share/nginx/html

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost/ || exit 1

CMD ["nginx", "-g", "daemon off;"]
```

### Passo 4: Verificar nginx.conf

O arquivo `frontend/nginx.conf` deve conter:

```nginx
events {
    worker_connections 1024;
}

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;
    
    sendfile        on;
    keepalive_timeout  65;
    
    server {
        listen 80;
        server_name _;
        root /usr/share/nginx/html;
        index index.html;
        
        # Handle client-side routing
        location / {
            try_files $uri $uri/ /index.html;
        }
        
        # Security headers
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header X-Content-Type-Options "nosniff" always;
        
        # Cache static assets
        location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
            expires 1y;
            add_header Cache-Control "public, immutable";
        }
    }
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