# 🚨 CORREÇÃO URGENTE - PROBLEMAS EASYPANEL IDENTIFICADOS

## 📋 PROBLEMAS ENCONTRADOS

### 1. ❌ Redirect 307 Desnecessário
- `https://cortefacil.app/api` → `https://www.cortefacil.app/api`
- Causa lentidão e problemas de CORS

### 2. ❌ Backend Servindo Frontend
- `https://www.cortefacil.app/api` retorna HTML do frontend
- Rotas POST retornam erro 405 (Method Not Allowed)
- API não está funcionando corretamente

### 3. ❌ Configuração de Domínio Incorreta
- Backend e frontend estão no mesmo domínio
- Falta separação adequada entre serviços

## ✅ SOLUÇÕES IMEDIATAS

### Solução 1: Configurar Domínios Separados (RECOMENDADO)

**No EasyPanel:**

#### Backend Service:
```
📋 Configuração Backend
├── Nome: cortefacil-backend
├── Tipo: App
├── Build Context: backend/
├── Dockerfile Path: Dockerfile
├── Port: 3001
├── Domínio: api.cortefacil.app  ← CRIAR ESTE SUBDOMÍNIO
└── Start Command: (vazio)
```

#### Frontend Service:
```
📋 Configuração Frontend
├── Nome: cortefacil-frontend
├── Tipo: App
├── Build Context: frontend/
├── Dockerfile Path: Dockerfile
├── Port: 80
├── Domínio: cortefacil.app (sem www)
└── Start Command: (vazio)
```

### Solução 2: Usar Path-Based Routing (ALTERNATIVA)

Se não puder criar subdomínio:

#### Backend Service:
```
📋 Configuração Backend
├── Nome: cortefacil-backend
├── Tipo: App
├── Build Context: backend/
├── Dockerfile Path: Dockerfile
├── Port: 3001
├── Path: /api/*  ← CONFIGURAR PATH
└── Start Command: (vazio)
```

#### Frontend Service:
```
📋 Configuração Frontend
├── Nome: cortefacil-frontend
├── Tipo: App
├── Build Context: frontend/
├── Dockerfile Path: Dockerfile
├── Port: 80
├── Path: /*  ← CATCH-ALL PARA FRONTEND
└── Start Command: (vazio)
```

## 🔧 PASSOS PARA CORREÇÃO

### Passo 1: Configurar DNS (se usar Solução 1)

1. **Acesse seu provedor de DNS**
2. **Adicione registro A:**
   ```
   api.cortefacil.app → IP do EasyPanel
   ```
3. **Aguarde propagação (5-30 minutos)**

### Passo 2: Reconfigurar Serviços no EasyPanel

1. **Acesse EasyPanel Dashboard**
2. **Vá para o serviço backend:**
   - Clique em "Settings" ou "Configurações"
   - Altere o domínio para `api.cortefacil.app`
   - Salve as configurações
3. **Vá para o serviço frontend:**
   - Clique em "Settings" ou "Configurações"
   - Configure domínio como `cortefacil.app`
   - Salve as configurações
4. **Redeploy ambos os serviços**

### Passo 3: Atualizar Variáveis de Ambiente

**Frontend (.env.production):**
```env
VITE_API_URL=https://api.cortefacil.app
```

**Backend (.env.easypanel):**
```env
CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app
NODE_ENV=production
PORT=3001
```

### Passo 4: Verificar Configuração

**Teste as URLs:**
- ✅ `https://cortefacil.app` → Frontend
- ✅ `https://api.cortefacil.app/health` → Backend Health Check
- ✅ `https://api.cortefacil.app/auth/register` → POST deve funcionar

## 🚨 CONFIGURAÇÃO ATUAL PROBLEMÁTICA

**Problema identificado:**
```
https://www.cortefacil.app/api/health → Retorna HTML do frontend
https://www.cortefacil.app/api/auth/register → 405 Method Not Allowed
```

**Causa:**
O EasyPanel está servindo o frontend em todas as rotas, incluindo `/api/*`

## 📋 CHECKLIST DE VERIFICAÇÃO

- [ ] DNS configurado para `api.cortefacil.app`
- [ ] Backend configurado com domínio `api.cortefacil.app`
- [ ] Frontend configurado com domínio `cortefacil.app`
- [ ] Variáveis de ambiente atualizadas
- [ ] Ambos os serviços redeployados
- [ ] Health check funcionando: `https://api.cortefacil.app/health`
- [ ] POST funcionando: `https://api.cortefacil.app/auth/register`
- [ ] Frontend carregando: `https://cortefacil.app`

## 🎯 RESULTADO ESPERADO

**Após a correção:**
- ✅ `https://cortefacil.app` → Frontend React
- ✅ `https://api.cortefacil.app` → Backend API
- ✅ Rotas POST funcionando corretamente
- ✅ Sem redirects desnecessários
- ✅ CORS configurado adequadamente

## 📞 SUPORTE ADICIONAL

**Se ainda houver problemas:**
1. Verificar logs do backend no EasyPanel
2. Confirmar que o Dockerfile do backend está correto
3. Testar localmente com `docker build` e `docker run`
4. Verificar se as portas estão corretas (3001 para backend, 80 para frontend)

---

**Status:** 🔴 Crítico - Requer correção imediata
**Prioridade:** Alta
**Tempo estimado:** 30-60 minutos (incluindo propagação DNS)