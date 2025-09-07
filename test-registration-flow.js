const axios = require('axios');

// Configurações - usando localhost para teste local
const backendUrl = 'http://localhost:3001/api';
const frontendUrl = 'https://www.cortefacil.app';

async function testRegistrationFlow() {
    console.log('🧪 Testando fluxo completo de registro e redirecionamento...');
    console.log('=' .repeat(60));
    
    // Teste 1: Registro de Cliente
    console.log('\n📝 Teste 1: Registro de Cliente');
    console.log('-'.repeat(40));
    
    try {
        const clienteData = {
            nome: 'João Cliente Teste',
            email: `cliente.teste.${Date.now()}@exemplo.com`,
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
        
        console.log('✅ Registro de cliente bem-sucedido!');
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
        console.log('❌ Erro no registro de cliente:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, error.response.data);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
    }
    
    // Teste 2: Registro de Parceiro
    console.log('\n📝 Teste 2: Registro de Parceiro');
    console.log('-'.repeat(40));
    
    try {
        const parceiroData = {
            nome: 'Maria Parceira Teste',
            email: `parceiro.teste.${Date.now()}@exemplo.com`,
            password: 'senha123',
            telefone: '11888888888',
            tipo: 'parceiro',
            nome_salao: 'Salão Teste da Maria',
            endereco: 'Rua Teste, 123, São Paulo - SP, CEP: 01234-567'
        };
        
        console.log('📤 Enviando dados do parceiro:', {
            nome: parceiroData.nome,
            email: parceiroData.email,
            tipo: parceiroData.tipo,
            nome_salao: parceiroData.nome_salao
        });
        
        const parceiroResponse = await axios.post(
            `${backendUrl}/auth/register`,
            parceiroData,
            {
                timeout: 10000,
                headers: {
                    'Content-Type': 'application/json',
                    'Origin': frontendUrl
                }
            }
        );
        
        console.log('✅ Registro de parceiro bem-sucedido!');
        console.log('📊 Status:', parceiroResponse.status);
        console.log('📊 Resposta:', JSON.stringify(parceiroResponse.data, null, 2));
        
        // Verificar se retorna token e dados do usuário
        if (parceiroResponse.data.success && parceiroResponse.data.data) {
            const { token, user } = parceiroResponse.data.data;
            console.log('🔑 Token recebido:', token ? 'Sim' : 'Não');
            console.log('👤 Dados do usuário:', user);
            console.log('🎯 Tipo de usuário:', user.tipo);
            console.log('➡️  Redirecionamento esperado: /parceiro/dashboard');
        }
        
    } catch (error) {
        console.log('❌ Erro no registro de parceiro:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, error.response.data);
        } else {
            console.log(`   Erro: ${error.message}`);
        }
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('🎉 Teste de fluxo de registro concluído!');
    console.log('\n📋 Resumo dos ajustes implementados:');
    console.log('✅ AuthContext modificado para login automático após registro');
    console.log('✅ Register.jsx atualizado para redirecionamento baseado no tipo');
    console.log('✅ Rotas específicas identificadas: /cliente/dashboard e /parceiro/dashboard');
    console.log('\n🔧 Próximos passos:');
    console.log('1. Testar no frontend real');
    console.log('2. Verificar se os dashboards carregam corretamente');
    console.log('3. Confirmar que os logs de erro foram resolvidos');
}

// Executar teste
testRegistrationFlow().catch(console.error);