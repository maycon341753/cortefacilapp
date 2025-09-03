const mysql = require('mysql2/promise');
require('dotenv').config({ path: './backend/server/.env' });

async function testConnection() {
    console.log('🔍 Testando conexão com MySQL...');
    console.log('📋 Configurações:');
    console.log(`   Host: ${process.env.DB_HOST}`);
    console.log(`   Port: ${process.env.DB_PORT}`);
    console.log(`   User: ${process.env.DB_USER}`);
    console.log(`   Database: ${process.env.DB_NAME}`);
    console.log(`   Password: ${process.env.DB_PASSWORD ? '[DEFINIDA]' : '[NÃO DEFINIDA]'}`);
    console.log('');

    const config = {
        host: process.env.DB_HOST,
        port: parseInt(process.env.DB_PORT) || 3306,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME,
        connectTimeout: 10000,
        acquireTimeout: 10000,
        timeout: 10000
    };

    try {
        console.log('⏳ Tentando conectar...');
        const connection = await mysql.createConnection(config);
        
        console.log('✅ Conexão estabelecida com sucesso!');
        
        // Teste básico de query
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log('✅ Query de teste executada:', rows);
        
        // Verificar tabelas existentes
        const [tables] = await connection.execute('SHOW TABLES');
        console.log('📊 Tabelas no banco:', tables.length > 0 ? tables : 'Nenhuma tabela encontrada');
        
        await connection.end();
        console.log('✅ Conexão fechada com sucesso!');
        
    } catch (error) {
        console.error('❌ Erro na conexão:', error.message);
        console.error('📋 Detalhes do erro:');
        console.error(`   Código: ${error.code}`);
        console.error(`   Errno: ${error.errno}`);
        console.error(`   Syscall: ${error.syscall}`);
        console.error(`   Address: ${error.address}`);
        console.error(`   Port: ${error.port}`);
        
        if (error.code === 'ECONNREFUSED') {
            console.log('');
            console.log('🔧 Possíveis soluções para ECONNREFUSED:');
            console.log('   1. Verificar se o servidor MySQL está rodando no Hostinger');
            console.log('   2. Verificar se as conexões remotas estão habilitadas');
            console.log('   3. Verificar se o IP está na whitelist do MySQL');
            console.log('   4. Verificar configurações de firewall');
            console.log('   5. Confirmar se as credenciais estão corretas');
        }
        
        process.exit(1);
    }
}

testConnection();