# 🔧 CONFIGURAÇÃO DO PROXY REVERSO - EasyPanel

## 🚨 PROBLEMA IDENTIFICADO

O teste confirmou que:
- ❌ Requests `/api/*` ainda redirecionam para Vercel
- ❌ Proxy reverso não está configurado no EasyPanel
- ❌ Backend pode estar parado ou inacessível

---

## 🎯 SOLUÇÃO: Configurar Proxy /api/* → Backend

### 📋 PASSO 1: Acessar EasyPanel

1. **Abra o navegador**: https://easypanel.io
2. **Faça login** com suas credenciais
3. **Selecione o projeto**: `cortefacil`

### 🔍 PASSO 2: Localizar Configurações de Proxy

**Opção A: Via Domínios**
1. Clique em **"Domains"** ou **"Domínios"**
2. Encontre: `cortefacil.app`
3. Clique em **"Edit"** ou **"Editar"**
4. Procure por **"Proxy Rules"** ou **"Regras de Proxy"**

**Opção B: Via Services**
1. Clique em **"Services"** ou **"Serviços"**
2. Selecione o **frontend** (não o backend)
3. Vá para **"Domains"** ou **"Domínios"**
4. Clique em **"Advanced"** ou **"Avançado"**

### ⚙️ PASSO 3: Configurar Regra de Proxy

**Adicione uma nova regra:**

```
📍 Fonte (Source): /api/*
🎯 Destino (Target): http://backend:3001
🔧 Tipo: Proxy Pass
```

**Configuração detalhada:**
- **Path/Route**: `/api/*`
- **Upstream/Target**: `http://backend:3001`
- **Strip Path**: ✅ Habilitado (remove `/api` do caminho)
- **Headers**: Manter padrão

### 🔄 PASSO 4: Verificar Backend Ativo

1. **Vá para Services** → **Backend**
2. **Verifique o status**:
   - ✅ **"Running"** = OK
   - ❌ **"Stopped"** = Clique em **"Start"**
   - ⚠️ **"Error"** = Verifique logs

3. **Se necessário, reinicie**:
   - Clique em **"Stop"**
   - Aguarde parar completamente
   - Clique em **"Start"**

### 💾 PASSO 5: Salvar e Aplicar

1. **Clique em "Save"** ou **"Salvar"**
2. **Aguarde alguns segundos** para aplicar
3. **Verifique se aparece "Applied"** ou **"Aplicado"**

---

## 🧪 TESTE DA CORREÇÃO

### Método 1: Script Automático
```bash
node easypanel-auto-fix.js
```

### Método 2: Teste Manual
```bash
node verify-easypanel-fix.js
```

### Método 3: Navegador
1. Abra: https://cortefacil.app/api/health
2. **Esperado**: `{"status":"ok"}` (não página 404 do Vercel)

---

## 🔧 CONFIGURAÇÕES ALTERNATIVAS

### Se não encontrar "Proxy Rules":

**Opção 1: Nginx Custom**
```nginx
location /api/ {
    proxy_pass http://backend:3001/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

**Opção 2: Subdomínio**
- Criar: `api.cortefacil.app`
- Apontar para: `backend:3001`
- Atualizar frontend para usar: `https://api.cortefacil.app`

---

## 🚨 PROBLEMAS COMUNS

### ❌ "502 Bad Gateway"
**Causa**: Backend não está rodando
**Solução**: Iniciar o serviço backend

### ❌ "404 Not Found" (ainda Vercel)
**Causa**: Proxy não configurado corretamente
**Solução**: Verificar regra `/api/*` → `http://backend:3001`

### ❌ "CORS Error"
**Causa**: Headers CORS não configurados
**Solução**: Verificar variável `CORS_ORIGINS` no backend

### ❌ "Connection Timeout"
**Causa**: Backend demorado para responder
**Solução**: Verificar logs do backend, pode ser problema de DB

---

## ✅ VERIFICAÇÃO FINAL

Após configurar, você deve ver:

```bash
🔍 VERIFICAÇÃO PÓS-CORREÇÃO EASYPANEL
=====================================

📡 Testando: /api/health
   ✅ BACKEND Funcionando corretamente
   ⏱️  Tempo: 150ms

📡 Testando: /api/
   ✅ BACKEND Funcionando corretamente
   ⏱️  Tempo: 120ms

📊 ANÁLISE GERAL
================
✅ SUCESSO: Requests chegam ao backend
   - Proxy /api configurado corretamente
   - Backend respondendo normalmente
```

---

## 📞 SUPORTE

Se ainda houver problemas:
1. **Verifique logs** do backend no EasyPanel
2. **Execute**: `node easypanel-auto-fix.js`
3. **Consulte**: `EASYPANEL_CORRECAO_MANUAL.md`
4. **Teste**: `node verify-easypanel-fix.js`

**🎯 O objetivo é fazer `/api/*` chegar ao backend, não ao Vercel!**