# Correção do Backend no EasyPanel

## Problema Identificado
O backend está apresentando o erro `concurrently: not found` porque o EasyPanel está tentando executar comandos do package.json da raiz do projeto, que é para desenvolvimento local.

## Solução

### 1. Configuração Correta do Backend no EasyPanel

O backend deve ser configurado para usar apenas o diretório `backend/server/`:

**Configurações do Serviço Backend:**
- **Tipo**: App
- **Source**: GitHub Repository
- **Build Method**: Dockerfile
- **Dockerfile Path**: `backend/Dockerfile`
- **Build Context**: `backend/`
- **Port**: 3001

### 2. Verificar o Dockerfile do Backend

O Dockerfile está em `backend/Dockerfile` e já está configurado corretamente:

```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY server/package*.json ./
RUN npm ci --only=production
COPY server/ .
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nodejs -u 1001
RUN chown -R nodejs:nodejs /app
USER nodejs
EXPOSE 3001
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js || exit 1
CMD ["npm", "start"]
```

### 3. Verificar o package.json do Backend

O arquivo `backend/server/package.json` deve ter o script start:

```json
{
  "scripts": {
    "start": "node server.js",
    "dev": "nodemon server.js"
  }
}
```

### 4. Variáveis de Ambiente do Backend

Configure estas variáveis no EasyPanel:

```
NODE_ENV=production
PORT=3001
JWT_SECRET=seu_jwt_secret_aqui
DATABASE_URL=sua_database_url_aqui
FRONTEND_URL=https://cortefacil.app
BACKEND_URL=https://api.cortefacil.app
DB_HOST=seu_db_host
DB_USER=seu_db_user
DB_PASSWORD=sua_db_password
DB_NAME=cortefacil
CORS_ORIGINS=https://cortefacil.app
```

### 5. Configuração de Domínio

- **Domínio**: `api.cortefacil.app`
- **Port**: 3001
- **HTTPS**: Ativado (certificado automático)

### 6. Passos para Corrigir

1. **Edite o serviço backend no EasyPanel**
2. **Defina Build Context como `backend/`**
3. **Confirme que Dockerfile Path é `backend/Dockerfile`**
4. **Configure todas as variáveis de ambiente listadas**
5. **Salve as configurações**
6. **Faça o redeploy do serviço**

### 7. Verificação

Após o redeploy:
- O backend deve buildar usando apenas o contexto `backend/server/`
- Não deve tentar executar `concurrently`
- O serviço deve ficar verde no painel
- A API deve estar acessível em `https://api.cortefacil.app`

### 8. Estrutura Correta dos Diretórios

```
backend/
└── server/
    ├── Dockerfile          # Dockerfile do backend
    ├── package.json        # Dependências do backend
    ├── server.js          # Arquivo principal
    ├── config/            # Configurações
    ├── routes/            # Rotas da API
    └── middleware/        # Middlewares
```

## Observações Importantes

- **NÃO use o package.json da raiz** no ambiente de produção
- **Build Context deve ser `backend/`** não a raiz do projeto
- **O comando `concurrently` é apenas para desenvolvimento local**
- **Cada serviço (frontend/backend) deve ter configuração independente**
- **Verifique se o Dockerfile existe em `backend/Dockerfile`**

## Troubleshooting

Se o erro persistir:
1. Verifique se o Build Context está correto
2. Confirme que o Dockerfile existe no caminho especificado
3. Verifique os logs de build para outros erros
4. Teste o build localmente com Docker