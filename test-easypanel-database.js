const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

// Carregar configurações do EasyPanel
dotenv.config({ path: './backend/server/.env.easypanel' });

console.log('🔍 Testando Conexão com Banco de Dados EasyPanel');
console.log('=' .repeat(60));

// Configurações do banco
const dbConfig = {
    host: process.env.DB_HOST,
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    connectTimeout: 30000,
    acquireTimeout: 30000,
    timeout: 30000
};

console.log('📋 Configurações do Banco:');
console.log(`   Host: ${dbConfig.host}`);
console.log(`   Port: ${dbConfig.port}`);
console.log(`   User: ${dbConfig.user}`);
console.log(`   Database: ${dbConfig.database}`);
console.log(`   Password: ${dbConfig.password ? '***' : 'VAZIO'}`);
console.log('');

async function testDatabaseConnection() {
    let connection = null;
    
    try {
        console.log('🔄 Tentando conectar ao banco de dados...');
        
        // Teste 1: Conexão básica
        connection = await mysql.createConnection(dbConfig);
        console.log('✅ Conexão estabelecida com sucesso!');
        
        // Teste 2: Ping no servidor
        console.log('🏓 Testando ping...');
        await connection.ping();
        console.log('✅ Ping bem-sucedido!');
        
        // Teste 3: Verificar versão do MySQL
        console.log('📊 Verificando versão do MySQL...');
        const [versionRows] = await connection.execute('SELECT VERSION() as version');
        console.log(`✅ Versão MySQL: ${versionRows[0].version}`);
        
        // Teste 4: Listar tabelas
        console.log('📋 Listando tabelas do banco...');
        const [tables] = await connection.execute('SHOW TABLES');
        console.log(`✅ Encontradas ${tables.length} tabelas:`);
        tables.forEach((table, index) => {
            const tableName = Object.values(table)[0];
            console.log(`   ${index + 1}. ${tableName}`);
        });
        
        // Teste 5: Verificar tabela usuarios
        console.log('👥 Verificando tabela usuarios...');
        try {
            const [userCount] = await connection.execute('SELECT COUNT(*) as count FROM usuarios');
            console.log(`✅ Tabela usuarios existe com ${userCount[0].count} registros`);
        } catch (error) {
            console.log('⚠️  Tabela usuarios não encontrada ou erro:', error.message);
        }
        
        // Teste 6: Verificar outras tabelas importantes
        const importantTables = ['saloes', 'servicos', 'agendamentos', 'profissionais'];
        for (const tableName of importantTables) {
            try {
                const [count] = await connection.execute(`SELECT COUNT(*) as count FROM ${tableName}`);
                console.log(`✅ Tabela ${tableName}: ${count[0].count} registros`);
            } catch (error) {
                console.log(`⚠️  Tabela ${tableName}: ${error.message}`);
            }
        }
        
        console.log('');
        console.log('🎉 Todos os testes de conexão foram bem-sucedidos!');
        console.log('✅ O banco de dados está funcionando corretamente.');
        
    } catch (error) {
        console.log('');
        console.log('❌ ERRO na conexão com o banco de dados:');
        console.log('   Tipo:', error.code || 'UNKNOWN');
        console.log('   Mensagem:', error.message);
        console.log('   Stack:', error.stack);
        
        // Diagnósticos específicos
        if (error.code === 'ENOTFOUND') {
            console.log('');
            console.log('🔍 DIAGNÓSTICO:');
            console.log('   - O host não foi encontrado');
            console.log('   - Verifique se o IP/domínio está correto');
            console.log('   - Verifique sua conexão com a internet');
        } else if (error.code === 'ECONNREFUSED') {
            console.log('');
            console.log('🔍 DIAGNÓSTICO:');
            console.log('   - Conexão recusada pelo servidor');
            console.log('   - Verifique se o MySQL está rodando no EasyPanel');
            console.log('   - Verifique se a porta 3306 está aberta');
        } else if (error.code === 'ER_ACCESS_DENIED_ERROR') {
            console.log('');
            console.log('🔍 DIAGNÓSTICO:');
            console.log('   - Credenciais incorretas');
            console.log('   - Verifique usuário e senha no EasyPanel');
        } else if (error.code === 'ETIMEDOUT') {
            console.log('');
            console.log('🔍 DIAGNÓSTICO:');
            console.log('   - Timeout na conexão');
            console.log('   - Servidor pode estar sobrecarregado');
            console.log('   - Firewall pode estar bloqueando');
        }
        
    } finally {
        if (connection) {
            await connection.end();
            console.log('🔌 Conexão fechada.');
        }
    }
}

// Teste adicional: Verificar se as variáveis de ambiente estão carregadas
console.log('🔧 Verificando variáveis de ambiente...');
const requiredVars = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME'];
let missingVars = [];

requiredVars.forEach(varName => {
    if (!process.env[varName]) {
        missingVars.push(varName);
    }
});

if (missingVars.length > 0) {
    console.log('❌ Variáveis de ambiente faltando:', missingVars.join(', '));
    console.log('   Verifique o arquivo .env.easypanel');
    process.exit(1);
} else {
    console.log('✅ Todas as variáveis de ambiente estão definidas');
}

console.log('');

// Executar teste
testDatabaseConnection().catch(error => {
    console.error('💥 Erro fatal:', error);
    process.exit(1);
});