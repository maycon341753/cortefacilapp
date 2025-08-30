# Correção do Frontend no EasyPanel

## Problema Identificado
O frontend está tentando executar o comando `concurrently` que não existe no container do frontend. Isso acontece porque o EasyPanel está usando a configuração do package.json da raiz do projeto, que é para desenvolvimento local.

## Solução

### 1. Configuração Correta do Frontend no EasyPanel

O frontend deve ser configurado como um **serviço de aplicação estática** usando o Dockerfile que já existe:

**Configurações do Serviço Frontend:**
- **Tipo**: App
- **Source**: GitHub Repository
- **Build Method**: Dockerfile
- **Dockerfile Path**: `frontend/Dockerfile`
- **Build Context**: `frontend/`
- **Port**: 80 (porta do nginx)

### 2. Variáveis de Ambiente do Frontend

Configure estas variáveis de ambiente no EasyPanel:

```
VITE_API_URL=https://api.cortefacil.app/api
VITE_APP_NAME=CorteFácil
VITE_APP_VERSION=1.0.0
VITE_FRONTEND_URL=https://cortefacil.app
```

### 3. Configuração de Domínio

- **Domínio**: `cortefacil.app`
- **Port**: 80
- **HTTPS**: Ativado (certificado automático)

### 4. Passos para Corrigir

1. **Edite o serviço frontend no EasyPanel**
2. **Verifique se o Build Context está definido como `frontend/`**
3. **Confirme que o Dockerfile Path é `frontend/Dockerfile`**
4. **Configure as variáveis de ambiente listadas acima**
5. **Salve as configurações**
6. **Faça o redeploy do serviço**

### 5. Verificação

Após o redeploy:
- O frontend deve buildar corretamente usando o Dockerfile
- O nginx deve servir os arquivos estáticos na porta 80
- O serviço deve ficar verde no painel
- O site deve estar acessível em `https://cortefacil.app`

### 6. Estrutura Correta dos Serviços

**Frontend (cortefacil-frontend):**
- Build Context: `frontend/`
- Dockerfile: `frontend/Dockerfile`
- Port: 80
- Domínio: `cortefacil.app`

**Backend (cortefacil-backend):**
- Build Context: `backend/server/`
- Dockerfile: `backend/server/Dockerfile`
- Port: 3001
- Domínio: `api.cortefacil.app`

## Observações Importantes

- O package.json da raiz é apenas para desenvolvimento local
- No EasyPanel, cada serviço deve ter sua própria configuração
- O frontend usa nginx para servir arquivos estáticos
- O backend usa Node.js na porta 3001
- Não use o comando `concurrently` no ambiente de produção