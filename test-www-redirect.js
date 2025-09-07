const axios = require('axios');

// URLs para testar
const URLS = [
    'https://cortefacil.app/api',
    'https://www.cortefacil.app/api',
    'https://api.cortefacil.app',
    'https://cortefacil.7ebsu.easypanel.host/api'
];

console.log('🔍 TESTE DE REDIRECIONAMENTO - PROBLEMA 307');
console.log('=' .repeat(60));

// Função para testar URL específica
async function testUrl(baseUrl) {
    console.log(`\n🌐 Testando: ${baseUrl}`);
    console.log('-'.repeat(40));
    
    try {
        // Teste 1: Health Check
        console.log('❤️  Health Check...');
        const healthResponse = await axios.get(`${baseUrl}/health`, {
            maxRedirects: 0,
            validateStatus: () => true,
            timeout: 10000
        });
        console.log(`   Status: ${healthResponse.status} ${healthResponse.statusText}`);
        
        if (healthResponse.status === 307) {
            console.log(`   🔄 Redirect para: ${healthResponse.data?.redirect || 'N/A'}`);
        } else if (healthResponse.status === 200) {
            console.log(`   ✅ Sucesso: ${JSON.stringify(healthResponse.data)}`);
        }
        
        // Teste 2: POST Register (só se health check não for redirect)
        if (healthResponse.status !== 307) {
            console.log('📝 POST Register...');
            const registerData = {
                nome: 'Teste',
                email: 'teste@teste.com',
                password: 'teste123',
                telefone: '(11) 99999-9999',
                tipo: 'cliente'
            };
            
            const registerResponse = await axios.post(`${baseUrl}/auth/register`, registerData, {
                headers: {
                    'Content-Type': 'application/json'
                },
                maxRedirects: 0,
                validateStatus: () => true,
                timeout: 10000
            });
            
            console.log(`   Status: ${registerResponse.status} ${registerResponse.statusText}`);
            
            if (registerResponse.status === 405) {
                console.log('   ❌ Método não permitido - PROBLEMA IDENTIFICADO!');
            } else if (registerResponse.status === 400) {
                console.log('   ⚠️  Bad Request (esperado - email pode já existir)');
            } else if (registerResponse.status === 201) {
                console.log('   ✅ Registro criado com sucesso!');
            }
        }
        
        return {
            url: baseUrl,
            healthStatus: healthResponse.status,
            working: healthResponse.status === 200
        };
        
    } catch (error) {
        console.log(`   ❌ Erro: ${error.message}`);
        return {
            url: baseUrl,
            error: error.message,
            working: false
        };
    }
}

// Testar todas as URLs
async function runTests() {
    const results = [];
    
    for (const url of URLS) {
        const result = await testUrl(url);
        results.push(result);
    }
    
    // Resumo
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESUMO DOS TESTES');
    console.log('='.repeat(60));
    
    const workingUrls = results.filter(r => r.working);
    const redirectUrls = results.filter(r => r.healthStatus === 307);
    
    if (workingUrls.length > 0) {
        console.log('\n✅ URLs FUNCIONANDO:');
        workingUrls.forEach(r => console.log(`   • ${r.url}`));
    }
    
    if (redirectUrls.length > 0) {
        console.log('\n🔄 URLs COM REDIRECT 307:');
        redirectUrls.forEach(r => console.log(`   • ${r.url}`));
    }
    
    const errorUrls = results.filter(r => r.error);
    if (errorUrls.length > 0) {
        console.log('\n❌ URLs COM ERRO:');
        errorUrls.forEach(r => console.log(`   • ${r.url} - ${r.error}`));
    }
    
    // Recomendações
    console.log('\n💡 RECOMENDAÇÕES:');
    
    if (workingUrls.length > 0) {
        console.log('1. Use uma das URLs funcionando para o frontend');
        console.log('2. Configure o frontend para usar a URL correta');
        console.log('3. Atualize as variáveis de ambiente');
    } else if (redirectUrls.length > 0) {
        console.log('1. Problema de configuração de domínio no EasyPanel');
        console.log('2. Configure redirect de cortefacil.app para www.cortefacil.app');
        console.log('3. Ou configure www.cortefacil.app como domínio principal');
        console.log('4. Verifique configuração de DNS');
    } else {
        console.log('1. Nenhuma URL está funcionando');
        console.log('2. Verifique se o backend está rodando no EasyPanel');
        console.log('3. Verifique logs do serviço backend');
        console.log('4. Confirme configuração de porta e domínio');
    }
    
    console.log('\n🔧 CONFIGURAÇÃO RECOMENDADA PARA FRONTEND:');
    if (workingUrls.length > 0) {
        console.log(`VITE_API_URL=${workingUrls[0].url}`);
    } else {
        console.log('Aguardar correção do backend primeiro');
    }
}

// Executar testes
runTests().catch(error => {
    console.error('❌ Erro durante testes:', error);
    process.exit(1);
});