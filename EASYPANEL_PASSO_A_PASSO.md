# 🚨 GUIA PASSO-A-PASSO - Corrigir EasyPanel

## 🎯 Objetivo
Corrigir os 2 problemas críticos:
- ❌ Backend Node.js não acessível (porta 3001)
- ❌ Proxy /api não configurado (vai para Vercel)

---

## 📋 PASSO 1: Acessar EasyPanel

1. **Abra o navegador** e acesse:
   ```
   https://easypanel.io
   ```

2. **Faça login** com suas credenciais

3. **Selecione o projeto** `cortefacil`

---

## 🔍 PASSO 2: Verificar Status do Backend

1. **No painel lateral**, clique em:
   ```
   Services → [Nome do Backend Service]
   ```

2. **Verifique o status**:
   - ✅ Se estiver **"Running"** → Vá para PASSO 3
   - ❌ Se estiver **"Stopped"** → Continue abaixo

3. **Se estiver parado**:
   - Clique no botão **"Start"**
   - Aguarde alguns segundos
   - Verifique se mudou para "Running"

4. **Verificar logs**:
   - Clique na aba **"Logs"**
   - Procure por erros em vermelho
   - Verifique se aparece: `Server running on port 3001`

---

## ⚙️ PASSO 3: Verificar Configurações do Backend

1. **Clique na aba "Settings"**

2. **Verificar configurações essenciais**:
   ```
   Port: 3001
   Environment: production
   ```

3. **Verificar variáveis de ambiente**:
   - Clique em **"Environment Variables"**
   - Confirme se existem:
     ```
     NODE_ENV=production
     PORT=3001
     DB_HOST=31.97.171.104
     DB_USER=u690889028_mayconwender
     DB_PASSWORD=Maycon341753@
     DB_NAME=u690889028_mayconwender
     ```

4. **Se faltarem variáveis**:
   - Clique **"Add Variable"**
   - Adicione as variáveis necessárias
   - Clique **"Save"**
   - **Reinicie o serviço**

---

## 🌐 PASSO 4: Configurar Proxy /api

### Opção A: Usando Proxy Rules (Recomendado)

1. **No painel lateral**, procure por:
   ```
   Proxy / Load Balancer / Routing
   ```

2. **Clique em "Add Rule" ou "+"**

3. **Configure a regra**:
   ```
   Path/Pattern: /api/*
   Target/Upstream: http://[nome-do-backend-service]:3001
   Method: All
   Strip Path: No/False
   ```

4. **Salve a configuração**

### Opção B: Configuração Manual (Se disponível)

1. **Procure por "Nginx Config" ou "Custom Config"**

2. **Adicione a configuração**:
   ```nginx
   location /api/ {
       proxy_pass http://backend-service:3001/;
       proxy_set_header Host $host;
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header X-Forwarded-Proto $scheme;
   }
   ```

3. **Salve e aplique**

---

## 🧪 PASSO 5: Testar as Correções

1. **Aguarde 1-2 minutos** para as configurações serem aplicadas

2. **Teste no navegador**:
   ```
   https://cortefacil.app/api/health
   ```
   - ✅ **Deve retornar JSON**, não HTML
   - ✅ **Não deve mostrar página do Vercel**

3. **Execute o script de teste**:
   ```bash
   node easypanel-fix-script.js
   ```

4. **Teste o frontend**:
   - Acesse: https://cortefacil.app
   - Tente fazer login
   - Verifique se não há erros de API

---

## 🔧 TROUBLESHOOTING

### Backend não inicia
**Sintomas**: Container fica "Stopped" ou "Error"

**Soluções**:
1. Verificar logs para erros específicos
2. Confirmar variáveis de ambiente
3. Verificar se porta 3001 não está em uso
4. Reiniciar o serviço

### Proxy não funciona
**Sintomas**: `/api/health` ainda retorna HTML do Vercel

**Soluções**:
1. Verificar se a regra de proxy foi salva
2. Aguardar alguns minutos para propagação
3. Verificar nome correto do backend service
4. Tentar reiniciar o proxy/load balancer

### Erro de CORS
**Sintomas**: Erro "CORS policy" no console do navegador

**Soluções**:
1. Adicionar variável de ambiente:
   ```
   CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app
   ```
2. Reiniciar backend service

---

## ✅ CHECKLIST FINAL

- [ ] Backend service está "Running"
- [ ] Logs do backend não mostram erros
- [ ] Porta 3001 configurada
- [ ] Variáveis de ambiente definidas
- [ ] Regra de proxy `/api/*` criada
- [ ] `https://cortefacil.app/api/health` retorna JSON
- [ ] Frontend consegue fazer login
- [ ] Não há erros no console do navegador

---

## 🎯 RESULTADO ESPERADO

Após seguir todos os passos:

✅ **Backend funcionando**:
- Container "Running" no EasyPanel
- Logs sem erros críticos
- Porta 3001 acessível

✅ **Proxy configurado**:
- `/api/health` retorna JSON do backend
- Não há redirecionamentos para Vercel
- Frontend acessa API sem problemas

✅ **Sistema completo**:
- Login funciona
- Todas as funcionalidades disponíveis
- Performance normal

---

**⏱️ Tempo estimado**: 10-20 minutos
**🔴 Prioridade**: CRÍTICA - Sistema não funcional sem essas correções

---

## 📞 Se Precisar de Ajuda

1. **Execute o diagnóstico**:
   ```bash
   node test-easypanel-backend-status.js
   ```

2. **Verifique os logs** do EasyPanel

3. **Teste URLs específicas**:
   - https://cortefacil.app/api/health
   - https://cortefacil.app/api/

4. **Documente erros** encontrados para análise