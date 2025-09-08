const https = require('https');
const http = require('http');

// Configurações do EasyPanel
const config = {
    domain: 'cortefacil.app',
    backendPort: 3001,
    apiPath: '/api'
};

// Função para testar se backend está rodando
function testBackend(url, timeout = 5000) {
    return new Promise((resolve) => {
        const isHttps = url.startsWith('https');
        const client = isHttps ? https : http;
        
        const req = client.get(url, { timeout }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({
                    success: true,
                    status: res.statusCode,
                    server: res.headers.server || 'Unknown',
                    isBackend: !data.includes('<!DOCTYPE html>') && res.headers.server !== 'Vercel'
                });
            });
        });
        
        req.on('error', () => resolve({ success: false, error: 'Connection failed' }));
        req.on('timeout', () => {
            req.destroy();
            resolve({ success: false, error: 'Timeout' });
        });
    });
}

// Função principal de diagnóstico e correção
async function fixEasyPanel() {
    console.log('🔧 SCRIPT DE CORREÇÃO EASYPANEL');
    console.log('=' .repeat(50));
    
    // 1. Testar backend direto na porta 3001
    console.log('\n1️⃣ Testando backend na porta 3001...');
    const backendDirect = await testBackend(`https://${config.domain}:${config.backendPort}/health`);
    
    if (backendDirect.success && backendDirect.isBackend) {
        console.log('✅ Backend está rodando na porta 3001!');
    } else {
        console.log('❌ Backend NÃO está acessível na porta 3001');
        console.log('\n🚨 AÇÃO NECESSÁRIA NO EASYPANEL:');
        console.log('1. Acesse: https://easypanel.io');
        console.log('2. Vá em Services → Backend');
        console.log('3. Verifique se o container está "Running"');
        console.log('4. Se estiver "Stopped", clique em "Start"');
        console.log('5. Verifique os logs para erros');
        console.log('\n📋 Configurações necessárias:');
        console.log('   - Port: 3001');
        console.log('   - Environment: NODE_ENV=production');
        console.log('   - Health Check: /health');
    }
    
    // 2. Testar proxy /api
    console.log('\n2️⃣ Testando proxy /api...');
    const apiProxy = await testBackend(`https://${config.domain}/api/health`);
    
    if (apiProxy.success && apiProxy.isBackend) {
        console.log('✅ Proxy /api está funcionando!');
    } else {
        console.log('❌ Proxy /api NÃO está configurado');
        if (apiProxy.server === 'Vercel') {
            console.log('⚠️  Requests /api estão indo para Vercel!');
        }
        
        console.log('\n🚨 CONFIGURAÇÃO DE PROXY NECESSÁRIA:');
        console.log('\n📋 Opção 1 - Proxy Rules no EasyPanel:');
        console.log('1. Acesse: Services → Proxy/Load Balancer');
        console.log('2. Adicione nova regra:');
        console.log('   Path: /api/*');
        console.log('   Target: http://backend-service:3001');
        console.log('   Strip Path: false');
        
        console.log('\n📋 Opção 2 - Configuração Nginx:');
        console.log('Adicione no arquivo de configuração:');
        console.log('```nginx');
        console.log('location /api/ {');
        console.log('    proxy_pass http://backend-service:3001/;');
        console.log('    proxy_set_header Host $host;');
        console.log('    proxy_set_header X-Real-IP $remote_addr;');
        console.log('    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;');
        console.log('    proxy_set_header X-Forwarded-Proto $scheme;');
        console.log('}');
        console.log('```');
        
        console.log('\n📋 Opção 3 - Subdomínio (Alternativa):');
        console.log('1. Criar subdomínio: api.cortefacil.app');
        console.log('2. Apontar para o backend service');
        console.log('3. Atualizar frontend para usar: https://api.cortefacil.app');
    }
    
    // 3. Resumo das ações
    console.log('\n📊 RESUMO DAS AÇÕES NECESSÁRIAS:');
    console.log('=' .repeat(50));
    
    if (!backendDirect.success || !backendDirect.isBackend) {
        console.log('🚨 URGENTE: Iniciar container backend no EasyPanel');
    }
    
    if (!apiProxy.success || !apiProxy.isBackend) {
        console.log('🚨 URGENTE: Configurar proxy /api → backend:3001');
    }
    
    console.log('\n✅ Após as correções, execute novamente este script para verificar.');
    
    // 4. Gerar comandos de teste
    console.log('\n🧪 COMANDOS PARA TESTAR APÓS CORREÇÕES:');
    console.log('node easypanel-fix-script.js');
    console.log('curl -I https://cortefacil.app/api/health');
    console.log('curl https://cortefacil.app/api/health');
}

// Executar script
if (require.main === module) {
    fixEasyPanel().catch(console.error);
}

module.exports = { fixEasyPanel, testBackend };