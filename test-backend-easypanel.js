const axios = require('axios');
const dotenv = require('dotenv');

// Carregar configurações do EasyPanel
dotenv.config({ path: './backend/server/.env.easypanel' });

console.log('🧪 Testando Backend com Configurações EasyPanel');
console.log('=' .repeat(60));

// URLs para teste (baseadas nas configurações encontradas)
const BACKEND_URLS = [
    'https://cortefacil-backend.7ebsu.easypanel.host',
    'https://api.cortefacil.app',
    'https://cortefacil.app/api'
];

// Testar múltiplas URLs
let BACKEND_URL = BACKEND_URLS[0];
let API_URL = `${BACKEND_URL}/api`;

console.log(`🔗 Backend URL: ${BACKEND_URL}`);
console.log(`🔗 API URL: ${API_URL}`);
console.log('');

async function testBackendEndpoints() {
    const tests = [
        {
            name: 'Health Check',
            url: `${API_URL}/health`,
            method: 'GET'
        },
        {
            name: 'Test Endpoint',
            url: `${API_URL}/test`,
            method: 'GET'
        },
        {
            name: 'Registro de Cliente',
            url: `${API_URL}/auth/register`,
            method: 'POST',
            data: {
                nome: 'Cliente Teste EasyPanel',
                email: `cliente.easypanel.${Date.now()}@exemplo.com`,
                password: 'senha123',
                telefone: '(11) 99999-9999',
                tipo: 'cliente'
            }
        }
    ];

    for (const test of tests) {
        console.log(`📝 Teste: ${test.name}`);
        console.log('-'.repeat(50));
        
        try {
            const config = {
                method: test.method,
                url: test.url,
                timeout: 30000,
                headers: {
                    'Content-Type': 'application/json',
                    'User-Agent': 'CortefacilApp-Test/1.0'
                }
            };

            if (test.data) {
                config.data = test.data;
                console.log(`📤 Enviando dados:`, JSON.stringify(test.data, null, 2));
            }

            const response = await axios(config);
            
            console.log(`✅ Status: ${response.status}`);
            console.log(`✅ Resposta:`, JSON.stringify(response.data, null, 2));
            
        } catch (error) {
            console.log(`❌ Erro no teste ${test.name}:`);
            
            if (error.response) {
                console.log(`   Status: ${error.response.status}`);
                console.log(`   Dados:`, JSON.stringify(error.response.data, null, 2));
            } else if (error.request) {
                console.log(`   Erro de rede:`, error.message);
                console.log(`   Code:`, error.code);
            } else {
                console.log(`   Erro:`, error.message);
            }
        }
        
        console.log('');
    }
}

async function testDatabaseConnection() {
    console.log('🗄️  Testando Conexão com Banco via Backend');
    console.log('-'.repeat(50));
    
    try {
        // Testar endpoint que usa banco de dados
        const response = await axios.get(`${API_URL}/health`, {
            timeout: 30000
        });
        
        console.log('✅ Backend respondeu ao health check');
        console.log('✅ Conexão com banco provavelmente OK');
        
    } catch (error) {
        console.log('❌ Erro no health check do backend:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, JSON.stringify(error.response.data, null, 2));
        } else {
            console.log(`   Erro:`, error.message);
        }
    }
    
    console.log('');
}

async function testCORS() {
    console.log('🌐 Testando CORS');
    console.log('-'.repeat(50));
    
    try {
        const response = await axios.options(`${API_URL}/auth/register`, {
            headers: {
                'Origin': 'https://cortefacilapp-frontend-maycon341753-projects.vercel.app',
                'Access-Control-Request-Method': 'POST',
                'Access-Control-Request-Headers': 'Content-Type'
            },
            timeout: 30000
        });
        
        console.log('✅ CORS configurado corretamente');
        console.log(`   Access-Control-Allow-Origin: ${response.headers['access-control-allow-origin']}`);
        console.log(`   Access-Control-Allow-Methods: ${response.headers['access-control-allow-methods']}`);
        
    } catch (error) {
        console.log('❌ Erro no teste de CORS:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
        } else {
            console.log(`   Erro:`, error.message);
        }
    }
    
    console.log('');
}

async function findWorkingBackendURL() {
    console.log('🔍 Testando URLs do backend...');
    console.log('-'.repeat(50));
    
    for (const url of BACKEND_URLS) {
        console.log(`\n📡 Testando: ${url}`);
        
        try {
            const testUrl = url.includes('/api') ? `${url}/health` : `${url}/api/health`;
            const response = await axios.get(testUrl, {
                timeout: 10000,
                headers: {
                    'User-Agent': 'CortefacilApp-Test/1.0'
                }
            });
            
            console.log(`✅ URL funcionando: ${url}`);
            console.log(`✅ Status: ${response.status}`);
            
            // Atualizar URLs globais
            BACKEND_URL = url;
            API_URL = url.includes('/api') ? url : `${url}/api`;
            
            return true;
            
        } catch (error) {
            console.log(`❌ URL não funciona: ${url}`);
            if (error.response) {
                console.log(`   Status: ${error.response.status}`);
            } else {
                console.log(`   Erro: ${error.message}`);
            }
        }
    }
    
    console.log('\n❌ Nenhuma URL do backend está funcionando!');
    return false;
}

async function runAllTests() {
    console.log('🚀 Iniciando testes do backend...');
    console.log('');
    
    // Primeiro, encontrar URL que funciona
    const backendWorking = await findWorkingBackendURL();
    
    if (!backendWorking) {
        console.log('\n💥 Não foi possível conectar ao backend!');
        console.log('\n🔧 Possíveis soluções:');
        console.log('   1. Verificar se o backend está rodando no EasyPanel');
        console.log('   2. Verificar configurações de domínio');
        console.log('   3. Verificar se o serviço está com status verde');
        return;
    }
    
    console.log('\n' + '='.repeat(60));
    console.log(`🎯 Usando backend: ${BACKEND_URL}`);
    console.log(`🎯 API URL: ${API_URL}`);
    console.log('='.repeat(60));
    
    await testDatabaseConnection();
    await testCORS();
    await testBackendEndpoints();
    
    console.log('🎉 Testes concluídos!');
    console.log('');
    console.log('📋 Resumo:');
    console.log('   - Se todos os testes passaram, o backend está funcionando');
    console.log('   - Se houve erros, verifique as configurações do EasyPanel');
    console.log('   - Certifique-se de que o backend está rodando no EasyPanel');
    console.log('');
    console.log('🔧 URLs importantes:');
    console.log(`   Backend: ${BACKEND_URL}`);
    console.log(`   API: ${API_URL}`);
    console.log(`   Health: ${API_URL}/health`);
}

// Executar testes
runAllTests().catch(error => {
    console.error('💥 Erro fatal nos testes:', error.message);
    process.exit(1);
});