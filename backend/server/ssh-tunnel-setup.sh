#!/bin/bash

# Script para configurar túnel SSH para MySQL no EasyPanel
# Este script deve ser executado antes do servidor Node.js iniciar

echo "🔧 Configurando túnel SSH para MySQL..."

# Verificar se as variáveis de ambiente estão definidas
if [ -z "$SSH_HOST" ] || [ -z "$SSH_USER" ] || [ -z "$SSH_PASSWORD" ]; then
    echo "❌ Erro: Variáveis SSH não configuradas"
    echo "Configure: SSH_HOST, SSH_USER, SSH_PASSWORD"
    exit 1
fi

# Instalar sshpass se não existir
which sshpass > /dev/null || apk add --no-cache sshpass

# Configurar túnel SSH em background com senha
echo "🚇 Iniciando túnel SSH com senha..."
sshpass -p "$SSH_PASSWORD" ssh -f -N -L 3306:localhost:3306 -o StrictHostKeyChecking=no $SSH_USER@$SSH_HOST

if [ $? -eq 0 ]; then
    echo "✅ Túnel SSH configurado com sucesso"
    echo "📍 MySQL acessível via localhost:3306"
else
    echo "❌ Erro ao configurar túnel SSH"
    exit 1
fi

# Aguardar um momento para o túnel se estabelecer
sleep 2

echo "🚀 Túnel SSH pronto - iniciando servidor Node.js..."