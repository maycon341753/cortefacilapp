# 🚨 CORREÇÃO URGENTE - EasyPanel Backend

## Status Confirmado pelo Diagnóstico

✅ **Banco de dados MySQL**: FUNCIONANDO  
❌ **Backend Node.js**: NÃO ACESSÍVEL (porta 3001 timeout)  
❌ **Proxy /api**: NÃO CONFIGURADO (requests vão para Vercel)  

## Ações Urgentes Necessárias

### 1. 🚨 VERIFICAR E INICIAR BACKEND NO EASYPANEL

**Passos no painel EasyPanel:**

1. **Acessar o EasyPanel**
   - Login em: https://easypanel.io
   - Selecionar projeto `cortefacil`

2. **Verificar Status do Backend**
   ```
   Services → cortefacil (backend) → Status
   ```
   - Se status = "Stopped" → Clicar em "Start"
   - Se status = "Running" → Verificar logs

3. **Verificar Logs do Container**
   ```
   Services → cortefacil (backend) → Logs
   ```
   - Procurar por erros de inicialização
   - Verificar se porta 3001 está sendo usada
   - Confirmar conexão com banco de dados

4. **Verificar Configurações do Serviço**
   ```
   Services → cortefacil (backend) → Settings
   ```
   - **Port**: 3001
   - **Environment Variables**: Verificar se estão todas definidas
   - **Health Check**: Configurar se necessário

### 2. 🚨 CONFIGURAR PROXY REVERSO

**Opção A: Configurar Proxy no EasyPanel**

1. **Acessar Configurações de Proxy**
   ```
   Services → Frontend/Proxy → Proxy Rules
   ```

2. **Adicionar Regra de Proxy**
   ```
   Path: /api/*
   Target: http://cortefacil-backend:3001
   Strip Path: false
   ```

3. **Configuração Nginx (se disponível)**
   ```nginx
   location /api/ {
       proxy_pass http://cortefacil-backend:3001/;
       proxy_set_header Host $host;
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header X-Forwarded-Proto $scheme;
       proxy_set_header X-Forwarded-Host $host;
   }
   ```

**Opção B: Usar Subdomínio (Alternativa)**

1. **Criar Subdomínio**
   - Configurar `api.cortefacil.app` → Backend Service
   - Atualizar DNS se necessário

2. **Atualizar Frontend**
   - Mudar `VITE_API_URL` para `https://api.cortefacil.app`

### 3. 🔧 VERIFICAR VARIÁVEIS DE AMBIENTE

**No EasyPanel, confirmar estas variáveis no backend:**

```env
# Servidor
NODE_ENV=production
PORT=3001

# Banco de Dados (usar host interno se disponível)
DB_HOST=cortefacil_mysql  # ou IP interno do MySQL
DB_PORT=3306
DB_USER=u690889028_mayconwender
DB_PASSWORD=Maycon341753@
DB_NAME=u690889028_mayconwender

# URLs
FRONTEND_URL=https://cortefacil.app
BACKEND_URL=https://cortefacil.app/api

# CORS
CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app,https://cortefacil.vercel.app

# JWT
JWT_SECRET=3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53
JWT_EXPIRES_IN=24h
```

### 4. 🧪 TESTAR APÓS CORREÇÕES

**Executar teste de verificação:**
```bash
node test-easypanel-backend-status.js
```

**URLs para testar manualmente:**
- ✅ https://cortefacil.app/api/health (deve retornar JSON, não HTML)
- ✅ https://cortefacil.app/api/ (deve retornar resposta do backend)
- ✅ https://www.cortefacil.app/api/health (deve funcionar)

## Checklist de Verificação

- [ ] Backend container está rodando no EasyPanel
- [ ] Logs do backend não mostram erros
- [ ] Porta 3001 está configurada corretamente
- [ ] Variáveis de ambiente estão definidas
- [ ] Proxy /api está configurado
- [ ] Teste de conectividade passa
- [ ] Frontend consegue acessar API

## Comandos de Diagnóstico

**Para verificar status após correções:**
```bash
# Teste completo
node test-easypanel-backend-status.js

# Teste rápido de endpoint
curl -I https://cortefacil.app/api/health

# Verificar se resposta vem do backend (deve retornar JSON)
curl https://cortefacil.app/api/health
```

## Problemas Comuns e Soluções

### Backend não inicia
- **Causa**: Erro nas variáveis de ambiente
- **Solução**: Verificar logs e corrigir configurações

### Proxy não funciona
- **Causa**: Regras de proxy não configuradas
- **Solução**: Adicionar regras de proxy no EasyPanel

### CORS errors
- **Causa**: Domínio não está na lista CORS_ORIGINS
- **Solução**: Adicionar todos os domínios necessários

### Timeout na porta 3001
- **Causa**: Container não está rodando ou porta não exposta
- **Solução**: Verificar status do container e configurações de rede

---

## 🎯 Resultado Esperado

Após as correções:
- ✅ `https://cortefacil.app/api/health` retorna JSON do backend
- ✅ Frontend consegue fazer login e usar todas as funcionalidades
- ✅ Não há mais erros 404 ou redirecionamentos para Vercel
- ✅ Sistema funciona completamente em produção

**Tempo estimado para correção: 15-30 minutos**

---

*Última atualização: Diagnóstico executado com sucesso*  
*Status: 🔴 CRÍTICO - Ação imediata necessária*