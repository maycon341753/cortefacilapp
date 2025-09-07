# 🚀 Guia Completo: Deploy Frontend no Vercel

## 📋 Resumo da Configuração

**Frontend**: Vercel (React/Vite)  
**Backend**: EasyPanel (Node.js/Express)  
**Banco de Dados**: MySQL no EasyPanel  

---

## ✅ Configurações Já Implementadas

### 1. **Redirecionamento Pós-Registro**
- ✅ AuthContext modificado para login automático
- ✅ Register.jsx atualizado para redirecionamento baseado no tipo de usuário
- ✅ Clientes → `/cliente/dashboard`
- ✅ Parceiros → `/parceiro/dashboard`

### 2. **Configurações de CORS**
- ✅ Backend configurado para aceitar requisições do Vercel
- ✅ Domínios permitidos:
  - `https://cortefacilapp-frontend-maycon341753-projects.vercel.app`
  - `https://cortefacil.vercel.app`
  - `https://cortefacil.app`
  - `https://www.cortefacil.app`

### 3. **Arquivos de Configuração**
- ✅ `vercel.json` configurado
- ✅ `.env.vercel` com variáveis corretas
- ✅ Build command otimizado

---

## 🔧 URLs e Endpoints

### **Frontend (Vercel)**
```
URL Principal: https://cortefacilapp-frontend-maycon341753-projects.vercel.app
URL Alternativa: https://cortefacil.vercel.app (se configurado domínio customizado)
```

### **Backend (EasyPanel)**
```
URL da API: https://cortefacil-backend.7ebsu.easypanel.host/api
Health Check: https://cortefacil-backend.7ebsu.easypanel.host/api/health
```

---

## 📝 Passos para Deploy no Vercel

### **1. Preparar o Repositório**
```bash
# Navegar para a pasta do frontend
cd frontend

# Instalar dependências
npm install

# Testar build local
npm run build
```

### **2. Configurar Variáveis de Ambiente no Vercel**
No painel do Vercel, adicionar as seguintes variáveis:

```env
VITE_API_URL=https://cortefacil-backend.7ebsu.easypanel.host/api
VITE_APP_NAME=CortefácilApp
VITE_APP_VERSION=1.0.0
```

### **3. Deploy via CLI do Vercel**
```bash
# Instalar Vercel CLI (se não tiver)
npm i -g vercel

# Login no Vercel
vercel login

# Deploy
vercel --prod
```

### **4. Deploy via GitHub (Recomendado)**
1. Fazer push do código para GitHub
2. Conectar repositório no painel do Vercel
3. Configurar:
   - **Framework**: Vite
   - **Build Command**: `npm install && npx vite build --mode production`
   - **Output Directory**: `dist`
   - **Root Directory**: `frontend`

---

## 🧪 Testes Pós-Deploy

### **1. Verificar se o Frontend Carrega**
```
✅ Acessar: https://cortefacilapp-frontend-maycon341753-projects.vercel.app
✅ Verificar se não há erros no console
✅ Testar navegação entre páginas
```

### **2. Testar Comunicação com Backend**
```
✅ Página de registro deve carregar
✅ Formulário de registro deve funcionar
✅ Redirecionamento automático deve ocorrer
✅ Dashboard deve carregar após registro
```

### **3. Testar Fluxo Completo**
1. **Registro de Cliente**:
   - Preencher formulário → Registro automático → Redirecionamento para `/cliente/dashboard`

2. **Registro de Parceiro**:
   - Preencher formulário → Registro automático → Redirecionamento para `/parceiro/dashboard`

---

## 🔍 Troubleshooting

### **Problema: CORS Error**
**Solução**: Verificar se o domínio do Vercel está nas origens CORS do backend

### **Problema: API não responde**
**Solução**: Verificar se o backend no EasyPanel está rodando:
```
https://cortefacil-backend.7ebsu.easypanel.host/api/health
```

### **Problema: Redirecionamento não funciona**
**Solução**: Verificar se as rotas estão configuradas no `App.jsx`:
- `/cliente/dashboard`
- `/parceiro/dashboard`

### **Problema: Build falha no Vercel**
**Solução**: Verificar:
1. `package.json` tem todas as dependências
2. Não há erros de TypeScript/ESLint
3. Build command está correto

---

## 📊 Status Atual

| Componente | Status | URL |
|------------|--------|-----|
| Frontend (Vercel) | ⏳ Pendente Deploy | `https://cortefacilapp-frontend-maycon341753-projects.vercel.app` |
| Backend (EasyPanel) | ✅ Rodando | `https://cortefacil-backend.7ebsu.easypanel.host/api` |
| Banco de Dados | ✅ Configurado | MySQL no EasyPanel |
| CORS | ✅ Configurado | Aceita requisições do Vercel |
| Redirecionamento | ✅ Implementado | Cliente/Parceiro → Dashboards |

---

## 🎯 Próximos Passos

1. **Deploy no Vercel**
   - Conectar repositório GitHub
   - Configurar variáveis de ambiente
   - Fazer deploy

2. **Testes em Produção**
   - Testar registro de cliente
   - Testar registro de parceiro
   - Verificar redirecionamentos

3. **Configurar Domínio Customizado** (Opcional)
   - Configurar `cortefacil.app` para apontar para Vercel
   - Atualizar CORS no backend

4. **Monitoramento**
   - Configurar logs de erro
   - Monitorar performance
   - Configurar alertas

---

## 📞 Suporte

Se houver problemas durante o deploy:

1. **Verificar logs do Vercel** no painel de controle
2. **Testar localmente** com `npm run build && npm run preview`
3. **Verificar backend** acessando o health check
4. **Revisar configurações** de CORS e variáveis de ambiente

---

**✨ Tudo está pronto para o deploy no Vercel! ✨**