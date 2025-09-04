const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

// Carregar variáveis de ambiente
dotenv.config({ path: './backend/server/.env' });

async function testDatabaseConnection() {
    console.log('🔍 Testando conexão com o banco de dados...');
    console.log('📋 Configurações:');
    console.log(`   Host: ${process.env.DB_HOST}`);
    console.log(`   Port: ${process.env.DB_PORT || 3306}`);
    console.log(`   User: ${process.env.DB_USER}`);
    console.log(`   Database: ${process.env.DB_NAME}`);
    console.log(`   Password: ${process.env.DB_PASSWORD ? '[DEFINIDA]' : '[NÃO DEFINIDA]'}`);
    
    const config = {
        host: process.env.DB_HOST,
        port: process.env.DB_PORT || 3306,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME,
        connectTimeout: 10000,
        acquireTimeout: 10000
    };
    
    try {
        console.log('\n🔌 Tentando conectar...');
        const connection = await mysql.createConnection(config);
        
        console.log('✅ Conexão estabelecida com sucesso!');
        
        // Testar uma query simples
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log('✅ Query de teste executada:', rows);
        
        // Verificar tabelas existentes
        const [tables] = await connection.execute('SHOW TABLES');
        console.log('📋 Tabelas encontradas:', tables.length);
        tables.forEach(table => {
            console.log(`   - ${Object.values(table)[0]}`);
        });
        
        await connection.end();
        console.log('\n🎉 Teste de conexão concluído com sucesso!');
        
    } catch (error) {
        console.error('❌ Erro na conexão:');
        console.error(`   Código: ${error.code}`);
        console.error(`   Mensagem: ${error.message}`);
        console.error(`   SQL State: ${error.sqlState}`);
        
        if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.log('\n💡 Sugestões para resolver o erro de acesso:');
            console.log('   1. Verifique se o usuário e senha estão corretos');
            console.log('   2. Verifique se o usuário tem permissões para acessar o banco');
            console.log('   3. Verifique se o IP está liberado no firewall do servidor');
            console.log('   4. Tente acessar via phpMyAdmin para confirmar as credenciais');
        }
        
        process.exit(1);
    }
}

// Executar teste
testDatabaseConnection();