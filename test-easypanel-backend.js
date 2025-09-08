const https = require('https');
const http = require('http');

// Desabilitar verificação SSL para testes
process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

const testEndpoints = [
  {
    name: 'Backend Direto (Porta 3001)',
    url: 'https://cortefacil.app:3001/health',
    method: 'GET'
  },
  {
    name: 'Backend via Proxy (/api)',
    url: 'https://cortefacil.app/api/health',
    method: 'GET'
  },
  {
    name: 'Backend www via Proxy (/api)',
    url: 'https://www.cortefacil.app/api/health',
    method: 'GET'
  },
  {
    name: 'Root do Backend Direto',
    url: 'https://cortefacil.app:3001/',
    method: 'GET'
  },
  {
    name: 'Tables do Backend Direto',
    url: 'https://cortefacil.app:3001/tables',
    method: 'GET'
  }
];

function makeRequest(url, method = 'GET', data = null) {
  return new Promise((resolve, reject) => {
    const isHttps = url.startsWith('https');
    const client = isHttps ? https : http;
    
    const urlObj = new URL(url);
    const options = {
      hostname: urlObj.hostname,
      port: urlObj.port || (isHttps ? 443 : 80),
      path: urlObj.pathname + urlObj.search,
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'CorteFacil-Test/1.0'
      },
      timeout: 10000,
      rejectUnauthorized: false
    };

    if (data) {
      const postData = JSON.stringify(data);
      options.headers['Content-Length'] = Buffer.byteLength(postData);
    }

    const req = client.request(options, (res) => {
      let body = '';
      res.on('data', (chunk) => {
        body += chunk;
      });
      
      res.on('end', () => {
        resolve({
          status: res.statusCode,
          headers: res.headers,
          body: body,
          url: url
        });
      });
    });

    req.on('error', (err) => {
      resolve({
        status: 'ERROR',
        error: err.message,
        url: url
      });
    });

    req.on('timeout', () => {
      req.destroy();
      resolve({
        status: 'TIMEOUT',
        error: 'Request timeout',
        url: url
      });
    });

    if (data) {
      req.write(JSON.stringify(data));
    }
    
    req.end();
  });
}

async function testAllEndpoints() {
  console.log('🔍 TESTANDO BACKEND NO EASYPANEL');
  console.log('=' .repeat(50));
  
  for (const endpoint of testEndpoints) {
    console.log(`\n🔍 Testando: ${endpoint.method} ${endpoint.url}`);
    
    try {
      const result = await makeRequest(endpoint.url, endpoint.method);
      
      if (result.status === 'ERROR' || result.status === 'TIMEOUT') {
        console.log(`❌ ${result.status}: ${result.error}`);
      } else {
        console.log(`✅ Status: ${result.status}`);
        
        // Mostrar headers importantes
        if (result.headers) {
          if (result.headers['content-type']) {
            console.log(`📋 Content-Type: ${result.headers['content-type']}`);
          }
          if (result.headers['server']) {
            console.log(`🖥️  Server: ${result.headers['server']}`);
          }
        }
        
        // Mostrar início da resposta
        if (result.body) {
          const preview = result.body.substring(0, 200);
          console.log(`📄 Resposta (200 chars): ${preview}${result.body.length > 200 ? '...' : ''}`);
          
          // Tentar parsear como JSON
          try {
            const json = JSON.parse(result.body);
            console.log(`✅ JSON válido:`, json);
          } catch (e) {
            if (result.body.includes('<!DOCTYPE')) {
              console.log(`⚠️  Resposta é HTML (não JSON)`);
            } else {
              console.log(`⚠️  Erro ao parsear JSON: ${e.message.substring(0, 50)}`);
            }
          }
        }
      }
    } catch (error) {
      console.log(`❌ Erro: ${error.message}`);
    }
  }
  
  console.log('\n📊 ANÁLISE');
  console.log('=' .repeat(50));
  console.log('Se o backend direto (porta 3001) funcionar mas /api não,');
  console.log('então o problema é no roteamento do EasyPanel.');
  console.log('\nSe nenhum funcionar, o backend não está rodando.');
  console.log('\nSe ambos retornarem HTML, o proxy está direcionando');
  console.log('para o frontend em vez do backend.');
}

testAllEndpoints().catch(console.error);