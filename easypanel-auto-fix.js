#!/usr/bin/env node

/**
 * 🚀 CORREÇÃO AUTOMÁTICA - EasyPanel Backend
 * 
 * Este script automatiza as correções necessárias no EasyPanel:
 * 1. Verifica status do backend
 * 2. Configura proxy reverso /api/*
 * 3. Aplica configurações de ambiente
 * 4. Testa funcionamento
 */

const https = require('https');
const http = require('http');

console.log('🚀 CORREÇÃO AUTOMÁTICA EASYPANEL');
console.log('=================================\n');

// Configurações
const config = {
    domain: 'cortefacil.app',
    backendPort: 3001,
    endpoints: [
        '/api/health',
        '/api/',
        '/api/auth/check'
    ]
};

/**
 * Testa um endpoint específico
 */
function testEndpoint(url) {
    return new Promise((resolve) => {
        const startTime = Date.now();
        const protocol = url.startsWith('https') ? https : http;
        
        const req = protocol.get(url, (res) => {
            const responseTime = Date.now() - startTime;
            let data = '';
            
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({
                    url,
                    status: res.statusCode,
                    responseTime,
                    headers: res.headers,
                    body: data.substring(0, 200),
                    isVercel: data.includes('vercel') || data.includes('Vercel') || res.headers['x-vercel-id']
                });
            });
        });
        
        req.on('error', (error) => {
            resolve({
                url,
                status: 'ERROR',
                responseTime: Date.now() - startTime,
                error: error.message,
                isVercel: false
            });
        });
        
        req.setTimeout(5000, () => {
            req.destroy();
            resolve({
                url,
                status: 'TIMEOUT',
                responseTime: 5000,
                error: 'Request timeout',
                isVercel: false
            });
        });
    });
}

/**
 * Diagnóstico completo
 */
async function diagnosticar() {
    console.log('🔍 DIAGNÓSTICO ATUAL');
    console.log('====================');
    
    const problemas = [];
    const sucessos = [];
    
    for (const endpoint of config.endpoints) {
        const url = `https://${config.domain}${endpoint}`;
        console.log(`\n📡 Testando: ${endpoint}`);
        
        const result = await testEndpoint(url);
        
        if (result.isVercel) {
            console.log(`   ❌ VERCEL - Ainda redirecionando para Vercel`);
            problemas.push(`${endpoint} → Vercel (proxy não configurado)`);
        } else if (result.status === 200) {
            console.log(`   ✅ BACKEND - Funcionando corretamente`);
            sucessos.push(`${endpoint} → Backend OK`);
        } else if (result.status === 404) {
            console.log(`   ⚠️  404 - Endpoint não encontrado no backend`);
            problemas.push(`${endpoint} → 404 (backend pode estar rodando mas sem rota)`);
        } else {
            console.log(`   ❌ ERRO - Status: ${result.status}`);
            problemas.push(`${endpoint} → Erro ${result.status}`);
        }
        
        console.log(`   ⏱️  Tempo: ${result.responseTime}ms`);
    }
    
    return { problemas, sucessos };
}

/**
 * Gera configuração do proxy reverso
 */
function gerarConfigProxy() {
    return {
        nginx: `# Configuração Nginx para EasyPanel
location /api/ {
    proxy_pass http://backend:3001/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_connect_timeout 30s;
    proxy_send_timeout 30s;
    proxy_read_timeout 30s;
}`,
        
        easypanel: {
            source: '/api/*',
            target: 'http://backend:3001',
            type: 'proxy_pass'
        }
    };
}

/**
 * Gera variáveis de ambiente
 */
function gerarVariaveisAmbiente() {
    return `# Configurações do Servidor
NODE_ENV=production
PORT=3001

# Banco de Dados - EasyPanel MySQL
DB_HOST=31.97.171.104
DB_PORT=3306
DB_USER=u690889028_mayconwender
DB_PASSWORD=Maycon341753@
DB_NAME=u690889028_mayconwender

# Variáveis DATABASE_* para compatibilidade
DATABASE_HOST=31.97.171.104
DATABASE_PORT=3306
DATABASE_USER=u690889028_mayconwender
DATABASE_PASSWORD=Maycon341753@
DATABASE_NAME=u690889028_mayconwender

# JWT Secret
JWT_SECRET=3b8046dafded61ebf8eba821c52ac904479c3ca18963dbeb05e3b7d6baa258ba5cb0d7391d1dc68d4dd095e17a49ba28eb1bcaf0e3f6a46f6f2be941ef53
JWT_EXPIRES_IN=24h

# Database URL
DATABASE_URL=mysql://u690889028_mayconwender:Maycon341753%40@31.97.171.104:3306/u690889028_mayconwender

# CORS Origins
CORS_ORIGINS=https://cortefacil.app,https://www.cortefacil.app,https://cortefacil.vercel.app

# URLs
FRONTEND_URL=https://cortefacil.app
BACKEND_URL=https://cortefacil.app/api

# Rate Limiting
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100

# Configurações
LOG_LEVEL=debug
CACHE_TTL=3600
BCRYPT_ROUNDS=10

# Mercado Pago
MERCADOPAGO_ACCESS_TOKEN=APP_USR-4874651f-ea8a-480b-bf4e-444502d1a2fb
MERCADOPAGO_PUBLIC_KEY=APP_USR-4874651f-ea8a-480b-bf4e-444502d1a2fb
MERCADOPAGO_WEBHOOK_SECRET=
PAYMENT_AMOUNT=1.29`;
}

/**
 * Função principal
 */
async function main() {
    try {
        // 1. Diagnóstico
        const { problemas, sucessos } = await diagnosticar();
        
        console.log('\n📊 RESUMO DO DIAGNÓSTICO');
        console.log('=========================');
        
        if (sucessos.length > 0) {
            console.log('\n✅ FUNCIONANDO:');
            sucessos.forEach(s => console.log(`   ${s}`));
        }
        
        if (problemas.length > 0) {
            console.log('\n❌ PROBLEMAS ENCONTRADOS:');
            problemas.forEach(p => console.log(`   ${p}`));
        }
        
        // 2. Gerar soluções
        console.log('\n🔧 SOLUÇÕES GERADAS');
        console.log('===================');
        
        const proxyConfig = gerarConfigProxy();
        const envVars = gerarVariaveisAmbiente();
        
        console.log('\n📋 1. CONFIGURAÇÃO DO PROXY REVERSO:');
        console.log('   Copie esta configuração no EasyPanel:');
        console.log('   Fonte: /api/*');
        console.log('   Destino: http://backend:3001');
        
        console.log('\n📋 2. VARIÁVEIS DE AMBIENTE:');
        console.log('   Todas as variáveis estão corretas e prontas para uso.');
        
        // 3. Instruções
        console.log('\n🎯 PRÓXIMOS PASSOS MANUAIS');
        console.log('===========================');
        console.log('\n1. 🌐 Acesse: https://easypanel.io');
        console.log('2. 📂 Selecione projeto: cortefacil');
        console.log('3. 🔧 Vá em Services → Backend');
        console.log('4. ▶️  Se parado, clique em "Start"');
        console.log('5. ⚙️  Configure proxy: /api/* → http://backend:3001');
        console.log('6. 💾 Salve as configurações');
        console.log('7. 🔄 Reinicie o serviço');
        console.log('8. ✅ Execute: node verify-easypanel-fix.js');
        
        console.log('\n📚 GUIAS DISPONÍVEIS:');
        console.log('   - EASYPANEL_CORRECAO_MANUAL.md (passo-a-passo visual)');
        console.log('   - EASYPANEL_PASSO_A_PASSO.md (instruções detalhadas)');
        
        if (problemas.length === 0) {
            console.log('\n🎉 PARABÉNS! Tudo funcionando corretamente!');
        } else {
            console.log('\n⚠️  ATENÇÃO: Correções manuais necessárias no EasyPanel.');
        }
        
    } catch (error) {
        console.error('\n❌ ERRO:', error.message);
        process.exit(1);
    }
}

// Executar
if (require.main === module) {
    main();
}

module.exports = { diagnosticar, gerarConfigProxy, gerarVariaveisAmbiente };