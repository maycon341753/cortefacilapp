/**
 * Teste para verificar se a correção da URL da API está funcionando
 * Este script testa a conectividade com o backend no EasyPanel
 */

const axios = require('axios');

// URLs para testar
const API_URLS = {
  'Domínio Personalizado (Correto)': 'https://api.cortefacil.app',
  'EasyPanel Direto (Antigo)': 'https://cortefacil-backend.7ebsu.easypanel.host/api',
  'EasyPanel Interno (não deve funcionar externamente)': 'http://cortefacil-backend:3001/api'
};

async function testAPIConnection(name, url) {
  console.log(`\n🔍 Testando ${name}:`);
  console.log(`URL: ${url}`);
  
  try {
    // Teste de health check
    const healthResponse = await axios.get(`${url}/health`, {
      timeout: 10000,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });
    
    console.log(`✅ Health Check: ${healthResponse.status} - ${healthResponse.statusText}`);
    console.log(`📊 Resposta:`, healthResponse.data);
    
    // Teste de rota de registro (apenas verificar se a rota existe)
    try {
      const registerResponse = await axios.post(`${url}/auth/register`, {
        // Dados inválidos propositalmente para testar apenas a conectividade
        email: 'test@test.com',
        senha: '123'
      }, {
        timeout: 5000,
        validateStatus: function (status) {
          // Aceitar qualquer status para verificar conectividade
          return status < 500;
        }
      });
      
      console.log(`✅ Rota /auth/register acessível: ${registerResponse.status}`);
      
    } catch (registerError) {
      if (registerError.response) {
        console.log(`✅ Rota /auth/register acessível: ${registerError.response.status}`);
      } else {
        console.log(`❌ Erro na rota /auth/register:`, registerError.message);
      }
    }
    
    return true;
    
  } catch (error) {
    console.log(`❌ Falha na conexão:`);
    if (error.response) {
      console.log(`   Status: ${error.response.status}`);
      console.log(`   Dados:`, error.response.data);
    } else if (error.request) {
      console.log(`   Erro de rede: ${error.message}`);
    } else {
      console.log(`   Erro: ${error.message}`);
    }
    return false;
  }
}

async function main() {
  console.log('🚀 Teste de Correção da URL da API');
  console.log('=====================================');
  
  const results = {};
  
  for (const [name, url] of Object.entries(API_URLS)) {
    results[name] = await testAPIConnection(name, url);
  }
  
  console.log('\n📋 RESUMO DOS TESTES:');
  console.log('=====================');
  
  for (const [name, success] of Object.entries(results)) {
    console.log(`${success ? '✅' : '❌'} ${name}`);
  }
  
  console.log('\n💡 PRÓXIMOS PASSOS:');
  console.log('===================');
  
  if (results['EasyPanel Externo']) {
    console.log('✅ A URL externa do EasyPanel está funcionando!');
    console.log('📝 Agora você pode fazer o deploy no Vercel com confiança.');
    console.log('🔄 Execute: git add . && git commit -m "Fix: Corrigir URL da API para produção" && git push');
  } else {
    console.log('❌ A URL externa do EasyPanel não está funcionando.');
    console.log('🔧 Verifique se o backend está rodando no EasyPanel.');
    console.log('🌐 Verifique se o domínio está configurado corretamente.');
  }
  
  if (!results['EasyPanel Interno (não deve funcionar externamente)']) {
    console.log('✅ Correto: A URL interna não é acessível externamente (como esperado).');
  }
}

// Executar o teste
main().catch(console.error);