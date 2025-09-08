const https = require('https');

// Desabilitar verificação SSL para testes
process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

const tests = [
  {
    name: '🔍 Backend Direto (Porta 3001)',
    url: 'https://cortefacil.app:3001/health',
    expected: 'JSON'
  },
  {
    name: '🔍 API via Proxy (/api/health)',
    url: 'https://cortefacil.app/api/health',
    expected: 'JSON'
  },
  {
    name: '🔍 API www via Proxy',
    url: 'https://www.cortefacil.app/api/health',
    expected: 'JSON'
  },
  {
    name: '🔍 Tabelas do Backend',
    url: 'https://cortefacil.app/api/tables',
    expected: 'JSON'
  }
];

function quickTest(url) {
  return new Promise((resolve) => {
    const req = https.get(url, { timeout: 5000, rejectUnauthorized: false }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        const isJson = body.trim().startsWith('{') || body.trim().startsWith('[');
        const isHtml = body.includes('<!DOCTYPE') || body.includes('<html');
        
        resolve({
          status: res.statusCode,
          server: res.headers.server || 'Unknown',
          contentType: res.headers['content-type'] || 'Unknown',
          isJson,
          isHtml,
          bodyPreview: body.substring(0, 100)
        });
      });
    });
    
    req.on('error', () => resolve({ status: 'ERROR', error: 'Connection failed' }));
    req.on('timeout', () => {
      req.destroy();
      resolve({ status: 'TIMEOUT', error: 'Request timeout' });
    });
  });
}

async function runQuickTests() {
  console.log('🚀 TESTE RÁPIDO DE CORREÇÃO');
  console.log('=' .repeat(50));
  
  let passedTests = 0;
  let totalTests = tests.length;
  
  for (const test of tests) {
    console.log(`\n${test.name}`);
    console.log(`📡 ${test.url}`);
    
    const result = await quickTest(test.url);
    
    if (result.status === 'ERROR' || result.status === 'TIMEOUT') {
      console.log(`❌ ${result.status}: ${result.error}`);
    } else {
      console.log(`✅ Status: ${result.status}`);
      console.log(`🖥️  Server: ${result.server}`);
      console.log(`📋 Content-Type: ${result.contentType}`);
      
      if (test.expected === 'JSON') {
        if (result.isJson) {
          console.log(`✅ Resposta é JSON (correto!)`);
          passedTests++;
        } else if (result.isHtml) {
          console.log(`❌ Resposta é HTML (incorreto - deveria ser JSON)`);
        } else {
          console.log(`⚠️  Resposta não é JSON nem HTML`);
        }
      }
      
      console.log(`📄 Preview: ${result.bodyPreview}...`);
    }
  }
  
  console.log('\n📊 RESULTADO FINAL');
  console.log('=' .repeat(50));
  
  const successRate = (passedTests / totalTests * 100).toFixed(1);
  
  if (passedTests === totalTests) {
    console.log(`🎉 SUCESSO TOTAL! ${passedTests}/${totalTests} testes passaram (${successRate}%)`);
    console.log('✅ Backend está funcionando corretamente!');
    console.log('✅ Roteamento /api/* está correto!');
    console.log('✅ API pronta para uso!');
  } else if (passedTests > 0) {
    console.log(`⚠️  SUCESSO PARCIAL: ${passedTests}/${totalTests} testes passaram (${successRate}%)`);
    console.log('🔧 Algumas correções ainda são necessárias.');
  } else {
    console.log(`❌ FALHA TOTAL: 0/${totalTests} testes passaram`);
    console.log('🚨 Backend ainda não está funcionando.');
    console.log('📋 Verificar:');
    console.log('   - Container backend está rodando?');
    console.log('   - Porta 3001 está exposta?');
    console.log('   - Roteamento /api/* está configurado?');
  }
  
  console.log('\n🔧 PRÓXIMOS PASSOS');
  console.log('=' .repeat(50));
  
  if (passedTests < totalTests) {
    console.log('1. Verificar logs do backend no EasyPanel');
    console.log('2. Confirmar configuração de proxy /api/*');
    console.log('3. Reiniciar serviços se necessário');
    console.log('4. Executar este teste novamente');
  } else {
    console.log('1. Testar registro de usuário');
    console.log('2. Testar login');
    console.log('3. Verificar CORS');
    console.log('4. Deploy frontend se necessário');
  }
}

runQuickTests().catch(console.error);