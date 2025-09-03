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