#!/usr/bin/env node

const https = require('https');
const http = require('http');

// Configurações
const BACKEND_URL = 'https://cortefacil.app';
const API_ENDPOINTS = [
    '/api/health',
    '/api/',
    '/api/auth/check'
];

console.log('🔍 VERIFICAÇÃO PÓS-CORREÇÃO EASYPANEL');
console.log('=====================================\n');

// Função para fazer requisições HTTPS
function makeRequest(url, timeout = 10000) {
    return new Promise((resolve) => {
        const startTime = Date.now();
        
        const req = https.get(url, { timeout }, (res) => {
            let data = '';
            
            res.on('data', (chunk) => {
                data += chunk;
            });
            
            res.on('end', () => {
                const responseTime = Date.now() - startTime;
                resolve({
                    success: true,
                    statusCode: res.statusCode,
                    headers: res.headers,
                    data: data,
                    responseTime: responseTime
                });
            });
        });
        
        req.on('error', (error) => {
            const responseTime = Date.now() - startTime;
            resolve({
                success: false,
                error: error.message,
                responseTime: responseTime
            });
        });
        
        req.on('timeout', () => {
            req.destroy();
            const responseTime = Date.now() - startTime;
            resolve({
                success: false,
                error: 'Timeout',
                responseTime: responseTime
            });
        });
    });
}

// Função para verificar se é resposta do backend ou Vercel
function analyzeResponse(response, endpoint) {
    if (!response.success) {
        return {
            status: '❌ ERRO',
            details: `Erro: ${response.error}`,
            isBackend: false,
            isVercel: false
        };
    }

    const { statusCode, headers, data } = response;
    const serverHeader = headers.server || '';
    const contentType = headers['content-type'] || '';
    
    // Verificar se é Vercel
    const isVercel = 
        serverHeader.includes('Vercel') ||
        data.includes('Vercel') ||
        data.includes('404') && data.includes('This page could not be found') ||
        headers['x-vercel-id'] ||
        data.includes('__NEXT_DATA__');
    
    // Verificar se é backend Node.js
    const isBackend = 
        contentType.includes('application/json') ||
        data.includes('"status"') ||
        data.includes('"message"') ||
        (statusCode === 200 && !isVercel);
    
    let status, details;
    
    if (isVercel) {
        status = '❌ VERCEL';
        details = 'Ainda redirecionando para Vercel - Proxy não configurado';
    } else if (isBackend) {
        status = '✅ BACKEND';
        details = `Backend respondendo (${statusCode}) - Proxy funcionando!`;
    } else if (statusCode === 404) {
        status = '⚠️  404';
        details = 'Endpoint não encontrado - Backend funcionando mas rota inexistente';
    } else {
        status = '❓ INDEFINIDO';
        details = `Status ${statusCode} - Verificar manualmente`;
    }
    
    return {
        status,
        details,
        isBackend,
        isVercel,
        statusCode,
        responseTime: response.responseTime
    };
}



// Função para testar funcionalidades específicas
async function testSpecificFeatures(results) {
    console.log('\n🔧 TESTES ESPECÍFICOS');
    console.log('=====================');
    
    // Teste 1: Health check
    console.log('1️⃣ Testando health check...');
    const healthResponse = await makeRequest(BACKEND_URL + '/api/health');
    if (healthResponse.success && healthResponse.data.includes('"status"')) {
        console.log('   ✅ Health check funcionando');
    } else {
        console.log('   ❌ Health check com problema');
    }
    
    // Teste 2: CORS headers
    console.log('\n2️⃣ Verificando CORS...');
    const corsHeaders = healthResponse.headers || {};
    if (corsHeaders['access-control-allow-origin']) {
        console.log('   ✅ Headers CORS presentes');
    } else {
        console.log('   ⚠️  Headers CORS podem estar ausentes');
    }
    
    // Teste 3: Response time
    console.log('\n3️⃣ Verificando performance...');
    if (results && results.length > 0) {
        const avgResponseTime = results.reduce((acc, result) => {
            return acc + (result.responseTime || 0);
        }, 0) / results.length;
        
        if (avgResponseTime < 2000) {
            console.log(`   ✅ Tempo de resposta bom: ${Math.round(avgResponseTime)}ms`);
        } else {
            console.log(`   ⚠️  Tempo de resposta alto: ${Math.round(avgResponseTime)}ms`);
        }
    } else {
        console.log('   ⚠️  Não foi possível calcular performance - sem dados válidos');
    }
}

// Função principal de verificação
async function verifyEasyPanelFix() {
    console.log('🧪 Testando endpoints da API...\n');
    
    const results = [];
    
    for (const endpoint of API_ENDPOINTS) {
        const url = BACKEND_URL + endpoint;
        console.log(`📡 Testando: ${endpoint}`);
        
        const response = await makeRequest(url);
        const analysis = analyzeResponse(response, endpoint);
        
        console.log(`   ${analysis.status} ${analysis.details}`);
        if (analysis.responseTime) {
            console.log(`   ⏱️  Tempo: ${analysis.responseTime}ms`);
        }
        console.log('');
        
        results.push({
            endpoint,
            ...analysis
        });
    }
    
    // Análise geral
    console.log('📊 ANÁLISE GERAL');
    console.log('================');
    
    const backendWorking = results.some(r => r.isBackend);
    const vercelRedirects = results.some(r => r.isVercel);
    const allErrors = results.every(r => r.status.includes('❌ ERRO'));
    
    let success = false;
    
    if (backendWorking && !vercelRedirects) {
        console.log('🎉 ✅ SUCESSO! Backend e proxy funcionando corretamente!');
        console.log('   - Backend acessível');
        console.log('   - Proxy /api configurado');
        console.log('   - Não há redirecionamentos para Vercel');
        success = true;
    } else if (backendWorking && vercelRedirects) {
        console.log('⚠️  PARCIAL: Backend funciona mas ainda há problemas de proxy');
        console.log('   - Backend está rodando');
        console.log('   - Alguns endpoints ainda vão para Vercel');
        console.log('   - Verificar configuração de proxy');
    } else if (vercelRedirects) {
        console.log('❌ PROBLEMA: Requests ainda vão para Vercel');
        console.log('   - Proxy /api NÃO configurado');
        console.log('   - Backend pode estar rodando mas inacessível');
    } else if (allErrors) {
        console.log('❌ PROBLEMA: Backend não acessível');
        console.log('   - Container pode estar parado');
        console.log('   - Verificar status no EasyPanel');
    } else {
        console.log('❓ INDEFINIDO: Resultados mistos');
        console.log('   - Verificar manualmente cada endpoint');
    }
    
    return { success, results };
}

// Executar verificação
(async () => {
    try {
        const { success, results } = await verifyEasyPanelFix();
        await testSpecificFeatures(results);
        
        console.log('\n🎯 PRÓXIMOS PASSOS');
        console.log('==================');
        
        if (success) {
            console.log('✅ Sistema funcionando! Pode testar o frontend:');
            console.log('   - Acesse: https://cortefacil.app');
            console.log('   - Teste login e funcionalidades');
            console.log('   - Monitore por alguns minutos');
        } else {
            console.log('❌ Ainda há problemas. Ações necessárias:');
            console.log('   1. Verificar status do backend no EasyPanel');
            console.log('   2. Confirmar configuração de proxy');
            console.log('   3. Verificar logs para erros');
            console.log('   4. Executar este script novamente após correções');
        }
        
        console.log('\n📋 Para mais detalhes, consulte:');
        console.log('   - EASYPANEL_PASSO_A_PASSO.md');
        console.log('   - EASYPANEL_BACKEND_DIAGNOSTICO.md');
        
    } catch (error) {
        console.error('❌ Erro durante verificação:', error.message);
        process.exit(1);
    }
})();