# 🔧 Correção do Build Context do Backend no EasyPanel

## ❌ Problema Identificado

Na imagem fornecida, o backend está configurado com:
- **Caminho de Build**: `/` (INCORRETO)
- **Método**: Dockerfile
- **Construtor**: heroku/builder:24

Isso faz com que o EasyPanel tente executar o build a partir da raiz do projeto, onde existe um `package.json` com `concurrently`, causando o erro.

## ✅ Solução

### 1. Configuração Correta no EasyPanel

**Altere as seguintes configurações:**

```
Tipo: App
Source: GitHub
Método de Build: Dockerfile
Caminho de Build: backend/          ← ALTERAR DE "/" PARA "backend/"
Dockerfile Path: backend/Dockerfile  ← MANTER
Porta: 3001
```

### 2. Passos para Aplicar a Correção

1. **Acesse o serviço backend no EasyPanel**
2. **Vá para a aba "Fonte"**
3. **Altere o "Caminho de Build" de `/` para `backend/`**
4. **Clique em "Salvar"**
5. **Faça o redeploy do serviço**

### 3. Configurações Complementares

**Variáveis de Ambiente:**
```
NODE_ENV=production
PORT=3001
DB_HOST=[seu_host_do_banco]
DB_PORT=5432
DB_NAME=[nome_do_banco]
DB_USER=[usuario_do_banco]
DB_PASSWORD=[senha_do_banco]
JWT_SECRET=[sua_chave_secreta]
CORS_ORIGIN=https://cortefacil.app
```

**Domínio:**
```
Domínio: api.cortefacil.app
Porta: 3001
HTTPS: Habilitado
```

### 4. Verificação

Após aplicar as correções:

1. ✅ O serviço deve ficar **verde** (running)
2. ✅ Os logs não devem mostrar erro de "concurrently: not found"
3. ✅ A API deve responder em `https://api.cortefacil.app`

### 5. Estrutura Correta dos Serviços

```
📁 Projeto
├── 🔧 cortefacil-backend
│   ├── Build Context: backend/
│   ├── Dockerfile: backend/Dockerfile
│   └── Porta: 3001
├── 🌐 cortefacil-frontend  
│   ├── Build Context: frontend/
│   ├── Dockerfile: frontend/Dockerfile
│   └── Porta: 80
└── 🗄️ cortefacil_user (PostgreSQL)
```

## 🚨 Importante

O **Build Context** define qual diretório o EasyPanel usa como contexto para o build. Quando está definido como `/` (raiz), ele tenta executar o `package.json` da raiz que contém `concurrently` para desenvolvimento local.

Ao definir como `backend/`, o EasyPanel usa apenas o contexto do backend, onde o `Dockerfile` está configurado corretamente para produção.

---

**Após aplicar essas configurações, faça o redeploy e verifique se o serviço fica verde! 🟢**