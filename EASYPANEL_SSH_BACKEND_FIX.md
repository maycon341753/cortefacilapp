# 🔧 Ajustar Backend via SSH - EasyPanel

## 🎯 Objetivo
Corrigir problemas do backend Node.js via SSH no EasyPanel:
- ❌ Container backend não está rodando
- ❌ Proxy /api não configurado
- ❌ Variáveis de ambiente incorretas

---

## 🔑 1. Conectar via SSH

### Obter Credenciais SSH
1. **Acesse EasyPanel**: https://easypanel.io
2. **Vá em**: Project → Settings → SSH Access
3. **Copie as informações**:
   ```
   Host: [seu-host].easypanel.host
   Port: 22
   User: [seu-usuario]
   Password: [sua-senha]
   ```

### Conectar
```bash
# Windows (PowerShell)
ssh usuario@host.easypanel.host

# Ou com porta específica
ssh -p 22 usuario@host.easypanel.host
```

---

## 🐳 2. Verificar Status dos Containers

### Listar Containers
```bash
# Ver todos os containers
docker ps -a

# Ver apenas containers rodando
docker ps

# Filtrar por nome do projeto
docker ps -a | grep cortefacil
```

### Identificar Container Backend
```bash
# Procurar container do backend
docker ps -a | grep backend
docker ps -a | grep node
docker ps -a | grep 3001
```

---

## 🔍 3. Diagnosticar Problemas do Backend

### Verificar Logs do Container
```bash
# Substituir CONTAINER_ID pelo ID real
docker logs CONTAINER_ID

# Logs em tempo real
docker logs -f CONTAINER_ID

# Últimas 50 linhas
docker logs --tail 50 CONTAINER_ID
```

### Verificar Status do Container
```bash
# Inspecionar container
docker inspect CONTAINER_ID

# Ver configurações de rede
docker inspect CONTAINER_ID | grep -A 10 "NetworkSettings"

# Ver variáveis de ambiente
docker inspect CONTAINER_ID | grep -A 20 "Env"
```

---

## 🚀 4. Iniciar/Reiniciar Backend

### Se Container Estiver Parado
```bash
# Iniciar container
docker start CONTAINER_ID

# Verificar se iniciou
docker ps | grep CONTAINER_ID
```

### Se Container Estiver com Erro
```bash
# Parar container
docker stop CONTAINER_ID

# Remover container com problema
docker rm CONTAINER_ID

# Recriar container (usar docker-compose se disponível)
docker-compose up -d backend

# Ou recriar manualmente
docker run -d \
  --name backend \
  -p 3001:3001 \
  -e NODE_ENV=production \
  -e PORT=3001 \
  -e DB_HOST=31.97.171.104 \
  -e DB_USER=u690889028_mayconwender \
  -e DB_PASSWORD=Maycon341753@ \
  -e DB_NAME=u690889028_mayconwender \
  -e CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app \
  [imagem-do-backend]
```

---

## ⚙️ 5. Configurar Variáveis de Ambiente

### Verificar Variáveis Atuais
```bash
# Entrar no container
docker exec -it CONTAINER_ID /bin/bash

# Ou se não tiver bash
docker exec -it CONTAINER_ID /bin/sh

# Dentro do container, verificar variáveis
env | grep -E "NODE_ENV|PORT|DB_|CORS"
```

### Atualizar Variáveis (Método 1 - Restart)
```bash
# Parar container
docker stop CONTAINER_ID

# Iniciar com novas variáveis
docker run -d \
  --name backend-new \
  -p 3001:3001 \
  -e NODE_ENV=production \
  -e PORT=3001 \
  -e DB_HOST=31.97.171.104 \
  -e DB_USER=u690889028_mayconwender \
  -e DB_PASSWORD=Maycon341753@ \
  -e DB_NAME=u690889028_mayconwender \
  -e CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app \
  [imagem-do-backend]
```

### Atualizar Variáveis (Método 2 - Docker Compose)
```bash
# Editar docker-compose.yml
nano docker-compose.yml

# Ou vim
vim docker-compose.yml

# Recriar serviços
docker-compose down
docker-compose up -d
```

---

## 🌐 6. Configurar Proxy /api

### Verificar Configuração Nginx
```bash
# Encontrar container do proxy/nginx
docker ps | grep nginx
docker ps | grep proxy

# Ver configuração atual
docker exec NGINX_CONTAINER_ID cat /etc/nginx/nginx.conf
docker exec NGINX_CONTAINER_ID cat /etc/nginx/conf.d/default.conf
```

### Adicionar Configuração de Proxy
```bash
# Entrar no container nginx
docker exec -it NGINX_CONTAINER_ID /bin/bash

# Editar configuração
nano /etc/nginx/conf.d/default.conf

# Adicionar dentro do bloco server:
location /api/ {
    proxy_pass http://backend:3001/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $host;
    proxy_set_header X-Forwarded-Port $server_port;
}

# Testar configuração
nginx -t

# Recarregar nginx
nginx -s reload

# Ou reiniciar container
docker restart NGINX_CONTAINER_ID
```

---

## 🧪 7. Testar Configurações

### Teste Interno (Dentro do Servidor)
```bash
# Testar backend diretamente
curl http://localhost:3001/health
curl http://backend:3001/health

# Testar proxy
curl http://localhost/api/health
curl https://cortefacil.app/api/health
```

### Verificar Conectividade de Rede
```bash
# Testar conectividade entre containers
docker exec NGINX_CONTAINER_ID ping backend
docker exec NGINX_CONTAINER_ID telnet backend 3001

# Ver rede docker
docker network ls
docker network inspect [network-name]
```

---

## 🔧 8. Comandos de Troubleshooting

### Verificar Recursos do Sistema
```bash
# Uso de CPU e memória
docker stats

# Espaço em disco
df -h

# Processos rodando
top
htop
```

### Verificar Logs do Sistema
```bash
# Logs do Docker
journalctl -u docker

# Logs do sistema
tail -f /var/log/syslog
tail -f /var/log/messages
```

### Limpar Recursos Docker
```bash
# Remover containers parados
docker container prune

# Remover imagens não utilizadas
docker image prune

# Limpeza geral (CUIDADO!)
docker system prune
```

---

## 📋 9. Checklist de Verificação

### Backend Container
- [ ] Container está "Running"
- [ ] Porta 3001 está exposta
- [ ] Logs não mostram erros críticos
- [ ] Variáveis de ambiente corretas
- [ ] Conectividade com banco de dados

### Proxy Configuration
- [ ] Nginx/Proxy container rodando
- [ ] Configuração `/api/` adicionada
- [ ] Nginx configuração válida (`nginx -t`)
- [ ] Proxy recarregado/reiniciado
- [ ] Teste `curl` funciona

### Network & Connectivity
- [ ] Containers na mesma rede
- [ ] Backend acessível via nome do container
- [ ] Portas não conflitantes
- [ ] Firewall não bloqueando

---

## 🚨 10. Problemas Comuns e Soluções

### Container não inicia
**Sintomas**: `docker ps` não mostra o container

**Soluções**:
```bash
# Ver erro específico
docker logs CONTAINER_ID

# Verificar se porta está em uso
netstat -tulpn | grep 3001

# Matar processo na porta
fuser -k 3001/tcp
```

### Erro de variáveis de ambiente
**Sintomas**: Logs mostram "DB connection failed"

**Soluções**:
```bash
# Verificar variáveis
docker exec CONTAINER_ID env | grep DB_

# Recriar com variáveis corretas
docker stop CONTAINER_ID
docker rm CONTAINER_ID
# Usar comando docker run com -e para cada variável
```

### Proxy não funciona
**Sintomas**: `/api/health` retorna 404 ou HTML

**Soluções**:
```bash
# Verificar se backend está acessível
docker exec NGINX_CONTAINER_ID curl http://backend:3001/health

# Verificar configuração nginx
docker exec NGINX_CONTAINER_ID nginx -t

# Recarregar configuração
docker exec NGINX_CONTAINER_ID nginx -s reload
```

### Erro de rede entre containers
**Sintomas**: "Connection refused" entre containers

**Soluções**:
```bash
# Verificar rede
docker network ls
docker network inspect [network-name]

# Recriar containers na mesma rede
docker-compose down
docker-compose up -d
```

---

## 🎯 11. Script de Automação

### Criar script de verificação
```bash
# Criar arquivo
nano check-backend.sh

# Conteúdo do script:
#!/bin/bash
echo "🔍 Verificando status do backend..."

# Verificar container
BACKEND_ID=$(docker ps -q -f "name=backend")
if [ -z "$BACKEND_ID" ]; then
    echo "❌ Container backend não encontrado ou parado"
    exit 1
fi

echo "✅ Container backend rodando: $BACKEND_ID"

# Testar conectividade
echo "🧪 Testando conectividade..."
curl -s http://localhost:3001/health > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ Backend acessível na porta 3001"
else
    echo "❌ Backend não acessível na porta 3001"
fi

# Testar proxy
curl -s http://localhost/api/health > /dev/null
if [ $? -eq 0 ]; then
    echo "✅ Proxy /api funcionando"
else
    echo "❌ Proxy /api não funcionando"
fi

# Tornar executável
chmod +x check-backend.sh

# Executar
./check-backend.sh
```

---

## ⏱️ Tempo Estimado
- **Diagnóstico**: 5-10 minutos
- **Correção Backend**: 5-15 minutos
- **Configuração Proxy**: 10-20 minutos
- **Testes**: 5 minutos

**Total**: 25-50 minutos (dependendo da complexidade dos problemas)

---

## 📞 Comandos Rápidos de Emergência

```bash
# Reiniciar tudo rapidamente
docker-compose restart

# Ver logs de todos os containers
docker-compose logs

# Recriar tudo do zero
docker-compose down
docker-compose up -d

# Verificar status geral
docker ps
docker-compose ps
```

---

**🎯 Objetivo Final**: Backend rodando na porta 3001 e proxy /api configurado corretamente, permitindo que o frontend acesse a API sem problemas.