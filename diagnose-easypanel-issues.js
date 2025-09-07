const axios = require('axios');
const fs = require('fs');
const path = require('path');

// Configurações
const BASE_URL = 'https://cortefacil.app/api';
const TIMEOUT = 10000;

// Configurar axios com timeout
axios.defaults.timeout = TIMEOUT;

console.log('🔍 DIAGNÓSTICO COMPLETO - PROBLEMAS EASYPANEL');
console.log('=' .repeat(60));
console.log(`🌐 URL Base: ${BASE_URL}`);
console.log(`⏱️  Timeout: ${TIMEOUT}ms`);
console.log('');

// Função para fazer requisições com detalhes completos
async function makeRequest(method, endpoint, data = null, headers = {}) {
    const url = `${BASE_URL}${endpoint}`;
    const config = {
        method,
        url,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'User-Agent': 'CortefacilApp-Diagnostic/1.0',
            ...headers
        },
        validateStatus: () => true, // Aceitar todos os status codes
        maxRedirects: 0 // Não seguir redirects
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        config.data = data;
    }
    
    try {
        const response = await axios(config);
        return {
            success: true,
            status: response.status,
            statusText: response.statusText,
            headers: response.headers,
            data: response.data,
            contentType: response.headers['content-type'] || 'unknown'
        };
    } catch (error) {
        return {
            success: false,
            error: error.message,
            code: error.code,
            status: error.response?.status,
            statusText: error.response?.statusText,
            headers: error.response?.headers,
            data: error.response?.data
        };
    }
}

// Função para analisar resposta
function analyzeResponse(result, testName) {
    console.log(`\n📋 ${testName}`);
    console.log('-'.repeat(40));
    
    if (!result.success) {
        console.log(`❌ Erro: ${result.error}`);
        if (result.code) console.log(`🔧 Código: ${result.code}`);
        return false;
    }
    
    console.log(`📊 Status: ${result.status} ${result.statusText}`);
    console.log(`📄 Content-Type: ${result.contentType}`);
    
    // Verificar se é HTML quando deveria ser JSON
    const isHtml = result.contentType.includes('text/html');
    const isJson = result.contentType.includes('application/json');
    
    if (isHtml && !isJson) {
        console.log('🚨 PROBLEMA: API retornando HTML em vez de JSON!');
        console.log('💡 Possível causa: Proxy/Nginx servindo página estática');
        
        // Mostrar início do HTML para diagnóstico
        if (typeof result.data === 'string' && result.data.includes('<html')) {
            const htmlPreview = result.data.substring(0, 200) + '...';
            console.log(`📝 HTML Preview: ${htmlPreview}`);
        }
    }
    
    // Analisar status codes específicos
    switch (result.status) {
        case 200:
            console.log('✅ Sucesso');
            break;
        case 404:
            console.log('❌ Endpoint não encontrado');
            console.log('💡 Possível causa: Roteamento incorreto no EasyPanel');
            break;
        case 405:
            console.log('❌ Método não permitido');
            console.log('💡 Possível causa: Servidor não suporta este método HTTP');
            break;
        case 500:
            console.log('❌ Erro interno do servidor');
            break;
        case 502:
            console.log('❌ Bad Gateway');
            console.log('💡 Possível causa: Backend não está rodando');
            break;
        case 503:
            console.log('❌ Serviço indisponível');
            break;
        default:
            console.log(`⚠️  Status inesperado: ${result.status}`);
    }
    
    // Mostrar dados da resposta (limitado)
    if (result.data) {
        const dataStr = typeof result.data === 'string' ? result.data : JSON.stringify(result.data, null, 2);
        const preview = dataStr.length > 300 ? dataStr.substring(0, 300) + '...' : dataStr;
        console.log(`📦 Dados: ${preview}`);
    }
    
    return result.status >= 200 && result.status < 300;
}

// Testes específicos
async function runDiagnostics() {
    const results = {};
    
    console.log('🚀 Iniciando diagnósticos...');
    
    // 1. Teste básico de conectividade
    console.log('\n🔗 TESTE 1: Conectividade Básica');
    results.connectivity = await makeRequest('GET', '/');
    analyzeResponse(results.connectivity, 'GET /');
    
    // 2. Health Check
    console.log('\n❤️  TESTE 2: Health Check');
    results.health = await makeRequest('GET', '/health');
    analyzeResponse(results.health, 'GET /health');
    
    // 3. Teste de rota específica
    console.log('\n🧪 TESTE 3: Rota de Teste');
    results.test = await makeRequest('GET', '/test');
    analyzeResponse(results.test, 'GET /test');
    
    // 4. Teste OPTIONS (CORS Preflight)
    console.log('\n🌐 TESTE 4: CORS Preflight');
    results.options = await makeRequest('OPTIONS', '/auth/register', null, {
        'Origin': 'https://cortefacil.app',
        'Access-Control-Request-Method': 'POST',
        'Access-Control-Request-Headers': 'Content-Type'
    });
    analyzeResponse(results.options, 'OPTIONS /auth/register');
    
    // 5. Teste POST Register (problema principal)
    console.log('\n📝 TESTE 5: POST Register (PROBLEMA)');
    const registerData = {
        nome: 'Teste Diagnóstico',
        email: 'teste@diagnostico.com',
        password: 'teste123',
        telefone: '(11) 99999-9999',
        tipo: 'cliente'
    };
    results.register = await makeRequest('POST', '/auth/register', registerData);
    analyzeResponse(results.register, 'POST /auth/register');
    
    // 6. Teste POST Login
    console.log('\n🔐 TESTE 6: POST Login');
    const loginData = {
        email: 'teste@teste.com',
        password: 'teste123'
    };
    results.login = await makeRequest('POST', '/auth/login', loginData);
    analyzeResponse(results.login, 'POST /auth/login');
    
    // 7. Teste GET Auth (verificar se rota auth existe)
    console.log('\n👤 TESTE 7: GET Auth');
    results.authGet = await makeRequest('GET', '/auth');
    analyzeResponse(results.authGet, 'GET /auth');
    
    // 8. Teste com diferentes Content-Types
    console.log('\n📋 TESTE 8: POST com diferentes Content-Types');
    
    // 8a. application/json
    results.postJson = await makeRequest('POST', '/auth/register', registerData, {
        'Content-Type': 'application/json'
    });
    analyzeResponse(results.postJson, 'POST /auth/register (JSON)');
    
    // 8b. application/x-www-form-urlencoded
    const formData = new URLSearchParams(registerData).toString();
    results.postForm = await makeRequest('POST', '/auth/register', formData, {
        'Content-Type': 'application/x-www-form-urlencoded'
    });
    analyzeResponse(results.postForm, 'POST /auth/register (Form)');
    
    // Análise final
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESUMO DO DIAGNÓSTICO');
    console.log('='.repeat(60));
    
    const issues = [];
    const solutions = [];
    
    // Verificar problemas específicos
    if (results.connectivity.success && results.connectivity.contentType.includes('text/html')) {
        issues.push('🚨 API retornando HTML em vez de JSON');
        solutions.push('• Verificar configuração de proxy/nginx no EasyPanel');
        solutions.push('• Confirmar que backend está rodando na porta correta');
        solutions.push('• Verificar se domínio está apontando para o serviço correto');
    }
    
    if (results.register.status === 405 || results.login.status === 405) {
        issues.push('🚨 Rotas POST retornando erro 405');
        solutions.push('• Verificar se método POST está habilitado no servidor');
        solutions.push('• Confirmar configuração de rotas no Express');
        solutions.push('• Verificar middleware de CORS');
    }
    
    if (results.health.success && results.register.status === 405) {
        issues.push('🚨 Health check OK mas rotas POST falhando');
        solutions.push('• Problema específico com rotas POST');
        solutions.push('• Verificar middleware de parsing de JSON');
        solutions.push('• Verificar configuração de roteamento');
    }
    
    // Mostrar problemas encontrados
    if (issues.length > 0) {
        console.log('\n❌ PROBLEMAS IDENTIFICADOS:');
        issues.forEach(issue => console.log(issue));
        
        console.log('\n💡 SOLUÇÕES RECOMENDADAS:');
        solutions.forEach(solution => console.log(solution));
    } else {
        console.log('\n✅ Nenhum problema crítico identificado!');
    }
    
    // Configurações recomendadas para EasyPanel
    console.log('\n🔧 CONFIGURAÇÕES RECOMENDADAS EASYPANEL:');
    console.log('Backend Service:');
    console.log('  • Build Context: backend/');
    console.log('  • Dockerfile Path: Dockerfile');
    console.log('  • Port: 3001');
    console.log('  • Start Command: (vazio)');
    console.log('  • Domain: api.cortefacil.app ou cortefacil.app/api');
    
    console.log('\n📋 PRÓXIMOS PASSOS:');
    console.log('1. Verificar logs do backend no EasyPanel');
    console.log('2. Confirmar variáveis de ambiente');
    console.log('3. Verificar configuração de domínio/proxy');
    console.log('4. Testar rotas localmente vs produção');
    
    console.log('\n✅ Diagnóstico concluído!');
}

// Executar diagnósticos
runDiagnostics().catch(error => {
    console.error('❌ Erro durante diagnóstico:', error);
    process.exit(1);
});