const axios = require('axios');
const https = require('https');

// Configurar para ignorar certificados SSL inválidos (apenas para teste)
const httpsAgent = new https.Agent({
    rejectUnauthorized: false
});

console.log('🔍 Testando API do Backend Online');
console.log('=' .repeat(60));

// URLs para testar
const testUrls = [
    {
        name: 'Domínio Personalizado (api.cortefacil.app)',
        baseUrl: 'https://api.cortefacil.app',
        description: 'URL configurada no frontend'
    },
    {
        name: 'EasyPanel Direto (cortefacil-backend)',
        baseUrl: 'https://cortefacil-backend.7ebsu.easypanel.host',
        description: 'URL direta do EasyPanel'
    }
];

// Endpoints para testar
const endpoints = [
    {
        path: '/health',
        method: 'GET',
        description: 'Health check'
    },
    {
        path: '/api/health',
        method: 'GET',
        description: 'API Health check'
    },
    {
        path: '/api/auth/register',
        method: 'POST',
        description: 'Registro de usuário',
        data: {
            nome: 'Teste API',
            email: `teste.api.${Date.now()}@exemplo.com`,
            senha: 'senha123',
            tipo: 'cliente'
        }
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
            console.log(`\n   📡 ${endpoint.method} ${endpoint.path}`);
            console.log(`      URL: ${fullUrl}`);
            
            const config = {
                method: endpoint.method,
                url: fullUrl,
                timeout: 10000,
                httpsAgent: httpsAgent,
                headers: {
                    'Content-Type': 'application/json',
                    'User-Agent': 'CortefacilApp-Test/1.0'
                }
            };
            
            if (endpoint.data) {
                config.data = endpoint.data;
            }
            
            const response = await axios(config);
            
            console.log(`      ✅ Status: ${response.status} ${response.statusText}`);
            console.log(`      📄 Content-Type: ${response.headers['content-type'] || 'N/A'}`);
            
            // Mostrar resposta (limitada)
            if (response.data) {
                const responseStr = typeof response.data === 'string' 
                    ? response.data.substring(0, 200)
                    : JSON.stringify(response.data, null, 2).substring(0, 200);
                console.log(`      📋 Resposta: ${responseStr}${responseStr.length >= 200 ? '...' : ''}`);
            }
            
        } catch (error) {
            if (error.response) {
                // Servidor respondeu com erro
                console.log(`      ❌ Status: ${error.response.status} ${error.response.statusText}`);
                console.log(`      📄 Content-Type: ${error.response.headers['content-type'] || 'N/A'}`);
                
                if (error.response.data) {
                    const errorStr = typeof error.response.data === 'string'
                        ? error.response.data.substring(0, 200)
                        : JSON.stringify(error.response.data, null, 2).substring(0, 200);
                    console.log(`      📋 Erro: ${errorStr}${errorStr.length >= 200 ? '...' : ''}`);
                }
            } else if (error.request) {
                // Requisição foi feita mas não houve resposta
                console.log(`      ❌ Sem resposta do servidor`);
                console.log(`      🔍 Código: ${error.code || 'UNKNOWN'}`);
                console.log(`      📝 Mensagem: ${error.message}`);
            } else {
                // Erro na configuração da requisição
                console.log(`      ❌ Erro na requisição: ${error.message}`);
            }
        }
    }
}

async function runTests() {
    console.log('🚀 Iniciando testes da API...');
    
    for (const urlConfig of testUrls) {
        await testUrl(urlConfig);
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESUMO DOS TESTES');
    console.log('='.repeat(60));
    
    console.log('\n🔍 Diagnóstico:');
    console.log('1. Se api.cortefacil.app não funcionar: problema de DNS/domínio');
    console.log('2. Se EasyPanel direto não funcionar: problema no deploy do backend');
    console.log('3. Se health check falhar: backend não está rodando');
    console.log('4. Se registro falhar: problema nas rotas da API');
    
    console.log('\n💡 Próximos passos se houver falhas:');
    console.log('1. Verificar configurações DNS do domínio');
    console.log('2. Verificar deploy e logs do backend no EasyPanel');
    console.log('3. Verificar variáveis de ambiente do backend');
    console.log('4. Verificar se o banco de dados está conectado');
    console.log('5. Verificar configurações de CORS');
    
    console.log('\n✅ Teste concluído!');
}

// Executar testes
runTests().catch(console.error);