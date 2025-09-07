const axios = require('axios');

// URLs para testar após correção
const API_URL = 'https://api.cortefacil.app';
const FRONTEND_URL = 'https://cortefacil.app';

console.log('🧪 TESTE PÓS-CORREÇÃO EASYPANEL');
console.log('=' .repeat(50));
console.log(`🔗 API URL: ${API_URL}`);
console.log(`🌐 Frontend URL: ${FRONTEND_URL}`);
console.log('');

// Função para fazer requisição com detalhes
async function testEndpoint(method, url, data = null, expectedStatus = 200) {
    try {
        const config = {
            method,
            url,
            timeout: 10000,
            validateStatus: () => true,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        if (data && (method === 'POST' || method === 'PUT')) {
            config.data = data;
        }
        
        const response = await axios(config);
        
        const success = response.status === expectedStatus;
        const isJson = response.headers['content-type']?.includes('application/json');
        
        console.log(`${success ? '✅' : '❌'} ${method} ${url}`);
        console.log(`   Status: ${response.status} ${response.statusText}`);
        console.log(`   Content-Type: ${response.headers['content-type'] || 'N/A'}`);
        
        if (!isJson && method !== 'GET' && url === FRONTEND_URL) {
            console.log('   ⚠️  Não é JSON (pode ser normal para frontend)');
        } else if (!isJson) {
            console.log('   🚨 PROBLEMA: Deveria retornar JSON!');
        }
        
        if (response.data) {
            const dataStr = typeof response.data === 'string' ? response.data.substring(0, 100) : JSON.stringify(response.data, null, 2).substring(0, 200);
            console.log(`   Dados: ${dataStr}${dataStr.length >= 100 ? '...' : ''}`);
        }
        
        console.log('');
        return { success, status: response.status, isJson };
        
    } catch (error) {
        console.log(`❌ ${method} ${url}`);
        console.log(`   Erro: ${error.message}`);
        console.log('');
        return { success: false, error: error.message };
    }
}

// Executar todos os testes
async function runAllTests() {
    console.log('🚀 Iniciando testes pós-correção...');
    console.log('');
    
    const results = {};
    
    // 1. Teste Frontend
    console.log('🌐 TESTE 1: Frontend');
    results.frontend = await testEndpoint('GET', FRONTEND_URL, null, 200);
    
    // 2. Teste API Health Check
    console.log('❤️  TESTE 2: API Health Check');
    results.health = await testEndpoint('GET', `${API_URL}/health`, null, 200);
    
    // 3. Teste API Root
    console.log('🏠 TESTE 3: API Root');
    results.apiRoot = await testEndpoint('GET', `${API_URL}/`, null, 200);
    
    // 4. Teste API Test Endpoint
    console.log('🧪 TESTE 4: API Test Endpoint');
    results.apiTest = await testEndpoint('GET', `${API_URL}/test`, null, 200);
    
    // 5. Teste CORS Preflight
    console.log('🌐 TESTE 5: CORS Preflight');
    try {
        const corsResponse = await axios.options(`${API_URL}/auth/register`, {
            headers: {
                'Origin': FRONTEND_URL,
                'Access-Control-Request-Method': 'POST',
                'Access-Control-Request-Headers': 'Content-Type'
            },
            timeout: 10000,
            validateStatus: () => true
        });
        
        const corsSuccess = corsResponse.status === 200 || corsResponse.status === 204;
        console.log(`${corsSuccess ? '✅' : '❌'} OPTIONS ${API_URL}/auth/register`);
        console.log(`   Status: ${corsResponse.status}`);
        console.log(`   CORS Headers: ${corsResponse.headers['access-control-allow-origin'] || 'N/A'}`);
        console.log('');
        results.cors = { success: corsSuccess };
    } catch (error) {
        console.log(`❌ OPTIONS ${API_URL}/auth/register`);
        console.log(`   Erro: ${error.message}`);
        console.log('');
        results.cors = { success: false, error: error.message };
    }
    
    // 6. Teste POST Register (PRINCIPAL)
    console.log('📝 TESTE 6: POST Register (CRÍTICO)');
    const registerData = {
        nome: 'Teste Pós Correção',
        email: `teste.pos.correcao.${Date.now()}@teste.com`,
        password: 'teste123456',
        telefone: '(11) 99999-9999',
        tipo: 'cliente'
    };
    results.register = await testEndpoint('POST', `${API_URL}/auth/register`, registerData, 201);
    
    // 7. Teste POST Login
    console.log('🔐 TESTE 7: POST Login');
    const loginData = {
        email: 'teste@inexistente.com',
        password: 'senhaerrada'
    };
    // Esperamos 401 (não autorizado) para credenciais inválidas
    results.login = await testEndpoint('POST', `${API_URL}/auth/login`, loginData, 401);
    
    // 8. Teste GET Auth Routes
    console.log('👤 TESTE 8: GET Auth Routes');
    results.authGet = await testEndpoint('GET', `${API_URL}/auth`, null, 404); // Pode não existir
    
    // Análise dos resultados
    console.log('=' .repeat(50));
    console.log('📊 ANÁLISE DOS RESULTADOS');
    console.log('=' .repeat(50));
    
    const criticalTests = {
        'Frontend carregando': results.frontend?.success,
        'API Health Check': results.health?.success,
        'CORS funcionando': results.cors?.success,
        'POST Register funcionando': results.register?.success,
        'POST Login respondendo': results.login?.success
    };
    
    const passedTests = Object.values(criticalTests).filter(Boolean).length;
    const totalTests = Object.keys(criticalTests).length;
    
    console.log(`\n📈 Testes Críticos: ${passedTests}/${totalTests} passaram\n`);
    
    Object.entries(criticalTests).forEach(([test, passed]) => {
        console.log(`${passed ? '✅' : '❌'} ${test}`);
    });
    
    // Diagnóstico
    console.log('\n🔍 DIAGNÓSTICO:');
    
    if (passedTests === totalTests) {
        console.log('🎉 PERFEITO! Todas as correções foram aplicadas com sucesso!');
        console.log('✅ Backend e Frontend estão funcionando corretamente');
        console.log('✅ Rotas POST estão funcionando');
        console.log('✅ CORS está configurado adequadamente');
    } else {
        console.log('⚠️  Ainda há problemas a serem resolvidos:');
        
        if (!results.frontend?.success) {
            console.log('   • Frontend não está carregando corretamente');
        }
        if (!results.health?.success) {
            console.log('   • API Health Check falhando - backend pode não estar rodando');
        }
        if (!results.cors?.success) {
            console.log('   • CORS não está configurado - problemas de cross-origin');
        }
        if (!results.register?.success) {
            console.log('   • POST Register ainda não funciona - problema principal não resolvido');
        }
        if (!results.login?.success) {
            console.log('   • POST Login não está respondendo adequadamente');
        }
    }
    
    // Próximos passos
    console.log('\n📋 PRÓXIMOS PASSOS:');
    
    if (passedTests === totalTests) {
        console.log('1. ✅ Configuração completa!');
        console.log('2. 🧪 Testar funcionalidades do app');
        console.log('3. 🚀 App pronto para uso!');
    } else {
        console.log('1. 🔧 Revisar configurações do EasyPanel');
        console.log('2. 📋 Verificar se DNS propagou (api.cortefacil.app)');
        console.log('3. 🔄 Redeploy dos serviços se necessário');
        console.log('4. 📊 Verificar logs dos serviços no EasyPanel');
        console.log('5. 🔁 Executar este teste novamente');
    }
    
    console.log('\n✅ Teste concluído!');
}

// Executar testes
runAllTests().catch(error => {
    console.error('❌ Erro durante os testes:', error);
    process.exit(1);
});