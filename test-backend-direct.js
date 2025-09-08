#!/usr/bin/env node

/**
 * 🔍 TESTE DIRETO DO BACKEND - EasyPanel
 * 
 * Este script testa se o backend está rodando diretamente,
 * sem passar pelo proxy /api/*, para identificar se o problema
 * é apenas de configuração de roteamento.
 */

const https = require('https');
const http = require('http');

console.log('🔍 TESTE DIRETO DO BACKEND EASYPANEL');
console.log('====================================\n');

// URLs para testar diretamente o backend
const backendUrls = [
    // Possíveis URLs diretas do backend no EasyPanel
    'https://cortefacil-backend.7ebsu.easypanel.host',
    'https://backend.cortefacil.7ebsu.easypanel.host',
    'https://cortefacil-backend.7ebsu.easypanel.host/health',
    'https://backend.cortefacil.7ebsu.easypanel.host/health',
    
    // URLs internas (podem não funcionar externamente)
    'http://backend:3001/health',
    'http://cortefacil-backend:3001/health'
];

/**
 * Testa uma URL específica
 */
function testUrl(url) {
    return new Promise((resolve) => {
        const startTime = Date.now();
        const protocol = url.startsWith('https') ? https : http;
        
        console.log(`📡 Testando: ${url}`);
        
        const req = protocol.get(url, (res) => {
            const responseTime = Date.now() - startTime;
            let data = '';
            
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                const result = {
                    url,
                    status: res.statusCode,
                    responseTime,
                    headers: res.headers,
                    body: data.substring(0, 500),
                    isBackend: data.includes('status') || data.includes('health') || data.includes('api'),
                    isVercel: data.includes('vercel') || data.includes('Vercel') || res.headers['x-vercel-id']
                };
                
                // Análise da resposta
                if (result.status === 200 && result.isBackend) {
                    console.log(`   ✅ BACKEND FUNCIONANDO - Status: ${result.status}`);
                    console.log(`   📄 Resposta: ${data.substring(0, 100)}...`);
                } else if (result.status === 200) {
                    console.log(`   ⚠️  RESPOSTA OK mas pode não ser backend - Status: ${result.status}`);
                } else if (result.status === 404) {
                    console.log(`   ❌ NÃO ENCONTRADO - Status: 404`);
                } else if (result.isVercel) {
                    console.log(`   ❌ VERCEL - Redirecionando para Vercel`);
                } else {
                    console.log(`   ❌ ERRO - Status: ${result.status}`);
                }
                
                console.log(`   ⏱️  Tempo: ${result.responseTime}ms\n`);
                resolve(result);
            });
        });
        
        req.on('error', (error) => {
            console.log(`   ❌ ERRO DE CONEXÃO: ${error.message}`);
            console.log(`   ⏱️  Tempo: ${Date.now() - startTime}ms\n`);
            resolve({
                url,
                status: 'ERROR',
                error: error.message,
                responseTime: Date.now() - startTime
            });
        });
        
        req.setTimeout(10000, () => {
            req.destroy();
            console.log(`   ⏰ TIMEOUT - Sem resposta em 10s\n`);
            resolve({
                url,
                status: 'TIMEOUT',
                error: 'Request timeout',
                responseTime: 10000
            });
        });
    });
}

/**
 * Função principal
 */
async function main() {
    try {
        console.log('🎯 OBJETIVO: Encontrar URL direta do backend no EasyPanel\n');
        
        const results = [];
        
        for (const url of backendUrls) {
            const result = await testUrl(url);
            results.push(result);
            
            // Se encontrou o backend funcionando, para aqui
            if (result.status === 200 && result.isBackend) {
                console.log('🎉 BACKEND ENCONTRADO E FUNCIONANDO!');
                console.log(`📍 URL Direta: ${url}`);
                break;
            }
        }
        
        // Análise dos resultados
        console.log('📊 ANÁLISE DOS RESULTADOS');
        console.log('==========================');
        
        const funcionando = results.filter(r => r.status === 200 && r.isBackend);
        const erros = results.filter(r => r.status === 'ERROR' || r.status === 'TIMEOUT');
        const notFound = results.filter(r => r.status === 404);
        
        if (funcionando.length > 0) {
            console.log('\n✅ BACKEND FUNCIONANDO:');
            funcionando.forEach(r => {
                console.log(`   ${r.url} - Status: ${r.status}`);
            });
            
            console.log('\n🔧 SOLUÇÃO:');
            console.log('   O backend está funcionando!');
            console.log('   Problema é apenas de configuração de proxy.');
            console.log('   Configure: /api/* → http://backend:3001');
            
        } else if (notFound.length === results.length) {
            console.log('\n❌ BACKEND NÃO ENCONTRADO:');
            console.log('   Todas as URLs retornaram 404.');
            console.log('   O backend pode estar parado ou com nome diferente.');
            
            console.log('\n🔧 SOLUÇÕES:');
            console.log('   1. Verificar se o backend está rodando no EasyPanel');
            console.log('   2. Verificar o nome correto do serviço backend');
            console.log('   3. Iniciar o backend se estiver parado');
            
        } else {
            console.log('\n⚠️  RESULTADOS MISTOS:');
            console.log('   Algumas URLs funcionaram, outras não.');
            console.log('   Verifique os logs acima para mais detalhes.');
        }
        
        console.log('\n📋 PRÓXIMOS PASSOS:');
        console.log('   1. Acesse: https://easypanel.io');
        console.log('   2. Vá em: Services → Backend');
        console.log('   3. Verifique se está "Running"');
        console.log('   4. Configure proxy: /api/* → http://backend:3001');
        console.log('   5. Teste: node verify-easypanel-fix.js');
        
        console.log('\n📚 GUIAS:');
        console.log('   - EASYPANEL_PROXY_CONFIG.md (configuração de proxy)');
        console.log('   - EASYPANEL_CORRECAO_MANUAL.md (passo-a-passo)');
        
    } catch (error) {
        console.error('\n❌ ERRO GERAL:', error.message);
        process.exit(1);
    }
}

// Executar
if (require.main === module) {
    main();
}

module.exports = { testUrl };