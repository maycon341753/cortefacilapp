const axios = require('axios');
const dotenv = require('dotenv');

// Carregar configurações
dotenv.config({ path: './frontend/.env.production' });

async function testFrontendBackendConnection() {
    console.log('🔍 Testando conexão Frontend -> Backend...');
    console.log('=' .repeat(50));
    
    // URLs para testar
    const backendUrls = [
        'https://cortefacil-backend.7ebsu.easypanel.host/api/health',
        'https://cortefacil-backend.7ebsu.easypanel.host/api/test'
    ];
    
    const frontendUrl = 'https://cortefacil.7ebsu.easypanel.host';
    
    console.log('📍 URLs de teste:');
    console.log(`   Frontend: ${frontendUrl}`);
    backendUrls.forEach(url => console.log(`   Backend:  ${url}`));
    console.log('');
    
    // Testar cada endpoint do backend
    for (const url of backendUrls) {
        try {
            console.log(`🔄 Testando: ${url}`);
            
            const response = await axios.get(url, {
                timeout: 10000,
                headers: {
                    'Origin': frontendUrl,
                    'User-Agent': 'CortefacilApp-Test/1.0'
                }
            });
            
            console.log(`✅ Status: ${response.status}`);
            console.log(`📊 Dados:`, response.data);
            console.log(`🔧 Headers CORS:`);
            console.log(`   Access-Control-Allow-Origin: ${response.headers['access-control-allow-origin'] || 'Não definido'}`);
            console.log(`   Access-Control-Allow-Credentials: ${response.headers['access-control-allow-credentials'] || 'Não definido'}`);
            
        } catch (error) {
            console.log(`❌ Erro ao testar ${url}:`);
            if (error.response) {
                console.log(`   Status: ${error.response.status}`);
                console.log(`   Dados:`, error.response.data);
                console.log(`   Headers:`, error.response.headers);
            } else if (error.request) {
                console.log(`   Erro de rede: ${error.message}`);
                console.log(`   Código: ${error.code}`);
            } else {
                console.log(`   Erro: ${error.message}`);
            }
        }
        console.log('');
    }
    
    // Testar endpoint de registro (simulando requisição do frontend)
    try {
        console.log('🔄 Testando endpoint de registro...');
        
        const testUser = {
            nome: 'Teste Usuario',
            email: `teste${Date.now()}@exemplo.com`,
            senha: 'senha123',
            telefone: '11999999999',
            tipo_usuario: 'cliente'
        };
        
        const response = await axios.post(
            'https://cortefacil-backend.7ebsu.easypanel.host/api/auth/register',
            testUser,
            {
                timeout: 10000,
                headers: {
                    'Content-Type': 'application/json',
                    'Origin': frontendUrl,
                    'User-Agent': 'CortefacilApp-Test/1.0'
                }
            }
        );
        
        console.log('✅ Registro funcionando!');
        console.log(`📊 Status: ${response.status}`);
        console.log(`📊 Resposta:`, response.data);
        
    } catch (error) {
        console.log('❌ Erro no teste de registro:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, error.response.data);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
    }
    
    console.log('');
    console.log('🎉 Teste de conexão concluído!');
}

// Executar teste
testFrontendBackendConnection().catch(console.error);