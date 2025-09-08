const axios = require('axios');
const https = require('https');

// Configurar para ignorar certificados SSL inválidos (apenas para teste)
const httpsAgent = new https.Agent({
    rejectUnauthorized: false
});

console.log('🔍 Testando Domínio Funcionando: www.cortefacil.app');
console.log('=' .repeat(60));

// URL base que sabemos que funciona
const baseUrl = 'https://www.cortefacil.app';

// Endpoints para testar (incluindo possíveis rotas da API)
const endpoints = [
    {
        path: '',
        method: 'GET',
        description: 'Página inicial (frontend)'
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
    },
    {
        path: '/api/auth/register',
        method: 'GET',
        description: 'Rota de registro (GET para testar)'
    },
    {
        path: '/api/auth/register',
        method: 'POST',
        description: 'Rota de registro (POST)',
        data: {
            nome: 'Teste API Working',
            email: `teste.working.${Date.now()}@exemplo.com`,
            senha: 'senha123',
            tipo: 'cliente'
        }
    },
    {
        path: '/api/auth/login',
        method: 'POST',
        description: 'Rota de login (POST)',
        data: {
            email: 'admin@cortefacil.com',
            senha: 'admin123'
        }
    },
    {
        path: '/health',
        method: 'GET',
        description: 'Health check direto (sem /api)'
    }
];

async function testEndpoint(endpoint) {
    const fullUrl = `${baseUrl}${endpoint.path}`;
    
    try {
        console.log(`\n📡 ${endpoint.method} ${endpoint.path || '/'}`);
        console.log(`   URL: ${fullUrl}`);
        console.log(`   Descrição: ${endpoint.description}`);
        
        const config = {
            method: endpoint.method,
            url: fullUrl,
            timeout: 15000,
            httpsAgent: httpsAgent,
            headers: {
                'Content-Type': 'application/json',
                'User-Agent': 'CortefacilApp-Test/1.0',
                'Accept': 'application/json, text/html, */*'
            },
            maxRedirects: 5
        };
        
        if (endpoint.data) {
            config.data = endpoint.data;
            console.log(`   📋 Dados enviados: ${JSON.stringify(endpoint.data, null, 2)}`);
        }
        
        const response = await axios(config);
        
        console.log(`   ✅ Status: ${response.status} ${response.statusText}`);
        console.log(`   📄 Content-Type: ${response.headers['content-type'] || 'N/A'}`);
        
        // Verificar se houve redirecionamento
        const finalUrl = response.request.res.responseUrl || fullUrl;
        if (finalUrl !== fullUrl) {
            console.log(`   🔄 Redirecionado para: ${finalUrl}`);
        }
        
        // Analisar resposta
        const contentType = response.headers['content-type'] || '';
        if (contentType.includes('application/json')) {
            console.log(`   📋 Tipo: JSON Response ✅`);
            if (response.data) {
                const responseStr = JSON.stringify(response.data, null, 2);
                if (responseStr.length > 300) {
                    console.log(`   📋 Resposta: ${responseStr.substring(0, 300)}...`);
                } else {
                    console.log(`   📋 Resposta: ${responseStr}`);
                }
            }
        } else if (contentType.includes('text/html')) {
            console.log(`   📋 Tipo: HTML Response`);
            const htmlContent = response.data.toString();
            
            // Verificar se é uma página de erro ou frontend
            if (htmlContent.includes('<!DOCTYPE html>')) {
                if (htmlContent.toLowerCase().includes('cortefacil') || htmlContent.toLowerCase().includes('react')) {
                    console.log(`   🎯 Conteúdo: Frontend React detectado`);
                } else if (htmlContent.toLowerCase().includes('error') || htmlContent.toLowerCase().includes('404')) {
                    console.log(`   ❌ Conteúdo: Página de erro`);
                } else {
                    console.log(`   🎯 Conteúdo: HTML genérico`);
                }
            }
            
            // Mostrar início do HTML
            const htmlPreview = htmlContent.substring(0, 200).replace(/\n/g, ' ');
            console.log(`   📋 HTML Preview: ${htmlPreview}...`);
        } else {
            console.log(`   📋 Tipo: ${contentType}`);
            if (response.data) {
                console.log(`   📋 Dados: ${response.data.toString().substring(0, 200)}`);
            }
        }
        
    } catch (error) {
        if (error.response) {
            // Servidor respondeu com erro
            console.log(`   ❌ Status: ${error.response.status} ${error.response.statusText}`);
            console.log(`   📄 Content-Type: ${error.response.headers['content-type'] || 'N/A'}`);
            
            // Analisar tipo de erro
            if (error.response.status === 404) {
                console.log(`   💡 Diagnóstico: Endpoint não encontrado`);
            } else if (error.response.status === 405) {
                console.log(`   💡 Diagnóstico: Método não permitido - API pode estar servindo frontend`);
            } else if (error.response.status === 500) {
                console.log(`   💡 Diagnóstico: Erro interno do servidor`);
            } else if (error.response.status === 502) {
                console.log(`   💡 Diagnóstico: Bad Gateway - problema de proxy/backend`);
            } else if (error.response.status === 503) {
                console.log(`   💡 Diagnóstico: Serviço indisponível`);
            }
            
            // Mostrar resposta de erro se disponível
            if (error.response.data) {
                const errorData = typeof error.response.data === 'string' 
                    ? error.response.data.substring(0, 200)
                    : JSON.stringify(error.response.data, null, 2).substring(0, 200);
                console.log(`   📋 Erro: ${errorData}`);
            }
            
        } else if (error.request) {
            console.log(`   ❌ Sem resposta do servidor`);
            console.log(`   🔍 Código: ${error.code || 'UNKNOWN'}`);
            console.log(`   📝 Mensagem: ${error.message}`);
        } else {
            console.log(`   ❌ Erro na requisição: ${error.message}`);
        }
    }
}

async function runTests() {
    console.log('🚀 Testando endpoints no domínio funcionando...');
    console.log(`📍 URL Base: ${baseUrl}`);
    
    for (const endpoint of endpoints) {
        await testEndpoint(endpoint);
        
        // Pequena pausa entre requisições
        await new Promise(resolve => setTimeout(resolve, 1000));
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('📊 ANÁLISE DOS RESULTADOS');
    console.log('='.repeat(60));
    
    console.log('\n🔍 Interpretação dos resultados:');
    console.log('✅ JSON Response: API funcionando corretamente');
    console.log('⚠️  HTML Response em /api/*: Backend servindo frontend (problema)');
    console.log('❌ 404 em /api/*: Rotas da API não configuradas');
    console.log('❌ 405 em POST: Método não permitido (problema de roteamento)');
    
    console.log('\n💡 Próximos passos baseados nos resultados:');
    console.log('1. Se /api/* retornar HTML: Reconfigurar roteamento no EasyPanel');
    console.log('2. Se /api/* retornar 404: Verificar configuração do backend');
    console.log('3. Se POST retornar 405: Problema de proxy/nginx');
    console.log('4. Se JSON funcionar: Atualizar frontend para usar www.cortefacil.app');
    
    console.log('\n✅ Teste concluído!');
}

// Executar testes
runTests().catch(console.error);