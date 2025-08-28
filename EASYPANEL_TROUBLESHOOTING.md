# 🔧 Troubleshooting Easypanel Deploy

## ❌ Erro: "failed to build: resolve : lstat /etc/easypanel/projects/cortefacil/cortefacil-backend/code/backend/backend"

### 🔍 Diagnóstico
Este erro indica que o Easypanel está procurando pelo diretório `/backend/backend` quando deveria procurar apenas `/backend`. Isso acontece devido à configuração incorreta do **Build Path** no Easypanel.

### ✅ Soluções

#### Solução 1: Corrigir Build Path (Recomendado)

1. **Acesse o Easypanel Dashboard**
   - Vá para o seu projeto `cortefacil`
   - Clique no serviço `cortefacil-backend`

2. **Editar Configurações**
   - Clique em "Settings" ou "Edit"
   - Procure por "Build Path" ou "Context Path"

3. **Configurar Build Path Correto**
   ```
   Build Path: /backend
   Dockerfile Path: /backend/Dockerfile
   ```
   
   **OU se estiver usando a raiz:**
   ```
   Build Path: /
   Dockerfile Path: /backend/Dockerfile
   ```

4. **Salvar e Rebuild**
   - Salve as configurações
   - Clique em "Deploy" ou "Rebuild"

#### Solução 2: Usar Docker Compose (Alternativa)

1. **Criar Novo Projeto**
   - Tipo: "Docker Compose"
   - Repository: seu repositório GitHub
   - Branch: `main`

2. **Configurar Source**
   ```
   Build Path: /
   Docker Compose File: docker-compose.yml
   ```

3. **O Easypanel detectará automaticamente os serviços:**
   - `backend` (porta 3001)
   - `frontend` (porta 80)
   - `database` (porta 3306)

#### Solução 3: Verificar Estrutura do Projeto

Certifique-se de que a estrutura está correta:
```
cortefacilapp/
├── backend/
│   ├── Dockerfile
│   └── server/
│       ├── package.json
│       ├── server.js
│       └── ...
├── frontend/
│   ├── Dockerfile
│   └── ...
├── docker-compose.yml
└── README.md
```

### 🔧 Configurações Específicas do Easypanel

#### Para Deploy Individual (Backend)
```yaml
Service Type: App
Name: cortefacil-backend
Source: Git Repository
Build Path: /backend
Dockerfile Path: Dockerfile
Port: 3001
Environment Variables:
  NODE_ENV: production
  PORT: 3001
  DB_HOST: database
  DB_PORT: 3306
  DB_NAME: cortefacil
  DB_USER: cortefacil_user
  DB_PASSWORD: [sua-senha]
  JWT_SECRET: [seu-jwt-secret]
```

#### Para Deploy com Docker Compose
```yaml
Project Type: Docker Compose
Repository: seu-usuario/cortefacil-app
Branch: main
Build Path: /
Compose File: docker-compose.yml
```

### 🚨 Problemas Comuns e Soluções

#### 1. Build Path Duplicado
**Problema:** `/backend/backend` ao invés de `/backend`
**Solução:** Verificar configuração do Build Path no Easypanel

#### 2. Dockerfile não encontrado
**Problema:** `Dockerfile not found`
**Solução:** 
```
Build Path: /backend
Dockerfile Path: Dockerfile (relativo ao Build Path)
```

#### 3. Context Path Incorreto
**Problema:** Arquivos não encontrados durante build
**Solução:** Usar Build Path `/backend` e não `/`

### 📋 Checklist de Verificação

- [ ] Build Path configurado como `/backend`
- [ ] Dockerfile existe em `/backend/Dockerfile`
- [ ] Estrutura do projeto está correta
- [ ] Variáveis de ambiente configuradas
- [ ] Repository e branch corretos
- [ ] Permissões de acesso ao repositório

### 🔄 Passos para Rebuild

1. **Corrigir configurações** conforme soluções acima
2. **Salvar alterações** no Easypanel
3. **Fazer rebuild manual:**
   - Services → cortefacil-backend → Deploy → Rebuild
4. **Monitorar logs** durante o build
5. **Verificar health check** após deploy

### 📞 Se o problema persistir

1. **Verificar logs detalhados** no Easypanel
2. **Testar build local:**
   ```bash
   cd backend
   docker build -t test-backend .
   ```
3. **Verificar se o repositório está atualizado**
4. **Contatar suporte do Easypanel** com logs específicos

---

**💡 Dica:** Sempre teste o build localmente antes de fazer deploy no Easypanel para identificar problemas mais rapidamente.