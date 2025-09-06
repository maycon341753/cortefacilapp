#!/usr/bin/env node
/**
 * Script de Teste de Conexão MySQL
 * Testa a conexão com o banco de dados MySQL do Hostinger
 */

const mysql = require('mysql2/promise');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, 'backend/server/.env.production') });

async function testConnection() {
    console.log('🔍 Testando conexão MySQL...');
    console.log('📋 Configurações:');
    console.log(`   Host: ${process.env.DB_HOST}`);
    console.log(`   Port: ${process.env.DB_PORT}`);
    console.log(`   User: ${process.env.DB_USER}`);
    console.log(`   Database: ${process.env.DB_NAME}`);
    console.log('');

    try {
        // Teste de conexão
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
            port: parseInt(process.env.DB_PORT)
        });

        console.log('✅ Conexão estabelecida com sucesso!');

        // Teste de consulta
        const [rows] = await connection.execute('SELECT 1 as test');
        console.log('✅ Consulta de teste executada com sucesso!');
        console.log('📊 Resultado:', rows);

        // Verificar tabelas existentes
        const [tables] = await connection.execute('SHOW TABLES');
        console.log('📋 Tabelas no banco de dados:');
        if (tables.length === 0) {
            console.log('   ⚠️  Nenhuma tabela encontrada (banco vazio)');
        } else {
            tables.forEach(table => {
                console.log(`   - ${Object.values(table)[0]}`);
            });
        }

        await connection.end();
        console.log('\n🎉 Teste concluído com sucesso!');
        console.log('\n📝 Próximos passos:');
        console.log('   1. Execute as migrações do banco de dados');
        console.log('   2. Configure o NODE_ENV=production no servidor');
        console.log('   3. Inicie a aplicação com as novas credenciais');
        
    } catch (error) {
        console.error('❌ Erro na conexão:', error.message);
        console.error('\n🔧 Possíveis soluções:');
        console.error('   1. Verificar se o servidor MySQL está rodando');
        console.error('   2. Confirmar as credenciais no arquivo .env.production');
        console.error('   3. Verificar se o firewall permite conexões na porta 3306');
        console.error('   4. Confirmar se o usuário tem permissões adequadas');
        process.exit(1);
    }
}

// Executar teste
testConnection();