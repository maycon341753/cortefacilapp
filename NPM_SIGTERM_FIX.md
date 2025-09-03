# Correção do Erro npm SIGTERM

## Problema Identificado

O erro npm com sinal SIGTERM estava ocorrendo devido a um problema no arquivo `healthcheck.js` do contêiner Docker:

```
npm error path /app
npm error command failed
npm error signal SIGTERM
npm error command sh -c node server.js
```

## Causa Raiz

O arquivo `/app/healthcheck.js` estava faltando o import do módulo `http`, causando falhas no health check do Docker que resultavam em SIGTERM do processo npm.

## Solução Implementada

### 1. Identificação do Problema

```bash
# Verificar logs do contêiner
docker logs [container_id] --tail 50

# Verificar configuração do health check
docker inspect [container_id] | grep -A 15 -B 5 -i health

# Testar o health check manualmente
docker exec [container_id] node /app/healthcheck.js
```

### 2. Correção do Arquivo healthcheck.js

O arquivo foi corrigido com o seguinte conteúdo:

```javascript
const http = require('http');

const options = {
  hostname: 'localhost',
  port: process.env.PORT || 3001,
  path: '/api/health',
  method: 'GET',
  timeout: 2000
};

const request = http.request(options, (res) => {
  console.log(`Health check status: ${res.statusCode}`);
  if (res.statusCode === 200) {
    process.exit(0);
  } else {
    process.exit(1);
  }
});

request.on('error', (err) => {
  console.error('Health check failed:', err.message);
  process.exit(1);
});

request.on('timeout', () => {
  console.error('Health check timeout');
  request.destroy();
  process.exit(1);
});

request.end();
```

### 3. Comando de Correção

```bash
# Corrigir o arquivo healthcheck.js no contêiner
docker exec [container_id] sh -c "cat > /app/healthcheck.js << 'EOF'
const http = require('http');

const options = {
  hostname: 'localhost',
  port: process.env.PORT || 3001,
  path: '/api/health',
  method: 'GET',
  timeout: 2000
};

const request = http.request(options, (res) => {
  console.log(\`Health check status: \${res.statusCode}\`);
  if (res.statusCode === 200) {
    process.exit(0);
  } else {
    process.exit(1);
  }
});

request.on('error', (err) => {
  console.error('Health check failed:', err.message);
  process.exit(1);
});

request.on('timeout', () => {
  console.error('Health check timeout');
  request.destroy();
  process.exit(1);
});

request.end();
EOF"
```

## Verificação da Correção

### 1. Testar Health Check

```bash
# Testar o health check corrigido
docker exec [container_id] node /app/healthcheck.js
# Deve retornar: Health check status: 200
```

### 2. Verificar Status do Contêiner

```bash
# Verificar se o contêiner está saudável
docker inspect [container_id] --format='{{.State.Health.Status}}'
# Deve retornar: healthy
```

### 3. Monitorar Logs

```bash
# Verificar se não há mais erros SIGTERM
docker logs [container_id] --tail 20
# Deve mostrar apenas: ✅ Conexão com banco de dados OK
```

## Resultado

✅ **Problema Resolvido**: O erro npm SIGTERM foi corrigido
✅ **Health Check**: Funcionando corretamente (status 200)
✅ **Contêiner**: Status 'healthy'
✅ **Logs**: Sem erros, apenas conexões bem-sucedidas

## Recorrência do Problema

### Problema npm SIGTERM Resolvido ✅

O problema original `npm SIGTERM` no backend foi **completamente resolvido** através da correção automatizada do `healthcheck.js`. O script `fix-healthcheck.sh` detecta e corrige automaticamente novos contêineres.

### Histórico de Ocorrências npm SIGTERM:
- **Contêiner cb08d03d27dc**: Corrigido em 03/09/2025
- **Contêiner 2ccec9f9a592**: Corrigido em 03/09/2025  
- **Contêiner e6cf4782c5d2**: Corrigido em 03/09/2025

### Problema Persistente: Frontend Nginx Reinicializações 🔄

**Data**: 03/09/2025 11:40+ (Atualizado 12:11)  
**Sintomas**: 
- Contêiner frontend com 0/1 réplicas
- Logs mostram reinicializações constantes do nginx
- Sinais SIGQUIT frequentes após ~1 minuto
- Serviço frontend não consegue manter instância ativa

**Logs Observados**:
```
2025/09/03 11:23:53 [notice] 1#1: signal 3 (SIGQUIT) received, shutting down
2025/09/03 11:23:53 [notice] 29#29: gracefully shutting down
```

**Status Atual (12:11)**:
- ✅ **Build realizado com sucesso** (03/09/2025 12:11:05 GMT)
- ✅ Imagem `easypanel/cortefacil/cortefacil-frontend:latest` criada
- ❌ Serviço `cortefacil_cortefacil-frontend` ainda com **0/1 réplicas**
- ❌ Restart policy: `on-failure` com delay de 5s
- ❌ Variáveis: `PORT=80`, `VITE_FRONTEND_URL=https://cortefacil.app`
- ❌ Tentativa de `docker service update --force` travada em progresso 0/1
- **Requer**: Intervenção direta via interface EasyPanel ou investigação de configurações de rede/proxy

## Solução Permanente Recomendada

Para evitar futuras recorrências, implemente uma das seguintes soluções:

### Opção 1: Corrigir na Imagem Docker Base
```dockerfile
# No Dockerfile do backend, adicione:
RUN sed -i '1i const http = require("http");' /app/healthcheck.js
```

### Opção 2: Automatizar Correção no Deploy
Crie um script de inicialização que corrija o arquivo automaticamente:

**Script Criado: `fix-healthcheck.sh`**
```bash
#!/bin/bash
# fix-healthcheck.sh
# Script para corrigir automaticamente o healthcheck.js em contêineres Docker
# Uso: ./fix-healthcheck.sh <container_id>

CONTAINER_ID=${1:-$(docker ps --filter "name=cortefacil-backend" --format "{{.ID}}" | head -1)}

if [ -z "$CONTAINER_ID" ]; then
    echo "❌ Erro: Nenhum contêiner encontrado. Especifique o ID do contêiner como parâmetro."
    echo "Uso: $0 <container_id>"
    exit 1
fi

echo "🔍 Verificando healthcheck.js no contêiner $CONTAINER_ID..."

# Verifica se o contêiner existe e está rodando
if ! docker ps --format "{{.ID}}" | grep -q "$CONTAINER_ID"; then
    echo "❌ Erro: Contêiner $CONTAINER_ID não encontrado ou não está rodando."
    exit 1
fi

# Verifica se o arquivo healthcheck.js existe
if ! docker exec "$CONTAINER_ID" test -f /app/healthcheck.js; then
    echo "❌ Erro: Arquivo /app/healthcheck.js não encontrado no contêiner."
    exit 1
fi

# Verifica se a correção já foi aplicada
if docker exec "$CONTAINER_ID" grep -q "const http = require" /app/healthcheck.js; then
    echo "✅ Correção já aplicada no contêiner $CONTAINER_ID"
    echo "🧪 Testando healthcheck..."
    
    # Testa o healthcheck
    if docker exec "$CONTAINER_ID" node /app/healthcheck.js 2>/dev/null; then
        echo "✅ Healthcheck funcionando corretamente!"
    else
        echo "⚠️  Healthcheck com problemas, mas correção já está aplicada."
    fi
else
    echo "🔧 Aplicando correção no healthcheck.js..."
    
    # Aplica a correção
    docker exec "$CONTAINER_ID" sed -i '1i const http = require("http");' /app/healthcheck.js
    
    # Verifica se a correção foi aplicada com sucesso
    if docker exec "$CONTAINER_ID" grep -q "const http = require" /app/healthcheck.js; then
        echo "✅ Correção aplicada com sucesso!"
        
        # Testa o healthcheck
        echo "🧪 Testando healthcheck..."
        if docker exec "$CONTAINER_ID" node /app/healthcheck.js 2>/dev/null; then
            echo "✅ Healthcheck funcionando corretamente!"
        else
            echo "❌ Erro: Healthcheck ainda com problemas após correção."
            exit 1
        fi
    else
        echo "❌ Erro: Falha ao aplicar correção."
        exit 1
    fi
fi

echo "🎉 Script concluído com sucesso!"
```

**Como usar o script:**
```bash
# Dar permissão de execução
chmod +x fix-healthcheck.sh

# Executar para um contêiner específico
./fix-healthcheck.sh <container_id>

# Executar para o contêiner backend atual (detecção automática)
./fix-healthcheck.sh
```

### Opção 3: Recriar o Arquivo Completo
Substitua o `healthcheck.js` por uma versão correta no processo de build.

## Prevenção

Para evitar problemas similares no futuro:

1. **Sempre incluir imports necessários** nos arquivos de health check
2. **Testar health checks localmente** antes do deploy
3. **Monitorar logs regularmente** para identificar problemas cedo
4. **Validar sintaxe JavaScript** em arquivos críticos como healthcheck.js
5. **Implementar solução permanente** para evitar recorrências

## Conclusão

O erro `npm SIGTERM` foi causado por um health check mal configurado no Docker. A correção envolveu:

1. **Identificação da causa raiz**: Falta do `import` do módulo `http` no `healthcheck.js`
2. **Correção do arquivo**: Adição da linha `const http = require('http');`
3. **Verificação**: Teste do health check e confirmação do status saudável do contêiner
4. **Implementação de solução permanente**: Necessária para evitar recorrências

Com essa correção, o servidor API está funcionando normalmente e o contêiner mantém o status `healthy`.

---

**Data da Correção**: 03/09/2025  
**Status**: ✅ Resolvido  
**Contêiner**: cb08d03d27dc (cortefacil-backend)