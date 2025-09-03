const Database = require('./backend/server/config/database');
const dotenv = require('dotenv');

// Carregar variáveis de ambiente
dotenv.config();

async function testDatabaseConnection() {
    console.log('🔍 Testando conexão com banco MySQL do EasyPanel...');
    console.log('📋 Configurações:');
    console.log(`   Host: ${process.env.DB_HOST}`);
    console.log(`   User: ${process.env.DB_USER}`);
    console.log(`   Database: ${process.env.DB_NAME}`);
    console.log(`   Password: ${process.env.DB_PASSWORD ? '[DEFINIDA]' : '[VAZIA]'}`);
    console.log('');

    try {
        const db = Database.getInstance();
        
        // Forçar configuração online se necessário
        if (process.env.DB_HOST_ONLINE) {
            console.log('🌐 Aplicando configuração online...');
            db.forceOnlineConfig();
        }
        
        // Testar conexão
        const isConnected = await db.testConnection();
        
        if (isConnected) {
            console.log('✅ SUCESSO: Conexão com banco estabelecida!');
            
            // Testar uma query simples
            console.log('🔍 Testando query simples...');
            const result = await db.query('SELECT 1 as test');
            console.log('✅ Query executada com sucesso:', result);
            
        } else {
            console.log('❌ ERRO: Falha na conexão com banco');
        }
        
        // Fechar conexão
        await db.closePool();
        
    } catch (error) {
        console.error('❌ ERRO CRÍTICO:', error.message);
        console.error('📋 Detalhes do erro:', error);
    }
}

// Executar teste
testDatabaseConnection().then(() => {
    console.log('\n🏁 Teste finalizado');
    process.exit(0);
}).catch((error) => {
    console.error('❌ Erro fatal:', error);
    process.exit(1);
});