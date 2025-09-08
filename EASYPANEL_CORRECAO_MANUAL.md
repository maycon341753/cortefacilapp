# 🚨 CORREÇÃO MANUAL - EasyPanel Backend

## ⚠️ Problema: SSH não está acessível

Como o acesso SSH não está funcionando, vamos corrigir manualmente pelo painel web do EasyPanel.

---

## 🎯 PASSO 1: Acessar EasyPanel

1. **Abra o navegador** e acesse:
   ```
   https://easypanel.io
   ```

2. **Faça login** com suas credenciais

3. **Selecione o projeto** `cortefacil`

---

## 🔍 PASSO 2: Verificar Status do Backend

### A. Localizar o Serviço Backend
1. **No painel lateral**, procure por:
   - `Services` ou `Serviços`
   - Procure um serviço com nome similar a:
     - `cortefacil-backend`
     - `backend`
     - `api`
     - `server`

### B. Verificar Status
1. **Clique no serviço backend**
2. **Verifique o status**:
   - ✅ **"Running"** = Funcionando
   - ❌ **"Stopped"** = Parado
   - ⚠️ **"Error"** = Com erro

### C. Se Estiver Parado
1. **Clique no botão "Start"** ou "Iniciar"
2. **Aguarde alguns segundos**
3. **Verifique se mudou para "Running"**

---

## 📋 PASSO 3: Verificar Logs do Backend

1. **Com o serviço selecionado**, clique em:
   - `Logs` ou `Registros`

2. **Procure por mensagens importantes**:
   ```
   ✅ Mensagens boas:
   - "Server running on port 3001"
   - "Connected to database"
   - "Backend started successfully"
   
   ❌ Mensagens de erro:
   - "ECONNREFUSED"
   - "Port already in use"
   - "Database connection failed"
   - "Error starting server"
   ```

3. **Se houver erros**, anote-os para correção

---

## ⚙️ PASSO 4: Verificar Configurações

### A. Verificar Porta
1. **Clique em "Settings"** ou "Configurações"
2. **Procure por "Port" ou "Porta"**
3. **Confirme que está definida como**: `3001`

### B. Verificar Variáveis de Ambiente
1. **Procure por "Environment Variables"** ou "Variáveis de Ambiente"
2. **Confirme se existem estas variáveis**:
   ```
   NODE_ENV=production
   PORT=3001
   DB_HOST=31.97.171.104
   DB_PORT=3306
   DB_USER=u690889028_mayconwender
   DB_PASSWORD=Maycon341753@
   DB_NAME=u690889028_mayconwender
   DATABASE_URL=mysql://u690889028_mayconwender:Maycon341753%40@31.97.171.104:3306/u690889028_mayconwender
   CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app
   FRONTEND_URL=https://cortefacil.app
   BACKEND_URL=https://cortefacil.app/api
   JWT_SECRET=3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53
   ```

3. **Se faltarem variáveis**:
   - Clique **"Add Variable"** ou **"Adicionar Variável"**
   - Adicione as variáveis necessárias
   - Clique **"Save"** ou **"Salvar"**

---

## 🌐 PASSO 5: Configurar Proxy /api

### A. Localizar Configurações de Proxy
1. **Procure no painel por**:
   - `Proxy`
   - `Load Balancer`
   - `Routing`
   - `Nginx`
   - `Reverse Proxy`

### B. Adicionar Regra de Proxy
1. **Clique em "Add Rule"** ou **"Adicionar Regra"**
2. **Configure**:
   ```
   Path/Caminho: /api/*
   Target/Destino: http://[nome-do-backend-service]:3001
   Method/Método: All/Todos
   Strip Path: No/Não
   ```

### C. Configuração Alternativa (Nginx)
Se houver opção de configuração Nginx customizada:
```nginx
location /api/ {
    proxy_pass http://cortefacil-backend:3001/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

---

## 🔄 PASSO 6: Reiniciar Serviços

### A. Reiniciar Backend
1. **Selecione o serviço backend**
2. **Clique em "Restart"** ou **"Reiniciar"**
3. **Aguarde alguns segundos**
4. **Verifique se voltou para "Running"**

### B. Reiniciar Proxy (se necessário)
1. **Se houver um serviço de proxy separado**
2. **Reinicie-o também**

---

## ✅ PASSO 7: Testar Correções

### A. Testar Endpoints
1. **Abra uma nova aba do navegador**
2. **Teste estes URLs**:
   ```
   https://cortefacil.app/api/health
   https://cortefacil.app/api/
   ```

3. **Resultados esperados**:
   - ✅ **Não deve redirecionar para Vercel**
   - ✅ **Deve retornar dados JSON do backend**
   - ❌ **Se ainda redirecionar = proxy não configurado**

### B. Executar Script de Verificação
1. **No seu computador**, execute:
   ```powershell
   node verify-easypanel-fix.js
   ```

2. **Verifique se os problemas foram resolvidos**

---

## 🆘 PROBLEMAS COMUNS

### ❌ Backend não inicia
**Possíveis causas:**
- Porta 3001 já em uso
- Variáveis de ambiente incorretas
- Erro de conexão com banco de dados
- Código com erro de sintaxe

**Soluções:**
1. Verificar logs detalhadamente
2. Confirmar todas as variáveis de ambiente
3. Testar conexão com banco separadamente

### ❌ Proxy não funciona
**Possíveis causas:**
- Regra de proxy não configurada
- Nome do serviço backend incorreto
- Porta incorreta na configuração

**Soluções:**
1. Verificar nome exato do serviço backend
2. Confirmar porta 3001
3. Testar configuração passo a passo

### ❌ Ainda redireciona para Vercel
**Causa:**
- Proxy /api não está funcionando

**Solução:**
1. Verificar configuração de proxy
2. Reiniciar todos os serviços
3. Aguardar alguns minutos para propagação

---

## 📞 PRÓXIMOS PASSOS

1. **Execute este guia passo a passo**
2. **Anote qualquer erro encontrado**
3. **Teste com o script de verificação**
4. **Se ainda houver problemas, compartilhe:**
   - Screenshots do painel EasyPanel
   - Logs do backend
   - Mensagens de erro específicas

---

## 🎯 RESULTADO ESPERADO

Após seguir todos os passos:
- ✅ Backend rodando na porta 3001
- ✅ Proxy /api configurado
- ✅ Requests não vão mais para Vercel
- ✅ API funcionando corretamente

**Para confirmar, execute:**
```powershell
node verify-easypanel-fix.js
```

E veja se todos os testes passam! 🎉