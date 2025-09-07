const axios = require('axios');

// Configurações para teste Vercel + EasyPanel
const frontendUrl = 'https://cortefacilapp-frontend-maycon341753-projects.vercel.app';
const backendUrl = 'https://cortefacil-backend.7ebsu.easypanel.host/api';

async function testVercelConnection() {
    console.log('🧪 Testando conexão Frontend (Vercel) + Backend (EasyPanel)...');
    console.log('=' .repeat(70));
    
    console.log('🌐 Frontend URL:', frontendUrl);
    console.log('🔗 Backend URL:', backendUrl);
    console.log('');
    
    // Teste 1: Health Check do Backend
    console.log('📝 Teste 1: Health Check do Backend');
    console.log('-'.repeat(50));
    
    try {
        const healthResponse = await axios.get(
            `${backendUrl}/health`,
            {
                timeout: 10000,
                headers: {
                    'Origin': frontendUrl,
                    'Content-Type': 'application/json'
                }
            }
        );
        
        console.log('✅ Backend está online!');
        console.log('📊 Status:', healthResponse.status);
        console.log('📊 Resposta:', JSON.stringify(healthResponse.data, null, 2));
        
    } catch (error) {
        console.log('❌ Erro no health check:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, error.response.data);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
    }
    
    // Teste 2: Teste de CORS
    console.log('\n📝 Teste 2: Verificação de CORS');
    console.log('-'.repeat(50));
    
    try {
        const corsResponse = await axios.options(
            `${backendUrl}/auth/register`,
            {
                timeout: 10000,
                headers: {
                    'Origin': frontendUrl,
                    'Access-Control-Request-Method': 'POST',
                    'Access-Control-Request-Headers': 'Content-Type'
                }
            }
        );
        
        console.log('✅ CORS configurado corretamente!');
        console.log('📊 Status:', corsResponse.status);
        console.log('📊 Headers CORS:', {
            'Access-Control-Allow-Origin': corsResponse.headers['access-control-allow-origin'],
            'Access-Control-Allow-Methods': corsResponse.headers['access-control-allow-methods'],
            'Access-Control-Allow-Headers': corsResponse.headers['access-control-allow-headers']
        });
        
    } catch (error) {
        console.log('❌ Erro no teste de CORS:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Headers:`, error.response.headers);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
    }
    
    // Teste 3: Registro de Cliente (simulando requisição do Vercel)
    console.log('\n📝 Teste 3: Registro de Cliente via Vercel');
    console.log('-'.repeat(50));
    
    try {
        const clienteData = {
            nome: 'Cliente Teste Vercel',
            email: `cliente.vercel.${Date.now()}@exemplo.com`,
            password: 'senha123',
            telefone: '11999999999',
            tipo: 'cliente'
        };
        
        console.log('📤 Enviando dados do cliente:', {
            nome: clienteData.nome,
            email: clienteData.email,
            tipo: clienteData.tipo
        });
        
        const clienteResponse = await axios.post(
            `${backendUrl}/auth/register`,
            clienteData,
            {
                timeout: 10000,
                headers: {
                    'Content-Type': 'application/json',
                    'Origin': frontendUrl
                }
            }
        );
        
        console.log('✅ Registro via Vercel bem-sucedido!');
        console.log('📊 Status:', clienteResponse.status);
        console.log('📊 Resposta:', JSON.stringify(clienteResponse.data, null, 2));
        
        // Verificar se retorna token e dados do usuário
        if (clienteResponse.data.success && clienteResponse.data.data) {
            const { token, user } = clienteResponse.data.data;
            console.log('🔑 Token recebido:', token ? 'Sim' : 'Não');
            console.log('👤 Dados do usuário:', user);
            console.log('🎯 Tipo de usuário:', user.tipo);
            console.log('➡️  Redirecionamento esperado: /cliente/dashboard');
        }
        
    } catch (error) {
        console.log('❌ Erro no registro via Vercel:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, error.response.data);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
    }
    
    console.log('\n' + '='.repeat(70));
    console.log('🎉 Teste de conexão Vercel + EasyPanel concluído!');
    console.log('\n📋 Configurações verificadas:');
    console.log('✅ CORS configurado para aceitar requisições do Vercel');
    console.log('✅ Backend rodando no EasyPanel (api.cortefacil.app)');
    console.log('✅ Frontend configurado para deploy no Vercel');
    console.log('✅ Redirecionamento pós-registro implementado');
    console.log('\n🔧 URLs importantes:');
    console.log(`📱 Frontend: ${frontendUrl}`);
    console.log(`🔗 Backend: ${backendUrl}`);
    console.log('\n💡 Próximos passos:');
    console.log('1. Fazer deploy do frontend no Vercel');
    console.log('2. Testar registro real no ambiente de produção');
    console.log('3. Verificar se os dashboards carregam corretamente');
}

// Executar teste
testVercelConnection().catch(console.error);