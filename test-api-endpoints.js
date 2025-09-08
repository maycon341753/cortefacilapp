const https = require('https');
const http = require('http');

// Configurar para ignorar certificados SSL inválidos
process.env["NODE_TLS_REJECT_UNAUTHORIZED"] = 0;

// URLs para testar
const testUrls = {
  local: 'http://localhost:3002',
  production: 'https://www.cortefacil.app/api'
};

// Função para fazer requisições HTTP/HTTPS
function makeRequest(url, options = {}) {
  return new Promise((resolve, reject) => {
    const isHttps = url.startsWith('https');
    const client = isHttps ? https : http;
    
    const requestOptions = {
      method: options.method || 'GET',
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'CortefacilApp-Test/1.0',
        ...options.headers
      },
      timeout: 10000
    };
    
    const req = client.request(url, requestOptions, (res) => {
      let data = '';
      
      res.on('data', (chunk) => {
        data += chunk;
      });
      
      res.on('end', () => {
        try {
          const jsonData = JSON.parse(data);
          resolve({
            status: res.statusCode,
            headers: res.headers,
            data: jsonData,
            raw: data
          });
        } catch (e) {
          resolve({
            status: res.statusCode,
            headers: res.headers,
            data: null,
            raw: data,
            parseError: e.message
          });
        }
      });
    });
    
    req.on('error', (error) => {
      reject({
        error: error.message,
        code: error.code
      });
    });
    
    req.on('timeout', () => {
      req.destroy();
      reject({
        error: 'Request timeout',
        code: 'TIMEOUT'
      });
    });
    
    if (options.body) {
      req.write(JSON.stringify(options.body));
    }
    
    req.end();
  });
}

// Testar endpoint específico
async function testEndpoint(baseUrl, endpoint, options = {}) {
  const url = `${baseUrl}${endpoint}`;
  const method = options.method || 'GET';
  
  console.log(`\n🔍 Testando: ${method} ${url}`);
  
  try {
    const result = await makeRequest(url, options);
    
    console.log(`✅ Status: ${result.status}`);
    
    if (result.data) {
      console.log('📄 Resposta JSON:', JSON.stringify(result.data, null, 2));
    } else if (result.raw) {
      console.log('📄 Resposta Raw:', result.raw.substring(0, 200) + (result.raw.length > 200 ? '...' : ''));
      if (result.parseError) {
        console.log('⚠️  Erro ao parsear JSON:', result.parseError);
      }
    }
    
    return { success: true, status: result.status, data: result.data };
    
  } catch (error) {
    console.log(`❌ Erro: ${error.error || error.message}`);
    if (error.code) {
      console.log(`   Código: ${error.code}`);
    }
    return { success: false, error: error.error || error.message, code: error.code };
  }
}

// Testar todos os endpoints
async function testAllEndpoints(baseUrl, label) {
  console.log(`\n🚀 Testando ${label}: ${baseUrl}`);
  console.log('=' .repeat(50));
  
  const results = {};
  
  // Teste 1: Raiz da API
  results.root = await testEndpoint(baseUrl, '');
  
  // Teste 2: Health check
  results.health = await testEndpoint(baseUrl, '/health');
  
  // Teste 3: Listar tabelas
  results.tables = await testEndpoint(baseUrl, '/tables');
  
  // Teste 4: Registro (POST)
  const testUser = {
    nome: 'Teste Usuario',
    email: `teste${Date.now()}@example.com`,
    senha: '123456',
    telefone: '11999999999'
  };
  
  results.register = await testEndpoint(baseUrl, '/register', {
    method: 'POST',
    body: testUser
  });
  
  // Teste 5: Login (POST) - só se o registro funcionou
  if (results.register.success && results.register.status === 201) {
    results.login = await testEndpoint(baseUrl, '/login', {
      method: 'POST',
      body: {
        email: testUser.email,
        senha: testUser.senha
      }
    });
  } else {
    console.log('\n⏭️  Pulando teste de login (registro falhou)');
    results.login = { success: false, skipped: true };
  }
  
  return results;
}

// Função principal
async function main() {
  console.log('🧪 Iniciando testes dos endpoints da API');
  console.log('📅 Data:', new Date().toLocaleString());
  
  const allResults = {};
  
  // Testar servidor local
  console.log('\n1️⃣ Testando servidor local (deve estar rodando)');
  allResults.local = await testAllEndpoints(testUrls.local, 'Servidor Local');
  
  // Testar produção
  console.log('\n2️⃣ Testando produção (www.cortefacil.app/api)');
  allResults.production = await testAllEndpoints(testUrls.production, 'Produção');
  
  // Resumo dos resultados
  console.log('\n📊 RESUMO DOS TESTES');
  console.log('=' .repeat(50));
  
  for (const [env, results] of Object.entries(allResults)) {
    console.log(`\n🌐 ${env.toUpperCase()}:`);
    
    for (const [endpoint, result] of Object.entries(results)) {
      const status = result.success ? '✅' : '❌';
      const statusCode = result.status ? ` (${result.status})` : '';
      const error = result.error ? ` - ${result.error}` : '';
      const skipped = result.skipped ? ' (pulado)' : '';
      
      console.log(`   ${status} ${endpoint}${statusCode}${error}${skipped}`);
    }
  }
  
  // Análise e recomendações
  console.log('\n🔧 ANÁLISE E RECOMENDAÇÕES');
  console.log('=' .repeat(50));
  
  const localWorking = Object.values(allResults.local).some(r => r.success);
  const prodWorking = Object.values(allResults.production).some(r => r.success);
  
  if (localWorking && !prodWorking) {
    console.log('✅ Backend local funcionando, mas produção com problemas');
    console.log('🔧 Ações necessárias:');
    console.log('   1. Verificar configuração de roteamento no EasyPanel');
    console.log('   2. Confirmar se backend está deployado corretamente');
    console.log('   3. Verificar logs do EasyPanel');
    console.log('   4. Testar URLs alternativas (api.cortefacil.app)');
  } else if (!localWorking && !prodWorking) {
    console.log('❌ Problemas em ambos os ambientes');
    console.log('🔧 Verificar:');
    console.log('   1. Configurações do banco de dados');
    console.log('   2. Dependências instaladas');
    console.log('   3. Variáveis de ambiente');
  } else if (localWorking && prodWorking) {
    console.log('🎉 Ambos os ambientes funcionando!');
    console.log('✅ API está pronta para uso');
  }
}

// Executar testes
main().catch(console.error);