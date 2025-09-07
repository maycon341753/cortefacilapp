const axios = require('axios');

console.log('🧪 Testando Rotas do Backend - EasyPanel');
console.log('=' .repeat(60));

// URL correta do backend
const API_URL = 'https://cortefacil.app/api';

console.log(`🔗 API URL: ${API_URL}`);
console.log('');

// Lista de rotas para testar
const routes = [
    { method: 'GET', path: '/health', description: 'Health Check' },
    { method: 'GET', path: '/test', description: 'Test Endpoint' },
    { method: 'GET', path: '/', description: 'Root API' },
    { method: 'OPTIONS', path: '/auth/register', description: 'CORS Preflight Register' },
    { method: 'POST', path: '/auth/register', description: 'User Registration', data: {
        nome: 'Teste',
        email: `teste.${Date.now()}@exemplo.com`,
        password: 'senha123',
        telefone: '(11) 99999-9999',
        tipo: 'cliente'
    }},
    { method: 'GET', path: '/auth', description: 'Auth Routes Info' },
    { method: 'POST', path: '/auth/login', description: 'User Login', data: {
        email: 'teste@exemplo.com',
        password: 'senha123'
    }}
];

async function testRoute(route) {
    console.log(`📡 ${route.method} ${route.path} - ${route.description}`);
    console.log('-'.repeat(50));
    
    try {
        const config = {
            method: route.method.toLowerCase(),
            url: `${API_URL}${route.path}`,
            timeout: 15000,
            headers: {
                'Content-Type': 'application/json',
                'User-Agent': 'CortefacilApp-RouteTest/1.0'
            }
        };
        
        if (route.data) {
            config.data = route.data;
            console.log('📤 Dados enviados:', JSON.stringify(route.data, null, 2));
        }
        
        const response = await axios(config);
        
        console.log(`✅ Status: ${response.status}`);
        console.log(`✅ Headers:`);
        Object.keys(response.headers).forEach(key => {
            if (key.toLowerCase().includes('cors') || key.toLowerCase().includes('allow')) {
                console.log(`   ${key}: ${response.headers[key]}`);
            }
        });
        
        if (response.data) {
            const dataStr = typeof response.data === 'string' ? response.data : JSON.stringify(response.data, null, 2);
            if (dataStr.length > 500) {
                console.log(`✅ Resposta: ${dataStr.substring(0, 500)}...`);
            } else {
                console.log(`✅ Resposta:`, dataStr);
            }
        }
        
        return { success: true, status: response.status, data: response.data };
        
    } catch (error) {
        console.log(`❌ Erro:`);
        
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Status Text: ${error.response.statusText}`);
            
            // Mostrar headers importantes
            if (error.response.headers) {
                console.log(`   Headers importantes:`);
                Object.keys(error.response.headers).forEach(key => {
                    if (key.toLowerCase().includes('allow') || 
                        key.toLowerCase().includes('cors') || 
                        key.toLowerCase().includes('content-type')) {
                        console.log(`     ${key}: ${error.response.headers[key]}`);
                    }
                });
            }
            
            if (error.response.data) {
                console.log(`   Dados:`, JSON.stringify(error.response.data, null, 2));
            }
            
            // Análise específica de erros
            if (error.response.status === 405) {
                console.log(`   💡 Método ${route.method} não permitido nesta rota`);
                if (error.response.headers['allow']) {
                    console.log(`   💡 Métodos permitidos: ${error.response.headers['allow']}`);
                }
            } else if (error.response.status === 404) {
                console.log(`   💡 Rota não encontrada - pode não estar implementada`);
            } else if (error.response.status === 500) {
                console.log(`   💡 Erro interno do servidor`);
            }
            
        } else if (error.request) {
            console.log(`   Erro de rede: ${error.message}`);
            console.log(`   Code: ${error.code}`);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
        
        return { success: false, error: error.message, status: error.response?.status };
    }
}

async function discoverAPIStructure() {
    console.log('🔍 Descobrindo estrutura da API...');
    console.log('-'.repeat(50));
    
    // Testar se existe documentação ou lista de rotas
    const discoveryRoutes = [
        '/docs',
        '/api-docs', 
        '/swagger',
        '/routes',
        '/endpoints',
        '/',
        '/status'
    ];
    
    for (const path of discoveryRoutes) {
        try {
            const response = await axios.get(`${API_URL}${path}`, {
                timeout: 10000,
                headers: { 'Accept': 'application/json, text/html' }
            });
            
            console.log(`✅ Encontrado: ${path} (Status: ${response.status})`);
            
            if (response.data && typeof response.data === 'object') {
                console.log(`   Dados:`, JSON.stringify(response.data, null, 2).substring(0, 300));
            }
            
        } catch (error) {
            // Silencioso para discovery
        }
    }
}

async function runRouteTests() {
    console.log('🚀 Iniciando testes de rotas...');
    console.log('');
    
    // Descobrir estrutura da API
    await discoverAPIStructure();
    console.log('');
    
    const results = [];
    
    // Testar cada rota
    for (const route of routes) {
        const result = await testRoute(route);
        results.push({ ...route, result });
        console.log('');
    }
    
    // Resumo dos resultados
    console.log('='.repeat(60));
    console.log('📊 RESUMO DOS TESTES DE ROTAS');
    console.log('='.repeat(60));
    
    results.forEach(({ method, path, description, result }) => {
        const status = result.success ? '✅' : '❌';
        const statusCode = result.status ? `(${result.status})` : '';
        console.log(`${status} ${method} ${path} - ${description} ${statusCode}`);
    });
    
    console.log('');
    
    // Análise dos resultados
    const successCount = results.filter(r => r.result.success).length;
    const totalCount = results.length;
    
    console.log(`📈 Taxa de sucesso: ${successCount}/${totalCount} (${Math.round(successCount/totalCount*100)}%)`);
    
    if (successCount === 0) {
        console.log('\n💥 Nenhuma rota funcionou - backend pode estar com problemas');
    } else if (successCount < totalCount) {
        console.log('\n⚠️  Algumas rotas não funcionaram - verificar implementação');
    } else {
        console.log('\n🎉 Todas as rotas funcionaram!');
    }
    
    console.log('\n🔧 Próximos passos:');
    console.log('   1. Verificar logs do backend no EasyPanel');
    console.log('   2. Confirmar se todas as rotas estão implementadas');
    console.log('   3. Verificar configuração de CORS');
    console.log('   4. Testar com diferentes métodos HTTP');
}

// Executar testes
runRouteTests().catch(error => {
    console.error('💥 Erro fatal nos testes:', error.message);
    process.exit(1);
});