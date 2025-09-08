# Correção de Roteamento no EasyPanel

## Problema Identificado
O backend está deployado no EasyPanel mas não está respondendo corretamente às rotas da API em `/api/*`. O domínio `www.cortefacil.app` funciona para o frontend, mas as rotas da API retornam:
- 404 para `/api/`
- 405 para `/api/register` e `/api/login`
- HTML em vez de JSON

## Configuração Atual
- **Domínio funcionando**: www.cortefacil.app (frontend)
- **Backend**: Precisa responder em www.cortefacil.app/api/*
- **Banco de dados**: MySQL no EasyPanel (credenciais na imagem)

## Passos para Correção

### 1. Configurar Roteamento no EasyPanel

#### Opção A: Path-based Routing (Recomendado)
1. Acesse o painel do EasyPanel
2. Configure o serviço backend para responder em `/api/*`
3. Configure o serviço frontend para responder em `/*` (exceto `/api/*`)

#### Configuração de Proxy Reverso:
```nginx
# Frontend (React)
location / {
    try_files $uri $uri/ /index.html;
}

# Backend (Node.js/Express)
location /api/ {
    proxy_pass http://backend-service:3000/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

### 2. Verificar Configuração do Backend

O backend deve estar configurado para:
- Escutar na porta 3000
- Responder às rotas sem prefixo `/api` (o proxy adiciona)
- Ter CORS configurado para `www.cortefacil.app`

### 3. Variáveis de Ambiente do Backend

Verificar se estão configuradas:
```env
PORT=3000
DB_HOST=cortefacil_cortefacil
DB_PORT=3306
DB_USER=mysql
DB_PASSWORD=Maycon34175@
DB_NAME=u690889028_mayconwender
JWT_SECRET=seu_jwt_secret_aqui
CORS_ORIGIN=https://www.cortefacil.app
```

### 4. Testar Configuração

Após as correções, testar:
- `https://www.cortefacil.app/api/` (deve retornar JSON)
- `https://www.cortefacil.app/api/health` (deve retornar status)
- `https://www.cortefacil.app/api/register` (POST deve aceitar)
- `https://www.cortefacil.app/api/login` (POST deve aceitar)

## Alternativa: Subdomínio Separado

Se path-based routing não funcionar:
1. Configurar `api.cortefacil.app` para o backend
2. Atualizar DNS para apontar para o EasyPanel
3. Atualizar frontend para usar `https://api.cortefacil.app`

## Próximos Passos
1. Implementar correções no EasyPanel
2. Testar endpoints da API
3. Verificar logs do backend
4. Ajustar CORS se necessário