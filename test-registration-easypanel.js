const axios = require('axios');
const dotenv = require('dotenv');

// Carregar configurações do EasyPanel
dotenv.config({ path: './backend/server/.env.easypanel' });

console.log('🧪 Testando Registro de Usuário - EasyPanel');
console.log('=' .repeat(60));

// URL correta do backend (descoberta nos testes anteriores)
const API_URL = 'https://cortefacil.app/api';

console.log(`🔗 API URL: ${API_URL}`);
console.log('');

async function testUserRegistration() {
    console.log('👤 Testando Registro de Cliente');
    console.log('-'.repeat(50));
    
    const clienteData = {
        nome: 'Cliente Teste EasyPanel',
        email: `cliente.easypanel.${Date.now()}@exemplo.com`,
        password: 'senha123',
        telefone: '(11) 99999-9999',
        tipo: 'cliente'
    };
    
    try {
        console.log('📤 Enviando dados do cliente:');
        console.log(JSON.stringify(clienteData, null, 2));
        console.log('');
        
        const response = await axios.post(`${API_URL}/auth/register`, clienteData, {
            headers: {
                'Content-Type': 'application/json',
                'User-Agent': 'CortefacilApp-Test/1.0'
            },
            timeout: 30000
        });
        
        console.log('✅ Registro de cliente bem-sucedido!');
        console.log(`✅ Status: ${response.status}`);
        console.log('✅ Resposta:');
        console.log(JSON.stringify(response.data, null, 2));
        
        return response.data;
        
    } catch (error) {
        console.log('❌ Erro no registro de cliente:');
        
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, JSON.stringify(error.response.data, null, 2));
            
            // Analisar erros específicos
            if (error.response.status === 400) {
                console.log('   💡 Erro 400: Dados inválidos ou email já existe');
            } else if (error.response.status === 500) {
                console.log('   💡 Erro 500: Problema no servidor ou banco de dados');
            } else if (error.response.status === 405) {
                console.log('   💡 Erro 405: Método não permitido - verificar rota');
            }
        } else if (error.request) {
            console.log(`   Erro de rede:`, error.message);
            console.log(`   Code:`, error.code);
        } else {
            console.log(`   Erro:`, error.message);
        }
        
        return null;
    }
}

async function testPartnerRegistration() {
    console.log('\n💼 Testando Registro de Parceiro');
    console.log('-'.repeat(50));
    
    const parceiroData = {
        nome: 'Parceiro Teste EasyPanel',
        email: `parceiro.easypanel.${Date.now()}@exemplo.com`,
        password: 'senha123',
        telefone: '(11) 88888-8888',
        tipo: 'parceiro',
        salao: {
            nome: 'Salão Teste EasyPanel',
            endereco: 'Rua Teste, 123',
            cidade: 'São Paulo',
            estado: 'SP',
            cep: '01234-567',
            telefone: '(11) 77777-7777'
        }
    };
    
    try {
        console.log('📤 Enviando dados do parceiro:');
        console.log(JSON.stringify(parceiroData, null, 2));
        console.log('');
        
        const response = await axios.post(`${API_URL}/auth/register`, parceiroData, {
            headers: {
                'Content-Type': 'application/json',
                'User-Agent': 'CortefacilApp-Test/1.0'
            },
            timeout: 30000
        });
        
        console.log('✅ Registro de parceiro bem-sucedido!');
        console.log(`✅ Status: ${response.status}`);
        console.log('✅ Resposta:');
        console.log(JSON.stringify(response.data, null, 2));
        
        return response.data;
        
    } catch (error) {
        console.log('❌ Erro no registro de parceiro:');
        
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
            console.log(`   Dados:`, JSON.stringify(error.response.data, null, 2));
        } else if (error.request) {
            console.log(`   Erro de rede:`, error.message);
        } else {
            console.log(`   Erro:`, error.message);
        }
        
        return null;
    }
}

async function testHealthCheck() {
    console.log('🏥 Testando Health Check');
    console.log('-'.repeat(50));
    
    try {
        const response = await axios.get(`${API_URL}/health`, {
            timeout: 10000
        });
        
        console.log('✅ Health check OK');
        console.log(`✅ Status: ${response.status}`);
        console.log('✅ Resposta:', JSON.stringify(response.data, null, 2));
        
        return true;
        
    } catch (error) {
        console.log('❌ Health check falhou:');
        if (error.response) {
            console.log(`   Status: ${error.response.status}`);
        } else {
            console.log(`   Erro:`, error.message);
        }
        
        return false;
    }
}

async function runRegistrationTests() {
    console.log('🚀 Iniciando testes de registro...');
    console.log('');
    
    // 1. Testar health check primeiro
    const healthOK = await testHealthCheck();
    
    if (!healthOK) {
        console.log('\n💥 Health check falhou - backend pode não estar funcionando');
        return;
    }
    
    // 2. Testar registro de cliente
    const clienteResult = await testUserRegistration();
    
    // 3. Testar registro de parceiro
    const parceiroResult = await testPartnerRegistration();
    
    // 4. Resumo dos resultados
    console.log('\n' + '='.repeat(60));
    console.log('📊 RESUMO DOS TESTES');
    console.log('='.repeat(60));
    
    console.log(`🏥 Health Check: ${healthOK ? '✅ OK' : '❌ FALHOU'}`);
    console.log(`👤 Registro Cliente: ${clienteResult ? '✅ OK' : '❌ FALHOU'}`);
    console.log(`💼 Registro Parceiro: ${parceiroResult ? '✅ OK' : '❌ FALHOU'}`);
    
    if (healthOK && clienteResult && parceiroResult) {
        console.log('\n🎉 Todos os testes passaram! O backend está funcionando corretamente.');
    } else {
        console.log('\n⚠️  Alguns testes falharam. Verifique os logs acima para detalhes.');
    }
    
    console.log('\n🔧 Informações importantes:');
    console.log(`   API URL: ${API_URL}`);
    console.log(`   Health: ${API_URL}/health`);
    console.log(`   Register: ${API_URL}/auth/register`);
}

// Executar testes
runRegistrationTests().catch(error => {
    console.error('💥 Erro fatal nos testes:', error.message);
    process.exit(1);
});