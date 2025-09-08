#!/bin/bash

# 🔧 Script de Correção Automática via SSH - EasyPanel
# Executa correções do backend Node.js automaticamente

set -e  # Parar em caso de erro

echo "🚀 INICIANDO CORREÇÃO AUTOMÁTICA DO BACKEND"
echo "==========================================="
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log colorido
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Variáveis de configuração
BACKEND_IMAGE="node:18-alpine"
BACKEND_PORT="3001"
DB_HOST="31.97.171.104"
DB_USER="u690889028_mayconwender"
DB_PASSWORD="Maycon341753@"
DB_NAME="u690889028_mayconwender"
CORS_ORIGINS="https://cortefacil.app,https://www.cortefacil.app"

echo "🔍 ETAPA 1: DIAGNÓSTICO INICIAL"
echo "=============================="

# Verificar se Docker está rodando
if ! docker --version > /dev/null 2>&1; then
    log_error "Docker não está instalado ou não está rodando"
    exit 1
fi
log_success "Docker está disponível"

# Listar containers atuais
log_info "Containers atuais:"
docker ps -a --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

# Encontrar container do backend
BACKEND_CONTAINER=$(docker ps -a -q -f "name=backend" | head -1)
if [ -z "$BACKEND_CONTAINER" ]; then
    log_warning "Container 'backend' não encontrado, procurando por containers Node.js..."
    BACKEND_CONTAINER=$(docker ps -a -q -f "ancestor=node" | head -1)
fi

if [ -z "$BACKEND_CONTAINER" ]; then
    log_warning "Nenhum container backend encontrado, será criado um novo"
    CREATE_NEW_CONTAINER=true
else
    log_info "Container backend encontrado: $BACKEND_CONTAINER"
    CREATE_NEW_CONTAINER=false
fi

echo ""
echo "🔧 ETAPA 2: CORREÇÃO DO BACKEND"
echo "=============================="

if [ "$CREATE_NEW_CONTAINER" = true ]; then
    log_info "Criando novo container backend..."
    
    # Verificar se existe docker-compose.yml
    if [ -f "docker-compose.yml" ]; then
        log_info "Usando docker-compose para criar backend..."
        docker-compose up -d backend
    else
        log_info "Criando container manualmente..."
        docker run -d \
            --name backend \
            --restart unless-stopped \
            -p $BACKEND_PORT:$BACKEND_PORT \
            -e NODE_ENV=production \
            -e PORT=$BACKEND_PORT \
            -e DB_HOST=$DB_HOST \
            -e DB_USER=$DB_USER \
            -e DB_PASSWORD=$DB_PASSWORD \
            -e DB_NAME=$DB_NAME \
            -e CORS_ORIGINS="$CORS_ORIGINS" \
            $BACKEND_IMAGE \
            node server.js
    fi
    
    # Aguardar container iniciar
    sleep 5
    BACKEND_CONTAINER=$(docker ps -q -f "name=backend" | head -1)
else
    # Verificar status do container existente
    CONTAINER_STATUS=$(docker inspect --format='{{.State.Status}}' $BACKEND_CONTAINER)
    log_info "Status atual do container: $CONTAINER_STATUS"
    
    if [ "$CONTAINER_STATUS" != "running" ]; then
        log_info "Iniciando container parado..."
        docker start $BACKEND_CONTAINER
        sleep 3
    else
        log_info "Reiniciando container para aplicar configurações..."
        docker restart $BACKEND_CONTAINER
        sleep 5
    fi
fi

# Verificar se container está rodando
if docker ps -q -f "id=$BACKEND_CONTAINER" | grep -q .; then
    log_success "Container backend está rodando"
else
    log_error "Falha ao iniciar container backend"
    log_info "Logs do container:"
    docker logs $BACKEND_CONTAINER --tail 20
    exit 1
fi

# Verificar logs do backend
log_info "Verificando logs do backend..."
docker logs $BACKEND_CONTAINER --tail 10

echo ""
echo "🌐 ETAPA 3: CONFIGURAÇÃO DO PROXY"
echo "================================"

# Encontrar container do proxy/nginx
PROXY_CONTAINER=$(docker ps -q -f "name=nginx" | head -1)
if [ -z "$PROXY_CONTAINER" ]; then
    PROXY_CONTAINER=$(docker ps -q -f "name=proxy" | head -1)
fi
if [ -z "$PROXY_CONTAINER" ]; then
    PROXY_CONTAINER=$(docker ps -q -f "name=web" | head -1)
fi

if [ -z "$PROXY_CONTAINER" ]; then
    log_warning "Container de proxy não encontrado"
    log_info "Containers disponíveis:"
    docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}"
    
    log_warning "Configuração de proxy deve ser feita manualmente no EasyPanel"
    log_info "Acesse: Services → Proxy/Load Balancer"
    log_info "Adicione regra: /api/* → http://backend:3001"
else
    log_info "Container de proxy encontrado: $PROXY_CONTAINER"
    
    # Backup da configuração atual
    log_info "Fazendo backup da configuração nginx..."
    docker exec $PROXY_CONTAINER cp /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf.backup 2>/dev/null || true
    
    # Verificar se configuração /api já existe
    if docker exec $PROXY_CONTAINER grep -q "location /api/" /etc/nginx/conf.d/default.conf 2>/dev/null; then
        log_success "Configuração /api já existe no nginx"
    else
        log_info "Adicionando configuração /api ao nginx..."
        
        # Criar configuração temporária
        cat > /tmp/api-proxy.conf << EOF
    location /api/ {
        proxy_pass http://backend:3001/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_set_header X-Forwarded-Host \$host;
        proxy_set_header X-Forwarded-Port \$server_port;
    }
EOF
        
        # Copiar para container
        docker cp /tmp/api-proxy.conf $PROXY_CONTAINER:/tmp/api-proxy.conf
        
        # Adicionar configuração ao nginx
        docker exec $PROXY_CONTAINER sh -c '
            # Encontrar linha do server block e adicionar configuração
            sed -i "/server {/r /tmp/api-proxy.conf" /etc/nginx/conf.d/default.conf
        '
        
        # Testar configuração
        if docker exec $PROXY_CONTAINER nginx -t; then
            log_success "Configuração nginx válida"
            
            # Recarregar nginx
            docker exec $PROXY_CONTAINER nginx -s reload
            log_success "Nginx recarregado com nova configuração"
        else
            log_error "Configuração nginx inválida, restaurando backup"
            docker exec $PROXY_CONTAINER cp /etc/nginx/conf.d/default.conf.backup /etc/nginx/conf.d/default.conf 2>/dev/null || true
            docker exec $PROXY_CONTAINER nginx -s reload
        fi
        
        # Limpar arquivo temporário
        rm -f /tmp/api-proxy.conf
    fi
fi

echo ""
echo "🧪 ETAPA 4: TESTES DE VERIFICAÇÃO"
echo "================================"

# Aguardar serviços estabilizarem
log_info "Aguardando serviços estabilizarem..."
sleep 10

# Teste 1: Backend direto
log_info "Testando backend diretamente na porta 3001..."
if curl -s --max-time 10 http://localhost:3001/health > /dev/null 2>&1; then
    log_success "Backend acessível na porta 3001"
else
    log_warning "Backend não acessível na porta 3001"
    
    # Tentar via nome do container
    if docker exec $PROXY_CONTAINER curl -s --max-time 5 http://backend:3001/health > /dev/null 2>&1; then
        log_success "Backend acessível via nome do container"
    else
        log_error "Backend não acessível via nome do container"
    fi
fi

# Teste 2: Proxy /api
log_info "Testando proxy /api..."
if curl -s --max-time 10 http://localhost/api/health > /dev/null 2>&1; then
    log_success "Proxy /api funcionando"
else
    log_warning "Proxy /api não funcionando"
fi

# Teste 3: Conectividade de rede
log_info "Testando conectividade entre containers..."
if [ ! -z "$PROXY_CONTAINER" ]; then
    if docker exec $PROXY_CONTAINER ping -c 1 backend > /dev/null 2>&1; then
        log_success "Conectividade de rede OK"
    else
        log_warning "Problema de conectividade entre containers"
    fi
fi

echo ""
echo "📊 ETAPA 5: RELATÓRIO FINAL"
echo "==========================="

# Status dos containers
log_info "Status atual dos containers:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "(backend|nginx|proxy|web)"

# Verificar recursos
log_info "Uso de recursos:"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" | head -5

# URLs para teste
echo ""
log_info "URLs para teste manual:"
echo "  - Backend direto: http://localhost:3001/health"
echo "  - Via proxy: http://localhost/api/health"
echo "  - Produção: https://cortefacil.app/api/health"

echo ""
echo "🎉 CORREÇÃO CONCLUÍDA!"
echo "====================="
log_success "Script executado com sucesso"
log_info "Verifique os testes acima e monitore os logs por alguns minutos"
log_info "Para logs em tempo real: docker logs -f $BACKEND_CONTAINER"

echo ""
log_info "Próximos passos:"
echo "  1. Testar URLs manualmente"
echo "  2. Verificar frontend em https://cortefacil.app"
echo "  3. Monitorar logs por 5-10 minutos"
echo "  4. Executar: node verify-easypanel-fix.js (no local)"

echo ""
log_success "✨ Backend corrigido via SSH! ✨"