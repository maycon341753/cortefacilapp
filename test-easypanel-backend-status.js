const https = require('https');
const http = require('http');
const mysql = require('mysql2/promise');

// Configurações do teste
const config = {
    domain: 'cortefacil.app',
    backendPort: 3001,
    database: {
        host: '31.97.171.104',
        port: 3306,
        user: 'u690889028_mayconwender',
        password: 'Maycon341753@',
        database: 'u690889028_mayconwender'
    }
};

// Função para fazer requisições HTTP/HTTPS
function makeRequest(url, timeout = 10000) {
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
                    headers: res.headers,
                    data: data.substring(0, 500), // Limitar dados para análise
                    server: res.headers.server || 'Unknown'
                });
            });
        });
        
        req.on('error', (error) => {
            resolve({
                success: false,
                error: error.message,
                code: error.code
            });
        });
        
        req.on('timeout', () => {
            req.destroy();
            resolve({
                success: false,
                error: 'Request timeout',
                code: 'TIMEOUT'
            });
        });
    });
}

// Função para testar conexão com banco de dados
async function testDatabase() {
    console.log('\n🔍 Testando conexão com banco de dados...');
    
    try {
        const connection = await mysql.createConnection(config.database);
        console.log('✅ Conexão com MySQL estabelecida com sucesso');
        
        // Testar uma query simples
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log('✅ Query de teste executada com sucesso');
        
        await connection.end();
        return true;
    } catch (error) {
        console.log('❌ Erro na conexão com banco:', error.message);
        return false;
    }
}

// Função para testar endpoints específicos
async function testEndpoints() {
    console.log('\n🔍 Testando endpoints do backend...');
    
    const endpoints = [
        // Teste direto na porta 3001 (se backend estiver exposto)
        `https://${config.domain}:${config.backendPort}/health`,
        `https://${config.domain}:${config.backendPort}/`,
        
        // Teste via proxy /api (como deveria funcionar)
        `https://${config.domain}/api/health`,
        `https://${config.domain}/api/`,
        `https://www.${config.domain}/api/health`,
        `https://www.${config.domain}/api/`,
        
        // Teste de endpoints específicos
        `https://${config.domain}/api/auth/login`,
        `https://${config.domain}/api/users`
    ];
    
    const results = [];
    
    for (const url of endpoints) {
        console.log(`\nTestando: ${url}`);
        const result = await makeRequest(url);
        
        if (result.success) {
            console.log(`✅ Status: ${result.status}`);
            console.log(`📡 Server: ${result.server}`);
            
            // Verificar se é resposta do backend Node.js ou frontend
            if (result.data.includes('<!DOCTYPE html>') || result.server === 'Vercel') {
                console.log('⚠️  PROBLEMA: Resposta vem do frontend/Vercel, não do backend!');
            } else if (result.data.includes('Cannot GET') || result.status === 404) {
                console.log('⚠️  PROBLEMA: Endpoint não encontrado no backend');
            } else {
                console.log('✅ Resposta parece vir do backend Node.js');
            }
        } else {
            console.log(`❌ Erro: ${result.error} (${result.code})`);
        }
        
        results.push({ url, ...result });
    }
    
    return results;
}

// Função para analisar resultados e dar diagnóstico
function analyzeResults(endpointResults, dbResult) {
    console.log('\n📊 ANÁLISE DOS RESULTADOS\n');
    console.log('=' .repeat(50));
    
    // Análise do banco de dados
    if (dbResult) {
        console.log('✅ Banco de dados: FUNCIONANDO');
    } else {
        console.log('❌ Banco de dados: PROBLEMA DE CONEXÃO');
    }
    
    // Análise dos endpoints
    const directBackend = endpointResults.filter(r => r.url.includes(':3001'));
    const proxyApi = endpointResults.filter(r => r.url.includes('/api/') && !r.url.includes(':3001'));
    
    console.log('\n🔍 DIAGNÓSTICO:');
    
    // Verificar se backend está rodando diretamente
    const backendRunning = directBackend.some(r => r.success && r.status === 200);
    if (backendRunning) {
        console.log('✅ Backend está rodando na porta 3001');
    } else {
        console.log('❌ Backend NÃO está acessível na porta 3001');
        console.log('   → Container pode estar parado no EasyPanel');
    }
    
    // Verificar proxy /api
    const proxyWorking = proxyApi.some(r => r.success && !r.data.includes('<!DOCTYPE html>') && r.server !== 'Vercel');
    if (proxyWorking) {
        console.log('✅ Proxy /api está funcionando');
    } else {
        console.log('❌ Proxy /api NÃO está funcionando');
        console.log('   → Requests /api estão indo para Vercel em vez do backend');
        console.log('   → Configuração de proxy reverso necessária no EasyPanel');
    }
    
    // Recomendações
    console.log('\n🔧 AÇÕES NECESSÁRIAS:');
    
    if (!backendRunning) {
        console.log('1. 🚨 URGENTE: Verificar e iniciar container backend no EasyPanel');
        console.log('   - Acessar painel do EasyPanel');
        console.log('   - Verificar status do serviço backend');
        console.log('   - Verificar logs do container');
        console.log('   - Reiniciar se necessário');
    }
    
    if (!proxyWorking) {
        console.log('2. 🚨 URGENTE: Configurar proxy reverso no EasyPanel');
        console.log('   - Adicionar regra: location /api/ { proxy_pass http://backend:3001/; }');
        console.log('   - Ou configurar subdomínio api.cortefacil.app');
    }
    
    if (!dbResult) {
        console.log('3. ⚠️  Verificar configurações de rede do banco');
        console.log('   - Confirmar se MySQL está acessível externamente');
        console.log('   - Verificar credenciais e permissões');
    }
}

// Função principal
async function main() {
    console.log('🔍 DIAGNÓSTICO DO BACKEND EASYPANEL');
    console.log('=' .repeat(50));
    console.log(`Domínio: ${config.domain}`);
    console.log(`Porta Backend: ${config.backendPort}`);
    console.log(`Banco: ${config.database.host}:${config.database.port}`);
    
    try {
        // Testar banco de dados
        const dbResult = await testDatabase();
        
        // Testar endpoints
        const endpointResults = await testEndpoints();
        
        // Analisar resultados
        analyzeResults(endpointResults, dbResult);
        
        console.log('\n✅ Diagnóstico concluído!');
        console.log('\n📋 Próximos passos:');
        console.log('1. Acessar EasyPanel e verificar status do backend');
        console.log('2. Configurar proxy reverso se necessário');
        console.log('3. Executar este teste novamente após correções');
        
    } catch (error) {
        console.error('❌ Erro durante diagnóstico:', error.message);
    }
}

// Executar diagnóstico
if (require.main === module) {
    main();
}

module.exports = { testDatabase, testEndpoints, analyzeResults };