# Deploy do Frontend no Vercel

## ✅ **SIM, é possível implantar apenas o frontend no Vercel!**

Esta é uma excelente estratégia que oferece várias vantagens:

### 🎯 **Vantagens do Deploy Híbrido**

- **Frontend no Vercel**: Deploy rápido, CDN global, SSL automático
- **Backend no EasyPanel**: Controle total, banco de dados dedicado
- **Melhor performance**: Frontend otimizado + Backend estável
- **Economia**: Vercel gratuito para frontend, EasyPanel apenas para backend

## 🚀 **Passos para Deploy no Vercel**

### 1. Preparação do Repositório

```bash
# Certifique-se que o frontend está em uma pasta separada
git add frontend/
git commit -m "Preparar frontend para Vercel"
git push origin main
```

### 2. Configuração no Vercel

1. **Acesse**: https://vercel.com
2. **Conecte seu repositório GitHub**
3. **Configure o projeto**:
   - **Framework Preset**: Vite
   - **Root Directory**: `frontend`
   - **Build Command**: `npm install && npx vite build`
   - **Output Directory**: `dist`
   - **Install Command**: `npm install`
   - **Node.js Version**: 20.x

### 3. Variáveis de Ambiente no Vercel

Configure estas variáveis no painel do Vercel:

```
VITE_API_URL=https://api.cortefacil.app/api
VITE_APP_NAME=CortefácilApp
VITE_APP_VERSION=1.0.0
```

### 4. Configuração de Domínio (Opcional)

- **Domínio Vercel**: `cortefacil.vercel.app` (automático)
- **Domínio Personalizado**: Configure `cortefacil.app` no Vercel

## 🔧 **Configurações Necessárias**

### Backend no EasyPanel

O backend precisa permitir requisições do Vercel:

```javascript
// No backend, configure CORS para aceitar o domínio do Vercel
const corsOptions = {
  origin: [
    'https://cortefacil.vercel.app',
    'https://cortefacil.app', // se usar domínio personalizado
    'http://localhost:5173' // para desenvolvimento
  ],
  credentials: true
};
```

### Arquivo vercel.json

Já criado com configurações otimizadas:
- Roteamento SPA
- Cache de assets
- Variáveis de ambiente

## 📋 **Checklist de Deploy**

- [ ] Repositório atualizado no GitHub
- [ ] Projeto conectado no Vercel
- [ ] Root Directory configurado como `frontend`
- [ ] Variáveis de ambiente configuradas
- [ ] Backend no EasyPanel funcionando
- [ ] CORS configurado no backend
- [ ] Teste de comunicação frontend ↔ backend

## 🎉 **Resultado Final**

- **Frontend**: `https://cortefacil.vercel.app` (rápido, global)
- **Backend**: `https://api.cortefacil.app` (estável, dedicado)
- **Database**: No EasyPanel (seguro, persistente)

## 🔄 **Deploy Automático**

O Vercel fará deploy automático a cada push na branch `main`:

1. **Push código** → **Deploy automático**
2. **Preview branches** → **URLs de preview**
3. **Rollback fácil** → **Versões anteriores**

## 🛠️ **Comandos Úteis**

```bash
# Testar build local
npm run build
npm run preview

# Deploy manual (se necessário)
npx vercel --prod

# Verificar logs
npx vercel logs
```

## 🚨 **Pontos de Atenção**

1. **API URL**: Deve apontar para `https://api.cortefacil.app`
2. **CORS**: Backend deve aceitar requisições do Vercel
3. **HTTPS**: Vercel força HTTPS, certifique-se que a API suporta
4. **Environment**: Use `.env.vercel` para configurações específicas
5. **Node.js Version**: Especificada como 20.x para compatibilidade
6. **Build Command**: Usa `npx vite build` para evitar problemas de permissão

## 🔧 **Correções Aplicadas**

### ❌ Erro 126 - Permission Denied
**Problema**: `npm error code 126` - comando `sh -c vite build` falha
**Causa**: Problemas de permissão com executáveis do Vite

**Soluções Implementadas**:
1. **Script Build**: Alterado para `npx vite build --mode production` (removido NODE_ENV para compatibilidade Windows)
2. **Vercel Config**: Build command `npm install && npx vite build --mode production`
3. **Vite Config**: Corrigido uso de `__dirname` para ESM com `fileURLToPath`
4. **Package.json**: Removida duplicação de engines e corrigidos scripts
5. **Vercelignore**: Adicionado para evitar arquivos desnecessários no build
6. **Teste Local**: Build testado e funcionando corretamente (✓ built in 4.40s)

### Versão do Node.js
**Configurado**: Node.js 20.x especificado em:
- `package.json` (engines)
- `.nvmrc`
- Framework detection automático no Vercel

### Build Configuration
**Otimizado**: `vercel.json` simplificado com framework Vite

---

**✅ Conclusão**: Deploy híbrido é uma excelente estratégia que combina o melhor dos dois mundos!