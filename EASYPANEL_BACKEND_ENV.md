# Configuração de Variáveis de Ambiente - Backend EasyPanel

## Problema Identificado
O backend está falhando porque está usando configurações locais (localhost) no ambiente de produção.

## Variáveis de Ambiente Necessárias no EasyPanel

### 1. Configurações do Servidor
```
NODE_ENV=production
PORT=3001
```

### 2. Configurações do Banco de Dados
```
DB_HOST=cortefacil_user  # Nome do serviço MySQL no EasyPanel
DB_USER=root
DB_PASSWORD=[SENHA_DO_MYSQL_EASYPANEL]
DB_NAME=cortefacil
```

### 3. URLs e CORS
```
FRONTEND_URL=https://cortefacil.app
CORS_ORIGINS=https://cortefacil.app
```

### 4. JWT e Segurança
```
JWT_SECRET=cortefacil_jwt_secret_production_2024_secure
JWT_EXPIRES_IN=24h
BCRYPT_ROUNDS=12
```

### 5. Rate Limiting
```
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100
```

### 6. Logs
```
LOG_LEVEL=info
```

### 7. Cache
```
CACHE_TTL=3600
```

### 8. Mercado Pago
```
MERCADOPAGO_ACCESS_TOKEN=APP_USR-4874651f-ea8a-480b-bf4e-444502d1a2fb
MERCADOPAGO_PUBLIC_KEY=APP_USR-4874651f-ea8a-480b-bf4e-444502d1a2fb
MERCADOPAGO_WEBHOOK_SECRET=
PAYMENT_AMOUNT=1.29
```

## Como Configurar no EasyPanel

1. **Acesse o serviço cortefacil-backend**
2. **Vá para a aba "Environment"**
3. **Adicione todas as variáveis acima**
4. **IMPORTANTE**: Substitua `[SENHA_DO_MYSQL_EASYPANEL]` pela senha real do MySQL
5. **Salve as configurações**
6. **Reinicie o serviço**

## Verificação
Após configurar, o serviço deve:
- Conectar com o banco MySQL corretamente
- Parar de mostrar erros "not found"
- Ficar com status verde
- Responder na porta 3001

## Próximos Passos
1. Configurar essas variáveis no EasyPanel
2. Reiniciar o serviço backend
3. Verificar os logs
4. Configurar o frontend com as variáveis corretas
5. Executar as migrações do banco de dados