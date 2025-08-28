# 🚀 Deploy CortefácilApp no Easypanel

## 📋 Pré-requisitos

- Conta no Easypanel
- Repositório Git (GitHub, GitLab, etc.)
- Domínio configurado (opcional)

## 🔧 Configuração Inicial

### 1. Preparar o Repositório

```bash
# Fazer commit de todos os arquivos
git add .
git commit -m "Preparar para deploy no Easypanel"
git push origin main
```

### 2. Configurar Variáveis de Ambiente

No painel do Easypanel, configure as seguintes variáveis:

```env
# Banco de Dados
DB_HOST=database
DB_PORT=3306
DB_NAME=cortefacil
DB_USER=cortefacil_user
DB_PASSWORD=SuaSenhaSegura123
DB_ROOT_PASSWORD=RootSenhaSegura456

# API
NODE_ENV=production
PORT=3001
JWT_SECRET=SeuJWTSecretSuperSeguro789
JWT_EXPIRES_IN=7d

# Frontend
VITE_API_URL=https://seudominio.com/api
```

## 🐳 Deploy com Docker Compose

### Opção 1: Deploy Completo (Recomendado)

1. **Criar Novo Projeto no Easypanel**
   - Vá para "Projects" → "Create Project"
   - Nome: `cortefacil-app`
   - Selecione "Docker Compose"

2. **Configurar Source**
   - Repository: `seu-usuario/cortefacil-app`
   - Branch: `main`
   - Build Path: `/`

3. **Configurar Services**
   - O Easypanel detectará automaticamente o `docker-compose.yml`
   - Confirme os 3 serviços: `frontend`, `backend`, `database`

4. **Configurar Domínios**
   - Frontend: `seudominio.com` (porta 80)
   - Backend API: `api.seudominio.com` (porta 3001)

### Opção 2: Deploy Separado

#### Backend (API)

1. **Criar Service Backend**
   - Type: "App"
   - Name: `cortefacil-backend`
   - Source: Git Repository
   - Build Path: `/backend`
   - Port: `3001`

2. **Configurar Build**
   ```dockerfile
   # O Easypanel usará o Dockerfile em /backend/Dockerfile
   ```

3. **Configurar Variáveis**
   - Adicione todas as variáveis de ambiente listadas acima

#### Frontend

1. **Criar Service Frontend**
   - Type: "App"
   - Name: `cortefacil-frontend`
   - Source: Git Repository
   - Build Path: `/frontend`
   - Port: `80`

2. **Configurar Build**
   ```dockerfile
   # O Easypanel usará o Dockerfile em /frontend/Dockerfile
   ```

#### Database

1. **Criar Service Database**
   - Type: "Database"
   - Database Type: "MySQL"
   - Version: `8.0`
   - Database Name: `cortefacil`
   - Username: `cortefacil_user`
   - Password: `SuaSenhaSegura123`

## 🔗 Configuração de Rede

### Internal Network
- Backend se conecta ao database via hostname `database`
- Frontend faz requests para backend via domínio público

### External Access
- Frontend: `https://seudominio.com`
- API: `https://api.seudominio.com`

## 📊 Monitoramento

### Health Checks
- Backend: `GET /api/health`
- Frontend: Nginx status
- Database: MySQL connection

### Logs
- Acesse logs via painel do Easypanel
- Backend logs: aplicação Node.js
- Frontend logs: Nginx access/error

## 🔄 CI/CD Automático

O Easypanel detecta automaticamente mudanças no repositório:

1. **Push para main** → Deploy automático
2. **Pull Request** → Preview deploy (opcional)
3. **Rollback** → Versão anterior disponível

## 🛠️ Comandos Úteis

### Rebuild Manual
```bash
# No painel Easypanel
# Services → cortefacil-backend → Deploy → Rebuild
```

### Verificar Status
```bash
# Health check backend
curl https://api.seudominio.com/api/health

# Status frontend
curl -I https://seudominio.com
```

### Logs em Tempo Real
```bash
# No painel Easypanel
# Services → [service-name] → Logs → Live
```

## 🔧 Troubleshooting

### Problemas Comuns

1. **Backend não conecta ao banco**
   - Verificar variáveis `DB_HOST`, `DB_USER`, `DB_PASSWORD`
   - Confirmar que database service está rodando

2. **Frontend não carrega**
   - Verificar se build foi bem-sucedido
   - Confirmar configuração do Nginx

3. **CORS errors**
   - Atualizar `CORS_ORIGIN` no backend
   - Verificar `VITE_API_URL` no frontend

### Verificar Logs
```bash
# Backend logs
docker logs cortefacil-backend

# Frontend logs
docker logs cortefacil-frontend

# Database logs
docker logs cortefacil-db
```

## 📈 Otimizações

### Performance
- Nginx gzip habilitado
- Cache de assets estáticos
- Health checks configurados

### Segurança
- Headers de segurança no Nginx
- Variáveis de ambiente protegidas
- Usuário não-root nos containers

### Escalabilidade
- Containers stateless
- Database separado
- Load balancer ready

## 🎉 Finalização

Após o deploy bem-sucedido:

1. ✅ Frontend acessível em `https://seudominio.com`
2. ✅ API funcionando em `https://api.seudominio.com`
3. ✅ Database conectado e funcionando
4. ✅ Health checks passando
5. ✅ Logs disponíveis no painel

**Seu CortefácilApp está no ar! 🚀**

---

## 📞 Suporte

- Documentação Easypanel: https://easypanel.io/docs
- Issues do projeto: GitHub Issues
- Logs detalhados: Painel Easypanel