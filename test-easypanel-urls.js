const axios = require('axios');
const https = require('https');

// Configurar para ignorar certificados SSL inválidos (apenas para teste)
const httpsAgent = new https.Agent({
    rejectUnauthorized: false
});

console.log('🔍 Testando URLs Alternativas do EasyPanel');
console.log('=' .repeat(60));

// URLs alternativas para testar
const testUrls = [
    {
        name: 'Domínio Principal (www)',
        baseUrl: 'https://www.cortefacil.app',
        description: 'URL principal com www'
    },
    {
        name: 'Domínio Principal (sem www)',
        baseUrl: 'https://cortefacil.app',
        description: 'URL principal sem www'
    },
    {
        name: 'EasyPanel Frontend',
        baseUrl: 'https://cortefacil.7ebsu.easypanel.host',
        description: 'URL direta do frontend no EasyPanel'
    },
    {
        name: 'EasyPanel Backend',
        baseUrl: 'https://cortefacil-backend.7ebsu.easypanel.host',
        description: 'URL direta do backend no EasyPanel'
    },
    {
        name: 'Vercel Deploy',
        baseUrl: 'https://cortefacil.vercel.app',
        description: 'Deploy no Vercel'
    }
];

// Endpoints para testar
const endpoints = [
    {
        path: '',
        method: 'GET',
        description: 'Página inicial'
    },
    {
        path: '/health',
        method: 'GET',
        description: 'Health check direto'
    },
    {
        path: '/api',
        method: 'GET',
        description: 'API root'
    },
    {
        path: '/api/health',
        method: 'GET',
        description: 'API Health check'
    }
];

async function testUrl(urlConfig) {
    console.log(`\n🌐 Testando: ${urlConfig.name}`);
    console.log(`   URL Base: ${urlConfig.baseUrl}`);
    console.log(`   Descrição: ${urlConfig.description}`);
    console.log('   ' + '-'.repeat(50));
    
    for (const endpoint of endpoints) {
        const fullUrl = `${urlConfig.baseUrl}${endpoint.path}`;
        
        try {
            console.log(`\n   📡 ${endpoint.method} ${endpoint.path || '/'}`);
            
            const config = {
                method: endpoint.method,
                url: fullUrl,
                timeout: 10000,
                httpsAgent: httpsAgent,
                headers: {
                    'User-Agent': 'CortefacilApp-Test/1.0'
                },
                maxRedirects: 5
            };
            
            const response = await axios(config);
            
            console.log(`      ✅ Status: ${response.status} ${response.statusText}`);
            console.log(`      📄 Content-Type: ${response.headers['content-type'] || 'N/A'}`);
            console.log(`      🔗 Final URL: ${response.request.res.responseUrl || fullUrl}`);
            
            // Verificar se é HTML ou JSON
            const contentType = response.headers['content-type'] || '';
            if (contentType.includes('application/json')) {
                console.log(`      📋 Tipo: JSON Response`);
                if (response.data) {
                    const responseStr = JSON.stringify(response.data, null, 2).substring(0, 150);
                    console.log(`      📋 Dados: ${responseStr}${responseStr.length >= 150 ? '...' : ''}`);
                }
            } else if (contentType.includes('text/html')) {
                console.log(`      📋 Tipo: HTML Response`);
                // Verificar se contém indicadores de frontend ou backend
                const htmlContent = response.data.toString().toLowerCase();
                if (htmlContent.includes('cortefacil') || htmlContent.includes('react')) {
                    console.log(`      🎯 Conteúdo: Frontend detectado`);
                } else {
                    console.log(`      🎯 Conteúdo: HTML genérico`);
                }
            } else {
                console.log(`      📋 Tipo: ${contentType}`);
            }
            
        } catch (error) {
            if (error.response) {
                // Servidor respondeu com erro
                console.log(`      ❌ Status: ${error.response.status} ${error.response.statusText}`);
                console.log(`      📄 Content-Type: ${error.response.headers['content-type'] || 'N/A'}`);
                
                // Verificar se é erro 404, 405, etc.
                if (error.response.status === 404) {
                    console.log(`      💡 Endpoint não encontrado`);
                } else if (error.response.status === 405) {
                    console.log(`      💡 Método não permitido`);
                } else if (error.response.status >= 500) {
                    console.log(`      💡 Erro interno do servidor`);
                }
                
            } else if (error.request) {
                // Requisição foi feita mas não houve resposta
                console.log(`      ❌ Sem resposta do servidor`);
                console.log(`      🔍 Código: ${error.code || 'UNKNOWN'}`);
                
                if (error.code === 'ENOTFOUND') {
                    console.log(`      💡 Domínio não encontrado (DNS)`);
                } else if (error.code === 'ECONNREFUSED') {
                    console.log(`      💡 Conexão recusada (servidor offline)`);
                } else if (error.code === 'ETIMEDOUT') {
                    console.log(`      💡 Timeout (servidor lento/offline)`);
                }
                
            } else {
                // Erro na configuração da requisição
                console.log(`      ❌ Erro na requisição: ${error.message}`);
            }
        }
    }
}

async function runTests() {
    console.log('🚀 Iniciando testes de URLs...');
    
    for (const urlConfig of testUrls) {
        await testUrl(urlConfig);
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('📊 ANÁLISE DOS RESULTADOS');
    console.log('='.repeat(60));
    
    console.log('\n🔍 O que procurar:');
    console.log('✅ Status 200: Serviço funcionando');
    console.log('❌ ENOTFOUND: Domínio não configurado/DNS');
    console.log('❌ 404: Endpoint não existe');
    console.log('❌ 405: Método não permitido (problema de roteamento)');
    console.log('❌ 500+: Erro interno do servidor');
    
    console.log('\n💡 Próximos passos baseados nos resultados:');
    console.log('1. Se www.cortefacil.app funcionar: usar como base');
    console.log('2. Se EasyPanel direto funcionar: problema de DNS personalizado');
    console.log('3. Se Vercel funcionar: considerar usar Vercel para frontend');
    console.log('4. Se nenhum funcionar: problema de deploy/configuração');
    
    console.log('\n✅ Teste concluído!');
}

// Executar testes
runTests().catch(console.error);